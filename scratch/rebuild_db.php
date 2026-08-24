<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';

$config = require ROOT_PATH . '/config/database.php';
$connConfig = $config['connections']['mysql'];
$host = $connConfig['write']['host'][0] ?? '127.0.0.1';
$port = $connConfig['port'] ?? 3306;
$user = $connConfig['username'] ?? 'root';
$pass = $connConfig['password'] ?? '';
$dbname = $connConfig['dbname'] ?? 'ecommerce';

echo "Connecting to MySQL database '{$dbname}' at {$host}:{$port}...\n";
try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

function executeSqlFile(PDO $pdo, string $filePath): void {
    if (!file_exists($filePath)) {
        echo "File not found: {$filePath}\n";
        return;
    }
    echo "Executing SQL file: " . basename($filePath) . "...\n";
    $sql = file_get_contents($filePath);
    try {
        $pdo->exec($sql);
        // Flush multi statement results
        while ($pdo->nextRowset()) {}
        echo "Successfully executed " . basename($filePath) . "\n";
    } catch (Exception $e) {
        echo "Error executing " . basename($filePath) . ": " . $e->getMessage() . "\n";
    }
}

// 1. Core Schema and Seeders
executeSqlFile($pdo, ROOT_PATH . '/database/schema.sql');
executeSqlFile($pdo, ROOT_PATH . '/database/seeders.sql');

// 2. Extra SQL Schemas
executeSqlFile($pdo, ROOT_PATH . '/database/coupons_schema.sql');
executeSqlFile($pdo, ROOT_PATH . '/database/db_hardening.sql');
executeSqlFile($pdo, ROOT_PATH . '/database/homepage_sections_schema.sql');
executeSqlFile($pdo, ROOT_PATH . '/database/otp_schema.sql');
executeSqlFile($pdo, ROOT_PATH . '/database/razorpay_shiprocket_schema.sql');

// 3. Run PHP migration & seed scripts
$phpScripts = [
    'migrate_blogs.php',
    'migrate_categories_v2.php',
    'migrate_gallery_variations.php',
    'migrate_merge_redirects.php',
    'migrate_multi_pivot.php',
    'migrate_new_requirements.php',
    'migrate_password_reset.php',
    'migrate_razorpay_shiprocket.php',
    'migrate_spareparts_comparison.php',
    'migrate_video_product_url.php',
    'migrate_video_thumbnails.php',
    'seed_sample_blogs.php',
    'seed_more_blogs.php',
    'seed_enterprise_data.php'
];

foreach ($phpScripts as $script) {
    $scriptPath = ROOT_PATH . '/database/' . $script;
    if (file_exists($scriptPath)) {
        echo "Running PHP script: {$script}...\n";
        try {
            $output = [];
            exec("php " . escapeshellarg($scriptPath), $output, $returnCode);
            echo implode("\n", $output) . "\n";
        } catch (Exception $e) {
            echo "Error running {$script}: " . $e->getMessage() . "\n";
        }
    }
}

echo "Database rebuild complete!\n";
