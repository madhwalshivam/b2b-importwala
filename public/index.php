<?php
/**
 * Mudsor Custom B2C E-Commerce CMS Platform
 * Standard Native PHP MVC Bootstrapper
 */

// Define Root Directory Constant
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Set Default Timezone
date_default_timezone_set('Asia/Kolkata');

// Error & Exception Logging Configuration
define('APP_ENV', getenv('APP_ENV') ?: 'production');

if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
}

ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/logs/error.log');

// Global Exception Handler
set_exception_handler(function (\Throwable $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());
    
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['status' => 'error', 'message' => 'An unexpected server error occurred. Please try again.']);
    } else {
        http_response_code(500);
        if (APP_ENV === 'development') {
            echo "<h1>Application Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            echo '<div style="font-family:sans-serif; text-align:center; padding:60px; color:#333;"><h2 style="font-size:24px; font-weight:bold;">Something went wrong</h2><p style="color:#666;">We are experiencing a brief system issue. Please refresh or return to the homepage.</p><a href="/ecommerce/" style="display:inline-block; margin-top:15px; padding:10px 20px; background:#A8111C; color:#fff; text-decoration:none; border-radius:8px; font-weight:bold;">Return to Homepage</a></div>';
        }
    }
    exit;
});

// Composer Autoload
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Autoload Classes
spl_autoload_register(function ($class) {
    if (strncmp('App\\', $class, 4) === 0) {
        $file = __DIR__ . '/../app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
    if (strncmp('Lib\\', $class, 4) === 0) {
        $relPath = str_replace('\\', '/', substr($class, 4));
        $file = __DIR__ . '/../lib/' . $relPath . '.php';
        if (!file_exists($file)) {
            $parts = explode('/', $relPath);
            if (count($parts) > 1) {
                $parts[0] = strtolower($parts[0]);
                $file = __DIR__ . '/../lib/' . implode('/', $parts) . '.php';
            }
        }
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});


// Require Helper Functions
require_once __DIR__ . '/../app/Helpers/Functions.php';

// Initialize Application
use App\Core\Application;

$app = new Application();
$router = $app->router;

// Load Routes
require_once __DIR__ . '/../routes/web.php';


// Dispatch Application
$app->run();
