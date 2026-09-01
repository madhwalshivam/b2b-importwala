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

use App\Core\Database;

$db = Database::getInstance();

// 1. Create product_variants table
$sqlVariants = "CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `variant_code` VARCHAR(50) DEFAULT NULL,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `attribute_label` VARCHAR(100) DEFAULT 'Variant',
  `attribute_value` VARCHAR(100) NOT NULL,
  `weight` VARCHAR(50) DEFAULT NULL,
  `dimensions` VARCHAR(50) DEFAULT NULL,
  `stock_quantity` INT(11) NOT NULL DEFAULT 0,
  `wholesale_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `one_piece_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` INT(11) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$db->exec($sqlVariants);
echo "SUCCESS: product_variants table created/verified.\n";

// 2. Check product_specifications table
$sqlSpecs = "CREATE TABLE IF NOT EXISTS `product_specifications` (
  `id` INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT(10) UNSIGNED NOT NULL,
  `spec_key` VARCHAR(100) NOT NULL,
  `spec_value` VARCHAR(255) NOT NULL,
  `sort_order` INT(11) DEFAULT 0,
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$db->exec($sqlSpecs);
echo "SUCCESS: product_specifications table created/verified.\n";

// 3. Populate sample variants & specs for first active product if empty
$firstProduct = $db->query("SELECT id, name, slug, price FROM products WHERE status = 'active' ORDER BY id ASC LIMIT 1")->fetch(\PDO::FETCH_ASSOC);

if ($firstProduct) {
    $pid = (int)$firstProduct['id'];
    echo "Found Active Product: ID {$pid} - {$firstProduct['name']}\n";

    $existVarCount = $db->query("SELECT COUNT(*) FROM product_variants WHERE product_id = {$pid}")->fetchColumn();
    if ($existVarCount == 0) {
        $sampleVariants = [
            [
                'product_id' => $pid,
                'variant_code' => 'VAR-A01',
                'attribute_label' => 'Color / Style',
                'attribute_value' => 'Midnight Silver Steel',
                'weight' => '0.85 kg',
                'dimensions' => '15 x 8 x 4 cm',
                'stock_quantity' => 120,
                'wholesale_price' => 450.00,
                'one_piece_price' => 599.00,
                'sort_order' => 1,
                'is_active' => 1
            ],
            [
                'product_id' => $pid,
                'variant_code' => 'VAR-A02',
                'attribute_label' => 'Color / Style',
                'attribute_value' => 'Matte Onyx Black',
                'weight' => '0.88 kg',
                'dimensions' => '15 x 8 x 4 cm',
                'stock_quantity' => 85,
                'wholesale_price' => 470.00,
                'one_piece_price' => 620.00,
                'sort_order' => 2,
                'is_active' => 1
            ],
            [
                'product_id' => $pid,
                'variant_code' => 'VAR-A03',
                'attribute_label' => 'Color / Style',
                'attribute_value' => 'Rose Gold Luxury Edition',
                'weight' => '0.90 kg',
                'dimensions' => '16 x 8.5 x 4.5 cm',
                'stock_quantity' => 40,
                'wholesale_price' => 520.00,
                'one_piece_price' => 690.00,
                'sort_order' => 3,
                'is_active' => 1
            ]
        ];

        $stmt = $db->prepare("INSERT INTO product_variants 
            (product_id, variant_code, attribute_label, attribute_value, weight, dimensions, stock_quantity, wholesale_price, one_piece_price, sort_order, is_active)
            VALUES (:product_id, :variant_code, :attribute_label, :attribute_value, :weight, :dimensions, :stock_quantity, :wholesale_price, :one_piece_price, :sort_order, :is_active)");
        
        foreach ($sampleVariants as $v) {
            $stmt->execute($v);
        }
        echo "Added 3 sample variants to Product #{$pid}.\n";
    }

    $existSpecCount = $db->query("SELECT COUNT(*) FROM product_specifications WHERE product_id = {$pid}")->fetchColumn();
    if ($existSpecCount == 0) {
        $sampleSpecs = [
            ['spec_key' => 'Material', 'spec_value' => 'Grade 316L Stainless Steel / Silicone', 'sort_order' => 1],
            ['spec_key' => 'Surface Finish', 'spec_value' => 'Multi-Layer Electroplated UV Coating', 'sort_order' => 2],
            ['spec_key' => 'Item Net Weight', 'spec_value' => '0.85 kg / piece', 'sort_order' => 3],
            ['spec_key' => 'Package Dimensions', 'spec_value' => '22 x 14 x 6 cm', 'sort_order' => 4],
            ['spec_key' => 'Country of Origin', 'spec_value' => 'India / Verified OEM Import', 'sort_order' => 5],
            ['spec_key' => 'MOQ (Wholesale)', 'spec_value' => '10 Pieces', 'sort_order' => 6],
            ['spec_key' => 'Sample Availability', 'spec_value' => 'Yes (Available in One-Piece Mode)', 'sort_order' => 7],
            ['spec_key' => 'Warranty', 'spec_value' => '12 Months Replacement Guarantee', 'sort_order' => 8],
            ['spec_key' => 'Certification', 'spec_value' => 'ISO 9001:2015 Quality Tested', 'sort_order' => 9],
            ['spec_key' => 'Packaging Type', 'spec_value' => 'Individual Export Box Packaging', 'sort_order' => 10],
        ];

        $specStmt = $db->prepare("INSERT INTO product_specifications (product_id, spec_key, spec_value, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($sampleSpecs as $s) {
            $specStmt->execute([$pid, $s['spec_key'], $s['spec_value'], $s['sort_order']]);
        }
        echo "Added 10 sample specifications to Product #{$pid}.\n";
    }
}
