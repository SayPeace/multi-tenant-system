<?php
/**
 * Authentication Controller
 * Handles login, logout, password reset
 */

class AuthController
{
    public string $baseUrl;
    public array $config;
    public array $params;

    /**
     * Show login form
     */
    public function showLogin(): void
    {
        // Redirect if already logged in
        if (AdminAuth::check()) {
            $this->redirectToDashboard();
            return;
        }

        // Get tenants for journal user login
        require_once __DIR__ . '/../models/Tenant.php';
        $tenants = Tenant::getActive();

        $pageTitle = 'Login - ' . ($this->config['app_name'] ?? 'Admin');
        $baseUrl = $this->baseUrl;

        include __DIR__ . '/../templates/auth/login.php';
    }

    /**
     * Process login
     */
    public function login(): void
    {
        // Verify CSRF token
        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/login');
            exit;
        }

        // Get form data
        $loginType = $_POST['login_type'] ?? 'super_admin';
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        $tenantId = (int) ($_POST['tenant_id'] ?? 0);

        // Validate
        $validator = Validator::make($_POST, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            Flash::setErrors($validator->errors());
            Flash::setOldInput($_POST);
            Flash::error('Please correct the errors below.');
            header('Location: ' . $this->baseUrl . '/login');
            exit;
        }

        // Attempt login
        $success = false;

        if ($loginType === 'super_admin') {
            $success = AdminAuth::attemptSuperAdmin($email, $password, $remember);
        } else {
            if ($tenantId <= 0) {
                Flash::error('Please select a journal.');
                Flash::setOldInput($_POST);
                header('Location: ' . $this->baseUrl . '/login');
                exit;
            }
            $success = AdminAuth::attemptUser($tenantId, $email, $password, $remember);
        }

        if (!$success) {
            Flash::error('Invalid email or password.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/login');
            exit;
        }

        Flash::success('Welcome back!');
        $this->redirectToDashboard();
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        AdminAuth::logout();
        Flash::success('You have been logged out.');
        header('Location: ' . $this->baseUrl . '/login');
        exit;
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword(): void
    {
        $pageTitle = 'Forgot Password - ' . ($this->config['app_name'] ?? 'Admin');
        $baseUrl = $this->baseUrl;

        include __DIR__ . '/../templates/auth/forgot-password.php';
    }

    /**
     * Send password reset link
     */
    public function sendResetLink(): void
    {
        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/forgot-password');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $accountType = $_POST['account_type'] ?? 'super_admin';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Please enter a valid email address.');
            header('Location: ' . $this->baseUrl . '/forgot-password');
            exit;
        }

        // Generate reset token
        $token = AdminAuth::generatePasswordResetToken($email, $accountType);

        // Always show success message to prevent email enumeration
        Flash::success('If an account exists with that email, a password reset link has been sent.');

        // In a real application, send email here
        if ($token) {
            $resetUrl = $this->baseUrl . '/reset-password/' . $token;
            // TODO: Send email with $resetUrl
            // For development, log it
            error_log("Password reset link for $email: $resetUrl");
        }

        header('Location: ' . $this->baseUrl . '/login');
        exit;
    }

    /**
     * Show password reset form
     */
    public function showResetPassword(): void
    {
        $token = $this->params['token'] ?? '';

        if (empty($token)) {
            Flash::error('Invalid password reset link.');
            header('Location: ' . $this->baseUrl . '/login');
            exit;
        }

        // Verify token exists and is valid
        require_once __DIR__ . '/../models/SuperAdmin.php';
        require_once __DIR__ . '/../models/User.php';

        $admin = SuperAdmin::findByPasswordResetToken($token);
        $user = User::findByPasswordResetToken($token);

        if (!$admin && !$user) {
            Flash::error('This password reset link is invalid or has expired.');
            header('Location: ' . $this->baseUrl . '/forgot-password');
            exit;
        }

        $pageTitle = 'Reset Password - ' . ($this->config['app_name'] ?? 'Admin');
        $baseUrl = $this->baseUrl;

        include __DIR__ . '/../templates/auth/reset-password.php';
    }

    /**
     * Process password reset
     */
    public function resetPassword(): void
    {
        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/forgot-password');
            exit;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        if (empty($token)) {
            Flash::error('Invalid password reset link.');
            header('Location: ' . $this->baseUrl . '/forgot-password');
            exit;
        }

        // Validate password
        $validator = Validator::make($_POST, [
            'password' => 'required|min:8|password|confirmed',
        ]);

        if ($validator->fails()) {
            Flash::setErrors($validator->errors());
            Flash::error('Please correct the errors below.');
            header('Location: ' . $this->baseUrl . '/reset-password/' . $token);
            exit;
        }

        // Reset password
        $success = AdminAuth::resetPassword($token, $password);

        if (!$success) {
            Flash::error('This password reset link is invalid or has expired.');
            header('Location: ' . $this->baseUrl . '/forgot-password');
            exit;
        }

        Flash::success('Your password has been reset. Please login with your new password.');
        header('Location: ' . $this->baseUrl . '/login');
        exit;
    }

    /**
     * Stop impersonating and return to super admin
     */
    public function stopImpersonating(): void
    {
        if (AdminAuth::isImpersonating()) {
            AdminAuth::stopImpersonating();
            Flash::success('You are now back as Super Admin.');
        }

        header('Location: ' . $this->baseUrl . '/superadmin/dashboard');
        exit;
    }

    /**
     * Redirect to appropriate dashboard based on role
     */
    private function redirectToDashboard(): void
    {
        if (AdminAuth::isSuperAdmin()) {
            header('Location: ' . $this->baseUrl . '/superadmin/dashboard');
        } elseif (AdminAuth::isEditorInChief()) {
            header('Location: ' . $this->baseUrl . '/editor/dashboard');
        } elseif (AdminAuth::isAuthor()) {
            header('Location: ' . $this->baseUrl . '/author/dashboard');
        } elseif (AdminAuth::isReviewer()) {
            header('Location: ' . $this->baseUrl . '/reviewer/dashboard');
        } else {
            header('Location: ' . $this->baseUrl . '/dashboard');
        }
        exit;
    }
}
