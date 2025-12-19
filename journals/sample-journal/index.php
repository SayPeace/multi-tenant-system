<?php
/**
 * Sample Journal Frontend
 * Multi-Tenant Journal Management System
 *
 * This is an example of a distributed frontend for a specific journal.
 * Each journal would have their own copy of this with their unique API key.
 *
 * To create a new journal frontend:
 * 1. Copy the frontend-template folder
 * 2. Update config.php with the journal's API key
 * 3. Customize the theme as needed
 * 4. Point the journal's domain to this directory
 */

// Load the frontend template
require_once __DIR__ . '/../../frontend-template/ApiClient.php';
require_once __DIR__ . '/../../frontend-template/Cache.php';

// Load configuration
$config = require __DIR__ . '/config.php';

// Debug mode
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Initialize API client
$api = new ApiClient($config['api_url'], $config['api_key']);

// Initialize cache
$cache = new Cache($config['cache']['path'], $config['cache']['ttl']);

// Get tenant info (cached)
try {
    $tenant = $cache->remember('tenant', function() use ($api) {
        return $api->getTenant();
    });
} catch (Exception $e) {
    if ($config['debug']) {
        die('Failed to connect to API: ' . $e->getMessage());
    } else {
        die('Service temporarily unavailable. Please try again later.');
    }
}

// Get menu pages (cached)
$menuPages = $cache->remember('menu_pages', function() use ($api) {
    return $api->getMenuPages();
});

// Parse request
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/' && $basePath !== '\\') {
    $uri = str_replace($basePath, '', $uri);
}
$uri = '/' . trim($uri, '/');

// Use custom theme if exists, otherwise fall back to default
$customThemePath = __DIR__ . '/themes/' . ($config['theme'] ?? 'default');
$defaultThemePath = __DIR__ . '/../../frontend-template/themes/default';

$themePath = is_dir($customThemePath) ? $customThemePath : $defaultThemePath;

// Route request (same logic as frontend-template/index.php)
try {
    // Include the routing logic from frontend-template
    require_once __DIR__ . '/../../frontend-template/index.php';
} catch (Exception $e) {
    if ($config['debug']) {
        echo '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    } else {
        include $themePath . '/error.php';
    }
}
