<?php
/**
 * Migration Script for Razorpay & Shiprocket Integration
 */
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    echo "Starting migration...\n";

    // 1. Execute razorpay_shiprocket_schema.sql
    $sqlFile = __DIR__ . '/razorpay_shiprocket_schema.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        $db->exec($sql);
        echo "[SUCCESS] Created payment_gateway_settings, shipping_settings, and admin_audit_log tables.\n";
    }

    // Helper to safely add column if not exists
    $addColumnIfNotExists = function($table, $column, $definition) use ($db) {
        $check = $db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        if ($check->rowCount() === 0) {
            $db->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
            echo "[ADDED] Column '{$column}' added to table '{$table}'.\n";
        } else {
            echo "[EXISTS] Column '{$column}' already exists in table '{$table}'.\n";
        }
    };

    // 2. Add columns to orders table
    $addColumnIfNotExists('orders', 'user_id', 'INT UNSIGNED NULL AFTER customer_id');
    $addColumnIfNotExists('orders', 'payment_provider', "VARCHAR(50) DEFAULT 'razorpay' AFTER payment_method");
    $addColumnIfNotExists('orders', 'razorpay_order_id', "VARCHAR(100) DEFAULT NULL AFTER transaction_id");
    $addColumnIfNotExists('orders', 'razorpay_payment_id', "VARCHAR(100) DEFAULT NULL AFTER razorpay_order_id");
    $addColumnIfNotExists('orders', 'razorpay_signature', "VARCHAR(255) DEFAULT NULL AFTER razorpay_payment_id");
    $addColumnIfNotExists('orders', 'shipping_status', "ENUM('not_shipped','processing','shipped','delivered','cancelled') DEFAULT 'not_shipped' AFTER order_status");
    $addColumnIfNotExists('orders', 'shiprocket_order_id', "VARCHAR(100) DEFAULT NULL AFTER shipping_status");
    $addColumnIfNotExists('orders', 'shiprocket_shipment_id', "VARCHAR(100) DEFAULT NULL AFTER shiprocket_order_id");
    $addColumnIfNotExists('orders', 'awb_code', "VARCHAR(100) DEFAULT NULL AFTER shiprocket_shipment_id");
    $addColumnIfNotExists('orders', 'tracking_url', "VARCHAR(255) DEFAULT NULL AFTER courier_name");

    // 3. Add columns to order_items table
    $addColumnIfNotExists('order_items', 'variation_id', "INT UNSIGNED DEFAULT NULL AFTER product_id");
    $addColumnIfNotExists('order_items', 'weight_kg', "DECIMAL(6,2) DEFAULT 0.50 AFTER quantity");

    // 4. Ensure default initial settings rows exist if empty
    $checkPg = $db->query("SELECT COUNT(*) FROM payment_gateway_settings");
    if ((int)$checkPg->fetchColumn() === 0) {
        $stmtPg = $db->prepare("INSERT INTO payment_gateway_settings (provider, key_id, key_secret, webhook_secret, mode, is_active) VALUES ('razorpay', 'rzp_test_placeholder_key', 'rzp_test_placeholder_secret', '', 'test', 1)");
        $stmtPg->execute();
        echo "[INITIALIZED] Inserted default placeholder row for payment_gateway_settings.\n";
    }

    $checkSp = $db->query("SELECT COUNT(*) FROM shipping_settings");
    if ((int)$checkSp->fetchColumn() === 0) {
        $stmtSp = $db->prepare("INSERT INTO shipping_settings (provider, email, password, pickup_location, auto_assign_courier, is_active) VALUES ('shiprocket', 'admin@mudsor.com', 'demo_password', 'Primary', 1, 1)");
        $stmtSp->execute();
        echo "[INITIALIZED] Inserted default placeholder row for shipping_settings.\n";
    }

    echo "Migration completed successfully!\n";

} catch (\Throwable $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
