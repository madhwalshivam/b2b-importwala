<?php
define('ROOT_PATH', __DIR__ . '/..');

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once ROOT_PATH . '/app/Helpers/functions.php';

use App\Core\Database;
use App\Controllers\ApiController;

Database::init(require ROOT_PATH . '/config/database.php');

$_GET['q'] = 'ring';

$controller = new ApiController();

ob_start();
try {
    $controller->searchAutocomplete();
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
$output = ob_get_clean();

echo "Autocomplete Output:\n" . $output . "\n";
