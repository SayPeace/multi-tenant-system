<?php
/**
 * Author Submissions Controller
 * Handles article submissions for authors
 */

require_once __DIR__ . '/../../models/Tenant.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../../core/Database.php';

class SubmissionsController
{
    public string $baseUrl;
    public array $config;
    public array $params;

    /**
     * Author Dashboard
     */
    public function dashboard(): void
    {
        $pageTitle = 'Dashboard - Author';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        // Get tenant info
        $tenant = Tenant::find($tenantId);

        // Get submission statistics
        $stats = [
            'total' => $this->countSubmissions($tenantId, $userId),
            'draft' => $this->countSubmissionsByStatus($tenantId, $userId, 'draft'),
            'submitted' => $this->countSubmissionsByStatus($tenantId, $userId, 'submitted'),
            'under_review' => $this->countSubmissionsByStatus($tenantId, $userId, 'under_review'),
            'revision_required' => $this->countSubmissionsByStatus($tenantId, $userId, 'revision_required'),
            'accepted' => $this->countSubmissionsByStatus($tenantId, $userId, 'accepted'),
            'published' => $this->countSubmissionsByStatus($tenantId, $userId, 'published'),
        ];

        // Get recent submissions
        $recentSubmissions = $this->getRecentSubmissions($tenantId, $userId, 5);

        include __DIR__ . '/../../templates/author/dashboard.php';
    }

    /**
     * List author's submissions
     */
    public function index(): void
    {
        $pageTitle = 'My Submissions - Author';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        $tenant = Tenant::find($tenantId);

        // Get status filter
        $statusFilter = $_GET['status'] ?? '';

        $submissions = $this->getSubmissions($tenantId, $userId, $statusFilter);

        include __DIR__ . '/../../templates/author/submissions/index.php';
    }

    /**
     * Show create submission form
     */
    public function create(): void
    {
        $pageTitle = 'New Submission - Author';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenantId = AdminAuth::tenantId();

        $tenant = Tenant::find($tenantId);

        // Get volumes for this tenant
        $volumes = $this->getActiveVolumes($tenantId);

        include __DIR__ . '/../../templates/author/submissions/create.php';
    }

    /**
     * Store new submission
     */
    public function store(): void
    {
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/author/submissions/create');
            exit;
        }

        $validator = Validator::make($_POST, [
            'title' => 'required|min:10|max:500',
            'abstract' => 'required|min:100',
        ]);

        if ($validator->fails()) {
            Flash::setErrors($validator->errors());
            Flash::setOldInput($_POST);
            Flash::error('Please correct the errors below.');
            header('Location: ' . $this->baseUrl . '/author/submissions/create');
            exit;
        }

        // Determine status
        $status = isset($_POST['save_draft']) ? 'draft' : 'submitted';

        // Create article
        $data = [
            'tenant_id' => $tenantId,
            'title' => $_POST['title'],
            'abstract' => $_POST['abstract'],
            'keywords' => $_POST['keywords'] ?? null,
            'volume_id' => !empty($_POST['volume_id']) ? (int)$_POST['volume_id'] : null,
            'status' => $status,
            'submitted_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($status === 'submitted') {
            $data['submitted_at'] = date('Y-m-d H:i:s');
        }

        $id = \Core\Database::insert('articles', $data);

