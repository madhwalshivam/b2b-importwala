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

if (file_exists(__DIR__ . '/../app/Helpers/Functions.php')) {
    require_once __DIR__ . '/../app/Helpers/Functions.php';
}

use App\Core\Database;

try {
    $db = Database::getInstance();

    // 1. Create `factories` table
    $db->exec("
        CREATE TABLE IF NOT EXISTS factories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            factory_code VARCHAR(50) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            contact_person VARCHAR(255) NULL,
            phone VARCHAR(50) NULL,
            whatsapp VARCHAR(50) NULL,
            email VARCHAR(255) NULL,
            store_url TEXT NULL,
            source_platform VARCHAR(100) NULL,
            status ENUM('active', 'inactive', 'archived') NOT NULL DEFAULT 'active',
            notes TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_factory_code (factory_code),
            INDEX idx_factory_name (name),
            INDEX idx_factory_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "[PASS] 'factories' table created or updated successfully.\n";

    // 2. Add columns to `products` table
    $existingColumns = [];
    $stmtCols = $db->query("SHOW COLUMNS FROM products");
    while ($row = $stmtCols->fetch()) {
        $existingColumns[strtolower($row['Field'])] = true;
    }

    $columnsToAdd = [
        'factory_id'                  => "INT NULL",
        'weight'                      => "VARCHAR(100) NULL",
        'variety'                     => "VARCHAR(255) NULL",
        'manufacturer_id_code'        => "VARCHAR(50) NULL",
        'manufacturer_name'           => "VARCHAR(255) NULL",
        'manufacturer_contact_person' => "VARCHAR(255) NULL",
        'manufacturer_phone'          => "VARCHAR(50) NULL",
        'manufacturer_whatsapp'       => "VARCHAR(50) NULL",
        'manufacturer_email'          => "VARCHAR(255) NULL",
        'manufacturer_store_url'      => "TEXT NULL",
        'source_platform'             => "VARCHAR(100) NULL",
        'source_product_id'           => "VARCHAR(100) NULL",
        'source_product_url'          => "TEXT NULL",
        'import_date'                 => "DATETIME NULL",
        'admin_status'                => "VARCHAR(50) NULL",
    ];

    foreach ($columnsToAdd as $colName => $colDef) {
        if (!isset($existingColumns[$colName])) {
            $db->exec("ALTER TABLE products ADD COLUMN {$colName} {$colDef}");
            echo "[PASS] Column '{$colName}' added to 'products' table.\n";
        } else {
            echo "[INFO] Column '{$colName}' already exists on 'products' table.\n";
        }
    }

    // Add index and foreign key on factory_id if not present
    $stmtIndex = $db->query("SHOW INDEX FROM products WHERE Key_name = 'idx_products_factory_id'");
    if (!$stmtIndex->fetch()) {
        $db->exec("ALTER TABLE products ADD INDEX idx_products_factory_id (factory_id)");
        echo "[PASS] Index 'idx_products_factory_id' added to 'products' table.\n";
    }

    echo "Migration completed successfully!\n";
} catch (\Throwable $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
    exit(1);
}
