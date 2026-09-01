<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once __DIR__ . '/../app/Helpers/Functions.php';
use App\Models\NavLink;

$navModel = new NavLink();

// 1. Create a test nav link
$id = $navModel->createLink([
    'label' => 'Test Temp Link',
    'url' => '/test-temp',
    'type' => 'internal',
    'parent_id' => null,
    'sort_order' => 99,
    'is_active' => 1,
    'open_in_new_tab' => 0
]);
echo "Created Test Nav Link ID: {$id}\n";

// 2. Update the test nav link
$navModel->updateLink($id, [
    'label' => 'Updated Temp Link',
    'url' => '/test-temp-updated',
    'type' => 'internal',
    'parent_id' => null,
    'sort_order' => 100,
    'is_active' => 0,
    'open_in_new_tab' => 1
]);
echo "Updated Test Nav Link ID: {$id}\n";

// 3. Delete the test nav link
$navModel->deleteLink($id);
echo "Deleted Test Nav Link ID: {$id}\n";

echo "ALL NAV MODEL CRUD OPERATIONS SUCCESSFUL!\n";
