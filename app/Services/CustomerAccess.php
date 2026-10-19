<?php

namespace App\Services;

class CustomerAccess
{
    public static function loggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function profileComplete(): bool
    {
        if (!self::loggedIn()) {
            return false;
        }
        if (array_key_exists('profile_completed', $_SESSION)) {
            return (int)$_SESSION['profile_completed'] === 1;
        }
        $user = CustomerAuthService::findById($_SESSION['user_id']);
        $ok = $user && (int)($user['profile_completed'] ?? 0) === 1;
        $_SESSION['profile_completed'] = $ok ? 1 : 0;
        return $ok;
    }

    public static function requireCommerce(string $returnPath, string $action): void
    {
        $returnPath = CustomerAuthService::safeReturn($returnPath);
        if (!self::loggedIn()) {
            $_SESSION['auth_return'] = $returnPath;
            $_SESSION['pending_action'] = $action;
            if (self::wantsJson()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'code' => 'login_required',
                    'message' => 'Please log in to continue.',
                    'return' => $returnPath,
                ]);
                exit;
            }
            http_response_code(401);
            $error = 'Please log in to continue.';
            $returnUrl = $returnPath;
            $googleEnabled = CustomerAuthService::googleEnabled();
            $googleClientId = CustomerAuthService::clientId();
            include dirname(__DIR__, 2) . '/views/web/login.php';
            exit;
        }

        if (!self::profileComplete()) {
            $_SESSION['auth_return'] = $returnPath;
            $_SESSION['pending_action'] = $action;
            if (self::wantsJson()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'code' => 'profile_incomplete',
                    'message' => 'Add your mobile number before continuing.',
                    'redirect' => url('complete-profile?return=' . rawurlencode($returnPath)),
                ]);
                exit;
            }
            header('Location: ' . url('complete-profile?return=' . rawurlencode($returnPath)));
            exit;
        }
    }

    public static function blockIncompleteCart(): bool
    {
        if (!self::loggedIn() || self::profileComplete()) {
            return false;
        }
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'code' => 'profile_incomplete',
            'message' => 'Add your mobile number before adding items to the cart.',
            'redirect' => url('complete-profile'),
        ]);
        return true;
    }

    private static function wantsJson(): bool
    {
        $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
        if (str_contains($uri, '/api/')) {
            return true;
        }
        $accept = (string)($_SERVER['HTTP_ACCEPT'] ?? '');
        $xhr = (string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        if (stripos($xhr, 'xmlhttprequest') !== false) {
            return true;
        }
        return stripos($accept, 'application/json') !== false;
    }
}
