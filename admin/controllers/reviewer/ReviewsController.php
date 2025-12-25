<?php
/**
 * Reviewer Reviews Controller
 * Handles review submission for reviewers
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
     * Show review form
     */
    public function create(): void
    {
        $assignmentId = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        $assignment = $this->getAssignment($assignmentId, $tenantId, $userId);

        if (!$assignment) {
            Flash::error('Assignment not found.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments');
            exit;
        }

        if ($assignment['status'] !== 'accepted') {
            Flash::error('You must accept the assignment before submitting a review.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $assignmentId);
            exit;
        }

        // Check if already reviewed
        $existingReview = $this->getExistingReview($assignmentId, $tenantId);
        if ($existingReview) {
            Flash::info('You have already submitted a review for this article.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $assignmentId);
            exit;
        }

        $pageTitle = 'Submit Review - Reviewer';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenant = Tenant::find($tenantId);

        // Get the article
        $article = $this->getArticle($assignment['article_id'], $tenantId);

        include __DIR__ . '/../../templates/reviewer/reviews/create.php';
    }

    /**
     * Store review
     */
    public function store(): void
    {
        $assignmentId = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $assignmentId . '/review');
            exit;
        }

        $assignment = $this->getAssignment($assignmentId, $tenantId, $userId);

        if (!$assignment || $assignment['status'] !== 'accepted') {
            Flash::error('Invalid assignment or status.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments');
            exit;
        }

        // Check if already reviewed
        $existingReview = $this->getExistingReview($assignmentId, $tenantId);
        if ($existingReview) {
            Flash::error('You have already submitted a review for this article.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $assignmentId);
            exit;
        }

        $validator = Validator::make($_POST, [
            'recommendation' => 'required',
            'comments_to_author' => 'required|min:50',
            'overall_score' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Flash::setErrors($validator->errors());
            Flash::setOldInput($_POST);
            Flash::error('Please correct the errors below.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $assignmentId . '/review');
            exit;
        }

        // Create review
        $reviewData = [
            'tenant_id' => $tenantId,
            'article_id' => $assignment['article_id'],
            'assignment_id' => $assignmentId,
            'reviewer_id' => $userId,
            'recommendation' => $_POST['recommendation'],
            'comments_to_author' => $_POST['comments_to_author'],
            'comments_to_editor' => $_POST['comments_to_editor'] ?? null,
            'originality_score' => !empty($_POST['originality_score']) ? (int)$_POST['originality_score'] : null,
            'methodology_score' => !empty($_POST['methodology_score']) ? (int)$_POST['methodology_score'] : null,
            'clarity_score' => !empty($_POST['clarity_score']) ? (int)$_POST['clarity_score'] : null,
            'significance_score' => !empty($_POST['significance_score']) ? (int)$_POST['significance_score'] : null,
            'overall_score' => (int)$_POST['overall_score'],
            'submitted_at' => date('Y-m-d H:i:s'),
        ];

        $reviewId = \Core\Database::insert('article_reviews', $reviewData);

        if ($reviewId) {
            // Update assignment status to completed
            \Core\Database::update('review_assignments', [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$assignmentId]);

            Flash::success('Review submitted successfully. Thank you for your contribution.');
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $assignmentId);
        } else {
            Flash::error('Failed to submit review.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/reviewer/assignments/' . $assignmentId . '/review');
        }
        exit;
    }

    // Helper methods

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

    private function getExistingReview(int $assignmentId, int $tenantId): ?array
    {
        $sql = "SELECT * FROM article_reviews WHERE assignment_id = ? AND tenant_id = ?";
        $result = \Core\Database::query($sql, [$assignmentId, $tenantId])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