        if ($id) {
            if ($status === 'draft') {
                Flash::success('Draft saved successfully.');
            } else {
                Flash::success('Article submitted successfully. You will be notified when it is reviewed.');
            }
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id);
        } else {
            Flash::error('Failed to save submission.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/author/submissions/create');
        }
        exit;
    }

    /**
     * Show submission details
     */
    public function show(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        $submission = $this->getSubmission($id, $tenantId, $userId);

        if (!$submission) {
            Flash::error('Submission not found.');
            header('Location: ' . $this->baseUrl . '/author/submissions');
            exit;
        }

        $pageTitle = 'View Submission - Author';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenant = Tenant::find($tenantId);

        // Get reviews if available
        $reviews = $this->getSubmissionReviews($id, $tenantId);

        include __DIR__ . '/../../templates/author/submissions/show.php';
    }

    /**
     * Show edit submission form (for drafts only)
     */
    public function edit(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        $submission = $this->getSubmission($id, $tenantId, $userId);

        if (!$submission) {
            Flash::error('Submission not found.');
            header('Location: ' . $this->baseUrl . '/author/submissions');
            exit;
        }

        // Only drafts can be edited
        if ($submission['status'] !== 'draft') {
            Flash::error('Only draft submissions can be edited.');
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id);
            exit;
        }

        $pageTitle = 'Edit Submission - Author';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenant = Tenant::find($tenantId);
        $volumes = $this->getActiveVolumes($tenantId);

        include __DIR__ . '/../../templates/author/submissions/edit.php';
    }

    /**
     * Update submission
     */
    public function update(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id . '/edit');
            exit;
        }

        $submission = $this->getSubmission($id, $tenantId, $userId);

        if (!$submission) {
            Flash::error('Submission not found.');
            header('Location: ' . $this->baseUrl . '/author/submissions');
            exit;
        }

        if ($submission['status'] !== 'draft') {
            Flash::error('Only draft submissions can be edited.');
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id);
            exit;
        }

        $validator = Validator::make($_POST, [
            'title' => 'required|min:10|max:500',
            'abstract' => 'required|min:100',
        ]);

        if ($validator->fails()) {
            Flash::setErrors($validator->errors());
            Flash::setOldInput($_POST);
            Flash::error('Please correct the errors below.');
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id . '/edit');
            exit;
        }

        // Determine status
        $status = isset($_POST['submit_now']) ? 'submitted' : 'draft';

        $data = [
            'title' => $_POST['title'],
            'abstract' => $_POST['abstract'],
            'keywords' => $_POST['keywords'] ?? null,
            'volume_id' => !empty($_POST['volume_id']) ? (int)$_POST['volume_id'] : null,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($status === 'submitted' && $submission['status'] === 'draft') {
            $data['submitted_at'] = date('Y-m-d H:i:s');
        }

        $success = \Core\Database::update('articles', $data, 'id = ?', [$id]);

        if ($success) {
            if ($status === 'submitted') {
                Flash::success('Article submitted successfully.');
            } else {
                Flash::success('Draft saved successfully.');
            }
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id);
        } else {
            Flash::error('Failed to update submission.');
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id . '/edit');
        }
        exit;
    }

    /**
     * Show revision form
     */
    public function showRevise(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        $submission = $this->getSubmission($id, $tenantId, $userId);

        if (!$submission) {
            Flash::error('Submission not found.');
            header('Location: ' . $this->baseUrl . '/author/submissions');
            exit;
        }

        if ($submission['status'] !== 'revision_required') {
            Flash::error('This submission does not require revision.');
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id);
            exit;
        }

        $pageTitle = 'Submit Revision - Author';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenant = Tenant::find($tenantId);

        // Get reviews for this submission
        $reviews = $this->getSubmissionReviews($id, $tenantId);

        include __DIR__ . '/../../templates/author/submissions/revise.php';
    }

    /**
     * Submit revision
     */
    public function submitRevision(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id . '/revise');
            exit;
        }

        $submission = $this->getSubmission($id, $tenantId, $userId);

        if (!$submission || $submission['status'] !== 'revision_required') {
            Flash::error('Invalid submission or status.');
            header('Location: ' . $this->baseUrl . '/author/submissions');
            exit;
        }

        $validator = Validator::make($_POST, [
            'title' => 'required|min:10|max:500',
            'abstract' => 'required|min:100',
            'revision_notes' => 'required|min:50',
        ]);

        if ($validator->fails()) {
            Flash::setErrors($validator->errors());
            Flash::setOldInput($_POST);
            Flash::error('Please correct the errors below.');
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id . '/revise');
            exit;
        }

        // Update article
        $data = [
            'title' => $_POST['title'],
            'abstract' => $_POST['abstract'],
            'keywords' => $_POST['keywords'] ?? null,
            'status' => 'submitted', // Reset to submitted for re-review
            'current_revision' => ($submission['current_revision'] ?? 1) + 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $success = \Core\Database::update('articles', $data, 'id = ?', [$id]);

        if ($success) {
            // Log the revision
            $this->logRevision($id, $tenantId, $userId, $_POST['revision_notes']);

            Flash::success('Revision submitted successfully. The editor will review your changes.');
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id);
        } else {
            Flash::error('Failed to submit revision.');
            header('Location: ' . $this->baseUrl . '/author/submissions/' . $id . '/revise');
        }
        exit;
    }

    // Helper methods

    private function countSubmissions(int $tenantId, int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM articles WHERE tenant_id = ? AND submitted_by = ?";
        $result = \Core\Database::query($sql, [$tenantId, $userId])->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    private function countSubmissionsByStatus(int $tenantId, int $userId, string $status): int
    {
        $sql = "SELECT COUNT(*) as count FROM articles WHERE tenant_id = ? AND submitted_by = ? AND status = ?";
        $result = \Core\Database::query($sql, [$tenantId, $userId, $status])->fetch(\PDO::FETCH_ASSOC);
        return (int) $result['count'];
    }

    private function getRecentSubmissions(int $tenantId, int $userId, int $limit): array
    {
        $sql = "SELECT * FROM articles WHERE tenant_id = ? AND submitted_by = ? ORDER BY updated_at DESC LIMIT ?";
        return \Core\Database::query($sql, [$tenantId, $userId, $limit])->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getSubmissions(int $tenantId, int $userId, string $status = ''): array
    {
        $sql = "SELECT * FROM articles WHERE tenant_id = ? AND submitted_by = ?";
        $params = [$tenantId, $userId];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY updated_at DESC";

        return \Core\Database::query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getSubmission(int $id, int $tenantId, int $userId): ?array
    {
        $sql = "SELECT * FROM articles WHERE id = ? AND tenant_id = ? AND submitted_by = ?";
        $result = \Core\Database::query($sql, [$id, $tenantId, $userId])->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    private function getActiveVolumes(int $tenantId): array
    {
        $sql = "SELECT * FROM volumes WHERE tenant_id = ? AND is_open = 1 ORDER BY year DESC, number DESC";
        return \Core\Database::query($sql, [$tenantId])->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getSubmissionReviews(int $articleId, int $tenantId): array
    {
        try {
            $sql = "SELECT ar.*, u.first_name, u.last_name
                    FROM article_reviews ar
                    LEFT JOIN users u ON ar.reviewer_id = u.id
                    WHERE ar.article_id = ? AND ar.tenant_id = ?
                    ORDER BY ar.submitted_at DESC";
            return \Core\Database::query($sql, [$articleId, $tenantId])->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function logRevision(int $articleId, int $tenantId, int $userId, string $notes): void
    {
        try {
            $data = [
                'tenant_id' => $tenantId,
                'article_id' => $articleId,
                'revision_number' => 1, // Will be updated
                'submitted_by' => $userId,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            \Core\Database::insert('article_revisions', $data);
        } catch (\Exception $e) {
            // Table might not exist
        }
    }
}
