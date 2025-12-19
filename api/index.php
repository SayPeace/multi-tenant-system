<?php
/**
 * Central API Entry Point
 * Multi-Tenant Journal Management System
 *
 * This is the main entry point for the Central API.
 * All journal frontends communicate with this API using their API keys.
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Load dependencies
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Tenant.php';
require_once __DIR__ . '/../core/TenantAwareModel.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/middleware/ApiAuth.php';

// Load models
require_once __DIR__ . '/../core/Models/Article.php';
require_once __DIR__ . '/../core/Models/Volume.php';
require_once __DIR__ . '/../core/Models/Issue.php';
require_once __DIR__ . '/../core/Models/EditorialBoard.php';
require_once __DIR__ . '/../core/Models/Page.php';
require_once __DIR__ . '/../core/Models/Announcement.php';

use Core\Response;
use Api\Middleware\ApiAuth;

// Initialize API
ApiAuth::init();

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path (adjust based on your setup)
$basePath = '/multi-tenant-system/api';
$uri = str_replace($basePath, '', $uri);
$uri = '/' . trim($uri, '/');

// Route request
try {
    // Public endpoints (no auth required)
    $publicEndpoints = [
        '/health',
        '/info'
    ];

    $isPublic = in_array($uri, $publicEndpoints);

    // Authenticate for non-public endpoints
    if (!$isPublic) {
        ApiAuth::authenticate();
    }

    // Load and execute endpoint
    $endpoint = routeRequest($method, $uri);

    if ($endpoint === null) {
        Response::notFound("Endpoint not found: $method $uri");
    }

} catch (PDOException $e) {
    Response::serverError('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    Response::error($e->getMessage(), 400);
}

/**
 * Route request to appropriate endpoint
 */
function routeRequest(string $method, string $uri)
{
    // Health check
    if ($uri === '/health' && $method === 'GET') {
        Response::success(['status' => 'healthy', 'timestamp' => date('c')]);
    }

    // API info
    if ($uri === '/info' && $method === 'GET') {
        Response::success([
            'name' => 'Multi-Tenant Journal API',
            'version' => 'v1',
            'documentation' => '/docs'
        ]);
    }

    // Tenant info
    if ($uri === '/tenant' && $method === 'GET') {
        require_once __DIR__ . '/endpoints/tenant.php';
        return true;
    }

    // Articles
    if (preg_match('#^/articles(?:/(.+))?$#', $uri, $matches)) {
        require_once __DIR__ . '/endpoints/articles.php';
        return true;
    }

    // Volumes
    if (preg_match('#^/volumes(?:/(.+))?$#', $uri, $matches)) {
        require_once __DIR__ . '/endpoints/volumes.php';
        return true;
    }

    // Issues
    if (preg_match('#^/issues(?:/(.+))?$#', $uri, $matches)) {
        require_once __DIR__ . '/endpoints/issues.php';
        return true;
    }

    // Editorial board
    if ($uri === '/editorial-board' && $method === 'GET') {
        require_once __DIR__ . '/endpoints/editorial-board.php';
        return true;
    }

    // Pages
    if (preg_match('#^/pages(?:/(.+))?$#', $uri, $matches)) {
        require_once __DIR__ . '/endpoints/pages.php';
        return true;
    }

    // Announcements
    if ($uri === '/announcements' && $method === 'GET') {
        require_once __DIR__ . '/endpoints/announcements.php';
        return true;
    }

    // Search
    if ($uri === '/search' && $method === 'GET') {
        require_once __DIR__ . '/endpoints/search.php';
        return true;
    }

    return null;
}
