<?php
/**
 * Article Model
 * Multi-Tenant Journal Management System
 */

namespace Core\Models;

use Core\TenantAwareModel;
use Core\Database;
use Core\Tenant;

class Article extends TenantAwareModel
{
    protected static string $table = 'articles';

    protected static array $fillable = [
        'volume_id', 'issue_id', 'title', 'slug', 'abstract', 'keywords',
        'content', 'pdf_url', 'supplementary_files', 'doi', 'pages',
        'article_number', 'status', 'submitted_at', 'accepted_at',
        'published_at', 'meta_title', 'meta_description'
    ];

    /**
     * Get published articles
     */
    public static function published(int $limit = null): array
    {
        $sql = static::baseQuery() . " AND status = 'published' ORDER BY published_at DESC";
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        return Database::queryAll($sql, [Tenant::id()]);
    }

    /**
     * Get recent articles
     */
    public static function recent(int $limit = 5): array
    {
        return static::published($limit);
    }

    /**
     * Get articles by volume
     */
    public static function byVolume(int $volumeId): array
    {
        return static::where(
            ['volume_id' => $volumeId, 'status' => 'published'],
            ['published_at' => 'DESC']
        );
    }

    /**
     * Get articles by issue
     */
    public static function byIssue(int $issueId): array
    {
        return static::where(
            ['issue_id' => $issueId, 'status' => 'published'],
            ['article_number' => 'ASC', 'published_at' => 'DESC']
        );
    }

    /**
     * Get article with authors
     */
    public static function findWithAuthors(int $id): ?array
    {
        $article = static::find($id);
        if (!$article) {
            return null;
        }

        $authors = Database::queryAll(
            "SELECT * FROM article_authors
             WHERE article_id = ? AND tenant_id = ?
             ORDER BY author_order",
            [$id, Tenant::id()]
        );

        return [
            'article' => $article,
            'authors' => $authors
        ];
    }

    /**
     * Get article by slug with authors
     */
    public static function findBySlugWithAuthors(string $slug): ?array
    {
        $article = static::findBySlug($slug);
        if (!$article) {
            return null;
        }

        return static::findWithAuthors($article->id);
    }

    /**
     * Search articles
     */
    public static function search(string $query, int $limit = 20): array
    {
        $sql = "SELECT * FROM articles
                WHERE tenant_id = ?
                AND status = 'published'
                AND MATCH(title, abstract, keywords) AGAINST(? IN BOOLEAN MODE)
                ORDER BY published_at DESC
                LIMIT ?";

        return Database::queryAll($sql, [Tenant::id(), $query, $limit]);
    }

    /**
     * Increment view count
     */
    public static function incrementViews(int $id): void
    {
        Database::query(
            "UPDATE articles SET view_count = view_count + 1
             WHERE id = ? AND tenant_id = ?",
            [$id, Tenant::id()]
        );
    }

    /**
     * Increment download count
     */
    public static function incrementDownloads(int $id): void
    {
        Database::query(
            "UPDATE articles SET download_count = download_count + 1
             WHERE id = ? AND tenant_id = ?",
            [$id, Tenant::id()]
        );
    }

    /**
     * Get article statistics
     */
    public static function getStats(): array
    {
        $stats = Database::queryOne(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) as under_review,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as drafts,
                SUM(view_count) as total_views,
                SUM(download_count) as total_downloads
             FROM articles WHERE tenant_id = ?",
            [Tenant::id()]
        );

        return (array) $stats;
    }
}
