<?php
/**
 * Announcements API Endpoint
 * Multi-Tenant Journal Management System
 *
 * GET /announcements - Get active announcements
 */

use Core\Response;
use Core\Models\Announcement;

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$limit = isset($_GET['limit']) ? min(20, max(1, (int) $_GET['limit'])) : 10;
$announcements = Announcement::latest($limit);

Response::success($announcements);
