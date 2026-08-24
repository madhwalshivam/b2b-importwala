<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/Banner.php';

$bannerModel = new App\Models\Banner();
$heroBanners = $bannerModel->getActiveBanners();

echo "Active Banners Count: " . count($heroBanners) . "\n";
foreach ($heroBanners as $b) {
    echo "  - Banner ID: {$b['id']}, Link: {$b['link_url']}, Img: " . App\Models\Banner::getImageSrc($b) . "\n";
}
