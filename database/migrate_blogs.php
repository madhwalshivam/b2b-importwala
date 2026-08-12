<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "Running Migration for Blog Posts...\n";

// 1. Create blog_posts table
$sql = "CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `excerpt` TEXT NULL,
  `content` LONGTEXT NULL,
  `featured_image` VARCHAR(255) NULL,
  `featured_image_alt` VARCHAR(255) NULL,
  `meta_title` VARCHAR(255) NULL,
  `meta_description` TEXT NULL,
  `focus_keyword` VARCHAR(255) NULL,
  `author_name` VARCHAR(150) DEFAULT 'Admin',
  `status` ENUM('draft', 'published') DEFAULT 'draft',
  `views` INT UNSIGNED DEFAULT 0,
  `published_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_slug` (`slug`),
  INDEX `idx_status_published` (`status`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$db->exec($sql);
echo "Table `blog_posts` created or already exists.\n";

// 2. Add permissions for blogs module if permissions table exists
$permCheck = $db->query("SHOW TABLES LIKE 'permissions'")->fetch();
if ($permCheck) {
    $permissions = [
        ['module' => 'blogs', 'action' => 'view', 'name' => 'View Blogs', 'key_code' => 'blogs.view'],
        ['module' => 'blogs', 'action' => 'add', 'name' => 'Add Blog', 'key_code' => 'blogs.add'],
        ['module' => 'blogs', 'action' => 'edit', 'name' => 'Edit Blog', 'key_code' => 'blogs.edit'],
        ['module' => 'blogs', 'action' => 'delete', 'name' => 'Delete Blog', 'key_code' => 'blogs.delete'],
    ];

    foreach ($permissions as $p) {
        $stmt = $db->prepare("SELECT id FROM permissions WHERE key_code = ?");
        $stmt->execute([$p['key_code']]);
        if (!$stmt->fetch()) {
            $ins = $db->prepare("INSERT INTO permissions (module, action, name, key_code) VALUES (?, ?, ?, ?)");
            $ins->execute([$p['module'], $p['action'], $p['name'], $p['key_code']]);
            $permId = $db->lastInsertId();
            echo "Added permission: {$p['key_code']}\n";

            // Give permission to super admin role (role id 1) if exists
            $db->exec("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (1, $permId)");
        }
    }
}

echo "Migration completed successfully!\n";
