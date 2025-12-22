<?php
/**
 * Tenant Model
 * Handles tenant (journal) database operations
 */

// Include the core Database class
require_once __DIR__ . '/../../core/Database.php';

use Core\Database;

class Tenant
{
    const TABLE = 'tenants';

    /**
     * Find tenant by ID
     */
    public static function find(int $id): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = ?";
        $result = Database::query($sql, [$id])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find tenant by slug
     */
    public static function findBySlug(string $slug): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE slug = ?";
        $result = Database::query($sql, [$slug])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find tenant by API key
     */
    public static function findByApiKey(string $apiKey): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE api_key = ? AND is_active = 1";
        $result = Database::query($sql, [$apiKey])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get all tenants
     */
    public static function all(): array
    {
        $sql = "SELECT * FROM " . self::TABLE . " ORDER BY name ASC";
        return Database::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all active tenants
     */
    public static function getActive(): array
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE is_active = 1 ORDER BY name ASC";
        return Database::query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Create a new tenant
     */
    public static function create(array $data): int
    {
        // Generate API key if not provided
        if (!isset($data['api_key'])) {
            $data['api_key'] = self::generateApiKey();
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return Database::insert(self::TABLE, $data);
    }

    /**
     * Update tenant
     */
    public static function update(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        return Database::update(self::TABLE, $data, 'id = ?', [$id]) > 0;
    }

    /**
     * Delete tenant
     */
    public static function delete(int $id): bool
    {
        return Database::delete(self::TABLE, 'id = ?', [$id]) > 0;
    }

    /**
     * Count total tenants
     */
    public static function count(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        $result = Database::query($sql)->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * Count active tenants
     */
    public static function countActive(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE is_active = 1";
        $result = Database::query($sql)->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * Generate a new API key
     */
    public static function generateApiKey(): string
    {
        return 'api_key_' . bin2hex(random_bytes(24));
    }

    /**
     * Regenerate API key for tenant
     */
    public static function regenerateApiKey(int $id): ?string
    {
        $apiKey = self::generateApiKey();

        $data = [
            'api_key' => $apiKey,
            'api_key_created_at' => date('Y-m-d H:i:s'),
        ];

        $success = Database::update(self::TABLE, $data, 'id = ?', [$id]) > 0;

        return $success ? $apiKey : null;
    }

    /**
     * Check if slug exists
     */
    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE slug = ?";
        $params = [$slug];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = Database::query($sql, $params)->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'] > 0;
    }

    /**
     * Get tenant statistics
     */
    public static function getStats(int $id): array
    {
        // Get user count
        $userSql = "SELECT COUNT(*) as count FROM users WHERE tenant_id = ?";
        $userResult = Database::query($userSql, [$id])->fetch(\PDO::FETCH_ASSOC);

        // Get article count
        $articleSql = "SELECT COUNT(*) as count FROM articles WHERE tenant_id = ?";
        $articleResult = Database::query($articleSql, [$id])->fetch(\PDO::FETCH_ASSOC);

        // Get published article count
        $publishedSql = "SELECT COUNT(*) as count FROM articles WHERE tenant_id = ? AND status = 'published'";
        $publishedResult = Database::query($publishedSql, [$id])->fetch(\PDO::FETCH_ASSOC);

        // Get volume count
        $volumeSql = "SELECT COUNT(*) as count FROM volumes WHERE tenant_id = ?";
        $volumeResult = Database::query($volumeSql, [$id])->fetch(\PDO::FETCH_ASSOC);

        return [
            'users' => (int) $userResult['count'],
            'articles' => (int) $articleResult['count'],
            'published_articles' => (int) $publishedResult['count'],
            'volumes' => (int) $volumeResult['count'],
        ];
    }

    /**
     * Get all tenants with stats
     */
    public static function allWithStats(): array
    {
        $tenants = self::all();

        foreach ($tenants as &$tenant) {
            $stats = self::getStats($tenant['id']);
            $tenant['stats'] = $stats;
            // Also add flat properties for easier template access
            $tenant['user_count'] = $stats['users'];
            $tenant['article_count'] = $stats['articles'];
        }

        return $tenants;
    }

    /**
     * Get a single tenant with stats
     */
    public static function findWithStats(int $id): ?array
    {
        $tenant = self::find($id);
        if (!$tenant) {
            return null;
        }

        $stats = self::getStats($id);
        $tenant['stats'] = $stats;
        $tenant['user_count'] = $stats['users'];
        $tenant['article_count'] = $stats['articles'];

        return $tenant;
    }
}
