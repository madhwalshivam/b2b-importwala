<?php

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Forbidden. Run from CLI or delete this file.");
}

echo "=== System Health Check ===\n\n";

$pass = 0;
$fail = 0;

function check($test, $result, $details = '') {
    global $pass, $fail;
    if ($result) {
        $pass++;
        echo "[PASS] $test\n";
    } else {
        $fail++;
        echo "[FAIL] $test" . ($details ? " - $details" : "") . "\n";
    }
}

// 1. Env Loader & .env
$envPath = __DIR__ . '/../.env';
$envLoaded = file_exists($envPath);
check('.env file exists', $envLoaded);

if ($envLoaded) {
    require_once __DIR__ . '/../app/Core/EnvLoader.php';
    \App\Core\EnvLoader::load(__DIR__ . '/..');
    check('Env variables loaded (APP_ENV is set)', getenv('APP_ENV') !== false);
} else {
    check('Env variables loaded', false, 'Missing .env');
}

// 2. PHP Extensions
$extensions = ['mysqli', 'pdo_mysql', 'curl', 'gd', 'mbstring', 'zip', 'fileinfo'];
foreach ($extensions as $ext) {
    check("PHP Extension: $ext", extension_loaded($ext));
}

// 3. Database Connection
try {
    $dbConfig = require __DIR__ . '/../config/database.php';
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $dbname = getenv('DB_DATABASE');
    $user = getenv('DB_USERNAME');
    $pass_str = getenv('DB_PASSWORD');
    
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new \PDO($dsn, $user, $pass_str, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    check('Database Connection', true);
} catch (\Throwable $e) {
    check('Database Connection', false, $e->getMessage());
}

// 4. Folder permissions
$storagePath = __DIR__ . '/../storage';
$uploadsPath = __DIR__ . '/../public/uploads';

check("Storage folder is writable ($storagePath)", is_dir($storagePath) && is_writable($storagePath));
if (!is_dir($storagePath . '/logs')) @mkdir($storagePath . '/logs', 0777, true);
check("Logs folder is writable", is_dir($storagePath . '/logs') && is_writable($storagePath . '/logs'));

check("Uploads folder is writable ($uploadsPath)", is_dir($uploadsPath) && is_writable($uploadsPath));

// 5. R2 Credentials
$r2_account = getenv('R2_ACCOUNT_ID');
$r2_key = getenv('R2_ACCESS_KEY_ID');
$r2_secret = getenv('R2_SECRET_ACCESS_KEY');
$r2_bucket = getenv('R2_BUCKET');

check("R2 Credentials Present", !empty($r2_account) && !empty($r2_key) && !empty($r2_secret) && !empty($r2_bucket));

echo "\n--- Summary ---\n";
echo "Total Passed: $pass\n";
echo "Total Failed: $fail\n";

if ($fail > 0) {
    echo "\nWARNING: The system has failing checks. Please review and fix before going live.\n";
} else {
    echo "\nSUCCESS: All checks passed. The system is ready for production.\n";
}
echo "NOTE: Delete this file (scripts/health_check.php) after use.\n";
