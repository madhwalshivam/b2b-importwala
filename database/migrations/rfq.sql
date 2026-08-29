-- =============================================================
-- RFQ (Request For Quote) Feature - Database Migration
-- importwala.com
-- Run this SQL in your database before using the RFQ feature
-- =============================================================

CREATE TABLE IF NOT EXISTS `rfq_requests` (
  `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

  -- Step 1: Product Details
  `product_name`         VARCHAR(255)      NOT NULL,
  `product_reference_link` VARCHAR(1000)   DEFAULT NULL,
  `quantity`             INT UNSIGNED      NOT NULL,
  `unit`                 VARCHAR(50)       NOT NULL,
  `target_price`         DECIMAL(12,2)     NOT NULL DEFAULT 0.00,
  `overall_budget`       VARCHAR(100)      NOT NULL,
  `sourcing_purpose`     VARCHAR(100)      NOT NULL,
  `specifications`       TEXT              DEFAULT NULL,

  -- Step 2: Contact Details
  `full_name`            VARCHAR(150)      NOT NULL,
  `phone`                VARCHAR(15)       NOT NULL,
  `email`                VARCHAR(255)      NOT NULL,
  `pincode`              CHAR(6)           NOT NULL,

  -- Step 3: Business Details
  `business_type`        VARCHAR(100)      NOT NULL,
  `has_gst`              TINYINT(1)        NOT NULL DEFAULT 0,
  `additional_comments`  TEXT              DEFAULT NULL,

  -- Admin / System
  `status`               ENUM('New','Contacted','Quoted','Closed') NOT NULL DEFAULT 'New',
  `admin_notes`          TEXT              DEFAULT NULL,
  `created_at`           DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           DATETIME          DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

  INDEX `idx_status`     (`status`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_phone`      (`phone`),
  INDEX `idx_email`      (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `rfq_reference_photos` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `rfq_id`        INT UNSIGNED  NOT NULL,
  `file_path`     VARCHAR(500)  NOT NULL,
  `original_name` VARCHAR(255)  DEFAULT NULL,
  `file_size`     INT UNSIGNED  DEFAULT NULL COMMENT 'in bytes',
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

  INDEX `idx_rfq_id` (`rfq_id`),
  FOREIGN KEY (`rfq_id`) REFERENCES `rfq_requests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
