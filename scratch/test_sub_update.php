<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Models/Subcategory.php';

use App\Core\Database;
use App\Models\Subcategory;

Database::init(require ROOT_PATH . '/config/database.php');

$subModel = new Subcategory();
$sub = $subModel->find(2);
echo "Subcategory ID 2 from DB:\n";
var_dump($sub);
