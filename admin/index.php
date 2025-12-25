<?php
/**
 * Admin Dashboard Entry Point
 * Multi-Tenant Journal Management System
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
$config = require __DIR__ . '/config/admin.php';

// Set timezone
date_default_timezone_set($config['timezone'] ?? 'UTC');

// Load core classes
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Flash.php';
require_once __DIR__ . '/core/CSRF.php';
require_once __DIR__ . '/core/Validator.php';
require_once __DIR__ . '/core/AdminAuth.php';

// Start session
Session::start();

// Get base URL
$baseUrl = $config['base_url'] ?? '/multi-tenant-system/admin';

// Parse the request URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$basePath = parse_url($baseUrl, PHP_URL_PATH);

// Remove base path from URI
$uri = $requestUri;
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Remove query string
if (($pos = strpos($uri, '?')) !== false) {
    $uri = substr($uri, 0, $pos);
}

// Normalize URI
$uri = '/' . trim($uri, '/');
if ($uri === '/') {
    $uri = '/';
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Define routes
$routes = [
    // Authentication routes (public)
    'GET /login' => ['AuthController', 'showLogin'],
    'POST /login' => ['AuthController', 'login'],
    'GET /logout' => ['AuthController', 'logout'],
    'GET /forgot-password' => ['AuthController', 'showForgotPassword'],
    'POST /forgot-password' => ['AuthController', 'sendResetLink'],
    'GET /reset-password/{token}' => ['AuthController', 'showResetPassword'],
    'POST /reset-password' => ['AuthController', 'resetPassword'],

    // Dashboard routes (require auth)
    'GET /' => ['DashboardController', 'index'],
    'GET /dashboard' => ['DashboardController', 'index'],

    // Super Admin routes
    'GET /superadmin/dashboard' => ['superadmin/TenantsController', 'dashboard'],
    'GET /superadmin/tenants' => ['superadmin/TenantsController', 'index'],
    'GET /superadmin/tenants/create' => ['superadmin/TenantsController', 'create'],
    'POST /superadmin/tenants' => ['superadmin/TenantsController', 'store'],
    'GET /superadmin/tenants/{id}/edit' => ['superadmin/TenantsController', 'edit'],
    'POST /superadmin/tenants/{id}' => ['superadmin/TenantsController', 'update'],
    'POST /superadmin/tenants/{id}/delete' => ['superadmin/TenantsController', 'destroy'],
    'GET /superadmin/tenants/{id}/impersonate' => ['superadmin/TenantsController', 'impersonate'],
    'GET /superadmin/settings' => ['superadmin/SettingsController', 'index'],
    'POST /superadmin/settings' => ['superadmin/SettingsController', 'update'],

    // Editor routes
    'GET /editor/dashboard' => ['editor/DashboardController', 'dashboard'],
    'GET /editor/articles' => ['editor/ArticlesController', 'index'],
    'GET /editor/articles/{id}' => ['editor/ArticlesController', 'show'],
    'POST /editor/articles/{id}/status' => ['editor/ArticlesController', 'updateStatus'],
    'GET /editor/articles/{id}/assign-reviewers' => ['editor/ReviewsController', 'showAssign'],
    'POST /editor/articles/{id}/assign-reviewers' => ['editor/ReviewsController', 'assign'],
    'GET /editor/articles/{id}/decision' => ['editor/ArticlesController', 'showDecision'],
    'POST /editor/articles/{id}/decision' => ['editor/ArticlesController', 'submitDecision'],
    'GET /editor/users' => ['editor/UsersController', 'index'],
    'GET /editor/users/create' => ['editor/UsersController', 'create'],
    'POST /editor/users' => ['editor/UsersController', 'store'],
    'GET /editor/users/{id}/edit' => ['editor/UsersController', 'edit'],
    'POST /editor/users/{id}' => ['editor/UsersController', 'update'],
    'POST /editor/users/{id}/delete' => ['editor/UsersController', 'destroy'],
    'GET /editor/volumes' => ['editor/VolumesController', 'index'],
    'POST /editor/volumes' => ['editor/VolumesController', 'store'],
    'GET /editor/pages' => ['editor/PagesController', 'index'],
    'GET /editor/announcements' => ['editor/AnnouncementsController', 'index'],
    'GET /editor/settings' => ['editor/SettingsController', 'index'],
    'POST /editor/settings' => ['editor/SettingsController', 'update'],

    // Author routes
    'GET /author/dashboard' => ['author/SubmissionsController', 'dashboard'],
    'GET /author/submissions' => ['author/SubmissionsController', 'index'],
    'GET /author/submissions/create' => ['author/SubmissionsController', 'create'],
    'POST /author/submissions' => ['author/SubmissionsController', 'store'],
    'GET /author/submissions/{id}' => ['author/SubmissionsController', 'show'],
    'GET /author/submissions/{id}/edit' => ['author/SubmissionsController', 'edit'],
    'POST /author/submissions/{id}' => ['author/SubmissionsController', 'update'],
    'GET /author/submissions/{id}/revise' => ['author/SubmissionsController', 'showRevise'],
    'POST /author/submissions/{id}/revise' => ['author/SubmissionsController', 'submitRevision'],
    'GET /author/profile' => ['author/ProfileController', 'edit'],
    'POST /author/profile' => ['author/ProfileController', 'update'],

    // Reviewer routes
    'GET /reviewer/dashboard' => ['reviewer/AssignmentsController', 'dashboard'],
    'GET /reviewer/assignments' => ['reviewer/AssignmentsController', 'index'],
    'GET /reviewer/assignments/{id}' => ['reviewer/AssignmentsController', 'show'],
    'POST /reviewer/assignments/{id}/accept' => ['reviewer/AssignmentsController', 'accept'],
    'POST /reviewer/assignments/{id}/decline' => ['reviewer/AssignmentsController', 'decline'],
    'GET /reviewer/assignments/{id}/review' => ['reviewer/ReviewsController', 'create'],
    'POST /reviewer/assignments/{id}/review' => ['reviewer/ReviewsController', 'store'],
    'GET /reviewer/profile' => ['reviewer/ProfileController', 'edit'],
    'POST /reviewer/profile' => ['reviewer/ProfileController', 'update'],

    // Stop impersonation
    'GET /stop-impersonating' => ['AuthController', 'stopImpersonating'],
];

// Public routes that don't require authentication
$publicRoutes = [
    'GET /login',
    'POST /login',
    'GET /forgot-password',
    'POST /forgot-password',
    'GET /reset-password/{token}',
    'POST /reset-password',
];

/**
 * Match route with URI
 */
