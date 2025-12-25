<?php
/**
 * Editor Articles Controller
 * Handles article management and editorial decisions
 */

require_once __DIR__ . '/../../models/Tenant.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../../core/Database.php';

class ArticlesController
{
    public string $baseUrl;
    public array $config;
    public array $params;

    /**
     * List all articles
     */
    public function index(): void
    {
        $pageTitle = 'Manage Articles - Editor';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenantId = AdminAuth::tenantId();

        $tenant = Tenant::find($tenantId);

        // Get status filter
        $statusFilter = $_GET['status'] ?? '';

        // Get articles with author info
        $articles = $this->getArticles($tenantId, $statusFilter);

        // Get status counts
        $statusCounts = $this->getStatusCounts($tenantId);

        include __DIR__ . '/../../templates/editor/articles/index.php';
    }

    /**
     * Show article details
     */
    public function show(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();

        $article = $this->getArticle($id, $tenantId);

        if (!$article) {
            Flash::error('Article not found.');
            header('Location: ' . $this->baseUrl . '/editor/articles');
            exit;
        }

        $pageTitle = 'View Article - Editor';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenant = Tenant::find($tenantId);

        // Get reviewer assignments
        $assignments = $this->getArticleAssignments($id, $tenantId);

        // Get reviews
        $reviews = $this->getArticleReviews($id, $tenantId);

        include __DIR__ . '/../../templates/editor/articles/show.php';
    }

    /**
     * Update article status
     */
    public function updateStatus(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/editor/articles/' . $id);
            exit;
        }

        $article = $this->getArticle($id, $tenantId);

        if (!$article) {
            Flash::error('Article not found.');
            header('Location: ' . $this->baseUrl . '/editor/articles');
            exit;
        }

        $newStatus = $_POST['status'] ?? '';
        $validStatuses = ['submitted', 'under_review', 'revision_required', 'accepted', 'rejected', 'published'];

        if (!in_array($newStatus, $validStatuses)) {
            Flash::error('Invalid status.');
            header('Location: ' . $this->baseUrl . '/editor/articles/' . $id);
            exit;
        }

        $data = [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $success = \Core\Database::update('articles', $data, 'id = ? AND tenant_id = ?', [$id, $tenantId]);

        if ($success) {
            Flash::success('Article status updated to: ' . ucwords(str_replace('_', ' ', $newStatus)));
        } else {
            Flash::error('Failed to update article status.');
        }

        header('Location: ' . $this->baseUrl . '/editor/articles/' . $id);
        exit;
    }

    /**
     * Show decision form
     */
    public function showDecision(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();

        $article = $this->getArticle($id, $tenantId);

        if (!$article) {
            Flash::error('Article not found.');
            header('Location: ' . $this->baseUrl . '/editor/articles');
            exit;
        }

        $pageTitle = 'Editorial Decision - Editor';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenant = Tenant::find($tenantId);

        // Get reviews
        $reviews = $this->getArticleReviews($id, $tenantId);

        include __DIR__ . '/../../templates/editor/articles/decision.php';
    }

    /**
     * Submit editorial decision
     */
    public function submitDecision(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/editor/articles/' . $id . '/decision');
            exit;
        }

        $article = $this->getArticle($id, $tenantId);

        if (!$article) {
            Flash::error('Article not found.');
            header('Location: ' . $this->baseUrl . '/editor/articles');
            exit;
        }

        $decision = $_POST['decision'] ?? '';
        $validDecisions = ['accepted', 'rejected', 'revision_required'];

        if (!in_array($decision, $validDecisions)) {
            Flash::error('Please select a valid decision.');
            header('Location: ' . $this->baseUrl . '/editor/articles/' . $id . '/decision');
            exit;
        }

        $decisionNotes = $_POST['decision_notes'] ?? '';

        if (empty($decisionNotes)) {
            Flash::error('Please provide notes explaining your decision.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/editor/articles/' . $id . '/decision');
            exit;
        }

