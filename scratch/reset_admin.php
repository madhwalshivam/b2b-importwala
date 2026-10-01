<?php
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo = new PDO('mysql:host=localhost;dbname=importwala', 'root', '');
$stmt = $pdo->prepare('UPDATE admin_users SET password = ? WHERE email = ?');
$stmt->execute([$hash, 'mudsorinfo@gmail.com']);
echo "Password reset to admin123";
