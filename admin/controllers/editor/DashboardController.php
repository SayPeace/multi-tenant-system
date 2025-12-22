<?php
/**
 * Editor Dashboard Controller
 * Main dashboard for Editor-in-Chief and journal editors
 */

require_once __DIR__ . '/../../models/Tenant.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../../core/Database.php';

class DashboardController
{
    public string $baseUrl;
    public array $config;
    public array $params;

    /**
     * Editor Dashboard
     */
    public function dashboard(): void
    {
        $pageTitle = 'Dashboard - Editor';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenantId = AdminAuth::tenantId();

        // Get tenant info
        $tenant = Tenant::find($tenantId);

        // Get statistics for this tenant
        $stats = [
            'total_users' => User::countForTenant($tenantId),
            'total_articles' => $this->countArticles($tenantId),
            'pending_submissions' => $this->countArticlesByStatus($tenantId, 'submitted'),
            'under_review' => $this->countArticlesByStatus($tenantId, 'under_review'),
            'published' => $this->countArticlesByStatus($tenantId, 'published'),
        ];

        // Get recent submissions
        $recentSubmissions = $this->getRecentSubmissions($tenantId, 5);

        // Get pending reviews
        $pendingReviews = $this->getPendingReviews($tenantId, 5);

        include __DIR__ . '/../../templates/editor/dashboard.php';
    }

    /**
     * Count total articles for tenant
     */
    private function countArticles(int $tenantId): int
    {
        $sql = "SELECT COUNT(*) as count FROM articles WHERE tenant_id = ?";
        $result = \Core\Database::query($sql, [$tenantId])->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * Count articles by status
     */
    private function countArticlesByStatus(int $tenantId, string $status): int
    {
        $sql = "SELECT COUNT(*) as count FROM articles WHERE tenant_id = ? AND status = ?";
        $result = \Core\Database::query($sql, [$tenantId, $status])->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    /**
     * Get recent submissions
     */
    private function getRecentSubmissions(int $tenantId, int $limit = 5): array
    {
        $sql = "SELECT a.*, u.first_name, u.last_name, u.email as author_email
                FROM articles a
                LEFT JOIN users u ON a.submitted_by = u.id
                WHERE a.tenant_id = ?
                ORDER BY a.created_at DESC
                LIMIT ?";
        return \Core\Database::query($sql, [$tenantId, $limit])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get pending reviews
     */
    private function getPendingReviews(int $tenantId, int $limit = 5): array
    {
        $sql = "SELECT ra.*, a.title as article_title, u.first_name, u.last_name
                FROM review_assignments ra
                JOIN articles a ON ra.article_id = a.id
                JOIN users u ON ra.reviewer_id = u.id
                WHERE ra.tenant_id = ? AND ra.status = 'pending'
                ORDER BY ra.assigned_at DESC
                LIMIT ?";

        try {
            return \Core\Database::query($sql, [$tenantId, $limit])->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Table might not exist yet
            return [];
        }
    }
}
