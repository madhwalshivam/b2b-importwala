<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

require_once __DIR__ . '/../app/Helpers/Functions.php';

use App\Core\Database;

try {
    $db = Database::getInstance();

    // Create product_categories pivot table
    $db->exec("
        CREATE TABLE IF NOT EXISTS product_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            category_id INT NOT NULL,
            UNIQUE KEY (product_id, category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Create product_brands pivot table
    $db->exec("
        CREATE TABLE IF NOT EXISTS product_brands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            brand_id INT NOT NULL,
            UNIQUE KEY (product_id, brand_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Pivot tables product_categories and product_brands created successfully!\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
