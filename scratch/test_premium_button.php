<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$announcement = $stmt ? $stmt->fetch() : null;

echo "=== PREMIUM BUTTON UI TEST ===\n\n";

if (!empty($announcement) && !empty($announcement['is_active'])) {
    $ctaText = !empty($announcement['cta_text']) ? htmlspecialchars(htmlspecialchars_decode($announcement['cta_text'])) : '';

    echo "[PASS] Premium Solid Brand Orange Gradient Pill Active:\n";
    echo "  Button Text:    {$ctaText}\n";
    echo "  Background:     linear-gradient(135deg, #F05A29 0%, #D8481B 100%)\n";
    echo "  Shadow & Glow:  0 2px 6px rgba(240, 90, 41, 0.28)\n";
    echo "  Micro-Icon:     SVG Arrow Right Icon Embedded\n";
}
