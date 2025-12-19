<?php
/**
 * Editorial Board Model
 * Multi-Tenant Journal Management System
 */

namespace Core\Models;

use Core\TenantAwareModel;
use Core\Database;
use Core\Tenant;

class EditorialBoard extends TenantAwareModel
{
    protected static string $table = 'editorial_board';

    protected static array $fillable = [
        'user_id', 'name', 'email', 'title', 'affiliation', 'country',
        'photo_url', 'bio', 'position', 'display_order', 'is_active'
    ];

    /**
     * Get active board members
     */
    public static function active(): array
    {
        return static::where(
            ['is_active' => 1],
            ['display_order' => 'ASC']
        );
    }

    /**
     * Get board members by position
     */
    public static function byPosition(string $position): array
    {
        return static::where(
            ['position' => $position, 'is_active' => 1],
            ['display_order' => 'ASC']
        );
    }

    /**
     * Get grouped by position
     */
    public static function groupedByPosition(): array
    {
        $members = static::active();
        $grouped = [
            'editor_in_chief' => [],
            'managing_editor' => [],
            'associate_editor' => [],
            'editorial_board' => [],
            'advisory_board' => []
        ];

        foreach ($members as $member) {
            $grouped[$member->position][] = $member;
        }

        return $grouped;
    }

    /**
     * Get editor in chief
     */
    public static function editorInChief(): ?object
    {
        return static::findWhere([
            'position' => 'editor_in_chief',
            'is_active' => 1
        ]);
    }
}
