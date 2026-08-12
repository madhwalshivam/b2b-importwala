<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "MySQL Server connected successfully!\n";

    // Ensure database exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `ecommerce` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database 'ecommerce' verified/created.\n";
} catch (PDOException $e) {
    echo "Connection Failed: " . $e->getMessage() . "\n";
}
