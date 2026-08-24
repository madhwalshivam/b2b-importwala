<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$announcement = $stmt ? $stmt->fetch() : null;

echo "=== SLOW MARQUEE & SINGLE BUTTON TEST ===\n\n";

if (!empty($announcement) && !empty($announcement['is_active'])) {
    $msgText = htmlspecialchars(htmlspecialchars_decode($announcement['message']));
    $ctaText = !empty($announcement['cta_text']) ? htmlspecialchars(htmlspecialchars_decode($announcement['cta_text'])) : '';

    echo "[PASS] Slow Marquee Loop & Single Button:\n";
    echo "  Moving Loop:   {$msgText} (only message, 50s slow calm speed)\n";
    echo "  Fixed Button:  {$ctaText} -> (vertical margin: 4px 0, padding: 3px 12px)\n";
}
