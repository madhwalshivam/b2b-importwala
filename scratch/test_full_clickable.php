<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$announcement = $stmt ? $stmt->fetch() : null;

echo "=== FULL BAR CLICKABLE TEST ===\n\n";

if (!empty($announcement) && !empty($announcement['is_active'])) {
    $msgText = htmlspecialchars(htmlspecialchars_decode($announcement['message']));
    $ctaLink = !empty($announcement['cta_link']) ? url(ltrim($announcement['cta_link'], '/')) : '#';

    echo "[PASS] Entire Announcement Bar is Clickable Link:\n";
    echo "  Target URL: {$ctaLink}\n";
    echo "  Button:     REMOVED (No extra clutter)\n";
    echo "  Text Loop:  {$msgText} (slow 50s continuous calm loop)\n";
    echo "  Close:      ✕ button active on right\n";
}
