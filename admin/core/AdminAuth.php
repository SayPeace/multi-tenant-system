<?php
/**
 * Admin Authentication Class
 * Handles authentication for the admin dashboard
 */

class AdminAuth
{
    // User types
    const TYPE_SUPER_ADMIN = 'super_admin';
    const TYPE_USER = 'user';

    // Roles
    const ROLE_EDITOR_IN_CHIEF = 'editor_in_chief';
    const ROLE_EDITOR = 'editor';
    const ROLE_ADMIN = 'admin';
    const ROLE_AUTHOR = 'author';
    const ROLE_REVIEWER = 'reviewer';

    /**
     * Attempt login for super admin
     */
    public static function attemptSuperAdmin(string $email, string $password, bool $remember = false): bool
    {
        require_once __DIR__ . '/../models/SuperAdmin.php';

        $admin = SuperAdmin::findByEmail($email);

        if (!$admin) {
            return false;
        }

        if (!$admin['is_active']) {
            return false;
        }

        if (!password_verify($password, $admin['password_hash'])) {
            return false;
        }

        // Set session
        Session::regenerate();
        Session::set('auth_type', self::TYPE_SUPER_ADMIN);
        Session::set('auth_id', $admin['id']);
        Session::set('auth_email', $admin['email']);
        Session::set('auth_name', $admin['first_name'] . ' ' . $admin['last_name']);

        // Update last login
        SuperAdmin::updateLastLogin($admin['id'], $_SERVER['REMOTE_ADDR'] ?? null);

        // Handle remember me
        if ($remember) {
            self::setRememberToken($admin['id'], self::TYPE_SUPER_ADMIN);
        }

        return true;
    }

