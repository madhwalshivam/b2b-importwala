<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Services\TokenService;

class AuthController extends Controller {

    // ----------------------------------------------------
    // Admin Employee Auth Methods
    // ----------------------------------------------------
    public function login(): string {
        if (!empty($_SESSION['admin_user_id'])) {
            $this->response->redirect(url('admin/dashboard'));
            return '';
        }
        return $this->render('admin/login', [
            'error' => $this->getFlash('error')
        ]);
    }

    public function processLogin(): void {
        $username = trim($this->request->input('username', ''));
        $password = trim($this->request->input('password', ''));

        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'Please enter username and password.');
            $this->response->redirect(url('admin/login'));
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            if ($admin['status'] !== 'active') {
                $this->setFlash('error', 'Your account has been deactivated.');
                $this->response->redirect(url('admin/login'));
                return;
            }

            // Regenerate Session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['admin_user_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role_id'] = $admin['role_id'];

            // Issue JWT Access Token & Refresh Token (optional)
            try {
                TokenService::issueTokens($admin['id'], 'admin');
            } catch (\Throwable $e) {}

            // Update last login (optional column)
            try {
                $db->prepare("UPDATE admin_users SET last_login_at = NOW() WHERE id = ?")->execute([$admin['id']]);
            } catch (\Throwable $e) {}

