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

    // 1. live_sessions table
    $db->exec("
        CREATE TABLE IF NOT EXISTS live_sessions (
            session_id VARCHAR(191) PRIMARY KEY,
            user_id INT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 2. Ensure updated_at column on cart_items table
    $cartCols = $db->query("SHOW COLUMNS FROM cart_items LIKE 'updated_at'")->fetchAll();
    if (empty($cartCols)) {
        $db->exec("ALTER TABLE cart_items ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");
    }

    // 3. site_settings key-value store table
    $db->exec("
        CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Insert default notification seed settings if not existing
    $stmt = $db->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->execute(['notification_email', 'mudsorinfo@gmail.com']);
    $stmt->execute(['notification_whatsapp', '9217714452']);
    $stmt->execute(['whatsapp_api_token', '']);
    $stmt->execute(['whatsapp_phone_number_id', '']);

    // 4. notification_log table
    $db->exec("
        CREATE TABLE IF NOT EXISTS notification_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(50) NOT NULL,
            channel VARCHAR(20) NOT NULL,
            recipient VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL,
            error_message TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Migration completed successfully!\n";
} catch (\Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}
