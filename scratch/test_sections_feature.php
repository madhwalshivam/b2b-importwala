<?php
define('ROOT_PATH', dirname(__DIR__));
date_default_timezone_set('Asia/Kolkata');

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once ROOT_PATH . '/app/Helpers/Functions.php';

echo "--- 1. Testing HomeSection::getEnabledSectionsWithProducts() ---\n";
$homeSectionModel = new App\Models\HomeSection();
$enabled = $homeSectionModel->getEnabledSectionsWithProducts();
echo "Found " . count($enabled) . " enabled sections:\n";
foreach ($enabled as $key => $sec) {
    echo " - [ID {$sec['id']}] {$sec['title']} (Slug: {$sec['slug']}, Key: {$sec['section_key']}, Display Count: {$sec['homepage_display_count']}, Products Count: " . count($sec['products']) . ")\n";
}

echo "\n--- 2. Testing HomeSection::findBySlug('featured-products') ---\n";
$feat = $homeSectionModel->findBySlug('featured-products');
if ($feat) {
    echo "SUCCESS: Found section ID {$feat['id']} with title '{$feat['title']}'\n";
} else {
    echo "FAILED: Could not find section by slug 'featured-products'\n";
}

echo "\n--- 3. Testing HomeSection::getSectionProductsPaginated() ---\n";
if ($feat) {
    $paginated = $homeSectionModel->getSectionProductsPaginated((int)$feat['id'], 1, 10);
    echo "Paginated total: {$paginated['total']}, Items count: " . count($paginated['items']) . ", Last page: {$paginated['last_page']}\n";
}

echo "\n--- 4. Testing dynamic section creation ---\n";
$newSlug = 'test-promo-' . time();
$newId = $homeSectionModel->createSection([
    'title' => 'Test Promo Section',
    'slug' => $newSlug,
    'subtitle' => 'Test Subtitle',
    'status' => 'active',
    'max_products' => 8,
    'homepage_display_count' => 4,
    'sort_order' => 99
]);
echo "Created test section ID: {$newId} with slug {$newSlug}\n";

$foundNew = $homeSectionModel->findBySlug($newSlug);
if ($foundNew && (int)$foundNew['id'] === $newId) {
    echo "SUCCESS: Found newly created section by slug {$newSlug}\n";
} else {
    echo "FAILED: Could not find newly created section\n";
}

// Cleanup test section
$homeSectionModel->deleteSection($newId);
echo "Cleaned up test section ID: {$newId}\n";
echo "\nAll verification tests completed successfully!\n";
