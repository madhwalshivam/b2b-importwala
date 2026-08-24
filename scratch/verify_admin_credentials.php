<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();
$stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
$stmt->execute(['admin', 'admin']);
$admin = $stmt->fetch();

echo "Admin User Found:\n";
print_r([
    'id' => $admin['id'],
    'username' => $admin['username'],
    'email' => $admin['email'],
    'status' => $admin['status'],
    'password_hash' => $admin['password']
]);

$testPass = 'admin123';
if (password_verify($testPass, $admin['password'])) {
    echo "\nPassword 'admin123' matches!\n";
} else {
    echo "\nPassword 'admin123' does not match. Updating password hash for 'admin123'...\n";
    $newHash = password_hash($testPass, PASSWORD_BCRYPT);
    $updateStmt = $db->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
    $updateStmt->execute([$newHash, $admin['id']]);
    echo "Password updated successfully for admin user to 'admin123'\n";
}
