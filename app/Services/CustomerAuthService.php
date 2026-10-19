<?php

namespace App\Services;

use App\Core\Database;
use App\Services\TokenService;

foreach ([
    'AuthSchema.php',
    'PhoneNumber.php',
    'RateLimiter.php',
    'PhoneOtpService.php',
    'GoogleIdTokenVerifier.php',
    'ProductHistoryService.php',
    'Sms/SmsSenderInterface.php',
    'Sms/LogSmsSender.php',
] as $authDep) {
    $authDepPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $authDep);
    if (is_file($authDepPath)) {
        require_once $authDepPath;
    }
}

class CustomerAuthService
{
    public static function googleEnabled(): bool
    {
        return (string)\App\Models\Setting::get('google_login_enabled', '1') !== '0';
    }

    public static function clientId(): string
    {
        return trim((string)env('GOOGLE_CLIENT_ID', ''));
    }

    public static function findById($id): ?array
    {
        if ($id === null || $id === '') {
            return null;
        }
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([(string)$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            try {
                $stmt = $db->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
                $stmt->execute([(string)$id]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            } catch (\Throwable $e) {
                $row = null;
            }
        }
        return $row ? self::present($row) : null;
    }

    public static function normalizeGstNumber(string $raw): array
    {
        $value = strtoupper(trim($raw));
        $value = preg_replace('/\s+/', '', $value) ?? '';
        if ($value === '') {
            return ['ok' => true, 'value' => null];
        }
        if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $value)) {
            return ['ok' => false, 'error' => 'Enter a valid 15-character GSTIN (for example 22AAAAA0000A1Z5), or leave GST Number blank.'];
        }
        return ['ok' => true, 'value' => $value];
    }

    public static function registerWithPassword(string $name, string $email, string $phone, string $password, bool $consent, string $gstRaw = ''): array
    {
        self::boot();
        AuthSchema::ensure();
        self::repairBrokenUserRows();
        $name = trim($name);
        $email = strtolower(trim($email));
        $phone = PhoneNumber::normalize($phone);
        if ($name === '' || $email === '' || $password === '' || $phone === null) {
            return ['ok' => false, 'error' => 'Full name, email, mobile number and password are required.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Enter a valid email address.'];
        }
        if (strlen($password) < 6) {
            return ['ok' => false, 'error' => 'Password must be at least 6 characters long.'];
        }
        if (!$consent) {
            return ['ok' => false, 'error' => 'Please agree to be contacted about your inquiries and orders.'];
        }
        $gst = self::normalizeGstNumber($gstRaw);
        if (empty($gst['ok'])) {
            return ['ok' => false, 'error' => $gst['error']];
        }
        if (self::emailTaken($email)) {
            return ['ok' => false, 'error' => 'An account with this email already exists.'];
        }
        if (self::phoneTaken($phone)) {
            return ['ok' => false, 'error' => 'This mobile number is already registered with another account.'];
        }

        $otp = self::otpOn();
        try {
            $userId = self::insertUser([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'auth_provider' => 'email',
                'email_verified' => 0,
                'phone_verified' => 0,
                'profile_completed' => $otp ? 0 : 1,
                'contact_consent' => 1,
                'consent_at' => date('Y-m-d H:i:s'),
                'signup_source' => 'email',
                'company_name' => null,
                'gst_number' => $gst['value'],
                'google_id' => null,
            ]);
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => self::signupInsertError($e)];
        }