function matchRoute(string $routePattern, string $uri): ?array
{
    // Convert route pattern to regex
    $pattern = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $routePattern);
    $pattern = '#^' . $pattern . '$#';

    if (preg_match($pattern, $uri, $matches)) {
        // Extract named parameters
        $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        return $params;
    }

    return null;
}

/**
 * Find matching route
 */
function findRoute(string $method, string $uri, array $routes): ?array
{
    foreach ($routes as $route => $handler) {
        list($routeMethod, $routePattern) = explode(' ', $route, 2);

        if ($routeMethod !== $method) {
            continue;
        }

        $params = matchRoute($routePattern, $uri);

        if ($params !== null) {
            return [
                'handler' => $handler,
                'params' => $params,
                'route' => $route,
            ];
        }
    }

    return null;
}

// Find matching route
$match = findRoute($method, $uri, $routes);

if (!$match) {
    // 404 Not Found
    http_response_code(404);
    include __DIR__ . '/templates/errors/404.php';
    exit;
}

$routeKey = $method . ' ' . explode(' ', $match['route'])[1];

// Check if route requires authentication
$isPublicRoute = false;
foreach ($publicRoutes as $publicRoute) {
    if (matchRoute(explode(' ', $publicRoute)[1], $uri) !== null &&
        explode(' ', $publicRoute)[0] === $method) {
        $isPublicRoute = true;
        break;
    }
}

if (!$isPublicRoute) {
    // Require authentication
    if (!AdminAuth::check()) {
        Flash::info('Please login to continue.');
        header('Location: ' . $baseUrl . '/login');
        exit;
    }

    // Redirect to appropriate dashboard if accessing root
    if ($uri === '/' || $uri === '/dashboard') {
        if (AdminAuth::isSuperAdmin()) {
            header('Location: ' . $baseUrl . '/superadmin/dashboard');
            exit;
        } elseif (AdminAuth::isEditorInChief()) {
            header('Location: ' . $baseUrl . '/editor/dashboard');
            exit;
        } elseif (AdminAuth::isAuthor()) {
            header('Location: ' . $baseUrl . '/author/dashboard');
            exit;
        } elseif (AdminAuth::isReviewer()) {
            header('Location: ' . $baseUrl . '/reviewer/dashboard');
            exit;
        }
    }

    // Check role-based access
    if (strpos($uri, '/superadmin') === 0 && !AdminAuth::isSuperAdmin()) {
        http_response_code(403);
        include __DIR__ . '/templates/errors/403.php';
        exit;
    }

    if (strpos($uri, '/editor') === 0 && !AdminAuth::isEditorInChief()) {
        http_response_code(403);
        include __DIR__ . '/templates/errors/403.php';
        exit;
    }

    if (strpos($uri, '/author') === 0 && !AdminAuth::isAuthor() && !AdminAuth::isEditorInChief()) {
        http_response_code(403);
        include __DIR__ . '/templates/errors/403.php';
        exit;
    }

    if (strpos($uri, '/reviewer') === 0 && !AdminAuth::isReviewer() && !AdminAuth::isEditorInChief()) {
        http_response_code(403);
        include __DIR__ . '/templates/errors/403.php';
        exit;
    }
}

// Load controller and execute action
list($controller, $action) = $match['handler'];

// Build controller file path
$controllerFile = __DIR__ . '/controllers/' . $controller . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(500);
    if ($config['debug']) {
        echo "Controller not found: $controllerFile";
    } else {
        include __DIR__ . '/templates/errors/500.php';
    }
    exit;
}

require_once $controllerFile;

// Get controller class name
$controllerClass = basename($controller);

if (!class_exists($controllerClass)) {
    http_response_code(500);
    if ($config['debug']) {
        echo "Controller class not found: $controllerClass";
    } else {
        include __DIR__ . '/templates/errors/500.php';
    }
    exit;
}

// Create controller instance
$controllerInstance = new $controllerClass();

// Set common variables
$controllerInstance->baseUrl = $baseUrl;
$controllerInstance->config = $config;
$controllerInstance->params = $match['params'];

// Execute action
if (!method_exists($controllerInstance, $action)) {
    http_response_code(500);
    if ($config['debug']) {
        echo "Action not found: $controllerClass::$action";
    } else {
        include __DIR__ . '/templates/errors/500.php';
    }
    exit;
}

try {
    $controllerInstance->$action();
} catch (Exception $e) {
    http_response_code(500);
    if ($config['debug']) {
        echo "<h1>Error</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        include __DIR__ . '/templates/errors/500.php';
    }
    exit;
}
