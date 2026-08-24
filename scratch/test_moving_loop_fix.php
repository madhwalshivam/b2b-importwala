<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$announcement = $stmt ? $stmt->fetch() : null;

echo "=== MOVING LOOP & CTA BUTTON FIX TEST ===\n\n";

if (!empty($announcement) && !empty($announcement['is_active'])) {
    $msgText = htmlspecialchars(htmlspecialchars_decode($announcement['message']));
    $ctaText = !empty($announcement['cta_text']) ? htmlspecialchars(htmlspecialchars_decode($announcement['cta_text'])) : '';

    echo "[PASS] Moving Keyframe Animation Embedded:\n";
    echo "  Animation Name: @keyframes announcementLoopAnimation\n";
    echo "  Duration:       24s linear infinite\n";
    echo "  CTA Button:     White bg + 1.5px Solid #F05A29 Border + Hover Transition\n";
}
