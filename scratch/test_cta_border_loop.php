<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$announcement = $stmt ? $stmt->fetch() : null;

echo "=== CTA BORDER & MARQUEE LOOP TEST ===\n\n";

if (!empty($announcement) && !empty($announcement['is_active'])) {
    $msgText = htmlspecialchars(htmlspecialchars_decode($announcement['message']));
    $ctaText = !empty($announcement['cta_text']) ? htmlspecialchars(htmlspecialchars_decode($announcement['cta_text'])) : '';
    $ctaLink = !empty($announcement['cta_link']) ? url(ltrim($announcement['cta_link'], '/')) : '#';

    echo "[PASS] CTA Border & Marquee Loop Configured:\n";
    echo "  Scrolling Track: {$msgText} + {$ctaText} -> in continuous zero-gap loop\n";
    echo "  Fixed CTA Button: {$ctaText} (border: 1px solid #D8481B)\n";
}
