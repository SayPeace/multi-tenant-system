<?php
/**
 * Tenant-Aware Model Base Class
 * Multi-Tenant Journal Management System
 *
 * All models that contain tenant-specific data should extend this class.
 * Automatically injects tenant_id in all queries to ensure data isolation.
 */

namespace Core;

use Exception;

abstract class TenantAwareModel
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];

    /**
     * Get the base query with tenant filter applied
     */
    protected static function baseQuery(): string
    {
        return "SELECT * FROM " . static::$table . " WHERE tenant_id = ?";
    }

    /**
     * Find by ID (tenant-scoped)
     */
    public static function find(int $id): ?object
    {
        return Database::queryOne(
            static::baseQuery() . " AND " . static::$primaryKey . " = ?",
            [Tenant::id(), $id]
        );
    }

    /**
     * Get all records (tenant-scoped)
     */
    public static function all(array $orderBy = []): array
    {
        $sql = static::baseQuery();

        if (!empty($orderBy)) {
            $orders = [];
            foreach ($orderBy as $column => $direction) {
                $orders[] = "$column $direction";
            }
            $sql .= " ORDER BY " . implode(', ', $orders);
        }

        return Database::queryAll($sql, [Tenant::id()]);
    }

    /**
     * Find records by conditions (tenant-scoped)
     */
    public static function where(array $conditions, array $orderBy = [], ?int $limit = null): array
    {
        $sql = static::baseQuery();
        $params = [Tenant::id()];

        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                // Handle IN clause
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $sql .= " AND $column IN ($placeholders)";
                $params = array_merge($params, $value);
            } else {
                $sql .= " AND $column = ?";
                $params[] = $value;
            }
        }

        if (!empty($orderBy)) {
            $orders = [];
            foreach ($orderBy as $column => $direction) {
                $orders[] = "$column $direction";
            }
            $sql .= " ORDER BY " . implode(', ', $orders);
        }

        if ($limit !== null) {
            $sql .= " LIMIT $limit";
        }

        return Database::queryAll($sql, $params);
    }

    /**
     * Find single record by conditions (tenant-scoped)
     */
    public static function findWhere(array $conditions): ?object
    {
        $results = static::where($conditions, [], 1);
        return $results[0] ?? null;
    }

    /**
     * Create a new record (automatically adds tenant_id)
     */
    public static function create(array $data): int
    {
        // Filter to only fillable fields if defined
        if (!empty(static::$fillable)) {
            $data = array_intersect_key($data, array_flip(static::$fillable));
        }

        // Add tenant_id
        $data['tenant_id'] = Tenant::id();

        return Database::insert(static::$table, $data);
    }

    /**
     * Update record (tenant-scoped)
     */
    public static function update(int $id, array $data): int
    {
        // Filter to only fillable fields if defined
        if (!empty(static::$fillable)) {
            $data = array_intersect_key($data, array_flip(static::$fillable));
        }

        // Ensure we only update within current tenant
        return Database::update(
            static::$table,
            $data,
            static::$primaryKey . " = ? AND tenant_id = ?",
            [$id, Tenant::id()]
        );
    }

    /**
     * Delete record (tenant-scoped)
     */
    public static function delete(int $id): int
    {
        return Database::delete(
            static::$table,
            static::$primaryKey . " = ? AND tenant_id = ?",
            [$id, Tenant::id()]
        );
    }

    /**
     * Count records (tenant-scoped)
     */
    public static function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . static::$table . " WHERE tenant_id = ?";
        $params = [Tenant::id()];

        foreach ($conditions as $column => $value) {
            $sql .= " AND $column = ?";
            $params[] = $value;
        }

        $result = Database::queryOne($sql, $params);
        return (int) $result->count;
    }

    /**
     * Check if record exists (tenant-scoped)
     */
    public static function exists(array $conditions): bool
    {
        return static::count($conditions) > 0;
    }

    /**
     * Find by slug (common pattern)
     */
    public static function findBySlug(string $slug): ?object
    {
        return static::findWhere(['slug' => $slug]);
    }

    /**
     * Paginate results (tenant-scoped)
     */
    public static function paginate(int $page = 1, int $perPage = 10, array $conditions = [], array $orderBy = []): array
    {
        $offset = ($page - 1) * $perPage;
        $total = static::count($conditions);

        $sql = static::baseQuery();
        $params = [Tenant::id()];

        foreach ($conditions as $column => $value) {
            $sql .= " AND $column = ?";
            $params[] = $value;
        }

        if (!empty($orderBy)) {
            $orders = [];
            foreach ($orderBy as $column => $direction) {
                $orders[] = "$column $direction";
            }
            $sql .= " ORDER BY " . implode(', ', $orders);
        }

        $sql .= " LIMIT $perPage OFFSET $offset";

        return [
            'data' => Database::queryAll($sql, $params),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
        ];
    }
}
