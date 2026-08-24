<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();

try {
    $stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $announcement = $stmt->fetch();
    echo "Active Announcement:\n";
    print_r($announcement);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
