<?php
require_once __DIR__ . '/../config/database.php';
$dbConfig = require __DIR__ . '/../config/database.php';
$conn = $dbConfig['connections']['mysql'];
$dsn = "mysql:host={$conn['write']['host'][0]};port={$conn['port']};dbname={$conn['dbname']};charset={$conn['charset']}";

try {
    $pdo = new PDO($dsn, $conn['username'], $conn['password'], $conn['options']);
    echo "Connected successfully to DB: {$conn['dbname']}\n";

    // Update settings table
    $phone = '+91 95403 17079';
    $waPhone = '919540317079';
    $address = '476, Basement A1, Niti Khand-2, Indirapuram, Ghaziabad, UP - 201014';

    // 1. Check settings table
    $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
    if ($stmt->fetch()) {
        echo "Updating 'settings' table...\n";
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('contact_phone', '{$phone}') ON DUPLICATE KEY UPDATE setting_value = '{$phone}'");
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('whatsapp_business_number', '{$waPhone}') ON DUPLICATE KEY UPDATE setting_value = '{$waPhone}'");
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('address', '{$address}') ON DUPLICATE KEY UPDATE setting_value = '{$address}'");
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES ('contact_address', '{$address}') ON DUPLICATE KEY UPDATE setting_value = '{$address}'");
    }

    // 2. Check site_settings table
    $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");
    if ($stmt->fetch()) {
        echo "Updating 'site_settings' table...\n";
        $pdo->exec("INSERT INTO site_settings (setting_key, setting_value) VALUES ('notification_whatsapp', '9540317079') ON DUPLICATE KEY UPDATE setting_value = '9540317079'");
        $pdo->exec("INSERT INTO site_settings (setting_key, setting_value) VALUES ('contact_phone', '{$phone}') ON DUPLICATE KEY UPDATE setting_value = '{$phone}'");
        $pdo->exec("INSERT INTO site_settings (setting_key, setting_value) VALUES ('contact_address', '{$address}') ON DUPLICATE KEY UPDATE setting_value = '{$address}'");
    }

    // 3. Check pages / cms pages if any
    $stmt = $pdo->query("SHOW TABLES LIKE 'pages'");
    if ($stmt->fetch()) {
        echo "Checking 'pages' table...\n";
        $rows = $pdo->query("SELECT id, title, content FROM pages WHERE content LIKE '%92177%' OR content LIKE '%Bawana%' OR content LIKE '%Niti Khand%'")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $newContent = str_replace(
                ['+91 9217714452', '+91 92177 14452', '9217714452', '92177 14452', 'Floor 3rd, M-192, Block M, Pocket N, Sector 3, Bawana Industrial Area, New Delhi - 110039', '3rd Floor, M-192, Block M, Pocket N, Sector 3, Bawana Industrial Area, New Delhi, Delhi – 110039', '476 A1, Niti Khand-2, Indirapuram, Ghaziabad, Uttar Pradesh 201014, India'],
                [$phone, $phone, $waPhone, $phone, $address, $address, $address],
                $r['content']
            );
            $uStmt = $pdo->prepare("UPDATE pages SET content = ? WHERE id = ?");
            $uStmt->execute([$newContent, $r['id']]);
            echo "Updated page ID: {$r['id']}\n";
        }
    }

    echo "Database updates complete!\n";

} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
