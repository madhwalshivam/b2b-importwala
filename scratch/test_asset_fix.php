<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Helpers/Functions.php';

$testPath1 = '/uploads/dfix/1785595009_6a6e0481eaa0f.png';
$testPath2 = 'uploads/dfix/1785595009_6a6e0481eaa0f.png';
$testPath3 = 'http://localhost/ecommerce/uploads/dfix/1785595009_6a6e0481eaa0f.png';

echo "Path 1 asset(): " . asset($testPath1) . "\n";
echo "Path 2 asset(): " . asset($testPath2) . "\n";
echo "Path 3 asset(): " . asset($testPath3) . "\n";
