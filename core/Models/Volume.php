<?php
/**
 * Volume Model
 * Multi-Tenant Journal Management System
 */

namespace Core\Models;

use Core\TenantAwareModel;
use Core\Database;
use Core\Tenant;

class Volume extends TenantAwareModel
{
    protected static string $table = 'volumes';

    protected static array $fillable = [
        'volume_number', 'title', 'year', 'description',
        'is_published', 'published_at'
    ];

    /**
     * Get published volumes
     */
    public static function published(): array
    {
        return static::where(
            ['is_published' => 1],
            ['year' => 'DESC', 'volume_number' => 'DESC']
        );
    }

    /**
     * Get volume with issues
     */
    public static function findWithIssues(int $id): ?array
    {
        $volume = static::find($id);
        if (!$volume) {
            return null;
        }

        $issues = Issue::byVolume($id);

        return [
            'volume' => $volume,
            'issues' => $issues
        ];
    }

    /**
     * Get current volume (latest published)
     */
    public static function current(): ?object
    {
        $volumes = static::published();
        return $volumes[0] ?? null;
    }

    /**
     * Get all volumes with their issues
     */
    public static function allWithIssues(): array
    {
        $volumes = static::published();
        $result = [];

        foreach ($volumes as $volume) {
            $result[] = [
                'volume' => $volume,
                'issues' => Issue::byVolume($volume->id)
            ];
        }

        return $result;
    }

    /**
     * Get volume by number
     */
    public static function findByNumber(int $number): ?object
    {
        return static::findWhere(['volume_number' => $number]);
    }

    /**
     * Get article count for volume
     */
    public static function articleCount(int $id): int
    {
        $result = Database::queryOne(
            "SELECT COUNT(*) as count FROM articles
             WHERE tenant_id = ? AND volume_id = ? AND status = 'published'",
            [Tenant::id(), $id]
        );
        return (int) $result->count;
    }
}
