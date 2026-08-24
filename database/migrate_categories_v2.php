<?php
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $pdo = App\Core\Database::getInstance();
    
    echo "Updating categories table structure...\n";

    // 1. Add parent_id column if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'parent_id'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN parent_id INT UNSIGNED NULL DEFAULT NULL AFTER id");
        $pdo->exec("ALTER TABLE categories ADD CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL");
        echo "Added parent_id column.\n";
    }

    // 2. Add icon_type column if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'icon_type'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN icon_type ENUM('library', 'custom') DEFAULT 'library' AFTER slug");
        echo "Added icon_type column.\n";
    }

    // 3. Add custom_icon column if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM categories LIKE 'custom_icon'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE categories ADD COLUMN custom_icon VARCHAR(255) NULL DEFAULT NULL AFTER icon");
        echo "Added custom_icon column.\n";
    }

    echo "Categories table structure is up to date!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
