<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/Banner.php';

$bannerModel = new App\Models\Banner();
$banners = $bannerModel->getActiveBanners();

echo "Active Banners Count: " . count($banners) . "\n";
print_r($banners);
