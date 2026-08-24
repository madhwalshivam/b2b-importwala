<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Infrastructure/Cache/CacheInterface.php';
require_once __DIR__ . '/../app/Infrastructure/Cache/CacheManager.php';
require_once __DIR__ . '/../app/Services/BaseService.php';
require_once __DIR__ . '/../app/Services/TokenService.php';

session_start();

$username = 'admin';
$password = 'admin123';

$db = App\Core\Database::getInstance();
$stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute([$username, $username]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_user_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'] ?? $admin['username'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role_id'] = $admin['role_id'] ?? 1;

    try {
        App\Services\TokenService::issueTokens($admin['id'], 'admin');
    } catch (\Throwable $e) {
        echo "TokenService caught safely: " . $e->getMessage() . "\n";
    }

    try {
        $db->prepare("UPDATE admin_users SET last_login_at = NOW() WHERE id = ?")->execute([$admin['id']]);
    } catch (\Throwable $e) {
        echo "last_login_at update caught safely: " . $e->getMessage() . "\n";
    }

    echo "[SUCCESS] Admin Login Processed Cleanly! Session ID: " . $_SESSION['admin_user_id'] . "\n";
} else {
    echo "[FAILED] Admin Login Invalid Credentials\n";
}
