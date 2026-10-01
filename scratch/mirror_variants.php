<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    $rows = $db->query("SELECT pv.id, pv.product_id, pv.attribute_label, pv.attribute_value, pv.image_url, p.sku FROM product_variants pv JOIN products p ON pv.product_id = p.id WHERE pv.image_url LIKE '%bulkflowai%'")->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($rows) . " variants to mirror.\n";

    if (count($rows) > 0) {
        $mirror = new \App\Services\ImageMirrorService();
        $success = 0;
        foreach ($rows as $r) {
            echo "Mirroring variant ID {$r['id']} ({$r['sku']}) ... ";
            $res = $mirror->mirrorImage($r['image_url'], $r['sku'], 'variant');
            if ($res['success']) {
                $newUrl = $res['r2_url'] ?? $res['local_url'];
                $db->prepare("UPDATE product_variants SET image_url = ? WHERE id = ?")->execute([$newUrl, $r['id']]);
                echo "OK: $newUrl\n";
                $success++;
            } else {
                echo "FAIL: " . ($res['message'] ?? 'Unknown error') . "\n";
            }
        }
        echo "Successfully mirrored $success variants.\n";
    }

} catch (Exception $e) {
    echo $e->getMessage();
}
