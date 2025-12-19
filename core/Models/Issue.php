<?php
/**
 * Issue Model
 * Multi-Tenant Journal Management System
 */

namespace Core\Models;

use Core\TenantAwareModel;
use Core\Database;
use Core\Tenant;

class Issue extends TenantAwareModel
{
    protected static string $table = 'issues';

    protected static array $fillable = [
        'volume_id', 'issue_number', 'title', 'month', 'cover_image',
        'description', 'is_special_issue', 'is_published', 'published_at'
    ];

    /**
     * Get published issues
     */
    public static function published(): array
    {
        return static::where(
            ['is_published' => 1],
            ['published_at' => 'DESC']
        );
    }

    /**
     * Get issues by volume
     */
    public static function byVolume(int $volumeId): array
    {
        return static::where(
            ['volume_id' => $volumeId, 'is_published' => 1],
            ['issue_number' => 'DESC']
        );
    }

    /**
     * Get issue with articles
     */
    public static function findWithArticles(int $id): ?array
    {
        $issue = static::find($id);
        if (!$issue) {
            return null;
        }

        $articles = Article::byIssue($id);

        // Get volume info
        $volume = Volume::find($issue->volume_id);

        return [
            'issue' => $issue,
            'volume' => $volume,
            'articles' => $articles
        ];
    }

    /**
     * Get current issue (latest published)
     */
    public static function current(): ?object
    {
        $issues = static::published();
        return $issues[0] ?? null;
    }

    /**
     * Get current issue with articles
     */
    public static function currentWithArticles(): ?array
    {
        $issue = static::current();
        if (!$issue) {
            return null;
        }

        return static::findWithArticles($issue->id);
    }

    /**
     * Get special issues
     */
    public static function specialIssues(): array
    {
        return static::where(
            ['is_special_issue' => 1, 'is_published' => 1],
            ['published_at' => 'DESC']
        );
    }

    /**
     * Get article count for issue
     */
    public static function articleCount(int $id): int
    {
        $result = Database::queryOne(
            "SELECT COUNT(*) as count FROM articles
             WHERE tenant_id = ? AND issue_id = ? AND status = 'published'",
            [Tenant::id(), $id]
        );
        return (int) $result->count;
    }
}
