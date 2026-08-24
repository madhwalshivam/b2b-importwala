<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$announcement = $stmt ? $stmt->fetch() : null;

echo "=== STOREFRONT ANNOUNCEMENT BAR TEST ===\n\n";

if (!empty($announcement) && !empty($announcement['is_active'])) {
    echo "[ACTIVE] Announcement Status: ENABLED\n";
    echo "Message:  " . htmlspecialchars_decode($announcement['message']) . "\n";
    echo "CTA Text: " . htmlspecialchars_decode($announcement['cta_text']) . "\n";
    echo "CTA Link: " . url(ltrim($announcement['cta_link'], '/')) . "\n";
} else {
    echo "[DISABLED] Top Announcement Bar is hidden.\n";
}
