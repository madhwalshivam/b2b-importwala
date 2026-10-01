<?php
require 'vendor/autoload.php';
require 'app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(__DIR__);
spl_autoload_register(function ($c) {
    if (str_starts_with($c, 'App\\')) require 'app/' . str_replace('\\', '/', substr($c, 4)) . '.php';
});
$db = \App\Core\Database::getInstance();
$db->query("UPDATE image_mirror_queue SET status='pending', attempts=0 WHERE status='failed'");
echo "Queue reset successfully!\n";
