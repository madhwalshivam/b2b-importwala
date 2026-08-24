<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/Banner.php';

$bannerModel = new App\Models\Banner();

// 1. Create test banner
$bannerId = $bannerModel->create([
    'title' => 'Test ImportWala Banner',
    'subtitle' => 'Exclusive B2B Deals',
    'image_url' => 'https://via.placeholder.com/1400x480?text=ImportWala+Banner',
    'link_url' => '/catalog',
    'cta_text' => 'Shop Wholesale',
    'sort_order' => 1,
    'is_active' => 1
]);

echo "Created Banner ID: {$bannerId}\n";

// 2. Fetch created banner
$banner = $bannerModel->findById($bannerId);
echo "Fetched Banner Title: {$banner['title']}, Active: {$banner['is_active']}, Sort Order: {$banner['sort_order']}\n";

// 3. Update Banner
$bannerModel->update($bannerId, array_merge($banner, [
    'title' => 'Updated ImportWala Banner',
    'sort_order' => 5
]));

$updatedBanner = $bannerModel->findById($bannerId);
echo "Updated Banner Title: {$updatedBanner['title']}, Sort Order: {$updatedBanner['sort_order']}\n";

// 4. Fetch Active Banners for Live Site
$activeBanners = $bannerModel->getActiveBanners();
echo "Active Banners Count: " . count($activeBanners) . "\n";

// 5. Clean up test banner
$bannerModel->delete($bannerId);
echo "Test Banner Cleaned Up Successfully.\n";
