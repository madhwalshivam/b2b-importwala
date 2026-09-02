<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Core/Database.php';

$db = App\Core\Database::getInstance();

echo "Re-running Migration & Seeding for Indian Customer Testimonials...\n";

// 1. Create testimonials table if not exists
$sql = "CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reviewer_name` VARCHAR(150) NOT NULL,
  `location` VARCHAR(150) NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `review_text` TEXT NOT NULL,
  `photo_path` VARCHAR(255) NULL,
  `avatar_color` VARCHAR(20) DEFAULT '#2D3748',
  `product_id` INT UNSIGNED NULL,
  `display_order` INT NOT NULL DEFAULT 0,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 1,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_status_featured` (`status`, `is_featured`),
  INDEX `idx_display_order` (`display_order`),
  INDEX `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$db->exec($sql);

// Truncate existing and insert fresh Indian buyer reviews
$db->exec("TRUNCATE TABLE `testimonials`");

$indianReviews = [
    [
        'reviewer_name' => 'Rajesh Sharma',
        'location'      => 'Delhi, India',
        'rating'        => 5,
        'review_text'   => 'Bulk ordering wholesale gift items for our festival corporate gifting was seamless. Quality exceeded expectations and delivery was within 3 days. Extremely satisfied!',
        'photo_path'    => null,
        'avatar_color'  => '#f05a29',
        'display_order' => 1,
        'is_featured'   => 1,
        'status'        => 'active'
    ],
    [
        'reviewer_name' => 'Ankit Verma',
        'location'      => 'Surat, Gujarat',
        'rating'        => 5,
        'review_text'   => 'Sourced 500+ customized promotional pens & keychains. Direct factory prices saved us over 35% compared to local distributors. Will reorder again!',
        'photo_path'    => null,
        'avatar_color'  => '#1E293B',
        'display_order' => 2,
        'is_featured'   => 1,
        'status'        => 'active'
    ],
    [
        'reviewer_name' => 'Priya Patel',
        'location'      => 'Ahmedabad, Gujarat',
        'rating'        => 5,
        'review_text'   => 'Excellent quality laser-engraving blanks and acrylic items. Packaging was very safe and customer support answered all our inquiries instantly.',
        'photo_path'    => null,
        'avatar_color'  => '#0F766E',
        'display_order' => 3,
        'is_featured'   => 1,
        'status'        => 'active'
    ],
    [
        'reviewer_name' => 'Vikram Singh',
        'location'      => 'Jaipur, Rajasthan',
        'rating'        => 5,
        'review_text'   => 'First time ordering wholesale products online. Product quality matches photos 100%. Very pleased with ImportWale B2B pricing and service!',
        'photo_path'    => null,
        'avatar_color'  => '#312E81',
        'display_order' => 4,
        'is_featured'   => 1,
        'status'        => 'active'
    ],
    [
        'reviewer_name' => 'Sneha Kulkarni',
        'location'      => 'Mumbai, Maharashtra',
        'rating'        => 5,
        'review_text'   => 'ImportWale has made global wholesale sourcing effortless for our retail store chain. Super fast delivery and top-notch product quality!',
        'photo_path'    => null,
        'avatar_color'  => '#881337',
        'display_order' => 5,
        'is_featured'   => 1,
        'status'        => 'active'
    ],
    [
        'reviewer_name' => 'Deepak Agarwal',
        'location'      => 'Bengaluru, Karnataka',
        'rating'        => 5,
        'review_text'   => 'Great B2B pricing on bulk electronics accessories & gadgets. Custom quotation process was very fast and smooth. Highly recommended!',
        'photo_path'    => null,
        'avatar_color'  => '#2C3E50',
        'display_order' => 6,
        'is_featured'   => 1,
        'status'        => 'active'
    ],
];

$stmt = $db->prepare("INSERT INTO `testimonials` 
    (`reviewer_name`, `location`, `rating`, `review_text`, `photo_path`, `avatar_color`, `display_order`, `is_featured`, `status`) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($indianReviews as $item) {
    $stmt->execute([
        $item['reviewer_name'],
        $item['location'],
        $item['rating'],
        $item['review_text'],
        $item['photo_path'],
        $item['avatar_color'],
        $item['display_order'],
        $item['is_featured'],
        $item['status']
    ]);
}

echo "Successfully seeded " . count($indianReviews) . " Indian customer reviews!\n";
