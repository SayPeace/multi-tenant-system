<?php
/**
 * Articles API Endpoint
 * Multi-Tenant Journal Management System
 *
 * GET /articles - List published articles
 * GET /articles/recent - Get recent articles
 * GET /articles/stats - Get article statistics
 * GET /articles/{slug} - Get single article by slug
 * GET /articles/{id}/increment-view - Increment view count
 */

use Core\Response;
use Core\Models\Article;
use Api\Middleware\ApiAuth;

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extract path after /articles
$basePath = '/multi-tenant-system/api/articles';
$path = str_replace($basePath, '', $uri);
$path = '/' . trim($path, '/');

// Parse path segments
$segments = array_values(array_filter(explode('/', $path)));

switch ($method) {
    case 'GET':
        if (empty($segments) || $segments[0] === '') {
            // GET /articles - List all published articles (paginated)
            [$page, $perPage] = ApiAuth::getPagination();

            // Optional filters
            $volumeId = ApiAuth::query('volume_id');
            $issueId = ApiAuth::query('issue_id');

            $conditions = ['status' => 'published'];
            if ($volumeId) {
                $conditions['volume_id'] = (int) $volumeId;
            }
            if ($issueId) {
                $conditions['issue_id'] = (int) $issueId;
            }

            $result = Article::paginate(
                $page,
                $perPage,
                $conditions,
                ['published_at' => 'DESC']
            );

            Response::paginated($result);

        } elseif ($segments[0] === 'recent') {
            // GET /articles/recent - Get recent articles
            $limit = min(20, max(1, (int) ApiAuth::query('limit', 5)));
            $articles = Article::recent($limit);
            Response::success($articles);

        } elseif ($segments[0] === 'stats') {
            // GET /articles/stats - Get statistics
            $stats = Article::getStats();
            Response::success($stats);

        } elseif (isset($segments[1]) && $segments[1] === 'increment-view') {
            // GET /articles/{id}/increment-view
            $id = (int) $segments[0];
            Article::incrementViews($id);
            Response::success(null, 'View count incremented');

        } elseif (isset($segments[1]) && $segments[1] === 'increment-download') {
            // GET /articles/{id}/increment-download
            $id = (int) $segments[0];
            Article::incrementDownloads($id);
            Response::success(null, 'Download count incremented');

        } else {
            // GET /articles/{slug} - Get single article
            $slug = $segments[0];

            // Check if it's numeric (ID) or string (slug)
            if (is_numeric($slug)) {
                $result = Article::findWithAuthors((int) $slug);
            } else {
                $result = Article::findBySlugWithAuthors($slug);
            }

            if (!$result) {
                Response::notFound('Article not found');
            }

            Response::success($result);
        }
        break;

    default:
        Response::error('Method not allowed', 405);
}
