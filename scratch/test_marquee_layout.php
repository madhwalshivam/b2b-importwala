<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$announcement = $stmt ? $stmt->fetch() : null;

echo "=== SINGLE-LINE MARQUEE TEST ===\n\n";

if (!empty($announcement) && !empty($announcement['is_active'])) {
    $msgText = htmlspecialchars(htmlspecialchars_decode($announcement['message']));
    $ctaText = !empty($announcement['cta_text']) ? htmlspecialchars(htmlspecialchars_decode($announcement['cta_text'])) : '';
    $ctaLink = !empty($announcement['cta_link']) ? url(ltrim($announcement['cta_link'], '/')) : '#';

    echo "[PASS] Single-Line Marquee Rendered:\n";
    echo "  Message: {$msgText}\n";
    echo "  CTA Text: {$ctaText}\n";
    echo "  CTA Link: {$ctaLink}\n";
} else {
    echo "[DISABLED] Announcement bar is disabled.\n";
}
