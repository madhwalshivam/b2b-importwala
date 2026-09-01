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

$richDescription = <<<HTML
<div class="space-y-4">
    <p class="text-xs sm:text-sm font-semibold text-gray-800 leading-relaxed">
        Engineered for ultimate impact protection and sleek everyday ergonomics, the <strong>Mudsor Ultra-Slim Protective Case</strong> delivers military-grade defense without adding bulky weight to your phone.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 my-4">
        <div class="p-3.5 bg-gray-50/80 rounded-xl border border-gray-200/80 flex items-start gap-3">
            <div class="w-6 h-6 rounded-lg bg-orange-100 text-[#f05a29] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">🛡️</div>
            <div>
                <strong class="text-xs text-gray-900 block font-bold">10Ft Military Drop Protection</strong>
                <span class="text-[11px] text-gray-500 leading-tight block mt-0.5">Reinforced dual-layer TPU shock-absorbing corner bumpers mitigate drops up to 10 feet.</span>
            </div>
        </div>
        <div class="p-3.5 bg-gray-50/80 rounded-xl border border-gray-200/80 flex items-start gap-3">
            <div class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">⚡</div>
            <div>
                <strong class="text-xs text-gray-900 block font-bold">MagSafe &amp; Wireless Compatible</strong>
                <span class="text-[11px] text-gray-500 leading-tight block mt-0.5">Integrated 36 N52 magnet array ensures effortless alignment with wireless chargers &amp; wallets.</span>
            </div>
        </div>
        <div class="p-3.5 bg-gray-50/80 rounded-xl border border-gray-200/80 flex items-start gap-3">
            <div class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">✨</div>
            <div>
                <strong class="text-xs text-gray-900 block font-bold">Anti-Scratch Oleophobic Finish</strong>
                <span class="text-[11px] text-gray-500 leading-tight block mt-0.5">Nano-matte protective coating repels fingerprints, oils, and daily scratches.</span>
            </div>
        </div>
        <div class="p-3.5 bg-gray-50/80 rounded-xl border border-gray-200/80 flex items-start gap-3">
            <div class="w-6 h-6 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">📷</div>
            <div>
                <strong class="text-xs text-gray-900 block font-bold">Raised Lens &amp; Screen Ring</strong>
                <span class="text-[11px] text-gray-500 leading-tight block mt-0.5">1.5mm raised display lips &amp; 2.0mm camera bezel prevent direct flat surface contact.</span>
            </div>
        </div>
    </div>

    <p class="text-xs text-gray-600 leading-relaxed">
        Each case is precision-molded to provide crisp tactile button action and exact port cutouts for high-speed charging cables. Backed by Mudsor's 12-Month Guarantee against yellowing and material defects.
    </p>
</div>
HTML;

$db = App\Core\Database::getInstance();
$stmt = $db->prepare("UPDATE products SET description = ? WHERE id = ?");
$stmt->execute([$richDescription, $productId]);

echo "Updated product ID {$productId} description successfully.\n";

try {
    \App\Infrastructure\Cache\CacheManager::getInstance()->flush();
    echo "Cache flushed.\n";
} catch (\Throwable $e) {}
