<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

require_once ROOT_PATH . '/app/Helpers/Functions.php';
require_once ROOT_PATH . '/config/app.php';

$productId = 5;

$cleanDescription = <<<TEXT
Engineered for ultimate impact protection and sleek everyday ergonomics, the Mudsor Ultra-Slim Protective Case delivers military-grade defense without adding bulky weight to your phone.

• 10Ft Military Drop Protection: Reinforced dual-layer TPU shock-absorbing corner bumpers mitigate drops up to 10 feet.
• MagSafe & Wireless Compatible: Integrated 36 N52 magnet array ensures effortless alignment with wireless chargers & wallets.
• Anti-Scratch Oleophobic Finish: Nano-matte protective coating repels fingerprints, oils, and daily scratches.
• Raised Lens & Screen Ring: 1.5mm raised display lips & 2.0mm camera bezel prevent direct flat surface contact.

Each case is precision-molded to provide crisp tactile button action and exact port cutouts for high-speed charging cables. Backed by Mudsor's 12-Month Guarantee against yellowing and material defects.
TEXT;

$db = App\Core\Database::getInstance();
$stmt = $db->prepare("UPDATE products SET description = ? WHERE id = ?");
$stmt->execute([$cleanDescription, $productId]);

echo "Cleaned product ID {$productId} description in database.\n";

try {
    \App\Infrastructure\Cache\CacheManager::getInstance()->flush();
    echo "Cache flushed.\n";
} catch (\Throwable $e) {}
