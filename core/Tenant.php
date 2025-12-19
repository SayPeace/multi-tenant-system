<?php
/**
 * Tenant Management Class
 * Multi-Tenant Journal Management System
 *
 * Handles tenant resolution, context management, and API key validation
 */

namespace Core;

use Exception;

class Tenant
{
    private static ?object $current = null;

    /**
     * Resolve tenant from domain (for frontend)
     */
    public static function resolveFromDomain(string $domain): ?object
    {
        $domain = strtolower(trim($domain));

        // Remove port number if present
        if (strpos($domain, ':') !== false) {
            $domain = explode(':', $domain)[0];
        }

        $tenant = Database::queryOne(
            "SELECT * FROM tenants
             WHERE (subdomain = ? OR custom_domain = ?)
             AND is_active = TRUE",
            [$domain, $domain]
        );

        if ($tenant) {
            self::$current = $tenant;
            // Parse JSON settings
            if ($tenant->settings) {
                $tenant->settings = json_decode($tenant->settings, true);
            }
        }

        return $tenant;
    }

    /**
     * Resolve tenant from API key (for API requests)
     */
    public static function resolveFromApiKey(string $apiKey): ?object
    {
        $tenant = Database::queryOne(
            "SELECT * FROM tenants
             WHERE api_key = ?
             AND is_active = TRUE",
            [$apiKey]
        );

        if ($tenant) {
            self::$current = $tenant;
            if ($tenant->settings) {
                $tenant->settings = json_decode($tenant->settings, true);
            }
        }

        return $tenant;
    }

    /**
     * Set current tenant manually
     */
    public static function setCurrent(object $tenant): void
    {
        self::$current = $tenant;
    }

    /**
     * Get current tenant
     */
    public static function current(): object
    {
        if (self::$current === null) {
            throw new Exception('Tenant not resolved. Call resolve() first.');
        }
        return self::$current;
    }

    /**
     * Get current tenant ID
     */
    public static function id(): int
    {
        return (int) self::current()->id;
    }

    /**
     * Check if tenant is resolved
     */
    public static function isResolved(): bool
    {
        return self::$current !== null;
    }

    /**
     * Get all active tenants
     */
    public static function getAll(): array
    {
        return Database::queryAll(
            "SELECT id, slug, name, subdomain, custom_domain, logo_url, primary_color
             FROM tenants
             WHERE is_active = TRUE
             ORDER BY name"
        );
    }

    /**
     * Get tenant by slug
     */
    public static function getBySlug(string $slug): ?object
    {
        return Database::queryOne(
            "SELECT * FROM tenants WHERE slug = ? AND is_active = TRUE",
            [$slug]
        );
    }

    /**
     * Generate a new API key
     */
    public static function generateApiKey(): string
    {
        return 'api_' . bin2hex(random_bytes(30));
    }

    /**
     * Create a new tenant
     */
    public static function create(array $data): int
    {
        $data['api_key'] = $data['api_key'] ?? self::generateApiKey();
        $data['api_key_created_at'] = date('Y-m-d H:i:s');

        return Database::insert('tenants', $data);
    }

    /**
     * Update tenant
     */
    public static function update(int $id, array $data): int
    {
        return Database::update('tenants', $data, 'id = ?', [$id]);
    }

    /**
     * Get tenant branding/theme config
     */
    public static function getBranding(): array
    {
        $tenant = self::current();
        return [
            'name' => $tenant->name,
            'tagline' => $tenant->tagline,
            'logo_url' => $tenant->logo_url,
            'favicon_url' => $tenant->favicon_url,
            'primary_color' => $tenant->primary_color,
            'secondary_color' => $tenant->secondary_color,
            'theme' => $tenant->theme,
        ];
    }

    /**
     * Clear current tenant (for testing)
     */
    public static function clear(): void
    {
        self::$current = null;
    }
}
