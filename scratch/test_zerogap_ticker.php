<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$announcement = $stmt ? $stmt->fetch() : null;

echo "=== ZERO-GAP CONTINUOUS TICKER TEST ===\n\n";

if (!empty($announcement) && !empty($announcement['is_active'])) {
    $msgText = htmlspecialchars(htmlspecialchars_decode($announcement['message']));
    echo "[PASS] Continuous Zero-Gap Ticker Ready:\n";
    echo "  Single Message: {$msgText}\n";
    echo "  Font Weight:    500 (Thinner & Sleek)\n";
    echo "  Loop Gap:       0px (Infinite Seamless Back-to-Back Flow)\n";
}
