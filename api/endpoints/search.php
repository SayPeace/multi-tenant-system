<?php
/**
 * Search API Endpoint
 * Multi-Tenant Journal Management System
 *
 * GET /search?q={query} - Search articles
 */

use Core\Response;
use Core\Models\Article;

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$query = trim($_GET['q'] ?? '');

if (empty($query)) {
    Response::error('Search query is required. Use ?q=your+search+terms', 400);
}

if (strlen($query) < 3) {
    Response::error('Search query must be at least 3 characters', 400);
}

$limit = isset($_GET['limit']) ? min(50, max(1, (int) $_GET['limit'])) : 20;
$articles = Article::search($query, $limit);

Response::success([
    'query' => $query,
    'count' => count($articles),
    'articles' => $articles
]);
