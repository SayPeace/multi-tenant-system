<?php
/**
 * Pages API Endpoint
 * Multi-Tenant Journal Management System
 *
 * GET /pages - List all published pages
 * GET /pages/menu - Get menu pages only
 * GET /pages/{slug} - Get single page by slug
 */

use Core\Response;
use Core\Models\Page;

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extract path after /pages
$basePath = '/multi-tenant-system/api/pages';
$path = str_replace($basePath, '', $uri);
$path = '/' . trim($path, '/');

$segments = array_values(array_filter(explode('/', $path)));

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

if (empty($segments) || $segments[0] === '') {
    // GET /pages - List all published pages
    $pages = Page::published();
    Response::success($pages);

} elseif ($segments[0] === 'menu') {
    // GET /pages/menu - Get menu pages
    $pages = Page::menu();
    Response::success($pages);

} else {
    // GET /pages/{slug} - Get single page
    $slug = $segments[0];
    $page = Page::findPublishedBySlug($slug);

    if (!$page) {
        Response::notFound('Page not found');
    }

    Response::success($page);
}
