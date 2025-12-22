<?php
/**
 * SuperAdmin Model
 * Handles super admin database operations
 */

// Include the core Database class
require_once __DIR__ . '/../../core/Database.php';

use Core\Database;

class SuperAdmin
{
    const TABLE = 'super_admins';

    /**
     * Find super admin by ID
     */
    public static function find(int $id): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = ?";
        $result = Database::query($sql, [$id])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find super admin by email
     */
    public static function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE email = ?";
        $result = Database::query($sql, [$email])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find super admin by remember token
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
     * Find super admin by password reset token
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
     * Get all super admins
     */
    public static function all(): array
    {
        $sql = "SELECT id, email, first_name, last_name, is_active, last_login_at, created_at
                FROM " . self::TABLE . "
                ORDER BY id ASC";
        return Database::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Create a new super admin
     */
    public static function create(array $data): int
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);

        return Database::insert(self::TABLE, $data);
    }

    /**
     * Update super admin
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
     * Clear password reset token
     */
    public static function clearPasswordResetToken(int $id): bool
    {
        $data = [
            'password_reset_token' => null,
            'password_reset_expires' => null,
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
     * Delete super admin
     */
    public static function delete(int $id): bool
    {
        return Database::delete(self::TABLE, 'id = ?', [$id]) > 0;
    }

    /**
     * Count total super admins
     */
    public static function count(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        $result = Database::query($sql)->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * Check if email exists
     */
    public static function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = Database::query($sql, $params)->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'] > 0;
    }
}
