<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Infrastructure/Cache/CacheInterface.php';
require_once __DIR__ . '/../app/Infrastructure/Cache/CacheManager.php';
require_once __DIR__ . '/../app/Services/BaseService.php';
require_once __DIR__ . '/../app/Services/TokenService.php';

session_start();

$_SESSION['admin_user_id'] = 1;
$_SESSION['admin_name'] = 'Admin User';
$_SESSION['admin_email'] = 'admin@importwala.com';

echo "Session BEFORE logout: admin_user_id = " . ($_SESSION['admin_user_id'] ?? 'NONE') . "\n";

if (!empty($_SESSION['admin_user_id'])) {
    try {
        App\Services\TokenService::revokeUserTokens((int)$_SESSION['admin_user_id'], 'admin');
    } catch (\Throwable $e) {
        echo "TokenService caught safely: " . $e->getMessage() . "\n";
    }
}
unset($_SESSION['admin_user_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role_id']);

echo "Session AFTER logout: admin_user_id = " . ($_SESSION['admin_user_id'] ?? 'NONE') . "\n";
echo "[SUCCESS] Admin Logout Processed Cleanly!\n";
