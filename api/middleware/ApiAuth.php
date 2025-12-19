<?php
/**
 * API Authentication Middleware
 * Multi-Tenant Journal Management System
 *
 * Validates API keys and resolves tenant context
 */

namespace Api\Middleware;

use Core\Tenant;
use Core\Response;

class ApiAuth
{
    private static array $config;

    /**
     * Initialize middleware
     */
    public static function init(): void
    {
        self::$config = require __DIR__ . '/../../config/api.php';

        // Set CORS headers
        self::setCorsHeaders();

        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * Set CORS headers
     */
    private static function setCorsHeaders(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        // In production, validate against allowed origins
        if (in_array('*', self::$config['allowed_origins']) ||
            in_array($origin, self::$config['allowed_origins'])) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-API-Key, Authorization');
        header('Access-Control-Max-Age: 86400');
    }

    /**
     * Authenticate request via API key
     */
    public static function authenticate(): void
    {
        $apiKeyHeader = self::$config['api_key_header'];
        $apiKey = $_SERVER['HTTP_' . str_replace('-', '_', strtoupper($apiKeyHeader))] ?? null;

        if (!$apiKey) {
            // Try Authorization header as fallback
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
                $apiKey = $matches[1];
            }
        }

        if (!$apiKey) {
            Response::unauthorized('API key is required. Provide via X-API-Key header.');
        }

        $tenant = Tenant::resolveFromApiKey($apiKey);

        if (!$tenant) {
            Response::unauthorized('Invalid API key');
        }
    }

    /**
     * Get request body as JSON
     */
    public static function getJsonBody(): array
    {
        $body = file_get_contents('php://input');
        if (empty($body)) {
            return [];
        }

        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('Invalid JSON in request body', 400);
        }

        return $data;
    }

    /**
     * Get query parameter
     */
    public static function query(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get pagination parameters
     */
    public static function getPagination(): array
    {
        $page = max(1, (int) self::query('page', 1));
        $perPage = min(100, max(1, (int) self::query('per_page', 10)));

        return [$page, $perPage];
    }

    /**
     * Validate required fields
     */
    public static function validateRequired(array $data, array $requiredFields): void
    {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            Response::error(
                'Missing required fields',
                400,
                ['missing_fields' => $missing]
            );
        }
    }

    /**
     * Rate limiting check (simple implementation)
     */
    public static function checkRateLimit(): void
    {
        // In production, implement proper rate limiting with Redis
        // This is a simplified placeholder
        $limit = self::$config['rate_limit'];
        $key = 'rate_limit:' . Tenant::id() . ':' . date('Y-m-d-H-i');

        // For now, just log the access
        // In production: use Redis to track and enforce limits
    }
}
