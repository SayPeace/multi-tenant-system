<?php
/**
 * Frontend Configuration
 * Multi-Tenant Journal Management System
 *
 * Each journal frontend should have its own copy of this config
 * with their unique API key and settings.
 */

return [
    // Central API URL
    'api_url' => 'http://localhost/multi-tenant-system/api',

    // This journal's API key (get from admin dashboard)
    'api_key' => 'YOUR_API_KEY_HERE',

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

    // Theme
    'theme' => 'default',

    // Debug mode (disable in production)
    'debug' => true,
];
