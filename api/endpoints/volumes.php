<?php
/**
 * Volumes API Endpoint
 * Multi-Tenant Journal Management System
 *
 * GET /volumes - List all published volumes
 * GET /volumes/current - Get current volume
 * GET /volumes/with-issues - Get all volumes with their issues
 * GET /volumes/{id} - Get single volume with issues
 */

use Core\Response;
use Core\Models\Volume;
use Api\Middleware\ApiAuth;

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Extract path after /volumes
$basePath = '/multi-tenant-system/api/volumes';
$path = str_replace($basePath, '', $uri);
$path = '/' . trim($path, '/');

$segments = array_values(array_filter(explode('/', $path)));

switch ($method) {
    case 'GET':
        if (empty($segments) || $segments[0] === '') {
            // GET /volumes - List all published volumes
            $volumes = Volume::published();
            Response::success($volumes);

        } elseif ($segments[0] === 'current') {
            // GET /volumes/current - Get current volume
            $volume = Volume::current();
            if (!$volume) {
                Response::notFound('No current volume found');
            }
            Response::success($volume);

        } elseif ($segments[0] === 'with-issues') {
            // GET /volumes/with-issues - Get all volumes with issues
            $volumes = Volume::allWithIssues();
            Response::success($volumes);

        } else {
            // GET /volumes/{id} - Get single volume with issues
            $id = (int) $segments[0];
            $result = Volume::findWithIssues($id);

            if (!$result) {
                Response::notFound('Volume not found');
            }

            // Add article count
            $result['article_count'] = Volume::articleCount($id);

            Response::success($result);
        }
        break;

    default:
        Response::error('Method not allowed', 405);
}
