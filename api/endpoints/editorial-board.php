<?php
/**
 * Editorial Board API Endpoint
 * Multi-Tenant Journal Management System
 *
 * GET /editorial-board - Get editorial board members (grouped by position)
 */

use Core\Response;
use Core\Models\EditorialBoard;

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    Response::error('Method not allowed', 405);
}

$grouped = isset($_GET['grouped']) && $_GET['grouped'] === 'true';

if ($grouped) {
    // Get members grouped by position
    $members = EditorialBoard::groupedByPosition();
} else {
    // Get all active members
    $members = EditorialBoard::active();
}

Response::success($members);
