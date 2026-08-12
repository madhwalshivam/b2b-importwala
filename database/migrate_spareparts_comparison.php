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

    // 1. Extend products table with spare parts & OEM attributes
    $columnsToAdd = [
        "material VARCHAR(100) DEFAULT 'Heavy-Duty Steel / ABS'",
        "finish VARCHAR(100) DEFAULT 'UV-Resistant Powder Coat'",
        "weight_grams INT DEFAULT 850",
        "warranty_months INT DEFAULT 12",
        "installation_difficulty ENUM('easy','moderate','professional') DEFAULT 'easy'",
        "installation_time_minutes INT DEFAULT 15",
        "oem_price DECIMAL(10,2) DEFAULT NULL",
        "oem_material VARCHAR(100) DEFAULT 'Standard Steel / Plastic'",
        "oem_finish VARCHAR(100) DEFAULT 'Basic Paint'",
        "oem_fitment VARCHAR(100) DEFAULT 'Standard Fit'",
        "oem_warranty_months INT DEFAULT 6",
        "oem_value_rating DECIMAL(2,1) DEFAULT 3.5",
        "material_quality_pct INT DEFAULT 95",
        "paint_finish_pct INT DEFAULT 90",
        "fitment_pct INT DEFAULT 98",
        "durability_pct INT DEFAULT 92",
        "rating_avg DECIMAL(2,1) DEFAULT 4.8",
        "review_count INT DEFAULT 28"
    ];

    foreach ($columnsToAdd as $colDef) {
        $colName = explode(' ', trim($colDef))[0];
        $check = $db->query("SHOW COLUMNS FROM products LIKE '{$colName}'")->fetchAll();
        if (empty($check)) {
            $db->exec("ALTER TABLE products ADD COLUMN {$colDef};");
        }
    }

    // Update OEM Price for existing products where oem_price is null
    $db->exec("UPDATE products SET oem_price = ROUND(price * 1.45, 2) WHERE oem_price IS NULL OR oem_price = 0;");

    // 2. Table product_vehicle_compatibility
    $db->exec("
        CREATE TABLE IF NOT EXISTS product_vehicle_compatibility (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            vehicle_name VARCHAR(100) NOT NULL,
            is_compatible TINYINT(1) DEFAULT 1,
            UNIQUE KEY (product_id, vehicle_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 3. Table product_included_items
    $db->exec("
        CREATE TABLE IF NOT EXISTS product_included_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            item_name VARCHAR(100) NOT NULL,
            is_included TINYINT(1) DEFAULT 1,
            sort_order INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 4. Table product_vehicle_images
    $db->exec("
        CREATE TABLE IF NOT EXISTS product_vehicle_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            vehicle_name VARCHAR(100) NOT NULL,
            image_path TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 5. Table product_badges
    $db->exec("
        CREATE TABLE IF NOT EXISTS product_badges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            badge_text VARCHAR(100) NOT NULL,
            badge_icon VARCHAR(50) DEFAULT 'check',
            sort_order INT DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Seed default sample data for existing products
    $products = $db->query("SELECT id FROM products")->fetchAll();
    $vehicles = ['Ola S1 Pro (Gen 1 & 2)', 'Ola S1 X / Air', 'Ather 450X / 450S', 'TVS iQube S / ST', 'Hero Vida V1 Pro', 'Bajaj Chetak Premium'];
    $includedDefaults = ['Main Accessory Part', 'Heavy-Duty Brackets', 'Stainless Steel Bolts & Washers', 'Installation Manual Guide'];
    $badgeDefaults = [
        ['badge_text' => 'OEM Precise Fit', 'badge_icon' => 'check-circle'],
        ['badge_text' => 'Premium Heavy-Duty Steel', 'badge_icon' => 'shield-check'],
        ['badge_text' => 'UV Powder Coated', 'badge_icon' => 'sparkles'],
        ['badge_text' => '100% Water & Weather Proof', 'badge_icon' => 'droplets'],
        ['badge_text' => 'Made in India', 'badge_icon' => 'flag']
    ];

    $stmtComp = $db->prepare("INSERT IGNORE INTO product_vehicle_compatibility (product_id, vehicle_name, is_compatible) VALUES (?, ?, ?)");
    $stmtInc = $db->prepare("INSERT IGNORE INTO product_included_items (product_id, item_name, is_included, sort_order) VALUES (?, ?, 1, ?)");
    $stmtBadge = $db->prepare("INSERT IGNORE INTO product_badges (product_id, badge_text, badge_icon, sort_order) VALUES (?, ?, ?, ?)");

    foreach ($products as $p) {
        $pid = (int)$p['id'];
        
        // Seed vehicle compatibilities
        foreach ($vehicles as $idx => $v) {
            $isComp = ($idx < 4) ? 1 : ($pid % 2 === 0 ? 1 : 0);
            $stmtComp->execute([$pid, $v, $isComp]);
        }

        // Seed included items
        foreach ($includedDefaults as $idx => $inc) {
            $stmtInc->execute([$pid, $inc, $idx]);
        }

        // Seed badges
        foreach ($badgeDefaults as $idx => $bg) {
            $stmtBadge->execute([$pid, $bg['badge_text'], $bg['badge_icon'], $idx]);
        }
    }

    echo "Spare parts comparison migration & seeding completed successfully!\n";
} catch (\Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
