-- ImportWale AI Visual Search Embeddings Schema
CREATE TABLE IF NOT EXISTS `product_image_embeddings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `embedding_vector` LONGTEXT NOT NULL,
  `dhash` VARCHAR(64) NULL,
  `generated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_prod_img` (`product_id`, `image_path`),
  INDEX `idx_pve_product` (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