            $this->response->redirect(url('admin/dashboard') . '?success=Logged+in+successfully');
        } else {
            TokenService::logAuth('failed_login', null, 'admin', 'Failed admin login for: ' . $username);
            $this->setFlash('error', 'Invalid admin credentials.');
            $this->response->redirect(url('admin/login'));
        }
    }

    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!empty($_SESSION['admin_user_id'])) {
            try {
                TokenService::revokeUserTokens((int)$_SESSION['admin_user_id'], 'admin');
            } catch (\Throwable $e) {}
        }
        unset($_SESSION['admin_user_id']);
        unset($_SESSION['admin_name']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['admin_role_id']);

        $this->setFlash('success', 'You have been logged out successfully.');
        $this->response->redirect(url('admin/login'));
    }

    public function adminForgotPassword(): string {
        $session = new \App\Core\Session();
        return $this->render('admin/forgot_password', [
            'error' => $session->getFlash('error'),
            'success' => $session->getFlash('success'),
            'reset_link' => $session->getFlash('reset_link')
        ]);
    }

    public function processAdminForgotPassword(): void {
        $username = trim($this->request->input('username', ''));

        if (empty($username)) {
            $this->setFlash('error', 'Please enter your username or email.');
            $this->response->redirect(url('admin/forgot-password'));
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, email, name FROM admin_users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if (!$admin) {
            $this->setFlash('error', 'No admin account found with that username or email.');
            $this->response->redirect(url('admin/forgot-password'));
            return;
        }

        $token = bin2hex(random_bytes(32));

        $upd = $db->prepare("UPDATE admin_users SET reset_token = ?, reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?");
        $upd->execute([$token, $admin['id']]);

        $resetUrl = url('admin/reset-password?token=' . $token);

        if (!empty($admin['email'])) {
            $emailSubject = "ImportWale Admin Password Reset Request";
            $emailBody = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                    <h2 style='color: #dc2626; margin-bottom: 12px;'>ImportWale Admin Portal</h2>
                    <p>Hello <strong>" . htmlspecialchars($admin['name']) . "</strong>,</p>
                    <p>We received a request to reset your admin portal password.</p>
                    <p style='margin: 20px 0;'>
                        <a href='{$resetUrl}' style='background-color: #dc2626; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Reset Password Now</a>
                    </p>
                    <p style='color: #64748b; font-size: 12px;'>If you did not request a password reset, you can safely ignore this email.</p>
                </div>
            ";
            \App\Services\NotificationService::sendEmail('admin_forgot_password', $admin['email'], $emailSubject, $emailBody);
        }

        $this->setFlash('success', 'Password reset link created successfully!');
        $this->setFlash('reset_link', $resetUrl);
        $this->response->redirect(url('admin/forgot-password'));
    }

    public function adminResetPassword(): string {
        $token = trim($this->request->input('token', ''));
        $tokenValid = false;
        $error = null;

        if (empty($token)) {
            $error = 'Missing reset token.';
        } else {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT id FROM admin_users WHERE reset_token = ? AND reset_token_expires_at > NOW() LIMIT 1");
            $stmt->execute([$token]);
            if ($stmt->fetch()) {
                $tokenValid = true;
            } else {
                $error = 'Invalid or expired password reset link.';
            }
        }

        return $this->render('admin/reset_password', [
            'token' => $token,
            'tokenValid' => $tokenValid,
            'error' => $error
        ]);
    }

    public function processAdminResetPassword(): void {
        $token = trim($this->request->input('token', ''));
        $password = trim($this->request->input('password', ''));
        $confirmPassword = trim($this->request->input('confirm_password', ''));

        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $this->setFlash('error', 'All fields are required.');
            $this->response->redirect(url('admin/reset-password?token=' . urlencode($token)));
            return;
        }

        if (strlen($password) < 6) {
            $this->setFlash('error', 'Password must be at least 6 characters long.');
            $this->response->redirect(url('admin/reset-password?token=' . urlencode($token)));
            return;
        }

        if ($password !== $confirmPassword) {
            $this->setFlash('error', 'Passwords do not match.');
            $this->response->redirect(url('admin/reset-password?token=' . urlencode($token)));
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM admin_users WHERE reset_token = ? AND reset_token_expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $admin = $stmt->fetch();

        if (!$admin) {
            $this->setFlash('error', 'Invalid or expired password reset link.');
            $this->response->redirect(url('admin/forgot-password'));
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $upd = $db->prepare("UPDATE admin_users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE id = ?");
        $upd->execute([$hashedPassword, $admin['id']]);

        $this->setFlash('success', 'Your password has been updated successfully! Please login with your new password.');
        $this->response->redirect(url('admin/login'));
    }

    // ----------------------------------------------------
    // Customer Auth Methods (Supports normal & AJAX inline popups)
    // ----------------------------------------------------
    public function customerLogin(): string {
        if (!empty($_SESSION['user_id'])) {
            $dest = \App\Services\CustomerAccess::profileComplete() ? 'account' : 'complete-profile';
            if ($this->request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Already logged in', 'redirect' => url($dest)]);
                exit;
            }
            $this->response->redirect(url($dest));
            return '';
        }

        $error = null;
        $returnUrl = \App\Services\CustomerAuthService::safeReturn($this->request->input('return', $_SESSION['auth_return'] ?? 'account'));

        if ($this->request->isPost()) {
            $result = \App\Services\CustomerAuthService::attemptPassword(
                (string)$this->request->input('email', ''),
                (string)$this->request->input('password', '')
            );

            if (!empty($result['ok'])) {
                $target = $returnUrl;
                if ((int)($result['user']['profile_completed'] ?? 0) !== 1) {
                    $_SESSION['auth_return'] = $target;
                    $target = 'complete-profile?return=' . rawurlencode($returnUrl);
                } else {
                    unset($_SESSION['pending_action']);
                }
                if ($this->request->isAjax()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful!',
                        'redirect' => url($target),
                        'needs_profile' => (int)($result['user']['profile_completed'] ?? 0) !== 1,
                    ]);
                    exit;
                }
                $this->response->redirect(url($target));
                return '';
            }

            $error = $result['error'] ?? 'Invalid email address or password.';
            if ($this->request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }

        return $this->render('web/login', [
            'error' => $error,
            'returnUrl' => $returnUrl,
            'googleEnabled' => \App\Services\CustomerAuthService::googleEnabled(),
            'googleClientId' => \App\Services\CustomerAuthService::clientId(),
        ]);
    }

    public function customerSignup(): string {
        if (!empty($_SESSION['user_id'])) {
            $dest = \App\Services\CustomerAccess::profileComplete() ? 'account' : 'complete-profile';
            if ($this->request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Already logged in', 'redirect' => url($dest)]);
                exit;
            }
            $this->response->redirect(url($dest));
            return '';
        }

        $error = null;
        $returnUrl = \App\Services\CustomerAuthService::safeReturn($this->request->input('return', $_SESSION['auth_return'] ?? 'account'));
        if ($this->request->isPost()) {
            $password = (string)$this->request->input('password', '');
            $confirm = (string)$this->request->input('confirm_password', '');
            if ($confirm !== '' && $password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $result = \App\Services\CustomerAuthService::registerWithPassword(
                    (string)$this->request->input('name', ''),
                    (string)$this->request->input('email', ''),
                    (string)$this->request->input('phone', ''),
                    $password,
                    !empty($this->request->input('contact_consent')),
                    (string)$this->request->input('gst_number', '')
                );
                if (!empty($result['ok'])) {
                    $needs = !empty($result['needs_profile']);
                    $target = $needs
                        ? 'complete-profile?return=' . rawurlencode($returnUrl)
                        : $returnUrl;
                    if ($needs) {
                        $_SESSION['auth_return'] = $returnUrl;
                    }
                    if ($this->request->isAjax()) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'message' => 'Account created successfully!',
                            'redirect' => url($target),
                            'needs_profile' => $needs,
                        ]);
                        exit;
                    }
                    $this->response->redirect(url($target));
                    return '';
                }
                $error = $result['error'] ?? 'Could not create the account.';
            }

            if ($this->request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }

        return $this->render('web/signup', [
            'error' => $error,
            'returnUrl' => $returnUrl,
            'old' => [
                'name' => (string)$this->request->input('name', ''),
                'email' => (string)$this->request->input('email', ''),
                'phone' => (string)$this->request->input('phone', ''),
                'gst_number' => (string)$this->request->input('gst_number', ''),
            ],
            'googleEnabled' => \App\Services\CustomerAuthService::googleEnabled(),
            'googleClientId' => \App\Services\CustomerAuthService::clientId(),
        ]);
    }

    public function customerLogout(): void {
        if (!empty($_SESSION['user_id'])) {
            TokenService::revokeUserTokens((int)$_SESSION['user_id'], 'customer');
        }
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_phone']);
        session_unset();
        session_destroy();

        $this->response->redirect(url('/') . '?success=You+have+been+logged+out+successfully');
    }

    public function customerForgotPassword(): string {
        $session = new \App\Core\Session();
        return $this->render('web/forgot_password', [
            'error' => $session->getFlash('error'),
            'success' => $session->getFlash('success'),
        ]);
    }

    public function processCustomerForgotPassword(): void {
        $generic = 'If this email is registered, a reset link has been sent.';
        $email = strtolower(trim((string)$this->request->input('email', '')));

        if ($email === '') {
            $this->setFlash('error', 'Please enter your email address.');
            $this->response->redirect(url('forgot-password'));
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Please enter a valid email address.');
            $this->response->redirect(url('forgot-password'));
            return;
        }

        try {
            $this->ensurePasswordResetSchema();
            $user = $this->findCustomerAuthRow($email);
            if ($user) {
                $rawToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $rawToken);
                $this->storePasswordResetHash($user, $email, $tokenHash);

                $resetUrl = $this->customerPublicUrl(
                    'reset-password?token=' . rawurlencode($rawToken) . '&email=' . rawurlencode($email)
                );
                $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
                $emailBody = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 24px; border: 1px solid #e5e7eb; border-radius: 12px;'>
                        <h2 style='color: #f05a29; margin-bottom: 12px;'>ImportWale</h2>
                        <p>Hello,</p>
                        <p>We received a request to reset the password for this ImportWale account.</p>
                        <p style='margin: 24px 0;'>
                            <a href='{$safeUrl}' style='background-color: #f05a29; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Reset Password</a>
                        </p>
                        <p style='font-size: 12px; color: #6b7280; word-break: break-all;'>Or copy this link:<br>{$safeUrl}</p>
                        <p style='color: #6b7280; font-size: 12px;'>This link expires in 60 minutes. If you did not request a reset, you can ignore this email.</p>
                    </div>
                ";
                $sent = \App\Services\NotificationService::sendEmail(
                    'customer_forgot_password',
                    $email,
                    'Reset your ImportWale password',
                    $emailBody
                );
                if (!$sent) {
                    error_log('Customer password reset email was not sent for a registered address.');
                }
            }
        } catch (\Throwable $e) {
            error_log('Customer forgot password failed: ' . $e->getMessage());
        }

        $this->setFlash('success', $generic);
        $this->response->redirect(url('forgot-password'));
    }

    public function customerResetPassword(): string {
        $token = trim((string)$this->request->input('token', ''));
        $email = strtolower(trim((string)$this->request->input('email', '')));
        $tokenValid = false;
        $error = $this->getFlash('error');

        if ($token === '' || $email === '') {
            $error = $error ?: 'This password reset link is invalid or has expired.';
        } else {
            try {
                $this->ensurePasswordResetSchema();
                $tokenValid = (bool)$this->findRowByResetToken($email, $token);
            } catch (\Throwable $e) {
                error_log('Customer reset token check failed: ' . $e->getMessage());
            }
            if (!$tokenValid) {
                $error = $error ?: 'This password reset link is invalid or has expired.';
            }
        }

        return $this->render('web/reset_password', [
            'token' => $token,
            'email' => $email,
            'tokenValid' => $tokenValid,
            'error' => $error,
        ]);
    }

    public function processCustomerResetPassword(): void {
        $token = trim((string)$this->request->input('token', ''));
        $email = strtolower(trim((string)$this->request->input('email', '')));
        $password = (string)$this->request->input('password', '');
        $confirmPassword = (string)$this->request->input('confirm_password', '');
        $back = 'reset-password?token=' . rawurlencode($token) . '&email=' . rawurlencode($email);

        if ($token === '' || $email === '' || $password === '' || $confirmPassword === '') {
            $this->setFlash('error', 'All fields are required.');
            $this->response->redirect(url($back));
            return;
        }
        if (strlen($password) < 6) {
            $this->setFlash('error', 'Password must be at least 6 characters long.');
            $this->response->redirect(url($back));
            return;
        }
        if ($password !== $confirmPassword) {
            $this->setFlash('error', 'Passwords do not match.');
            $this->response->redirect(url($back));
            return;
        }

        try {
            $this->ensurePasswordResetSchema();
            $row = $this->findRowByResetToken($email, $token);
            if (!$row) {
                $this->setFlash('error', 'This password reset link is invalid or has expired.');
                $this->response->redirect(url('forgot-password'));
                return;
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $this->updateCustomerPassword($row['_table'], $email, $hash);
        } catch (\Throwable $e) {
            error_log('Customer password reset failed: ' . $e->getMessage());
            $this->setFlash('error', 'Could not update the password. Please try again.');
            $this->response->redirect(url($back));
            return;
        }

        $this->setFlash('success', 'Your password has been updated. Please log in with your new password.');
        $this->response->redirect(url('login'));
    }

    public function refreshToken(): void {
        header('Content-Type: application/json');
        
        $refreshToken = $_COOKIE['refresh_token'] ?? $this->request->input('refresh_token', '');
        
        if (empty($refreshToken)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Refresh token required']);
            exit;
        }

        $newTokens = TokenService::refreshToken($refreshToken);

        if ($newTokens) {
            echo json_encode(['success' => true, 'tokens' => $newTokens]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid or revoked refresh token']);
        }
        exit;
    }

    public function account(): string {
        if (empty($_SESSION['user_id'])) {
            $this->response->redirect(url('login?return=account'));
            return '';
        }

        $userId = $_SESSION['user_id'];
        $userEmail = (string)($_SESSION['user_email'] ?? '');
        $user = \App\Services\CustomerAuthService::findById($userId);
        if (!$user) {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        }

        $orders = [];
        $wishlistItems = [];
        try {
            $db = Database::getInstance();
            $orderStmt = $db->prepare("SELECT * FROM orders WHERE customer_id = ? OR customer_email = ? ORDER BY id DESC");
            $orderStmt->execute([$userId, $userEmail !== '' ? $userEmail : ($user['email'] ?? '')]);
            $orders = $orderStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
        }
        try {
            $db = Database::getInstance();
            $wishStmt = $db->prepare("
                SELECT p.* FROM wishlist w
                JOIN products p ON w.product_id = p.id
                WHERE w.user_id = ?
                ORDER BY w.id DESC
            ");
            $wishStmt->execute([$userId]);
            $wishlistItems = $wishStmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
        }

        return $this->render('web/account', [
            'user' => $user,
            'orders' => $orders,
            'wishlistItems' => $wishlistItems
        ]);
    }

    public function google(): void
    {
        header('Content-Type: application/json');
        $credential = trim((string)$this->request->input('credential', ''));
        $returnUrl = \App\Services\CustomerAuthService::safeReturn(
            $this->request->input('return', $_SESSION['auth_return'] ?? 'account')
        );
        if ($credential === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Sign-in was cancelled. Please try again.']);
            exit;
        }
        $result = \App\Services\CustomerAuthService::loginWithGoogle($credential);
        if (empty($result['ok'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => $result['error'] ?? 'Google sign-in failed.']);
            exit;
        }

        $target = $returnUrl;
        if (!empty($result['needs_profile'])) {
            $_SESSION['auth_return'] = $returnUrl;
            $target = 'complete-profile?return=' . rawurlencode($returnUrl);
        } else {
            unset($_SESSION['pending_action']);
        }
        echo json_encode([
            'success' => true,
            'message' => 'Signed in with Google.',
            'redirect' => url($target),
            'needs_profile' => !empty($result['needs_profile']),
        ]);
        exit;
    }

    public function completeProfile(): string
    {
        if (empty($_SESSION['user_id'])) {
            $this->response->redirect(url('login'));
            return '';
        }
        $user = \App\Services\CustomerAuthService::findById($_SESSION['user_id']);
        if (!$user) {
            $this->response->redirect(url('login'));
            return '';
        }
        if ((int)($user['profile_completed'] ?? 0) === 1 && !$this->request->isPost()) {
            $this->response->redirect(url(\App\Services\CustomerAuthService::safeReturn($_SESSION['auth_return'] ?? 'account')));
            return '';
        }

        $returnUrl = \App\Services\CustomerAuthService::safeReturn(
            $this->request->input('return', $_SESSION['auth_return'] ?? 'account')
        );
        $_SESSION['auth_return'] = $returnUrl;
        $error = null;
        $otpStep = !empty($_SESSION['otp_pending']) && \App\Services\PhoneOtpService::otpRequired();
        $debugCode = null;

        if ($this->request->isPost()) {
            $action = (string)$this->request->input('action', 'save');
            if ($action === 'resend') {
                $phone = \App\Services\PhoneNumber::normalize((string)($user['phone'] ?? ''));
                $sent = $phone ? \App\Services\PhoneOtpService::send($phone) : ['ok' => false, 'error' => 'Save your mobile number first.'];
                $otpStep = true;
                if (empty($sent['ok'])) {
                    $error = $sent['error'] ?? 'Could not resend the code.';
                } else {
                    $debugCode = $sent['debug_code'] ?? null;
                    $this->setFlash('success', 'A new code was sent to your mobile.');
                }
            } else {
                $result = \App\Services\CustomerAuthService::completeProfile(
                    $user,
                    (string)$this->request->input('phone', $user['phone'] ?? ''),
                    (string)$this->request->input('company_name', ''),
                    !empty($this->request->input('contact_consent')),
                    $action === 'verify_otp' ? (string)$this->request->input('otp', '') : '',
                    (string)$this->request->input('gst_number', $user['gst_number'] ?? '')
                );
                if (!empty($result['ok']) && empty($result['otp_step'])) {
                    $dest = $returnUrl;
                    unset($_SESSION['auth_return'], $_SESSION['pending_action']);
                    $this->response->redirect(url($dest));
                    return '';
                }
                $otpStep = !empty($result['otp_step']);
                $debugCode = $result['debug_code'] ?? null;
                if (empty($result['ok'])) {
                    $error = $result['error'] ?? 'Could not save your profile.';
                }
                $user = \App\Services\CustomerAuthService::findById($_SESSION['user_id']) ?? $user;
                if (!empty($error)) {
                    $user['phone'] = (string)$this->request->input('phone', $user['phone'] ?? '');
                    $user['company_name'] = (string)$this->request->input('company_name', $user['company_name'] ?? '');
                    $user['gst_number'] = (string)$this->request->input('gst_number', $user['gst_number'] ?? '');
                }
            }
        }

        return $this->render('web/complete_profile', [
            'error' => $error,
            'user' => $user,
            'returnUrl' => $returnUrl,
            'otpStep' => $otpStep,
            'otpRequired' => \App\Services\PhoneOtpService::otpRequired(),
            'debugCode' => $debugCode,
        ]);
    }

    private function ensurePasswordResetSchema(): void
    {
        $db = Database::getInstance();
        foreach (['users', 'customers'] as $table) {
            try {
                $rows = $db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {
                continue;
            }
            $have = [];
            foreach ($rows as $row) {
                $have[$row['Field']] = true;
            }
            try {
                if (empty($have['reset_token'])) {
                    $db->exec("ALTER TABLE `{$table}` ADD COLUMN `reset_token` VARCHAR(64) NULL DEFAULT NULL");
                }
                if (empty($have['reset_token_expires_at'])) {
                    $db->exec("ALTER TABLE `{$table}` ADD COLUMN `reset_token_expires_at` DATETIME NULL DEFAULT NULL");
                }
            } catch (\Throwable $e) {
                error_log("Password reset schema ({$table}): " . $e->getMessage());
            }
        }
    }

    private function findCustomerAuthRow(string $email): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            $row['_table'] = 'users';
            return $row;
        }
        try {
            $stmt = $db->prepare("SELECT * FROM customers WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $row['_table'] = 'customers';
                return $row;
            }
        } catch (\Throwable $e) {
        }
        return null;
    }

    private function storePasswordResetHash(array $user, string $email, string $tokenHash): void
    {
        $db = Database::getInstance();
        $table = $user['_table'] ?? 'users';
        $db->prepare("UPDATE `{$table}` SET reset_token = ?, reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 60 MINUTE) WHERE email = ?")
            ->execute([$tokenHash, $email]);
        $other = $table === 'users' ? 'customers' : 'users';
        try {
            $db->prepare("UPDATE `{$other}` SET reset_token = ?, reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 60 MINUTE) WHERE email = ?")
                ->execute([$tokenHash, $email]);
        } catch (\Throwable $e) {
        }
    }

    private function findRowByResetToken(string $email, string $rawToken): ?array
    {
        if ($email === '' || $rawToken === '') {
            return null;
        }
        $hash = hash('sha256', $rawToken);
        $db = Database::getInstance();
        foreach (['users', 'customers'] as $table) {
            try {
                $stmt = $db->prepare("
                    SELECT * FROM `{$table}`
                    WHERE email = ? AND reset_token = ? AND reset_token_expires_at > NOW()
                    LIMIT 1
                ");
                $stmt->execute([$email, $hash]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    $row['_table'] = $table;
                    return $row;
                }
            } catch (\Throwable $e) {
            }
        }
        return null;
    }

    private function updateCustomerPassword(string $table, string $email, string $passwordHash): void
    {
        $db = Database::getInstance();
        $passCol = $this->passwordColumn($db, $table);
        if ($passCol) {
            $db->prepare("
                UPDATE `{$table}`
                SET `{$passCol}` = ?, reset_token = NULL, reset_token_expires_at = NULL
                WHERE email = ?
            ")->execute([$passwordHash, $email]);
        }
        $other = $table === 'users' ? 'customers' : 'users';
        try {
            $otherCol = $this->passwordColumn($db, $other);
            if ($otherCol) {
                $db->prepare("
                    UPDATE `{$other}`
                    SET `{$otherCol}` = ?, reset_token = NULL, reset_token_expires_at = NULL
                    WHERE email = ?
                ")->execute([$passwordHash, $email]);
            }
        } catch (\Throwable $e) {
        }
    }

    private function passwordColumn(\PDO $db, string $table): ?string
    {
        try {
            $rows = $db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return null;
        }
        $have = [];
        foreach ($rows as $row) {
            $have[$row['Field']] = true;
        }
        if (isset($have['password'])) {
            return 'password';
        }
        if (isset($have['password_hash'])) {
            return 'password_hash';
        }
        return null;
    }

    private function customerPublicUrl(string $path): string
    {
        $configured = rtrim((string)env('APP_URL', ''), '/');
        $forwarded = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        $https = $forwarded === 'https'
            || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (int)($_SERVER['SERVER_PORT'] ?? 80) === 443;
        $host = (string)($_SERVER['HTTP_HOST'] ?? '');
        if ($host !== '' && ($configured === '' || str_contains($configured, 'localhost') || str_contains($configured, '127.0.0.1'))) {
            $configured = ($https ? 'https' : 'http') . '://' . $host;
        }
        if ($configured === '') {
            $configured = 'https://importwale.com';
        }
        return $configured . '/' . ltrim($path, '/');
    }
}
