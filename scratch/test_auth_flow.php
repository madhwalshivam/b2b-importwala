<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

session_start();

$db = App\Core\Database::getInstance();
$username = 'admin';
$password = 'admin123';

$stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$username, $username]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password'])) {
    if ($admin['status'] === 'active') {
        $_SESSION['admin_user_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role_id'] = $admin['role_id'];
        echo "Auth Verification Success: User ID " . $_SESSION['admin_user_id'] . " authenticated cleanly.\n";
    } else {
        echo "Auth Failed: User is inactive.\n";
    }
} else {
    echo "Auth Failed: Invalid credentials.\n";
}
