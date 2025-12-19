<?php
/**
 * Issues API Endpoint
 * Multi-Tenant Journal Management System
 *
 * GET /issues - List all published issues
 * GET /issues/current - Get current issue with articles
 * GET /issues/special - Get special issues
 * GET /issues/{id} - Get single issue with articles
 */

use Core\Response;
use Core\Models\Issue;
use Api\Middleware\ApiAuth;

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extract path after /issues
$basePath = '/multi-tenant-system/api/issues';
$path = str_replace($basePath, '', $uri);
$path = '/' . trim($path, '/');

$segments = array_values(array_filter(explode('/', $path)));

switch ($method) {
    case 'GET':
        if (empty($segments) || $segments[0] === '') {
            // GET /issues - List all published issues
            $volumeId = ApiAuth::query('volume_id');

            if ($volumeId) {
                $issues = Issue::byVolume((int) $volumeId);
            } else {
                $issues = Issue::published();
            }

            Response::success($issues);

        } elseif ($segments[0] === 'current') {
            // GET /issues/current - Get current issue with articles
            $result = Issue::currentWithArticles();

            if (!$result) {
                Response::notFound('No current issue found');
            }

            Response::success($result);

        } elseif ($segments[0] === 'special') {
            // GET /issues/special - Get special issues
            $issues = Issue::specialIssues();
            Response::success($issues);

        } else {
            // GET /issues/{id} - Get single issue with articles
            $id = (int) $segments[0];
            $result = Issue::findWithArticles($id);

            if (!$result) {
                Response::notFound('Issue not found');
            }

            // Add article count
            $result['article_count'] = Issue::articleCount($id);

            Response::success($result);
        }
        break;

    default:
        Response::error('Method not allowed', 405);
}
