<?php

/**
 * Shared bootstrap for embedding CLI / cron scripts.
 */
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/app/Helpers/Functions.php';
if (file_exists(ROOT_PATH . '/app/Core/EnvLoader.php')) {
    require_once ROOT_PATH . '/app/Core/EnvLoader.php';
    \App\Core\EnvLoader::load(ROOT_PATH);
}

spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

if (PHP_SAPI !== 'cli') {
    $key = (string) ($_GET['key'] ?? '');
    $expected = (string) (getenv('EMBEDDING_CRON_KEY') ?: '');
    if ($expected === '' || !hash_equals($expected, $key)) {
        http_response_code(403);
        header('Content-Type: text/plain');
        echo "Forbidden\n";
        exit(1);
    }
    header('Content-Type: text/plain; charset=utf-8');
}

@set_time_limit(0);
@ini_set('memory_limit', '512M');
date_default_timezone_set('Asia/Kolkata');
