<?php
/**
 * Reviewer Assignments Controller
 * Handles reviewer dashboard and assignments
 */

require_once __DIR__ . '/../../models/Tenant.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../../core/Database.php';

class AssignmentsController
{
    public string $baseUrl;
    public array $config;
    public array $params;

    /**
     * Reviewer Dashboard
     */
    public function dashboard(): void
    {
        $pageTitle = 'Dashboard - Reviewer';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        $tenant = Tenant::find($tenantId);

        // Get assignment statistics
        $stats = [
            'pending' => $this->countAssignmentsByStatus($tenantId, $userId, 'pending'),
            'accepted' => $this->countAssignmentsByStatus($tenantId, $userId, 'accepted'),
            'completed' => $this->countAssignmentsByStatus($tenantId, $userId, 'completed'),
            'declined' => $this->countAssignmentsByStatus($tenantId, $userId, 'declined'),
        ];

        // Get recent/pending assignments
        $pendingAssignments = $this->getPendingAssignments($tenantId, $userId, 5);
        $activeAssignments = $this->getActiveAssignments($tenantId, $userId, 5);

        include __DIR__ . '/../../templates/reviewer/dashboard.php';
    }

    /**
     * List reviewer's assignments
     */
    public function index(): void
    {
        $pageTitle = 'My Assignments - Reviewer';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        $tenant = Tenant::find($tenantId);

        // Get status filter
        $statusFilter = $_GET['status'] ?? '';

        $assignments = $this->getAssignments($tenantId, $userId, $statusFilter);

        include __DIR__ . '/../../templates/reviewer/assignments/index.php';
    }

    /**
     * Show assignment details
     */
    public function show(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        $assignment = $this->getAssignment($id, $tenantId, $userId);

        if (!$assignment) {
            Flash::error('Assignment not found.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments');
            exit;
        }

        $pageTitle = 'View Assignment - Reviewer';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenant = Tenant::find($tenantId);

        // Get the article
        $article = $this->getArticle($assignment['article_id'], $tenantId);

        include __DIR__ . '/../../templates/reviewer/assignments/show.php';
    }

    /**
     * Accept assignment
     */
    public function accept(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $id);
            exit;
        }

        $assignment = $this->getAssignment($id, $tenantId, $userId);

        if (!$assignment || $assignment['status'] !== 'pending') {
            Flash::error('Invalid assignment or status.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments');
            exit;
        }

        $data = [
            'status' => 'accepted',
            'responded_at' => date('Y-m-d H:i:s'),
        ];

        $success = \Core\Database::update('review_assignments', $data, 'id = ?', [$id]);

        if ($success) {
            Flash::success('Assignment accepted. You can now submit your review.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $id);
        } else {
            Flash::error('Failed to accept assignment.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $id);
        }
        exit;
    }

    /**
     * Decline assignment
     */
    public function decline(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $id);
            exit;
        }

        $assignment = $this->getAssignment($id, $tenantId, $userId);

        if (!$assignment || $assignment['status'] !== 'pending') {
            Flash::error('Invalid assignment or status.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments');
            exit;
        }

        $data = [
            'status' => 'declined',
            'responded_at' => date('Y-m-d H:i:s'),
            'notes' => $_POST['decline_reason'] ?? null,
        ];

        $success = \Core\Database::update('review_assignments', $data, 'id = ?', [$id]);

        if ($success) {
            Flash::success('Assignment declined.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments');
        } else {
            Flash::error('Failed to decline assignment.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $id);
        }
        exit;
    }

    // Helper methods

    private function countAssignmentsByStatus(int $tenantId, int $userId, string $status): int
    {
        $sql = "SELECT COUNT(*) as count FROM review_assignments WHERE tenant_id = ? AND reviewer_id = ? AND status = ?";
        $result = \Core\Database::query($sql, [$tenantId, $userId, $status])->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    private function getPendingAssignments(int $tenantId, int $userId, int $limit): array
    {
        $sql = "SELECT ra.*, a.title as article_title
                FROM review_assignments ra
                JOIN articles a ON ra.article_id = a.id
                WHERE ra.tenant_id = ? AND ra.reviewer_id = ? AND ra.status = 'pending'
                ORDER BY ra.assigned_at DESC
                LIMIT ?";
        return \Core\Database::query($sql, [$tenantId, $userId, $limit])->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getActiveAssignments(int $tenantId, int $userId, int $limit): array
    {
        $sql = "SELECT ra.*, a.title as article_title
                FROM review_assignments ra
                JOIN articles a ON ra.article_id = a.id
                WHERE ra.tenant_id = ? AND ra.reviewer_id = ? AND ra.status = 'accepted'
                ORDER BY ra.deadline_at ASC
                LIMIT ?";
        return \Core\Database::query($sql, [$tenantId, $userId, $limit])->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getAssignments(int $tenantId, int $userId, string $status = ''): array
    {
        $sql = "SELECT ra.*, a.title as article_title
                FROM review_assignments ra
                JOIN articles a ON ra.article_id = a.id
                WHERE ra.tenant_id = ? AND ra.reviewer_id = ?";
        $params = [$tenantId, $userId];

        if ($status) {
            $sql .= " AND ra.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY ra.assigned_at DESC";

        return \Core\Database::query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getAssignment(int $id, int $tenantId, int $userId): ?array
    {
        $sql = "SELECT ra.*, a.title as article_title
                FROM review_assignments ra
                JOIN articles a ON ra.article_id = a.id
                WHERE ra.id = ? AND ra.tenant_id = ? AND ra.reviewer_id = ?";
        $result = \Core\Database::query($sql, [$id, $tenantId, $userId])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    private function getArticle(int $id, int $tenantId): ?array
    {
        $sql = "SELECT * FROM articles WHERE id = ? AND tenant_id = ?";
        $result = \Core\Database::query($sql, [$id, $tenantId])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
