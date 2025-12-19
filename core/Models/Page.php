<?php
/**
 * Page Model
 * Multi-Tenant Journal Management System
 */

namespace Core\Models;

use Core\TenantAwareModel;

class Page extends TenantAwareModel
{
    protected static string $table = 'pages';

    protected static array $fillable = [
        'slug', 'title', 'content', 'menu_order',
        'show_in_menu', 'is_published', 'meta_title', 'meta_description'
    ];

    /**
     * Get published pages
     */
    public static function published(): array
    {
        return static::where(
            ['is_published' => 1],
            ['menu_order' => 'ASC']
        );
    }

    /**
     * Get menu pages
     */
    public static function menu(): array
    {
        return static::where(
            ['show_in_menu' => 1, 'is_published' => 1],
            ['menu_order' => 'ASC']
        );
    }

    /**
     * Find published page by slug
     */
    public static function findPublishedBySlug(string $slug): ?object
    {
        return static::findWhere([
            'slug' => $slug,
            'is_published' => 1
        ]);
    }
}
