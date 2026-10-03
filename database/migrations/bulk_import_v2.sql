-- ============================================================
-- Migration: bulk_import_v2
-- Adds normalized_title, source_product_id indexes, and cleans
-- any orphan variant rows from deleted products.
-- Run ONCE on the live/local database before deploying the new
-- BulkImportService.
-- ============================================================

-- 1. Add normalized_title column to products (if missing)
ALTER TABLE `products`
    ADD COLUMN IF NOT EXISTS `normalized_title` VARCHAR(512) DEFAULT NULL
        COMMENT 'lowercase, punctuation-stripped title used for duplicate detection';

-- 2. Index for fast duplicate lookups
-- (category_id, subcategory_id, normalized_title)
CREATE INDEX IF NOT EXISTS `idx_prod_norm_title`
    ON `products` (`category_id`, `subcategory_id`, `normalized_title`(128));

-- 3. Index on (source_product_id, source_platform)
CREATE INDEX IF NOT EXISTS `idx_prod_source`
    ON `products` (`source_product_id`, `source_platform`);

-- 4. Backfill normalized_title for all existing products
--    normalized = lowercase, trim, collapse spaces, strip punctuation
UPDATE `products`
SET `normalized_title` = LOWER(
    REGEXP_REPLACE(
        REGEXP_REPLACE(TRIM(`name`), '[^a-z0-9 ]', ''),
        ' {2,}', ' '
    )
)
WHERE `normalized_title` IS NULL OR `normalized_title` = '';

-- 5. Clean orphan product_variants whose product was deleted
--    (product_variants already has ON DELETE CASCADE via FK, but
--     run an explicit cleanup in case any slipped through)
DELETE pv FROM `product_variants` pv
LEFT JOIN `products` p ON p.id = pv.product_id
WHERE p.id IS NULL;

-- 6. Clean orphan product_colors
DELETE pc FROM `product_colors` pc
LEFT JOIN `products` p ON p.id = pc.product_id
WHERE p.id IS NULL;

-- 7. Clean orphan product_color_sizes
DELETE pcs FROM `product_color_sizes` pcs
LEFT JOIN `product_colors` pc ON pc.id = pcs.color_id
WHERE pc.id IS NULL;

-- Done.
SELECT 'bulk_import_v2 migration completed' AS status;
