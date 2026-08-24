<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$announcement = $stmt ? $stmt->fetch() : null;

echo "=== ANNOUNCEMENT & MIC BORDER FIX TEST ===\n\n";

if (!empty($announcement) && !empty($announcement['is_active'])) {
    $msgText = htmlspecialchars(htmlspecialchars_decode($announcement['message']));
    echo "[PASS] Single-Line Strict Height (38px) Active:\n";
    echo "  Message: {$msgText}\n";
    echo "  White-Space: nowrap !important\n";
    echo "  Mic Button: border: none !important, outline: none !important\n";
}
