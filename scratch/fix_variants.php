<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    $sql = "
        UPDATE product_variants pv
        JOIN product_colors pc ON pv.product_id = pc.product_id 
            AND (
                (pv.attribute_label = 'Color' AND pv.attribute_value = pc.color_name)
                OR 
                (pv.attribute_label = 'Color / Size' AND pv.attribute_value LIKE CONCAT(pc.color_name, ' - %'))
            )
        SET pv.image_url = pc.swatch_hex_or_image
        WHERE pv.image_url LIKE '%bulkflowai%'
    ";
    $affected = $db->exec($sql);
    echo "Updated variants from product_colors: $affected\n";

    // Delete orphaned variants
    $del = $db->exec("DELETE pv FROM product_variants pv LEFT JOIN products p ON pv.product_id = p.id WHERE p.id IS NULL");
    echo "Deleted orphaned variants: $del\n";
    
    $c = $db->query("SELECT COUNT(*) FROM product_variants WHERE image_url LIKE '%bulkflowai%'")->fetchColumn();
    echo "Remaining bulkflow variants: $c\n";
} catch (Exception $e) {
    echo $e->getMessage();
}
