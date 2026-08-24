<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Core/Model.php';
require_once ROOT_PATH . '/app/Core/Session.php';
require_once ROOT_PATH . '/app/Core/View.php';
require_once ROOT_PATH . '/app/Core/Controller.php';
require_once ROOT_PATH . '/app/Core/Request.php';
require_once ROOT_PATH . '/app/Core/Response.php';
require_once ROOT_PATH . '/app/Core/Auth.php';
require_once ROOT_PATH . '/app/Helpers/Functions.php';
require_once ROOT_PATH . '/app/Models/Subcategory.php';
require_once ROOT_PATH . '/app/Models/Category.php';
require_once ROOT_PATH . '/app/Controllers/Admin/SubcategoryController.php';

use App\Core\Database;
use App\Controllers\Admin\SubcategoryController;

Database::init(require ROOT_PATH . '/config/database.php');

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['category_id'] = '1';
$_POST['name'] = 'Stainless Steel Necklaces Updated';
$_POST['sort_order'] = '1';
$_POST['status'] = 'active';

// Set fake admin auth session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

try {
    $controller = new SubcategoryController();
    $controller->update(2);
    echo "Update executed successfully!\n";
} catch (\Throwable $e) {
    echo "ERROR OCCURRED: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
