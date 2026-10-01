<?php
/**
 * ImportWale Custom E-Commerce CMS Platform
 * Standard Native PHP MVC Bootstrapper
 */

// Define Root Directory Constant
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Set Default Timezone
date_default_timezone_set('Asia/Kolkata');

// Load environment variables early
require_once __DIR__ . '/../app/Core/EnvLoader.php';
\App\Core\EnvLoader::load(ROOT_PATH);

// Require Helper Functions so env() is available
require_once __DIR__ . '/../app/Helpers/Functions.php';

// Error & Exception Logging Configuration
$appDebug = env('APP_DEBUG', false);
$appEnv = env('APP_ENV', 'production');

if ($appEnv === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
} elseif ($appDebug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
}

$logDir = __DIR__ . '/../storage/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/app.log');

// Global Exception Handler
set_exception_handler(function (\Throwable $e) {
    $requestUrl = $_SERVER['REQUEST_URI'] ?? 'CLI';
    error_log("[" . date('Y-m-d H:i:s') . "] Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\nURL: " . $requestUrl . "\n" . $e->getTraceAsString());
    
    if (env('APP_DEBUG', false) === true) {
        http_response_code(500);
        echo "<div style='font-family:monospace; padding:30px; background:#fff0f0; border:1px solid #f5c6cb; color:#721c24; margin:20px; border-radius:8px;'>";
        echo "<h2 style='margin-top:0;'>Application Error:</h2>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line " . $e->getLine() . ")</p>";
        echo "<h3>Trace:</h3><pre style='background:#fff; padding:15px; border-radius:4px; overflow:auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
        exit;
    }

    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['status' => 'error', 'message' => 'An unexpected server error occurred. Please try again.']);
    } else {
        http_response_code(500);
        echo '<div style="font-family:sans-serif; text-align:center; padding:60px; color:#333;"><h2 style="font-size:24px; font-weight:bold;">Something went wrong</h2><p style="color:#666;">We are experiencing a brief system issue. Please refresh or return to the homepage.</p><a href="/" style="display:inline-block; margin-top:15px; padding:10px 20px; background:#f05a29; color:#fff; text-decoration:none; border-radius:8px; font-weight:bold;">Return to Homepage</a></div>';
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


// Initialize Application
use App\Core\Application;

$app = new Application();
$router = $app->router;

// Load Routes
require_once __DIR__ . '/../routes/web.php';


// Dispatch Application
$app->run();