        $user = self::findById($userId);
        if (!$user) {
            return ['ok' => false, 'error' => 'Account was created but we could not sign you in. Please log in.'];
        }
        self::loginUser($user);
        if ($otp) {
            $sent = PhoneOtpService::send($phone);
            $_SESSION['otp_pending'] = 1;
            if (empty($sent['ok'])) {
                return ['ok' => true, 'user' => $user, 'needs_profile' => true, 'otp_error' => $sent['error'] ?? 'Could not send the code.'];
            }
            return ['ok' => true, 'user' => $user, 'needs_profile' => true, 'debug_code' => $sent['debug_code'] ?? null];
        }
        return ['ok' => true, 'user' => $user, 'needs_profile' => false];
    }

    public static function attemptPassword(string $email, string $password): array
    {
        self::boot();
        AuthSchema::ensure();
        $email = strtolower(trim($email));
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '0');
        $bucket = 'login:' . hash('sha256', $ip . '|' . $email);
        if (class_exists(RateLimiter::class) && RateLimiter::tooMany($bucket, 8, 900)) {
            return ['ok' => false, 'error' => 'Too many login attempts. Please wait 15 minutes and try again.'];
        }
        if ($email === '' || $password === '') {
            return ['ok' => false, 'error' => 'Please fill in all fields.'];
        }

        $user = self::findByEmail($email);
        $hash = $user['password'] ?? $user['password_hash'] ?? '';
        if (!$user || $hash === '' || !password_verify($password, $hash)) {
            TokenService::logAuth('failed_login', null, 'customer', 'Failed customer login');
            return ['ok' => false, 'error' => 'Invalid email address or password.'];
        }
        if (($user['status'] ?? 'active') === 'suspended') {
            return ['ok' => false, 'error' => 'This account has been suspended. Contact support.'];
        }

        self::loginUser($user);
        return ['ok' => true, 'user' => self::findById($user['id'])];
    }

    public static function loginWithGoogle(string $idToken): array
    {
        self::boot();
        AuthSchema::ensure();
        if (!self::googleEnabled()) {
            return ['ok' => false, 'error' => 'Google sign-in is turned off.'];
        }
        $clientId = self::clientId();
        if ($clientId === '') {
            return ['ok' => false, 'error' => 'Google sign-in is not configured yet.'];
        }

        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '0');
        if (class_exists(RateLimiter::class) && RateLimiter::tooMany('google:' . hash('sha256', $ip), 20, 900)) {
            return ['ok' => false, 'error' => 'Too many Google sign-in attempts. Please wait and try again.'];
        }

        $verified = GoogleIdTokenVerifier::verify($idToken, $clientId);
        if (empty($verified['ok'])) {
            return ['ok' => false, 'error' => $verified['error'] ?? 'Google sign-in token is invalid.'];
        }
        if ($verified['sub'] === '') {
            return ['ok' => false, 'error' => 'Google sign-in token is invalid.'];
        }

        $db = Database::getInstance();
        $byGoogle = $db->prepare("SELECT * FROM users WHERE google_id = ? LIMIT 1");
        $byGoogle->execute([$verified['sub']]);
        $user = $byGoogle->fetch(\PDO::FETCH_ASSOC);

        if ($user) {
            self::syncGoogleEmail($user, $verified['email']);
            $user = self::findById($user['id']);
        } else {
            $emailStmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $emailStmt->execute([$verified['email']]);
            $existing = $emailStmt->fetch(\PDO::FETCH_ASSOC);
            if ($existing) {
                if (!empty($existing['google_id']) && $existing['google_id'] !== $verified['sub']) {
                    return ['ok' => false, 'error' => 'This email is already linked to a different Google account.'];
                }
                $provider = (($existing['auth_provider'] ?? 'email') === 'google') ? 'google' : 'both';
                $db->prepare("
                    UPDATE users
                    SET google_id = ?, auth_provider = ?, email_verified = 1
                    WHERE id = ?
                ")->execute([$verified['sub'], $provider, $existing['id']]);
                $user = self::findById($existing['id']);
            } else {
                $legacyPassword = password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);
                try {
                    $cust = $db->prepare("SELECT password FROM customers WHERE email = ? LIMIT 1");
                    $cust->execute([$verified['email']]);
                    $legacy = $cust->fetchColumn();
                    if (is_string($legacy) && $legacy !== '') {
                        $legacyPassword = $legacy;
                    }
                } catch (\Throwable $e) {
                }
                try {
                    $userId = self::insertUser([
                        'name' => $verified['name'],
                        'email' => $verified['email'],
                        'phone' => null,
                        'password' => $legacyPassword,
                        'auth_provider' => 'google',
                        'email_verified' => 1,
                        'phone_verified' => 0,
                        'profile_completed' => 0,
                        'contact_consent' => 0,
                        'consent_at' => null,
                        'signup_source' => 'google',
                        'company_name' => null,
                        'google_id' => $verified['sub'],
                    ]);
                    $user = self::findById($userId);
                } catch (\Throwable $e) {
                    error_log('Google signup insert failed: ' . $e->getMessage());
                    return ['ok' => false, 'error' => 'We could not create your account with Google. Please try again.'];
                }
            }
        }

        if (!$user) {
            return ['ok' => false, 'error' => 'We could not sign you in with Google. Please try again.'];
        }
        if (($user['status'] ?? 'active') === 'suspended') {
            return ['ok' => false, 'error' => 'This account has been suspended. Contact support.'];
        }

        self::loginUser($user);
        $fresh = self::findById($user['id']);
        return ['ok' => true, 'user' => $fresh, 'needs_profile' => (int)($fresh['profile_completed'] ?? 0) !== 1];
    }

    public static function completeProfile(array $user, string $phoneRaw, string $company, bool $consent, string $otp = '', string $gstRaw = ''): array
    {
        self::boot();
        AuthSchema::ensure();
        $phone = PhoneNumber::normalize($phoneRaw);
        if ($phone === null) {
            return ['ok' => false, 'error' => 'Enter a valid 10-digit Indian mobile number starting with 6, 7, 8 or 9.'];
        }
        if (!$consent) {
            return ['ok' => false, 'error' => 'Please agree to be contacted about your inquiries and orders.'];
        }
        if (self::phoneTaken($phone, $user['id'])) {
            return ['ok' => false, 'error' => 'This mobile number is already registered with another account.'];
        }
        $gst = self::normalizeGstNumber($gstRaw);
        if (empty($gst['ok'])) {
            return ['ok' => false, 'error' => $gst['error']];
        }

        $company = trim($company);
        $company = $company === '' ? null : mb_substr($company, 0, 150);
        $db = Database::getInstance();
        $otpOn = self::otpOn();
        $hasGst = self::has('users', 'gst_number');

        if ($otpOn && $otp === '') {
            if ($hasGst) {
                $db->prepare("
                    UPDATE users
                    SET phone = ?, company_name = ?, gst_number = ?, contact_consent = 1, consent_at = NOW(),
                        phone_verified = 0, profile_completed = 0
                    WHERE id = ?
                ")->execute([$phone, $company, $gst['value'], $user['id']]);
            } else {
                $db->prepare("
                    UPDATE users
                    SET phone = ?, company_name = ?, contact_consent = 1, consent_at = NOW(),
                        phone_verified = 0, profile_completed = 0
                    WHERE id = ?
                ")->execute([$phone, $company, $user['id']]);
            }
            $_SESSION['user_phone'] = $phone;
            $_SESSION['profile_completed'] = 0;
            $_SESSION['otp_pending'] = 1;
            $sent = PhoneOtpService::send($phone);
            if (empty($sent['ok'])) {
                return ['ok' => false, 'error' => $sent['error'] ?? 'Could not send the code.', 'otp_step' => true];
            }
            return ['ok' => true, 'otp_step' => true, 'debug_code' => $sent['debug_code'] ?? null];
        }

        if ($otpOn) {
            $check = PhoneOtpService::verify($phone, $otp);
            if (empty($check['ok'])) {
                return ['ok' => false, 'error' => $check['error'], 'otp_step' => true];
            }
        }

        if ($hasGst) {
            $db->prepare("
                UPDATE users
                SET phone = ?, company_name = ?, gst_number = ?, contact_consent = 1, consent_at = IFNULL(consent_at, NOW()),
                    phone_verified = ?, profile_completed = 1
                WHERE id = ?
            ")->execute([$phone, $company, $gst['value'], $otpOn ? 1 : (int)($user['phone_verified'] ?? 0), $user['id']]);
        } else {
            $db->prepare("
                UPDATE users
                SET phone = ?, company_name = ?, contact_consent = 1, consent_at = IFNULL(consent_at, NOW()),
                    phone_verified = ?, profile_completed = 1
                WHERE id = ?
            ")->execute([$phone, $company, $otpOn ? 1 : (int)($user['phone_verified'] ?? 0), $user['id']]);
        }

        $_SESSION['user_phone'] = $phone;
        $_SESSION['profile_completed'] = 1;
        unset($_SESSION['otp_pending']);
        return ['ok' => true, 'otp_step' => false];
    }

    public static function loginUser(array $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $previousSessionIds = array_values(array_unique(array_filter([
            session_id(),
            $_SESSION['cart_session_id'] ?? null,
        ])));
        session_regenerate_id(true);

        $presented = self::present($user);
        $_SESSION['user_id'] = $presented['id'];
        $_SESSION['user_name'] = $presented['name'];
        $_SESSION['user_email'] = $presented['email'];
        $_SESSION['user_phone'] = $presented['phone'] ?? null;
        $_SESSION['profile_completed'] = (int)($presented['profile_completed'] ?? 0) === 1 ? 1 : 0;

        try {
            TokenService::issueTokens($presented['id'], 'customer');
        } catch (\Throwable $e) {
        }

        $db = Database::getInstance();
        if (self::has('users', 'last_login_at')) {
            $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")->execute([$presented['id']]);
        }
        self::mergeGuestCart($presented['id'], $previousSessionIds);
        try {
            ProductHistoryService::mergeFromGuest($presented['id']);
        } catch (\Throwable $e) {
        }
    }

    public static function mergeGuestCart($userId, array $extraSessionIds = []): void
    {
        $db = Database::getInstance();
        $sessionIds = $extraSessionIds;
        if (function_exists('get_current_session_id')) {
            $sessionIds[] = get_current_session_id();
        }
        $sessionIds[] = session_id();
        $sessionIds = array_values(array_unique(array_filter($sessionIds)));
        $hasVariant = isset(AuthSchema::columns($db, 'cart_items')['variant_id']);
        try {
            $guest = $db->prepare("SELECT * FROM cart_items WHERE session_id = ? AND (user_id IS NULL OR user_id = 0)");
            $guestRows = [];
            foreach ($sessionIds as $sessionId) {
                $guest->execute([$sessionId]);
                foreach ($guest->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                    $guestRows[$row['id']] = $row;
                }
            }
        } catch (\Throwable $e) {
            return;
        }

        foreach ($guestRows as $row) {
            $variant = $hasVariant ? ($row['variant_id'] ?? null) : null;
            if (!$hasVariant) {
                $match = $db->prepare("
                    SELECT id, quantity FROM cart_items
                    WHERE user_id = ? AND product_id = ?
                    LIMIT 1
                ");
                $match->execute([$userId, $row['product_id']]);
            } elseif ($variant === null || $variant === '' || $variant === 0) {
                $match = $db->prepare("
                    SELECT id, quantity FROM cart_items
                    WHERE user_id = ? AND product_id = ? AND (variant_id IS NULL OR variant_id = 0)
                    LIMIT 1
                ");
                $match->execute([$userId, $row['product_id']]);
            } else {
                $match = $db->prepare("
                    SELECT id, quantity FROM cart_items
                    WHERE user_id = ? AND product_id = ? AND variant_id = ?
                    LIMIT 1
                ");
                $match->execute([$userId, $row['product_id'], $variant]);
            }
            $existing = $match->fetch(\PDO::FETCH_ASSOC);
            if ($existing) {
                $qty = (int)$existing['quantity'] + (int)$row['quantity'];
                $db->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?")->execute([$qty, $existing['id']]);
                $db->prepare("DELETE FROM cart_items WHERE id = ?")->execute([$row['id']]);
            } else {
                $db->prepare("UPDATE cart_items SET user_id = ? WHERE id = ?")->execute([$userId, $row['id']]);
            }
        }

        if (!empty($_SESSION['guest_wishlist']) && is_array($_SESSION['guest_wishlist'])) {
            foreach ($_SESSION['guest_wishlist'] as $gPid) {
                try {
                    $db->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)")
                        ->execute([$userId, (int)$gPid]);
                } catch (\Throwable $e) {
                }
            }
            unset($_SESSION['guest_wishlist']);
        }
    }

    public static function safeReturn(?string $return): string
    {
        $return = trim((string)$return);
        if ($return === '' || str_contains($return, '://') || str_starts_with($return, '//') || str_contains($return, '\\')) {
            return 'account';
        }
        $return = ltrim($return, '/');
        if ($return === '' || str_starts_with($return, 'admin')) {
            return 'account';
        }
        return $return;
    }

    private static function syncGoogleEmail(array $user, string $email): void
    {
        if (strtolower((string)$user['email']) === $email) {
            return;
        }
        if (self::emailTaken($email, $user['id'])) {
            return;
        }
        Database::getInstance()->prepare("UPDATE users SET email = ?, email_verified = 1 WHERE id = ?")
            ->execute([$email, $user['id']]);
    }

    private static function findByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            try {
                $stmt = $db->prepare("SELECT * FROM customers WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
            } catch (\Throwable $e) {
                $row = null;
            }
        }
        return $row ? self::present($row) : null;
    }

    private static function emailTaken(string $email, $exceptId = null): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row && !self::sameId($row['id'] ?? null, $exceptId)) {
            return true;
        }
        try {
            $stmt = $db->prepare("SELECT id FROM customers WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row && !self::sameId($row['id'] ?? null, $exceptId);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function phoneTaken(string $phone, $exceptId = null): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
        $stmt->execute([$phone]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row && !self::sameId($row['id'] ?? null, $exceptId);
    }

    private static function sameId($a, $b): bool
    {
        if ($a === null || $b === null || $a === '' || $b === '') {
            return false;
        }
        return (string)$a === (string)$b;
    }

    private static function insertUser(array $data): string
    {
        $db = Database::getInstance();
        $cols = AuthSchema::columns($db, 'users');
        $fields = [];

        $idMeta = $cols['id'] ?? [];
        $idType = strtolower((string)($idMeta['Type'] ?? $idMeta['type'] ?? ''));
        $idExtra = strtolower((string)($idMeta['Extra'] ?? $idMeta['extra'] ?? ''));
        $autoId = str_contains($idExtra, 'auto_increment');
        $stringId = (bool)preg_match('/char|text|binary|uuid|json/', $idType);
        $generatedId = null;
        if (!$autoId && $stringId) {
            $generatedId = self::newUserId();
            $fields['id'] = $generatedId;
        }

        $name = $data['name'];
        if (isset($cols['name'])) {
            $fields['name'] = $name;
        }
        if (isset($cols['first_name'])) {
            $parts = preg_split('/\s+/', $name, 2) ?: [$name];
            $fields['first_name'] = $parts[0];
            if (isset($cols['last_name'])) {
                $fields['last_name'] = $parts[1] ?? '';
            }
        }
        $fields['email'] = $data['email'];
        if (isset($cols['phone'])) {
            $fields['phone'] = $data['phone'];
        }
        $passCol = isset($cols['password']) ? 'password' : (isset($cols['password_hash']) ? 'password_hash' : null);
        if ($passCol) {
            $fields[$passCol] = $data['password'];
        }
        foreach (['google_id', 'auth_provider', 'email_verified', 'phone_verified', 'profile_completed', 'contact_consent', 'consent_at', 'signup_source', 'company_name', 'gst_number'] as $key) {
            if (isset($cols[$key])) {
                $fields[$key] = $data[$key] ?? null;
            }
        }
        if (isset($cols['status'])) {
            $fields['status'] = 'active';
        }
        if (isset($cols['user_type'])) {
            $fields['user_type'] = 'customer';
        }

        $names = array_keys($fields);
        $placeholders = implode(', ', array_fill(0, count($names), '?'));
        $sql = "INSERT INTO users (`" . implode('`,`', $names) . "`) VALUES ({$placeholders})";
        $db->prepare($sql)->execute(array_values($fields));
        $id = $generatedId ?? (string)$db->lastInsertId();
        if ($id === '' || $id === '0') {
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$data['email']]);
            $id = (string)$stmt->fetchColumn();
        }
        if ($id === '' || $id === '0') {
            throw new \RuntimeException('User insert succeeded but no id was returned.');
        }

        self::syncCustomer($id, $data);
        return $id;
    }

    private static function repairBrokenUserRows(): void
    {
        try {
            $db = Database::getInstance();
            $db->exec("DELETE FROM users WHERE id IS NULL OR CAST(id AS CHAR) IN ('', '0')");
        } catch (\Throwable $e) {
        }
    }

    private static function signupInsertError(\Throwable $e): string
    {
        $msg = $e->getMessage();
        error_log('Customer signup insert failed: ' . $msg);
        if (stripos($msg, 'Duplicate') !== false) {
            if (stripos($msg, 'email') !== false) {
                return 'An account with this email already exists.';
            }
            if (stripos($msg, 'phone') !== false) {
                return 'This mobile number is already registered with another account.';
            }
            return 'An account with these details already exists. Try logging in.';
        }
        return 'Could not create the account. ' . $msg;
    }

    private static function newUserId(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }

    private static function syncCustomer(string $id, array $data): void
    {
        try {
            $db = Database::getInstance();
            $cols = AuthSchema::columns($db, 'customers');
            if (!$cols) {
                return;
            }
            $db->prepare("INSERT INTO customers (id, name, email, phone, password) VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE name = VALUES(name), phone = VALUES(phone)")
                ->execute([$id, $data['name'], $data['email'], $data['phone'], $data['password']]);
        } catch (\Throwable $e) {
        }
    }

    private static function present(array $row): array
    {
        if (empty($row['name'])) {
            $row['name'] = trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? ''));
        }
        if ($row['name'] === '') {
            $row['name'] = 'Customer';
        }
        $row['profile_completed'] = (int)($row['profile_completed'] ?? 0);
        return $row;
    }

    private static function has(string $table, string $column): bool
    {
        $db = Database::getInstance();
        return isset(AuthSchema::columns($db, $table)[$column]);
    }

    private static function boot(): void
    {
        if (!class_exists(AuthSchema::class, false) && is_file(__DIR__ . '/AuthSchema.php')) {
            require_once __DIR__ . '/AuthSchema.php';
        }
        if (!class_exists(PhoneNumber::class, false) && is_file(__DIR__ . '/PhoneNumber.php')) {
            require_once __DIR__ . '/PhoneNumber.php';
        }
    }

    private static function otpOn(): bool
    {
        return class_exists(PhoneOtpService::class) && PhoneOtpService::otpRequired();
    }
}

if (!class_exists(__NAMESPACE__ . '\\AuthSchema', false)) {
    class AuthSchema
    {
        private static bool $done = false;
        private static array $columnCache = [];

        public static function ensure(): void
        {
            if (self::$done) {
                return;
            }
            self::$done = true;
            try {
                $db = Database::getInstance();
                $cols = [
                    'google_id' => 'VARCHAR(255) NULL DEFAULT NULL',
                    'auth_provider' => "VARCHAR(20) NOT NULL DEFAULT 'email'",
                    'email_verified' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'phone_verified' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'profile_completed' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'contact_consent' => 'TINYINT(1) NOT NULL DEFAULT 0',
                    'consent_at' => 'DATETIME NULL DEFAULT NULL',
                    'last_login_at' => 'DATETIME NULL DEFAULT NULL',
                    'signup_source' => 'VARCHAR(32) NULL DEFAULT NULL',
                    'company_name' => 'VARCHAR(150) NULL DEFAULT NULL',
                    'gst_number' => 'VARCHAR(15) NULL DEFAULT NULL',
                    'reset_token' => 'VARCHAR(64) NULL DEFAULT NULL',
                    'reset_token_expires_at' => 'DATETIME NULL DEFAULT NULL',
                ];
                $existing = self::columns($db, 'users');
                foreach ($cols as $name => $ddl) {
                    if (!isset($existing[$name])) {
                        $db->exec("ALTER TABLE `users` ADD COLUMN `{$name}` {$ddl}");
                    }
                }
                self::$columnCache = [];
            } catch (\Throwable $e) {
                error_log('Auth schema ensure failed: ' . $e->getMessage());
            }
        }

        public static function columns(\PDO $db, string $table): array
        {
            if (isset(self::$columnCache[$table])) {
                return self::$columnCache[$table];
            }
            $safe = preg_replace('/[^a-z0-9_]/i', '', $table);
            $rows = $db->query("SHOW COLUMNS FROM `{$safe}`")->fetchAll(\PDO::FETCH_ASSOC);
            $out = [];
            foreach ($rows as $row) {
                $out[$row['Field']] = $row;
            }
            return self::$columnCache[$table] = $out;
        }
    }
}

if (!class_exists(__NAMESPACE__ . '\\PhoneNumber', false)) {
    class PhoneNumber
    {
        public static function normalize(string $raw): ?string
        {
            $digits = preg_replace('/\D+/', '', trim($raw)) ?? '';
            if (str_starts_with($digits, '91') && strlen($digits) === 12) {
                $digits = substr($digits, 2);
            } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
                $digits = substr($digits, 1);
            }
            if (!preg_match('/^[6-9]\d{9}$/', $digits)) {
                return null;
            }
            return $digits;
        }
    }
}
