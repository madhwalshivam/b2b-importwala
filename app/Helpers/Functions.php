<?php

if (!function_exists('sanitize_input')) {
    function sanitize_input(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map('sanitize_input', $data);
        }
        return htmlspecialchars(trim((string) $data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . $scriptDir;
            return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        }
        $baseUrl = rtrim($GLOBALS['app_config']['url'] ?? 'http://localhost/importwala/public', '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $path = trim($path);
        if (empty($path))
            return '';

        // External/Absolute URLs (http://, https://, //)
        if (preg_match('/^(https?:)?\/\//i', $path)) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');

        // If path already starts with uploads/ or assets/
        if (str_starts_with($cleanPath, 'uploads/') || str_starts_with($cleanPath, 'assets/')) {
            return url($cleanPath);
        }

        return url('assets/' . $cleanPath);
    }
}

if (!function_exists('format_price')) {
    function format_price(float|int|string|null $amount): string
    {
        $num = (float) ($amount ?? 0);
        return '₹' . number_format($num, 2);
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'n-a' : $text;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return (new \App\Core\Session())->generateCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('activity_log')) {
    function activity_log(string $action, string $module, ?int $recordId = null, ?string $details = null): void
    {
        try {
            $db = \App\Core\Database::getInstance();
            $admin = \App\Core\Auth::user();
            $stmt = $db->prepare("
                INSERT INTO activity_logs (admin_user_id, admin_name, action, module, record_id, details, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $admin['id'] ?? null,
                $admin['name'] ?? 'System',
                $action,
                $module,
                $recordId,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
        } catch (\Throwable $e) {
            // Silence logging errors to prevent breaking main application workflows
        }
    }
}

if (!function_exists('render_star_rating')) {
    /**
     * Render crisp, responsive inline SVG star ratings with linearGradient partial fill support.
     *
     * @param float|int|string|null $rating Numeric rating value (e.g. 4.3, 4.5, 4.8)
     * @param string $sizeClass Tailwind sizing classes (default: 'w-4 h-4')
     * @param bool $showNumeric Whether to display numeric rating next to stars
     * @return string Valid SVG HTML string
     */
    function render_star_rating(float|int|string|null $rating, string $sizeClass = 'w-4 h-4', bool $showNumeric = false): string
    {
        $numRating = max(0, min(5, (float) ($rating ?? 0)));
        $html = '<div class="inline-flex items-center gap-0.5" title="' . number_format($numRating, 1) . ' out of 5 stars">';

        for ($i = 1; $i <= 5; $i++) {
            $fraction = max(0, min(1, $numRating - ($i - 1)));

            if ($fraction >= 0.95) {
                // Full Star (Solid Amber Fill)
                $html .= '<svg class="' . $sizeClass . ' text-amber-500 shrink-0 inline-block" viewBox="0 0 24 24" fill="#F59E0B" stroke="#F59E0B" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
            } elseif ($fraction <= 0.05) {
                // Empty Star (Light Gray Fill / Outline)
                $html .= '<svg class="' . $sizeClass . ' text-gray-300 shrink-0 inline-block" viewBox="0 0 24 24" fill="#E5E7EB" stroke="#D1D5DB" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
            } else {
                // Partial / Fractional Star (SVG linearGradient)
                $pct = round($fraction * 100);
                $gradId = 'stargrad_' . mt_rand(10000, 999999);
                $html .= '<svg class="' . $sizeClass . ' shrink-0 inline-block" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="1.5">'
                    . '<defs><linearGradient id="' . $gradId . '"><stop offset="' . $pct . '%" stop-color="#F59E0B"/><stop offset="' . $pct . '%" stop-color="#E5E7EB"/></linearGradient></defs>'
                    . '<path fill="url(#' . $gradId . ')" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
            }
        }

        if ($showNumeric) {
            $html .= '<span class="ml-1.5 font-semibold text-gray-900 text-xs">' . number_format($numRating, 1) . '</span>';
        }

        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('encrypt_secret')) {
    function encrypt_secret(?string $plainText): string {
        return \App\Helpers\Encryption::encrypt($plainText);
    }
}

if (!function_exists('decrypt_secret')) {
    function decrypt_secret(?string $encryptedText): string {
        return \App\Helpers\Encryption::decrypt($encryptedText);
    }
}

if (!function_exists('mask_secret')) {
    function mask_secret(?string $plainText): string {
        return \App\Helpers\Encryption::maskSecret($plainText);
    }
}

if (!function_exists('admin_audit_log')) {
    function admin_audit_log(string $action, ?string $details = null): void {
        try {
            $db = \App\Core\Database::getInstance();
            $admin = \App\Core\Auth::user();
            $stmt = $db->prepare("
                INSERT INTO admin_audit_log (admin_id, admin_email, action, details, ip_address)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $admin['id'] ?? null,
                $admin['email'] ?? ($admin['username'] ?? 'admin@mudsor.com'),
                $action,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
        } catch (\Throwable $e) {
            error_log("Audit log failure: " . $e->getMessage());
        }
    }
}

if (!function_exists('check_api_rate_limit')) {
    function check_api_rate_limit(string $key = 'api', int $maxRequests = 60, int $decaySeconds = 60): bool {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $now = time();
        $rateKey = 'rate_limit_' . $key;
        if (!isset($_SESSION[$rateKey])) {
            $_SESSION[$rateKey] = ['count' => 1, 'reset_at' => $now + $decaySeconds];
            return true;
        }
        if ($now > $_SESSION[$rateKey]['reset_at']) {
            $_SESSION[$rateKey] = ['count' => 1, 'reset_at' => $now + $decaySeconds];
            return true;
        }
        $_SESSION[$rateKey]['count']++;
        return $_SESSION[$rateKey]['count'] <= $maxRequests;
    }
}