    /**
     * Attempt login for journal user (editor, author, reviewer)
     */
    public static function attemptUser(int $tenantId, string $email, string $password, bool $remember = false): bool
    {
        require_once __DIR__ . '/../models/User.php';

        $user = User::findByEmailAndTenant($email, $tenantId);

        if (!$user) {
            return false;
        }

        if (!$user['is_active']) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Set session
        Session::regenerate();
        Session::set('auth_type', self::TYPE_USER);
        Session::set('auth_id', $user['id']);
        Session::set('auth_email', $user['email']);
        Session::set('auth_name', trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
        Session::set('auth_tenant_id', $user['tenant_id']);
        Session::set('auth_role', $user['role']);

        // Update last login
        User::updateLastLogin($user['id'], $_SERVER['REMOTE_ADDR'] ?? null);

        // Handle remember me
        if ($remember) {
            self::setRememberToken($user['id'], self::TYPE_USER);
        }

        return true;
    }

    /**
     * Check if user is authenticated
     */
    public static function check(): bool
    {
        Session::start();

        if (Session::has('auth_id') && Session::has('auth_type')) {
            return true;
        }

        // Try remember me cookie
        return self::checkRememberToken();
    }

    /**
     * Get the authenticated user data
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        $type = Session::get('auth_type');
        $id = Session::get('auth_id');

        if ($type === self::TYPE_SUPER_ADMIN) {
            require_once __DIR__ . '/../models/SuperAdmin.php';
            return SuperAdmin::find($id);
        } else {
            require_once __DIR__ . '/../models/User.php';
            return User::find($id);
        }
    }

    /**
     * Get the authenticated user ID
     */
    public static function id(): ?int
    {
        return Session::get('auth_id');
    }

    /**
     * Get the authenticated user's tenant ID
     */
    public static function tenantId(): ?int
    {
        return Session::get('auth_tenant_id');
    }

    /**
     * Get the authenticated user's role
     */
    public static function role(): ?string
    {
        if (self::isSuperAdmin()) {
            return 'super_admin';
        }
        return Session::get('auth_role');
    }

    /**
     * Check if user is super admin
     */
    public static function isSuperAdmin(): bool
    {
        return Session::get('auth_type') === self::TYPE_SUPER_ADMIN;
    }

    /**
     * Check if user is editor-in-chief (or admin/editor)
     */
    public static function isEditorInChief(): bool
    {
        $role = Session::get('auth_role');
        return in_array($role, [self::ROLE_EDITOR_IN_CHIEF, self::ROLE_ADMIN, self::ROLE_EDITOR]);
    }

    /**
     * Check if user is author
     */
    public static function isAuthor(): bool
    {
        return Session::get('auth_role') === self::ROLE_AUTHOR;
    }

    /**
     * Check if user is reviewer
     */
    public static function isReviewer(): bool
    {
        return Session::get('auth_role') === self::ROLE_REVIEWER;
    }

    /**
     * Check if user has any of the given roles
     */
    public static function hasRole(array $roles): bool
    {
        $userRole = self::role();
        return in_array($userRole, $roles);
    }

    /**
     * Logout the user
     */
    public static function logout(): void
    {
        // Clear remember token
        self::clearRememberToken();

        // Destroy session
        Session::destroy();
    }

    /**
     * Set remember me token
     */
    private static function setRememberToken(int $id, string $type): void
    {
        $config = require __DIR__ . '/../config/admin.php';
        $lifetime = $config['remember_me']['lifetime'] ?? 2592000; // 30 days
        $cookieName = $config['remember_me']['cookie_name'] ?? 'admin_remember';

        $token = bin2hex(random_bytes(32));
        $expires = time() + $lifetime;

        // Store token in database
        if ($type === self::TYPE_SUPER_ADMIN) {
            require_once __DIR__ . '/../models/SuperAdmin.php';
            SuperAdmin::setRememberToken($id, $token, date('Y-m-d H:i:s', $expires));
        } else {
            require_once __DIR__ . '/../models/User.php';
            User::setRememberToken($id, $token, date('Y-m-d H:i:s', $expires));
        }

        // Set cookie
        $cookieValue = base64_encode($type . ':' . $id . ':' . $token);
        setcookie($cookieName, $cookieValue, [
            'expires' => $expires,
            'path' => '/',
            'httponly' => true,
            'secure' => isset($_SERVER['HTTPS']),
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Check remember me token
     */
    private static function checkRememberToken(): bool
    {
        $config = require __DIR__ . '/../config/admin.php';
        $cookieName = $config['remember_me']['cookie_name'] ?? 'admin_remember';

        if (!isset($_COOKIE[$cookieName])) {
            return false;
        }

        $decoded = base64_decode($_COOKIE[$cookieName]);
        $parts = explode(':', $decoded, 3);

        if (count($parts) !== 3) {
            self::clearRememberToken();
            return false;
        }

        list($type, $id, $token) = $parts;
        $id = (int) $id;

        // Verify token
        if ($type === self::TYPE_SUPER_ADMIN) {
            require_once __DIR__ . '/../models/SuperAdmin.php';
            $user = SuperAdmin::findByRememberToken($token);

            if (!$user || $user['id'] !== $id) {
                self::clearRememberToken();
                return false;
            }

            // Set session
            Session::regenerate();
            Session::set('auth_type', self::TYPE_SUPER_ADMIN);
            Session::set('auth_id', $user['id']);
            Session::set('auth_email', $user['email']);
            Session::set('auth_name', $user['first_name'] . ' ' . $user['last_name']);

            return true;
        } else {
            require_once __DIR__ . '/../models/User.php';
            $user = User::findByRememberToken($token);

            if (!$user || $user['id'] !== $id) {
                self::clearRememberToken();
                return false;
            }

            // Set session
            Session::regenerate();
            Session::set('auth_type', self::TYPE_USER);
            Session::set('auth_id', $user['id']);
            Session::set('auth_email', $user['email']);
            Session::set('auth_name', trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
            Session::set('auth_tenant_id', $user['tenant_id']);
            Session::set('auth_role', $user['role']);

            return true;
        }
    }

    /**
     * Clear remember me token
     */
    private static function clearRememberToken(): void
    {
        $config = require __DIR__ . '/../config/admin.php';
        $cookieName = $config['remember_me']['cookie_name'] ?? 'admin_remember';

        // Clear from database if logged in
        if (Session::has('auth_id') && Session::has('auth_type')) {
            $id = Session::get('auth_id');
            $type = Session::get('auth_type');

            if ($type === self::TYPE_SUPER_ADMIN) {
                require_once __DIR__ . '/../models/SuperAdmin.php';
                SuperAdmin::clearRememberToken($id);
            } else {
                require_once __DIR__ . '/../models/User.php';
                User::clearRememberToken($id);
            }
        }

        // Delete cookie
        setcookie($cookieName, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => isset($_SERVER['HTTPS']),
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Generate password reset token
     */
    public static function generatePasswordResetToken(string $email, string $type): ?string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        if ($type === self::TYPE_SUPER_ADMIN) {
            require_once __DIR__ . '/../models/SuperAdmin.php';
            $user = SuperAdmin::findByEmail($email);

            if ($user) {
                SuperAdmin::setPasswordResetToken($user['id'], $token, $expires);
                return $token;
            }
        } else {
            // For regular users, we need tenant context
            // This would be handled differently
            return null;
        }

        return null;
    }

    /**
     * Reset password with token
     */
    public static function resetPassword(string $token, string $newPassword): bool
    {
        // Try super admin first
        require_once __DIR__ . '/../models/SuperAdmin.php';
        $admin = SuperAdmin::findByPasswordResetToken($token);

        if ($admin) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            return SuperAdmin::updatePassword($admin['id'], $hash);
        }

        // Try regular user
        require_once __DIR__ . '/../models/User.php';
        $user = User::findByPasswordResetToken($token);

        if ($user) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            return User::updatePassword($user['id'], $hash);
        }

        return false;
    }

    /**
     * Require authentication (redirect if not logged in)
     */
    public static function requireAuth(string $redirectUrl = '/admin/login'): void
    {
        if (!self::check()) {
            $config = require __DIR__ . '/../config/admin.php';
            $baseUrl = $config['base_url'] ?? '/multi-tenant-system/admin';
            header('Location: ' . $baseUrl . '/login');
            exit;
        }
    }

    /**
     * Require specific role(s)
     */
    public static function requireRole(array $roles): void
    {
        self::requireAuth();

        if (!self::hasRole($roles)) {
            http_response_code(403);
            echo 'Access denied. You do not have permission to access this resource.';
            exit;
        }
    }

    /**
     * Impersonate a journal user (super admin only)
     */
    public static function impersonate(int $userId): bool
    {
        if (!self::isSuperAdmin()) {
            return false;
        }

        require_once __DIR__ . '/../models/User.php';
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        // Store original admin info
        Session::set('impersonating_from', [
            'id' => Session::get('auth_id'),
            'email' => Session::get('auth_email'),
            'type' => self::TYPE_SUPER_ADMIN,
        ]);

        // Switch to user
        Session::set('auth_type', self::TYPE_USER);
        Session::set('auth_id', $user['id']);
        Session::set('auth_email', $user['email']);
        Session::set('auth_name', trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
        Session::set('auth_tenant_id', $user['tenant_id']);
        Session::set('auth_role', $user['role']);
        Session::set('is_impersonating', true);

        return true;
    }

    /**
     * Stop impersonating and return to super admin
     */
    public static function stopImpersonating(): bool
    {
        if (!Session::get('is_impersonating')) {
            return false;
        }

        $original = Session::get('impersonating_from');

        if (!$original) {
            return false;
        }

        require_once __DIR__ . '/../models/SuperAdmin.php';
        $admin = SuperAdmin::find($original['id']);

        if (!$admin) {
            return false;
        }

        // Restore super admin session
        Session::set('auth_type', self::TYPE_SUPER_ADMIN);
        Session::set('auth_id', $admin['id']);
        Session::set('auth_email', $admin['email']);
        Session::set('auth_name', $admin['first_name'] . ' ' . $admin['last_name']);
        Session::remove('auth_tenant_id');
        Session::remove('auth_role');
        Session::remove('is_impersonating');
        Session::remove('impersonating_from');

        return true;
    }

    /**
     * Check if currently impersonating
     */
    public static function isImpersonating(): bool
    {
        return (bool) Session::get('is_impersonating');
    }
}
