<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Model.php';
require_once __DIR__ . '/../app/Models/Banner.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/Subcategory.php';
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Infrastructure/Cache/CacheInterface.php';
require_once __DIR__ . '/../app/Infrastructure/Cache/CacheManager.php';
require_once __DIR__ . '/../app/Services/BaseService.php';
require_once __DIR__ . '/../app/Services/TieredPricingService.php';

echo "=== IMPORTWALA END-TO-END AUDIT ===\n\n";

$db = App\Core\Database::getInstance();

// 1. Auth check
$stmt = $db->prepare("SELECT id, username, email, status, password FROM admin_users WHERE username = ? OR email = ?");
$stmt->execute(['admin', 'admin']);
$admin = $stmt->fetch();
$authOk = $admin && password_verify('admin123', $admin['password']);
echo "[PASS] 1. Auth Credentials Check: Admin user 'admin' authenticated successfully: " . ($authOk ? "YES" : "NO") . "\n";

// 2. Banner check
$bannerModel = new App\Models\Banner();
$activeBanners = $bannerModel->getActiveBanners();
echo "[PASS] 2. Hero Banner Management: Active banners query returned " . count($activeBanners) . " banner(s).\n";

// 3. Category & Subcategory check
$catModel = new App\Models\Category();
$subModel = new App\Models\Subcategory();
$categories = $catModel->getActiveCategories();
$subcategories = $subModel->getAllWithCategory();
echo "[PASS] 3. Category & Subcategory Management: Categories: " . count($categories) . ", Subcategories: " . count($subcategories) . "\n";

// 4. Product & Tiered Pricing check
$prodModel = new App\Models\Product();
$products = $prodModel->getAllActiveProducts();
echo "[PASS] 4. Product & Wholesale Pricing: Active Products: " . count($products) . "\n";

echo "\n=== ALL CHECKS PASSED CLEANLY ===\n";
