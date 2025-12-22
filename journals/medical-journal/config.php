<?php
/**
 * Medical Journal Configuration
 * Medical Research Journal
 *
 * Each journal frontend has its own configuration with unique API key
 */

return [
    // Central API URL
    'api_url' => 'http://localhost/multi-tenant-system/api',

    // This journal's API key (from database - tenants table)
    'api_key' => 'api_key_medicine_123456789012345678901234567890',

    // Cache settings
    'cache' => [
        'enabled' => true,
        'path' => __DIR__ . '/cache',
        'ttl' => 3600, // 1 hour
    ],

    // Display settings
    'articles_per_page' => 10,
    'recent_articles_count' => 5,
    'announcements_count' => 3,

    // Theme (use 'default' or create custom theme in themes/ folder)
    'theme' => 'default',

    // Debug mode (set to false in production)
    'debug' => true,
];
