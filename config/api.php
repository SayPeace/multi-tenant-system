<?php
/**
 * API Configuration
 * Multi-Tenant Journal Management System
 */

return [
    // Base URL of the Central API
    'base_url' => getenv('API_BASE_URL') ?: 'http://localhost/multi-tenant-system/api',

    // API Version
    'version' => 'v1',

    // Rate limiting (requests per minute per API key)
    'rate_limit' => 60,

    // Cache TTL in seconds
    'cache_ttl' => 3600,

    // Allowed origins for CORS
    'allowed_origins' => [
        '*' // In production, specify exact domains
    ],

    // API key header name
    'api_key_header' => 'X-API-Key',

    // JWT settings for admin authentication
    'jwt' => [
        'secret' => getenv('JWT_SECRET') ?: 'change-this-in-production-to-a-secure-random-key',
        'expiry' => 86400, // 24 hours
        'algorithm' => 'HS256'
    ]
];
