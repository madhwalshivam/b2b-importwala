<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Models/FeaturedCategory.php';

use App\Core\Database;
use App\Models\FeaturedCategory;

Database::init(require ROOT_PATH . '/config/database.php');

$m = new FeaturedCategory();
$cats = $m->getActiveWithSubcategories();
foreach ($cats as $c) {
    echo "Category: {$c['name']} | Image: {$c['image']}\n";
}
