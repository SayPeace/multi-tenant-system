<?php
/**
 * API Client for Distributed Frontend
 * Multi-Tenant Journal Management System
 *
 * This class handles all communication between the frontend and Central API.
 * Each journal frontend uses this client with their unique API key.
 */

class ApiClient
{
    private string $apiUrl;
    private string $apiKey;
    private int $timeout;
    private ?array $tenantCache = null;

    public function __construct(string $apiUrl, string $apiKey, int $timeout = 30)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
    }

    /**
     * Make API request
     */
    private function request(string $endpoint, string $method = 'GET', array $data = []): array
    {
        $url = $this->apiUrl . '/' . ltrim($endpoint, '/');

        // Add query params for GET requests
        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'X-API-Key: ' . $this->apiKey,
                'Content-Type: application/json',
                'Accept: application/json'
            ]
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new Exception("API request failed: $error");
        }

        $decoded = json_decode($response, true);

        if ($decoded === null) {
            throw new Exception("Invalid JSON response from API");
        }

        if ($httpCode >= 400) {
            throw new Exception($decoded['message'] ?? 'API error', $httpCode);
        }

        return $decoded;
    }

    /**
     * Get tenant (journal) information
     */
    public function getTenant(): array
    {
        if ($this->tenantCache === null) {
            $response = $this->request('/tenant');
            $this->tenantCache = $response['data'];
        }
        return $this->tenantCache;
    }

    /**
     * Get recent articles
     */
    public function getRecentArticles(int $limit = 5): array
    {
        $response = $this->request('/articles/recent', 'GET', ['limit' => $limit]);
        return $response['data'];
    }

    /**
     * Get articles (paginated)
     */
    public function getArticles(int $page = 1, int $perPage = 10, ?int $volumeId = null, ?int $issueId = null): array
    {
        $params = ['page' => $page, 'per_page' => $perPage];
        if ($volumeId) $params['volume_id'] = $volumeId;
        if ($issueId) $params['issue_id'] = $issueId;

        $response = $this->request('/articles', 'GET', $params);
        return $response;
    }

    /**
     * Get single article by slug
     */
    public function getArticle(string $slug): ?array
    {
        try {
            $response = $this->request("/articles/$slug");
            return $response['data'];
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Increment article view count
     */
    public function trackArticleView(int $articleId): void
    {
        try {
            $this->request("/articles/$articleId/increment-view");
        } catch (Exception $e) {
            // Silently fail - view tracking should not break the page
        }
    }

    /**
     * Get volumes
     */
    public function getVolumes(): array
    {
        $response = $this->request('/volumes');
        return $response['data'];
    }

    /**
     * Get volumes with issues
     */
    public function getVolumesWithIssues(): array
    {
        $response = $this->request('/volumes/with-issues');
        return $response['data'];
    }

    /**
     * Get single volume with issues
     */
    public function getVolume(int $id): ?array
    {
        try {
            $response = $this->request("/volumes/$id");
            return $response['data'];
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Get current issue
     */
    public function getCurrentIssue(): ?array
    {
        try {
            $response = $this->request('/issues/current');
            return $response['data'];
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Get single issue with articles
     */
    public function getIssue(int $id): ?array
    {
        try {
            $response = $this->request("/issues/$id");
            return $response['data'];
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Get editorial board
     */
    public function getEditorialBoard(bool $grouped = true): array
    {
        $response = $this->request('/editorial-board', 'GET', ['grouped' => $grouped ? 'true' : 'false']);
        return $response['data'];
    }

    /**
     * Get pages (for menu)
     */
    public function getMenuPages(): array
    {
        $response = $this->request('/pages/menu');
        return $response['data'];
    }

    /**
     * Get single page
     */
    public function getPage(string $slug): ?array
    {
        try {
            $response = $this->request("/pages/$slug");
            return (array) $response['data'];
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * Get announcements
     */
    public function getAnnouncements(int $limit = 5): array
    {
        $response = $this->request('/announcements', 'GET', ['limit' => $limit]);
        return $response['data'];
    }

    /**
     * Search articles
     */
    public function search(string $query, int $limit = 20): array
    {
        $response = $this->request('/search', 'GET', ['q' => $query, 'limit' => $limit]);
        return $response['data'];
    }

    /**
     * Get article statistics
     */
    public function getArticleStats(): array
    {
        $response = $this->request('/articles/stats');
        return $response['data'];
    }
}
