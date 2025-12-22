<?php
/**
 * User Model
 * Handles journal user database operations
 */

// Include the core Database class
require_once __DIR__ . '/../../core/Database.php';

use Core\Database;

class User
{
    const TABLE = 'users';

    /**
     * Find user by ID
     */
    public static function find(int $id): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = ?";
        $result = Database::query($sql, [$id])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find user by email and tenant
     */
    public static function findByEmailAndTenant(string $email, int $tenantId): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE email = ? AND tenant_id = ?";
        $result = Database::query($sql, [$email, $tenantId])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find user by remember token
     */
    public static function findByRememberToken(string $token): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE remember_token = ?
                AND remember_token_expires > NOW()
                AND is_active = 1";
        $result = Database::query($sql, [$token])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find user by password reset token
     */
    public static function findByPasswordResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE password_reset_token = ?
                AND password_reset_expires > NOW()
                AND is_active = 1";
        $result = Database::query($sql, [$token])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get all users for a tenant
     */
    public static function allForTenant(int $tenantId, ?string $role = null): array
    {
        $sql = "SELECT id, tenant_id, email, first_name, last_name, title, affiliation,
                       role, is_active, email_verified, last_login_at, created_at
                FROM " . self::TABLE . "
                WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        $sql .= " ORDER BY last_name, first_name";

        return Database::query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get users by role for a tenant
     */
    public static function getByRole(int $tenantId, string $role): array
    {
        return self::allForTenant($tenantId, $role);
    }

    /**
     * Get all reviewers for a tenant
     */
    public static function getReviewers(int $tenantId): array
    {
        return self::getByRole($tenantId, 'reviewer');
    }

    /**
     * Get all editors for a tenant
     */
    public static function getEditors(int $tenantId): array
    {
        $sql = "SELECT id, tenant_id, email, first_name, last_name, title, affiliation,
                       role, is_active, email_verified, last_login_at, created_at
                FROM " . self::TABLE . "
                WHERE tenant_id = ? AND role IN ('editor', 'editor_in_chief', 'admin')
                AND is_active = 1
                ORDER BY last_name, first_name";

        return Database::query($sql, [$tenantId])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Create a new user
     */
    public static function create(array $data): int
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return Database::insert(self::TABLE, $data);
    }

    /**
     * Update user
     */
    public static function update(int $id, array $data): bool
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        return Database::update(self::TABLE, $data, 'id = ?', [$id]) > 0;
    }

    /**
     * Update last login info
     */
    public static function updateLastLogin(int $id, ?string $ip = null): bool
    {
        $data = [
            'last_login_at' => date('Y-m-d H:i:s'),
        ];

        if ($ip) {
            $data['last_login_ip'] = $ip;
        }

        return Database::update(self::TABLE, $data, 'id = ?', [$id]) > 0;
    }

    /**
     * Set remember me token
     */
    public static function setRememberToken(int $id, string $token, string $expires): bool
    {
        $data = [
            'remember_token' => $token,
            'remember_token_expires' => $expires,
        ];

        return Database::update(self::TABLE, $data, 'id = ?', [$id]) > 0;
    }

    /**
     * Clear remember me token
     */
    public static function clearRememberToken(int $id): bool
    {
        $data = [
            'remember_token' => null,
            'remember_token_expires' => null,
        ];

        return Database::update(self::TABLE, $data, 'id = ?', [$id]) > 0;
    }

    /**
     * Set password reset token
     */
    public static function setPasswordResetToken(int $id, string $token, string $expires): bool
    {
        $data = [
            'password_reset_token' => $token,
            'password_reset_expires' => $expires,
        ];

        return Database::update(self::TABLE, $data, 'id = ?', [$id]) > 0;
    }

    /**
     * Update password
     */
    public static function updatePassword(int $id, string $hash): bool
    {
        $data = [
            'password_hash' => $hash,
            'password_reset_token' => null,
            'password_reset_expires' => null,
        ];

        return Database::update(self::TABLE, $data, 'id = ?', [$id]) > 0;
    }

    /**
     * Delete user
     */
    public static function delete(int $id): bool
    {
        return Database::delete(self::TABLE, 'id = ?', [$id]) > 0;
    }

    /**
     * Count users for a tenant
     */
    public static function countForTenant(int $tenantId, ?string $role = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        $result = Database::query($sql, $params)->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * Check if email exists for tenant
     */
    public static function emailExistsForTenant(string $email, int $tenantId, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE email = ? AND tenant_id = ?";
        $params = [$email, $tenantId];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = Database::query($sql, $params)->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'] > 0;
    }

    /**
     * Search users for a tenant
     */
    public static function search(int $tenantId, string $query): array
    {
        $sql = "SELECT id, tenant_id, email, first_name, last_name, title, affiliation,
                       role, is_active, email_verified, created_at
                FROM " . self::TABLE . "
                WHERE tenant_id = ?
                AND (email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)
                ORDER BY last_name, first_name
                LIMIT 50";

        $searchTerm = '%' . $query . '%';
        $params = [$tenantId, $searchTerm, $searchTerm, $searchTerm];

        return Database::query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get user with tenant info
     */
    public static function findWithTenant(int $id): ?array
    {
        $sql = "SELECT u.*, t.name as tenant_name, t.slug as tenant_slug
                FROM " . self::TABLE . " u
                JOIN tenants t ON u.tenant_id = t.id
                WHERE u.id = ?";
        $result = Database::query($sql, [$id])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Verify email
     */
    public static function verifyEmail(int $id): bool
    {
        $data = [
            'email_verified' => 1,
            'email_verification_token' => null,
        ];

        return Database::update(self::TABLE, $data, 'id = ?', [$id]) > 0;
    }

    /**
     * Set email verification token
     */
    public static function setEmailVerificationToken(int $id, string $token): bool
    {
        $data = [
            'email_verification_token' => $token,
        ];

        return Database::update(self::TABLE, $data, 'id = ?', [$id]) > 0;
    }

    /**
     * Find by email verification token
     */
    public static function findByEmailVerificationToken(string $token): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE email_verification_token = ?";
        $result = Database::query($sql, [$token])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
