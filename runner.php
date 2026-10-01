<?php
define('ROOT_PATH', __DIR__);
require_once __DIR__ . '/app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_PATH);
require_once __DIR__ . '/app/Helpers/Functions.php';
if (isset($argv[1])) {
    echo "Running " . $argv[1] . "...\n";
    require_once $argv[1];
} else {
    echo "No file provided";
}
