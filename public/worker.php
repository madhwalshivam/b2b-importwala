<?php
/**
 * Image Mirror Web Worker — web-accessible endpoint.
 * Called via HTTP to run the mirror queue on shared hosting
 * where exec() / shell_exec() are disabled.
 *
 * Protected by WORKER_SECRET token in .env.
 * Usage: GET /public/worker.php?token=<WORKER_SECRET>
 *        or via HTTP trigger from triggerWorker()
 */

// ----- Bootstrap -----
$root = dirname(__DIR__);
require_once $root . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load($root);

if (file_exists($root . '/vendor/autoload.php')) {
    require_once $root . '/vendor/autoload.php';
}
require_once $root . '/app/Helpers/Functions.php';

spl_autoload_register(function ($class) use ($root) {
    $prefix   = 'App\\';
    $base_dir = $root . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative = str_replace('\\', '/', substr($class, $len));
    $file = $base_dir . $relative . '.php';
    if (file_exists($file)) require $file;
});

use App\Services\ImageMirrorWorker;
use App\Core\Database;

// ----- Token auth -----
$secret = env('WORKER_SECRET', '');
$provided = $_GET['token'] ?? ($_SERVER['HTTP_X_WORKER_TOKEN'] ?? '');

if (empty($secret) || !hash_equals($secret, $provided)) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// ----- Lock: prevent concurrent runs -----
$lockFile = $root . '/storage/mirror.lock';
$lockDir  = dirname($lockFile);
if (!is_dir($lockDir)) @mkdir($lockDir, 0777, true);

$fp = @fopen($lockFile, 'w+');
if (!$fp || !flock($fp, LOCK_EX | LOCK_NB)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'already_running']);
    exit;
}

// ----- Increase time limit for web workers -----
@set_time_limit(90);
@ignore_user_abort(true);

// Reset any stuck items first
$db = Database::getInstance();
$db->query("UPDATE image_mirror_queue SET status = 'pending', updated_at = NOW() WHERE status = 'processing' AND updated_at < NOW() - INTERVAL 10 MINUTE");

// ----- Run worker -----
$worker    = new ImageMirrorWorker();
$startTime = time();
$maxTime   = 55; // stay under 60s PHP limit
$total     = ['processed' => 0, 'done' => 0, 'failed' => 0];

while (time() - $startTime < $maxTime) {
    $result = $worker->run(20, min(30, $maxTime - (time() - $startTime)));
    $total['processed'] += $result['processed'];
    $total['done']      += $result['done'];
    $total['failed']    += $result['failed'];

    if ($result['pending'] == 0) break;
    if ($result['processed'] == 0) break;
    usleep(300000); // 0.3s pause
}

flock($fp, LOCK_UN);
fclose($fp);

header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'result' => $total, 'elapsed' => time() - $startTime]);
exit;
