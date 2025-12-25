<?php
/**
 * Editor Reviews Controller
 * Handles reviewer assignment for editors
 */

require_once __DIR__ . '/../../models/Tenant.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../../core/Database.php';

class ReviewsController
{
    public string $baseUrl;
    public array $config;
    public array $params;

    /**
     * Show assign reviewers form
     */
    public function showAssign(): void
    {
        $articleId = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();

        $article = $this->getArticle($articleId, $tenantId);

        if (!$article) {
            Flash::error('Article not found.');
            header('Location: ' . $this->baseUrl . '/editor/articles');
            exit;
        }

        $pageTitle = 'Assign Reviewers - Editor';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenant = Tenant::find($tenantId);

        // Get available reviewers (users with reviewer role in this tenant)
        $reviewers = $this->getAvailableReviewers($tenantId);

        // Get existing assignments
        $assignments = $this->getArticleAssignments($articleId, $tenantId);

        include __DIR__ . '/../../templates/editor/reviews/assign.php';
    }

    /**
     * Assign reviewers to article
     */
    public function assign(): void
    {
        $articleId = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/editor/articles/' . $articleId . '/assign-reviewers');
            exit;
        }

        $article = $this->getArticle($articleId, $tenantId);

        if (!$article) {
            Flash::error('Article not found.');
            header('Location: ' . $this->baseUrl . '/editor/articles');
            exit;
        }

        $reviewerIds = $_POST['reviewers'] ?? [];
        $deadlineDays = (int) ($_POST['deadline_days'] ?? 14);
        $notes = $_POST['notes'] ?? '';

        if (empty($reviewerIds)) {
            Flash::error('Please select at least one reviewer.');
            header('Location: ' . $this->baseUrl . '/editor/articles/' . $articleId . '/assign-reviewers');
            exit;
        }

        $deadline = date('Y-m-d H:i:s', strtotime("+{$deadlineDays} days"));
        $assignedCount = 0;

        foreach ($reviewerIds as $reviewerId) {
            $reviewerId = (int) $reviewerId;

            // Check if already assigned
            $existing = $this->getExistingAssignment($articleId, $reviewerId, $tenantId);
            if ($existing) {
                continue; // Skip if already assigned
            }

            $data = [
                'tenant_id' => $tenantId,
                'article_id' => $articleId,
                'reviewer_id' => $reviewerId,
                'assigned_by' => $userId,
                'status' => 'pending',
                'assigned_at' => date('Y-m-d H:i:s'),
                'deadline_at' => $deadline,
                'notes' => $notes ?: null,
            ];

            $id = \Core\Database::insert('review_assignments', $data);
            if ($id) {
                $assignedCount++;
            }
        }

        if ($assignedCount > 0) {
            // Update article status to under_review if it was submitted
            if ($article['status'] === 'submitted') {
                \Core\Database::update('articles', [
                    'status' => 'under_review',
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'id = ?', [$articleId]);
            }

            Flash::success("Successfully assigned {$assignedCount} reviewer(s).");
        } else {
            Flash::info('No new reviewers were assigned (they may already be assigned).');
        }

        header('Location: ' . $this->baseUrl . '/editor/articles/' . $articleId);
        exit;
    }

    // Helper methods

    private function getArticle(int $id, int $tenantId): ?array
    {
        $sql = "SELECT a.*, u.first_name as author_first_name, u.last_name as author_last_name
                FROM articles a
                LEFT JOIN users u ON a.submitted_by = u.id
                WHERE a.id = ? AND a.tenant_id = ?";
        $result = \Core\Database::query($sql, [$id, $tenantId])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    private function getAvailableReviewers(int $tenantId): array
    {
        $sql = "SELECT id, first_name, last_name, email, title, affiliation
                FROM users
                WHERE tenant_id = ? AND role IN ('reviewer', 'editor', 'editor_in_chief') AND is_active = 1
                ORDER BY last_name, first_name";
        return \Core\Database::query($sql, [$tenantId])->fetchAll(\PDO::FETCH_ASSOC);
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

    private function getExistingAssignment(int $articleId, int $reviewerId, int $tenantId): ?array
    {
        $sql = "SELECT * FROM review_assignments
                WHERE article_id = ? AND reviewer_id = ? AND tenant_id = ?
                AND status NOT IN ('declined', 'cancelled')";
        $result = \Core\Database::query($sql, [$articleId, $reviewerId, $tenantId])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
