<?php
/**
 * Announcement Model
 * Multi-Tenant Journal Management System
 */

namespace Core\Models;

use Core\TenantAwareModel;
use Core\Database;
use Core\Tenant;

class Announcement extends TenantAwareModel
{
    protected static string $table = 'announcements';

    protected static array $fillable = [
        'title', 'content', 'is_published', 'published_at', 'expires_at'
    ];

    /**
     * Get active announcements
     */
    public static function active(): array
    {
        $sql = static::baseQuery() .
            " AND is_published = 1
              AND (expires_at IS NULL OR expires_at > NOW())
              ORDER BY published_at DESC";

        return Database::queryAll($sql, [Tenant::id()]);
    }

    /**
     * Get latest announcement
     */
    public static function latest(int $limit = 5): array
    {
        $sql = static::baseQuery() .
            " AND is_published = 1
              AND (expires_at IS NULL OR expires_at > NOW())
              ORDER BY published_at DESC
              LIMIT ?";

        return Database::queryAll($sql, [Tenant::id(), $limit]);
    }
}
