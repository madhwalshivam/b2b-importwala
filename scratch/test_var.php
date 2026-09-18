<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Model.php';
require __DIR__ . '/../app/Models/ProductColor.php';
require __DIR__ . '/../app/Models/ProductColorSize.php';
require __DIR__ . '/../app/Models/Product.php';
require __DIR__ . '/../app/Services/VariationService.php';

$db = \App\Core\Database::getInstance();
$vs = new \App\Services\VariationService();

try {
    $vs->saveNestedVariations(510, 'double', [
        [
            'id'         => 0,
            'color_name' => 'Test Color',
            'color_code' => '#ff0000',
            'sizes'      => [
                [
                    'id'         => 0,
                    'size_label' => 'M',
                    'price'      => 15.5,
                ]
            ],
        ]
    ]);
    echo "Success\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

