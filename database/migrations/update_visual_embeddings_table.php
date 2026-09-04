<?php

define('ROOT_PATH', dirname(__DIR__, 2));

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

use App\Core\Database;

try {
    $db = Database::getInstance();

    echo "Running migration: Update product_image_embeddings table schema...\n";

    // 1. Check existing columns in product_image_embeddings
    $cols = $db->query("SHOW COLUMNS FROM `product_image_embeddings`")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('image_id', $cols)) {
        echo "Adding `image_id` column...\n";
        $db->exec("ALTER TABLE `product_image_embeddings` ADD COLUMN `image_id` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `product_id`");
        $db->exec("ALTER TABLE `product_image_embeddings` ADD INDEX `idx_pve_image_id` (`image_id`)");
    }

    if (!in_array('variant_id', $cols)) {
        echo "Adding `variant_id` column...\n";
        $db->exec("ALTER TABLE `product_image_embeddings` ADD COLUMN `variant_id` INT UNSIGNED NULL DEFAULT NULL AFTER `image_id`");
        $db->exec("ALTER TABLE `product_image_embeddings` ADD INDEX `idx_pve_variant_id` (`variant_id`)");
    }

    if (!in_array('image_type', $cols)) {
        echo "Adding `image_type` column...\n";
        $db->exec("ALTER TABLE `product_image_embeddings` ADD COLUMN `image_type` VARCHAR(50) DEFAULT 'main' AFTER `variant_id`");
    }

    echo "SUCCESS: `product_image_embeddings` table schema updated successfully!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
