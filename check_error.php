<?php
require 'vendor/autoload.php';
require 'app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(__DIR__);
spl_autoload_register(function ($c) {
    if (str_starts_with($c, 'App\\')) require 'app/' . str_replace('\\', '/', substr($c, 4)) . '.php';
});
$db = \App\Core\Database::getInstance();
$err = $db->query("SELECT source_url, last_error FROM image_mirror_queue WHERE status = 'failed' LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($err);
