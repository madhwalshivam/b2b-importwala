<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    $stmt = $db->query("SELECT * FROM rfq_requests ORDER BY id DESC LIMIT 2");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($rows);
} catch (Exception $e) {
    echo $e->getMessage();
}
