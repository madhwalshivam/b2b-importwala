<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database.php';

session_start();

$_SESSION['user_id'] = 1;
$_SERVER['REQUEST_METHOD'] = 'POST';

$data = json_encode([
    'variation_mode' => 'double',
    'colors' => [
        [
            'id'         => 1,
            'color_name' => 'Test',
            'sizes'      => [
                [
                    'id'         => 1,
                    'size_label' => 'Test size',
                    'price'      => 99.99,
                    'stock_qty'  => 10,
                ],
            ],
        ],
    ],
]);

// Note: php://input cannot be mocked by file_put_contents.
// Use a curl request or a custom input wrapper for proper testing.
// $GLOBALS['mock_input'] = $data; // Alternative approach

echo "Test data prepared: " . $data . PHP_EOL;
