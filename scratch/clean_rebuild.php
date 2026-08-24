<?php
define('ROOT_PATH', dirname(__DIR__));

$config = require ROOT_PATH . '/config/database.php';
$connConfig = $config['connections']['mysql'];
$host = $connConfig['write']['host'][0] ?? '127.0.0.1';
$port = $connConfig['port'] ?? 3306;
$user = $connConfig['username'] ?? 'root';
$pass = $connConfig['password'] ?? '';
$dbname = $connConfig['dbname'] ?? 'ecommerce';

echo "Step 1: Connecting to MySQL server...\n";
$pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "Step 2: Dropping database '{$dbname}' if exists...\n";
try {
    $pdo->exec("DROP DATABASE IF EXISTS `{$dbname}`");
} catch (Exception $e) {
    echo "Warning dropping DB: " . $e->getMessage() . "\n";
}

echo "Step 3: Cleaning orphaned files in XAMPP mysql data directory for '{$dbname}'...\n";
$dataDir = 'C:\xampp\mysql\data\\' . $dbname;
if (is_dir($dataDir)) {
    $files = glob($dataDir . '\*');
    foreach ($files as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
    @rmdir($dataDir);
}

echo "Step 4: Re-creating database '{$dbname}'...\n";
$pdo->exec("CREATE DATABASE `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$dbname}`");

function runSqlStatements(PDO $pdo, string $filePath): void {
    if (!file_exists($filePath)) {
        echo "File not found: {$filePath}\n";
        return;
    }
    echo "Processing SQL file: " . basename($filePath) . "...\n";
    $sql = file_get_contents($filePath);
    
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ($statements as $stmt) {
        if (empty($stmt) || strspn($stmt, "-/\n\r\t ") === strlen($stmt)) {
            continue;
        }
        try {
            $pdo->exec($stmt);
        } catch (Exception $e) {
            echo "SQL Error in " . basename($filePath) . ": " . $e->getMessage() . "\n";
        }
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Finished " . basename($filePath) . "\n";
}

// Execute Schemas and Seeders
runSqlStatements($pdo, ROOT_PATH . '/database/schema.sql');
runSqlStatements($pdo, ROOT_PATH . '/database/seeders.sql');
runSqlStatements($pdo, ROOT_PATH . '/database/coupons_schema.sql');
runSqlStatements($pdo, ROOT_PATH . '/database/db_hardening.sql');
runSqlStatements($pdo, ROOT_PATH . '/database/homepage_sections_schema.sql');
runSqlStatements($pdo, ROOT_PATH . '/database/otp_schema.sql');
runSqlStatements($pdo, ROOT_PATH . '/database/razorpay_shiprocket_schema.sql');

// Execute PHP migrations & seeders in correct order
$phpScripts = [
    'migrate_enterprise_schema.php',
    'migrate_blogs.php',
    'fix_blog_columns.php',
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
        echo "Executing PHP script: {$script}...\n";
        $out = [];
        exec("php " . escapeshellarg($scriptPath), $out);
        echo implode("\n", $out) . "\n";
    }
}

echo "Database successfully cleaned and rebuilt!\n";
