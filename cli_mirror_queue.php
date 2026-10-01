<?php
/**
 * Image Mirror Queue Worker (CLI)
 * Runs in the background to process image_mirror_queue.
 */
// Ensure we use __DIR__ to not depend on working directory.
require_once __DIR__ . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(__DIR__);

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

use App\Services\ImageMirrorWorker;

$options = getopt("", ["selftest", "status", "reset-stuck"]);

if (isset($options['selftest'])) {
    // 1. Print which R2 variables are present (names only, never values)
    echo "R2 Variable Check:\n";
    $vars = ['R2_ACCOUNT_ID', 'R2_ACCESS_KEY_ID', 'R2_SECRET_ACCESS_KEY', 'R2_BUCKET', 'R2_PUBLIC_URL', 'R2_ENDPOINT'];
    foreach ($vars as $var) {
        $val = env($var);
        echo "- {$var}: " . (!empty($val) ? "PRESENT" : "MISSING") . "\n";
    }

    echo "\nStarting Self-Test Upload...\n";
    $r2 = new \App\Services\CloudflareR2();
    
    // Upload a 1x1 PNG to key _selftest/test.png
    $pngBase64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==";
    $tmpFile = tempnam(sys_get_temp_dir(), 'test_png');
    file_put_contents($tmpFile, base64_decode($pngBase64));
    
    $testKey = "_selftest/test.png";
    $uploaded = $r2->uploadFile($tmpFile, $testKey, 'image/png');
    @unlink($tmpFile);

    if (!$uploaded) {
        echo "FAIL: R2 Upload returned false. Check logs or verify credentials.\n";
        exit(1);
    }

    echo "Upload: PASS\n";
    
    // HEAD/GET it through R2_PUBLIC_URL
    $publicUrl = rtrim(env('R2_PUBLIC_URL'), '/') . '/' . ltrim($testKey, '/');
    $ch = curl_init($publicUrl);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errorMsg = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "Fetch via Public URL: PASS ($httpCode)\n";
    } else {
        echo "FAIL: Could not fetch from $publicUrl. HTTP Code: $httpCode. Error: $errorMsg\n";
    }
    
    // Delete it
    $deleted = $r2->deleteObject($testKey);
    if ($deleted) {
        echo "Delete: PASS\n";
    } else {
        echo "FAIL: Could not delete $testKey from bucket.\n";
    }

    if ($uploaded && $httpCode === 200 && $deleted) {
        echo "\nOVERALL SELF-TEST: PASS\n";
        exit(0);
    } else {
        echo "\nOVERALL SELF-TEST: FAIL\n";
        exit(1);
    }
}

if (isset($options['status'])) {
    $db = \App\Core\Database::getInstance();
    $worker = new ImageMirrorWorker();
    
    $stats = [
        'pending' => $db->query("SELECT COUNT(*) FROM image_mirror_queue WHERE status = 'pending' AND attempts < 3")->fetchColumn(),
        'processing' => $db->query("SELECT COUNT(*) FROM image_mirror_queue WHERE status = 'processing'")->fetchColumn(),
        'done' => $db->query("SELECT COUNT(*) FROM image_mirror_queue WHERE status = 'done'")->fetchColumn(),
        'failed' => $db->query("SELECT COUNT(*) FROM image_mirror_queue WHERE status = 'failed' OR attempts >= 3")->fetchColumn(),
        'last_error' => $db->query("SELECT last_error FROM image_mirror_queue WHERE status = 'failed' ORDER BY updated_at DESC LIMIT 1")->fetchColumn()
    ];
    
    echo json_encode($stats, JSON_PRETTY_PRINT);
    exit;
}

if (isset($options['reset-stuck'])) {
    $db = \App\Core\Database::getInstance();
    $stmt = $db->query("UPDATE image_mirror_queue SET status = 'pending', updated_at = NOW() WHERE status = 'processing' AND updated_at < NOW() - INTERVAL 10 MINUTE");
    echo "Reset {$stmt->rowCount()} stuck items to pending.\n";
    exit;
}

// Bounded Run (max 50 seconds, flock)
$lockFile = __DIR__ . '/storage/mirror.lock';
$lockDir = dirname($lockFile);
if (!is_dir($lockDir)) @mkdir($lockDir, 0777, true);

$fp = fopen($lockFile, 'w+');
if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) {
    echo "Worker is already running (locked).\n";
    exit;
}

echo "Starting Bounded Image Mirror Worker...\n";
$worker = new ImageMirrorWorker();

// Always reset stuck items at start
$db = \App\Core\Database::getInstance();
$db->query("UPDATE image_mirror_queue SET status = 'pending', updated_at = NOW() WHERE status = 'processing' AND updated_at < NOW() - INTERVAL 10 MINUTE");

$startTime = time();
$maxExecution = 50;
$totalProcessed = 0;

while (time() - $startTime < $maxExecution) {
    // We pass limits so it doesn't run too long per loop
    $timeLeft = $maxExecution - (time() - $startTime);
    if ($timeLeft <= 0) break;
    
    $result = $worker->run(20, min(40, $timeLeft));
    
    if ($result['processed'] > 0) {
        $totalProcessed += $result['processed'];
        echo date('Y-m-d H:i:s') . " - Processed: {$result['processed']}, Done: {$result['done']}, Failed: {$result['failed']}, Pending: {$result['pending']}\n";
    }

    if ($result['pending'] == 0) {
        break; // Queue is empty, exit gracefully
    } else {
        usleep(500000); // 0.5s pause
    }
}

echo "Worker finished gracefully. Total processed: $totalProcessed.\n";
flock($fp, LOCK_UN);
fclose($fp);
