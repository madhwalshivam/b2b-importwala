<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__ . '/..');
}
spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

use App\Core\Database;

$db = Database::getInstance();
$firstProd = $db->query("SELECT id, slug, name FROM products WHERE status = 'active' ORDER BY id ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);

if (!$firstProd) {
    echo "NO ACTIVE PRODUCTS FOUND\n";
    exit;
}

$url = 'http://localhost/importwala/product/' . $firstProd['slug'];
echo "TESTING PRODUCT URL: {$url}\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "PRODUCT DETAIL HTTP STATUS: {$code}\n";

if ($code === 200) {
    $hasToggleWholesale = str_contains($res, 'toggleWholesaleBtn');
    $hasToggleOnePiece  = str_contains($res, 'toggleOnePieceBtn');
    $hasVariantRows     = str_contains($res, 'variant-row');
    $hasSpecsTable      = str_contains($res, 'toggleSpecifications');
    $hasWhatsappEnquiry = str_contains($res, 'btnWhatsappEnquiry');
    $hasStickyMobile    = str_contains($res, 'mobileWhatsappBtn');

    echo "VERIFICATION RESULTS:\n";
    echo "- Dual Pricing Wholesale Toggle: " . ($hasToggleWholesale ? "PASSED" : "FAILED") . "\n";
    echo "- Dual Pricing One-Piece Toggle: " . ($hasToggleOnePiece ? "PASSED" : "FAILED") . "\n";
    echo "- List-Style Variant Selector Rows: " . ($hasVariantRows ? "PASSED" : "FAILED") . "\n";
    echo "- Dynamic Specifications Table: " . ($hasSpecsTable ? "PASSED" : "FAILED") . "\n";
    echo "- WhatsApp Pre-filled Enquiry Button: " . ($hasWhatsappEnquiry ? "PASSED" : "FAILED") . "\n";
    echo "- Mobile Sticky Action Bar: " . ($hasStickyMobile ? "PASSED" : "FAILED") . "\n";
} else {
    echo "ERROR RESPONSE:\n" . substr($res, 0, 500) . "\n";
}
