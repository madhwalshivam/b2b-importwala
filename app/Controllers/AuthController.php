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
            $emailSubject = "Mudsor Admin Password Reset Request";
            $emailBody = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                    <h2 style='color: #dc2626; margin-bottom: 12px;'>Mudsor Admin Portal</h2>
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
            if ($this->request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Already logged in']);
                exit;
            }
            $this->response->redirect(url('account'));
            return '';
        }

        $error = null;
        $returnUrl = $this->request->input('return', 'account');

        if ($this->request->isPost()) {
            $email = trim($this->request->input('email', ''));
            $password = trim($this->request->input('password', ''));

            if (empty($email) || empty($password)) {
                $error = "Please fill in all fields.";
            } else {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if (!$user) {
                    // Check fallback in customers table
                    $stmt = $db->prepare("SELECT * FROM customers WHERE email = ? LIMIT 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();
                }

                if ($user && password_verify($password, $user['password'])) {
                    // Regenerate Session ID
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_phone'] = $user['phone'] ?? null;

                    // Issue JWT Access Token & Refresh Token
                    $tokens = TokenService::issueTokens($user['id'], 'customer');

                    // Merge Guest Cart into User Cart
                    $sessionId = session_id();
                    $db->prepare("UPDATE cart_items SET user_id = ? WHERE session_id = ?")->execute([$user['id'], $sessionId]);

                    // Merge Guest Wishlist into User DB Wishlist
                    if (!empty($_SESSION['guest_wishlist']) && is_array($_SESSION['guest_wishlist'])) {
                        foreach ($_SESSION['guest_wishlist'] as $gPid) {
                            $ins = $db->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
                            $ins->execute([$user['id'], (int)$gPid]);
                        }
                        unset($_SESSION['guest_wishlist']);
                    }

                    if ($this->request->isAjax()) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Login successful!', 'tokens' => $tokens]);
                        exit;
                    }

                    $target = !empty($returnUrl) ? url(ltrim($returnUrl, '/')) : url('account');
                    $this->response->redirect($target . '?success=Logged+in+successfully');
                    return '';
                } else {
                    TokenService::logAuth('failed_login', null, 'customer', 'Failed customer login for: ' . $email);
                    $error = "Invalid email address or password.";
                }
            }

            if ($this->request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }

        return $this->render('storefront/auth/login', [
            'error' => $error,
            'returnUrl' => $returnUrl
        ]);
    }

    public function customerSignup(): string {
        if (!empty($_SESSION['user_id'])) {
            if ($this->request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Already logged in']);
                exit;
            }
            $this->response->redirect(url('account'));
            return '';
        }

        $error = null;
        if ($this->request->isPost()) {
            $name = trim($this->request->input('name', ''));
            $email = trim($this->request->input('email', ''));
            $phone = trim($this->request->input('phone', ''));
            $password = trim($this->request->input('password', ''));

            if (empty($name) || empty($email) || empty($password)) {
                $error = "All fields are required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } elseif (strlen($password) < 6) {
                $error = "Password must be at least 6 characters long.";
            } else {
                $db = Database::getInstance();
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ? UNION SELECT id FROM customers WHERE email = ?");
                $stmt->execute([$email, $email]);

                if ($stmt->fetch()) {
                    $error = "An account with this email already exists.";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert into users table
                    $insertStmt = $db->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
                    $insertStmt->execute([$name, $email, $phone, $hashedPassword]);
                    $userId = $db->lastInsertId();

                    // Also sync with customers table
                    $db->prepare("INSERT INTO customers (id, name, email, phone, password) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), phone=VALUES(phone)")
                       ->execute([$userId, $name, $email, $phone, $hashedPassword]);

                    // Auto login with session regeneration & tokens
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_phone'] = $phone;

                    $tokens = TokenService::issueTokens($userId, 'customer');

                    // Merge Guest Cart into User Cart
                    $sessionId = session_id();
                    $db->prepare("UPDATE cart_items SET user_id = ? WHERE session_id = ?")->execute([$userId, $sessionId]);

                    if ($this->request->isAjax()) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'Account created successfully!', 'tokens' => $tokens]);
                        exit;
                    }

                    $this->response->redirect(url('account') . '?success=Account+created+successfully');
                    return '';
                }
            }

            if ($this->request->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }

        return $this->render('storefront/auth/signup', [
            'error' => $error
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
        return $this->render('storefront/auth/forgot_password', [
            'error' => $session->getFlash('error'),
            'success' => $session->getFlash('success'),
            'reset_link' => $session->getFlash('reset_link')
        ]);
    }

    public function processCustomerForgotPassword(): void {
        $email = trim($this->request->input('email', ''));

        if (empty($email)) {
            $this->setFlash('error', 'Please enter your email address.');
            $this->response->redirect(url('forgot-password'));
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $stmt = $db->prepare("SELECT id FROM customers WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        }

        if (!$user) {
            $this->setFlash('error', 'No customer account found with that email address.');
            $this->response->redirect(url('forgot-password'));
            return;
        }

        $token = bin2hex(random_bytes(32));

        $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?")->execute([$token, $email]);
        $db->prepare("UPDATE customers SET reset_token = ?, reset_token_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?")->execute([$token, $email]);

        $resetUrl = url('reset-password?token=' . $token);

        if (!empty($email)) {
            $emailSubject = "Mudsor Account Password Reset";
            $emailBody = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                    <h2 style='color: #dc2626; margin-bottom: 12px;'>Mudsor Accessories</h2>
                    <p>Hello,</p>
                    <p>We received a request to reset your Mudsor account password.</p>
                    <p style='margin: 20px 0;'>
                        <a href='{$resetUrl}' style='background-color: #dc2626; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Reset Password Now</a>
                    </p>
                    <p style='color: #64748b; font-size: 12px;'>If you did not request a password reset, you can safely ignore this email.</p>
                </div>
            ";
            \App\Services\NotificationService::sendEmail('customer_forgot_password', $email, $emailSubject, $emailBody);
        }

        $this->setFlash('success', 'Password reset link created successfully!');
        $this->setFlash('reset_link', $resetUrl);
        $this->response->redirect(url('forgot-password'));
    }

    public function customerResetPassword(): string {
        $token = trim($this->request->input('token', ''));
        $tokenValid = false;
        $error = $this->getFlash('error');

        if (empty($token)) {
            $error = $error ?: 'Missing reset token.';
        } else {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW() UNION SELECT id FROM customers WHERE reset_token = ? AND reset_token_expires_at > NOW()");
            $stmt->execute([$token, $token]);
            if ($stmt->fetch()) {
                $tokenValid = true;
            } else {
                $error = $error ?: 'Invalid or expired password reset link.';
            }
        }

        return $this->render('storefront/auth/reset_password', [
            'token' => $token,
            'tokenValid' => $tokenValid,
            'error' => $error
        ]);
    }


    public function processCustomerResetPassword(): void {
        $token = trim($this->request->input('token', ''));
        $password = trim($this->request->input('password', ''));
        $confirmPassword = trim($this->request->input('confirm_password', ''));

        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $this->setFlash('error', 'All fields are required.');
            $this->response->redirect(url('reset-password?token=' . urlencode($token)));
            return;
        }

        if (strlen($password) < 6) {
            $this->setFlash('error', 'Password must be at least 6 characters long.');
            $this->response->redirect(url('reset-password?token=' . urlencode($token)));
            return;
        }

        if ($password !== $confirmPassword) {
            $this->setFlash('error', 'Passwords do not match.');
            $this->response->redirect(url('reset-password?token=' . urlencode($token)));
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT email FROM users WHERE reset_token = ? AND reset_token_expires_at > NOW() UNION SELECT email FROM customers WHERE reset_token = ? AND reset_token_expires_at > NOW()");
        $stmt->execute([$token, $token]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->setFlash('error', 'Invalid or expired password reset link.');
            $this->response->redirect(url('forgot-password'));
            return;
        }

        $email = $user['email'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE email = ?")->execute([$hashedPassword, $email]);
        $db->prepare("UPDATE customers SET password = ?, reset_token = NULL, reset_token_expires_at = NULL WHERE email = ?")->execute([$hashedPassword, $email]);

        $this->setFlash('success', 'Your password has been updated successfully! Please login with your new password.');
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

        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];
        $userEmail = $_SESSION['user_email'];

        // Fetch user details
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // Fetch User Orders
        $orderStmt = $db->prepare("SELECT * FROM orders WHERE customer_id = ? OR customer_email = ? ORDER BY id DESC");
        $orderStmt->execute([$userId, $userEmail]);
        $orders = $orderStmt->fetchAll();

        // Fetch Wishlist Items
        $wishStmt = $db->prepare("
            SELECT p.* FROM wishlist w
            JOIN products p ON w.product_id = p.id
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $wishStmt->execute([$userId]);
        $wishlistItems = $wishStmt->fetchAll();

        return $this->render('storefront/auth/account', [
            'user' => $user,
            'orders' => $orders,
            'wishlistItems' => $wishlistItems
        ]);
    }
}
