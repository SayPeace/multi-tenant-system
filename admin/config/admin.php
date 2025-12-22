<?php
/**
 * Admin Dashboard Configuration
 */

return [
    // Application settings
    'app_name' => 'Journal Admin',
    'app_version' => '1.0.0',

    // Base URL for admin (auto-detected if empty)
    'base_url' => '/multi-tenant-system/admin',

    // Session settings
    'session' => [
        'name' => 'ADMIN_SESSION',
        'lifetime' => 7200,        // 2 hours
        'cookie_httponly' => true,
        'cookie_secure' => false,  // Set to true in production with HTTPS
        'cookie_samesite' => 'Lax',
    ],

    // Remember me settings
    'remember_me' => [
        'enabled' => true,
        'lifetime' => 2592000,     // 30 days
        'cookie_name' => 'admin_remember',
    ],

    // Password settings
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_number' => true,
        'require_special' => false,
    ],

    // Login security
    'login' => [
        'max_attempts' => 5,
        'lockout_time' => 900,     // 15 minutes
        'log_attempts' => true,
    ],

    // Email settings (for password reset, notifications)
    'email' => [
        'enabled' => true,
        'from_address' => 'noreply@localhost',
        'from_name' => 'Journal System',
        'driver' => 'mail',        // 'mail' or 'smtp'

        // SMTP settings (if driver is 'smtp')
        'smtp' => [
            'host' => 'localhost',
            'port' => 587,
            'encryption' => 'tls',
            'username' => '',
            'password' => '',
        ],
    ],

    // File upload settings
    'uploads' => [
        'path' => __DIR__ . '/../../public/uploads',
        'max_size' => 10485760,    // 10MB
        'allowed_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
    ],

    // Pagination
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100,
    ],

    // Debug mode (set to false in production)
    'debug' => true,

    // Timezone
    'timezone' => 'UTC',
];