        $data = [
            'status' => $decision,
            'decision_notes' => $decisionNotes,
            'decision_at' => date('Y-m-d H:i:s'),
            'decision_by' => $userId,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // If publishing directly
        if ($decision === 'accepted' && !empty($_POST['publish_now'])) {
            $data['status'] = 'published';
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        $success = \Core\Database::update('articles', $data, 'id = ? AND tenant_id = ?', [$id, $tenantId]);

        if ($success) {
            // Log decision in status history
            $this->logStatusChange($id, $tenantId, $userId, $article['status'], $data['status'], $decisionNotes);

            $decisionText = match($data['status']) {
                'accepted' => 'accepted for publication',
                'published' => 'accepted and published',
                'rejected' => 'rejected',
                'revision_required' => 'returned for revision',
                default => 'updated',
            };
            Flash::success("Article has been {$decisionText}.");
            header('Location: ' . $this->baseUrl . '/editor/articles/' . $id);
        } else {
            Flash::error('Failed to submit decision.');
            header('Location: ' . $this->baseUrl . '/editor/articles/' . $id . '/decision');
        }
        exit;
    }

    // Helper methods

    private function getArticles(int $tenantId, string $status = ''): array
    {
        $sql = "SELECT a.*, u.first_name as author_first_name, u.last_name as author_last_name,
                       (SELECT COUNT(*) FROM review_assignments ra WHERE ra.article_id = a.id) as assignment_count,
                       (SELECT COUNT(*) FROM article_reviews ar WHERE ar.article_id = a.id) as review_count
                FROM articles a
                LEFT JOIN users u ON a.submitted_by = u.id
                WHERE a.tenant_id = ?";
        $params = [$tenantId];

        if ($status) {
            $sql .= " AND a.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY a.updated_at DESC";

        return \Core\Database::query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getStatusCounts(int $tenantId): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM articles WHERE tenant_id = ? GROUP BY status";
        $results = \Core\Database::query($sql, [$tenantId])->fetchAll(\PDO::FETCH_ASSOC);

        $counts = [
            'all' => 0,
            'draft' => 0,
            'submitted' => 0,
            'under_review' => 0,
            'revision_required' => 0,
            'accepted' => 0,
            'rejected' => 0,
            'published' => 0,
        ];

        foreach ($results as $row) {
            $counts[$row['status']] = (int) $row['count'];
            $counts['all'] += (int) $row['count'];
        }

        return $counts;
    }

    private function getArticle(int $id, int $tenantId): ?array
    {
        $sql = "SELECT a.*, u.first_name as author_first_name, u.last_name as author_last_name, u.email as author_email
                FROM articles a
                LEFT JOIN users u ON a.submitted_by = u.id
                WHERE a.id = ? AND a.tenant_id = ?";
        $result = \Core\Database::query($sql, [$id, $tenantId])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    private function getArticleAssignments(int $articleId, int $tenantId): array
    {
        $sql = "SELECT ra.*, u.first_name, u.last_name, u.email
                FROM review_assignments ra
                JOIN users u ON ra.reviewer_id = u.id
                WHERE ra.article_id = ? AND ra.tenant_id = ?
                ORDER BY ra.assigned_at DESC";
        return \Core\Database::query($sql, [$articleId, $tenantId])->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getArticleReviews(int $articleId, int $tenantId): array
    {
        $sql = "SELECT ar.*, u.first_name, u.last_name
                FROM article_reviews ar
                LEFT JOIN users u ON ar.reviewer_id = u.id
                WHERE ar.article_id = ? AND ar.tenant_id = ?
                ORDER BY ar.submitted_at DESC";
        return \Core\Database::query($sql, [$articleId, $tenantId])->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function logStatusChange(int $articleId, int $tenantId, int $userId, string $oldStatus, string $newStatus, string $notes): void
    {
        try {
            $data = [
                'tenant_id' => $tenantId,
                'article_id' => $articleId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $userId,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            \Core\Database::insert('article_status_history', $data);
        } catch (\Exception $e) {
            // Table might not exist, silently fail
        }
    }
}
