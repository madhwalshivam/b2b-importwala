<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/Banner.php';

$bannerModel = new App\Models\Banner();
$banners = $bannerModel->getActiveBanners();

foreach ($banners as $b) {
    echo "Banner ID {$b['id']}:\n";
    echo "  Desktop Src: " . App\Models\Banner::getImageSrc($b) . "\n";
    echo "  Tablet Src:  " . App\Models\Banner::getTabletImageSrc($b) . "\n";
    echo "  Mobile Src:  " . App\Models\Banner::getMobileImageSrc($b) . "\n";
}
