<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

try {
    $db = App\Core\Database::getInstance();
    
    $title = 'About ImportWale';
    $metaTitle = 'About ImportWale | B2B Wholesale Sourcing Marketplace';
    $metaDesc = 'ImportWale is India\'s premier B2B wholesale marketplace connecting buyers, retailers, and distributors with 50,000+ factory-direct products at competitive prices.';
    
    $content = '<div class="space-y-6 text-sm text-gray-700 font-light leading-relaxed">
        <p class="text-base text-gray-900 font-normal mb-4 leading-relaxed">
            Welcome to <strong>ImportWale</strong> — India\'s premier B2B wholesale sourcing marketplace connecting retailers, reselling businesses, corporate buyers, and distributors directly with factory-priced products across multiple commercial categories.
        </p>

        <p class="font-light">
            With a wide product catalog of over <strong>50,000+ items</strong> across fashion jewellery, bags & luggage, premium drinkware, party supplies, and general wholesale goods, ImportWale simplifies bulk procurement by eliminating middleman markups and traditional import barriers.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-6">
            <div class="p-4 bg-orange-50 border border-orange-100 rounded-xl">
                <h4 class="font-semibold text-gray-900 mb-1">50,000+ Product Range</h4>
                <p class="text-xs text-gray-600 font-light">Constantly updated inventory across high-margin product categories.</p>
            </div>
            <div class="p-4 bg-orange-50 border border-orange-100 rounded-xl">
                <h4 class="font-semibold text-gray-900 mb-1">Low MOQ Options</h4>
                <p class="text-xs text-gray-600 font-light">Flexible minimum order quantities to test demand with small batch orders.</p>
            </div>
            <div class="p-4 bg-orange-50 border border-orange-100 rounded-xl">
                <h4 class="font-semibold text-gray-900 mb-1">Fast & Free Air Shipping</h4>
                <p class="text-xs text-gray-600 font-light">Express air dispatch with door-to-door tracking for speedy delivery.</p>
            </div>
            <div class="p-4 bg-orange-50 border border-orange-100 rounded-xl">
                <h4 class="font-semibold text-gray-900 mb-1">Trade Assurance Protection</h4>
                <p class="text-xs text-gray-600 font-light">100% order protection, secure payment options, and automated GST billing.</p>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">How It Works</h3>
        <ol class="list-decimal pl-5 space-y-2 text-gray-700 font-light">
            <li><strong>Browse & Select:</strong> Explore 50,000+ products or use AI Visual Search.</li>
            <li><strong>Request Quote / Select Quantity:</strong> Choose tiered bulk rates or submit custom RFQs.</li>
            <li><strong>Secure Payment:</strong> Pay safely with Trade Assurance and GST-compliant invoicing.</li>
            <li><strong>Fast Delivery:</strong> Enjoy express air shipping with door-to-door tracking.</li>
        </ol>

        <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">Why Choose Us?</h3>
        <ul class="list-disc pl-5 space-y-2 text-gray-700 font-light">
            <li>Years of sourcing and supply chain management experience.</li>
            <li>Rigorously verified global manufacturers and suppliers.</li>
            <li>Multi-stage quality control checks on every order.</li>
            <li>Transparent wholesale pricing with zero hidden fees.</li>
        </ul>
    </div>';

    $stmt = $db->prepare("UPDATE cms_pages SET title = ?, content = ?, meta_title = ?, meta_description = ? WHERE slug = ?");
    $stmt->execute([$title, $content, $metaTitle, $metaDesc, 'about-us']);
    
    echo "cms_pages table updated for 'about-us' successfully with light typography!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
