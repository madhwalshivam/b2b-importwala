<?php
define('ROOT_PATH', __DIR__ . '/..');
$app_config = require __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Helpers/functions.php';

// Simulate Apache request environment with root .htaccess rewrite
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/importwala/public/index.php';

echo "Clean URL Test 1 (admin/login): " . url('admin/login') . "\n";
echo "Clean URL Test 2 (admin/dashboard): " . url('admin/dashboard') . "\n";
echo "Clean URL Test 3 (assets/images/importwale-logo.png): " . url('assets/images/importwale-logo.png') . "\n";
