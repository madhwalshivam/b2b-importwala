<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$admins = $db->query("SELECT id, name, email, username, status, role_id FROM admin_users")->fetchAll(PDO::FETCH_ASSOC);
echo "admin_users count: " . count($admins) . "\n";
print_r($admins);

$usersAdmin = $db->query("SELECT id, first_name, last_name, email, user_type, status FROM users WHERE user_type IN ('admin', 'superadmin')")->fetchAll(PDO::FETCH_ASSOC);
echo "users (admin type) count: " . count($usersAdmin) . "\n";
print_r($usersAdmin);
