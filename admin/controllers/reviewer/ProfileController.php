<?php
/**
 * Reviewer Profile Controller
 * Handles profile management for reviewers
 */

require_once __DIR__ . '/../../models/Tenant.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../../core/Database.php';

class ProfileController
{
    public string $baseUrl;
    public array $config;
    public array $params;

    /**
     * Show edit profile form
     */
    public function edit(): void
    {
        $pageTitle = 'My Profile - Reviewer';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        $tenant = Tenant::find($tenantId);

        // Get full user data
        $user = User::find($userId);

        // Get review statistics
        $stats = $this->getReviewStats($tenantId, $userId);

        include __DIR__ . '/../../templates/reviewer/profile.php';
    }

    /**
     * Update profile
     */
    public function update(): void
    {
        $tenantId = AdminAuth::tenantId();
        $userId = AdminAuth::id();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/reviewer/profile');
            exit;
        }

        $rules = [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email',
        ];

        if (!empty($_POST['password'])) {
            $rules['password'] = 'min:8';
        }

        $validator = Validator::make($_POST, $rules);

        if ($validator->fails()) {
            Flash::setErrors($validator->errors());
            Flash::setOldInput($_POST);
            Flash::error('Please correct the errors below.');
            header('Location: ' . $this->baseUrl . '/reviewer/profile');
            exit;
        }

        // Check if email is already taken
        $existingUser = User::findByEmail($_POST['email'], $tenantId);
        if ($existingUser && $existingUser['id'] != $userId) {
            Flash::error('This email is already registered to another user.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/reviewer/profile');
            exit;
        }

        $data = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'title' => $_POST['title'] ?? null,
            'affiliation' => $_POST['affiliation'] ?? null,
            'bio' => $_POST['bio'] ?? null,
            'orcid' => $_POST['orcid'] ?? null,
            'expertise' => $_POST['expertise'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!empty($_POST['password'])) {
            $user = User::find($userId);
            if (empty($_POST['current_password']) || !password_verify($_POST['current_password'], $user['password_hash'])) {
                Flash::error('Current password is incorrect.');
                Flash::setOldInput($_POST);
                header('Location: ' . $this->baseUrl . '/reviewer/profile');
                exit;
            }
            $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $success = \Core\Database::update('users', $data, 'id = ? AND tenant_id = ?', [$userId, $tenantId]);

        if ($success) {
            $_SESSION['admin_user']['first_name'] = $data['first_name'];
            $_SESSION['admin_user']['last_name'] = $data['last_name'];
            $_SESSION['admin_user']['email'] = $data['email'];

            Flash::success('Profile updated successfully.');
        } else {
            Flash::error('Failed to update profile.');
        }

        header('Location: ' . $this->baseUrl . '/reviewer/profile');
        exit;
    }

    private function getReviewStats(int $tenantId, int $userId): array
    {
        $stats = [
            'total_assignments' => 0,
            'completed_reviews' => 0,
            'pending_reviews' => 0,
        ];

        try {
            $sql = "SELECT COUNT(*) as count FROM review_assignments WHERE tenant_id = ? AND reviewer_id = ?";
            $result = \Core\Database::query($sql, [$tenantId, $userId])->fetch(\PDO::FETCH_ASSOC);
            $stats['total_assignments'] = (int) $result['count'];

            $sql = "SELECT COUNT(*) as count FROM review_assignments WHERE tenant_id = ? AND reviewer_id = ? AND status = 'completed'";
            $result = \Core\Database::query($sql, [$tenantId, $userId])->fetch(\PDO::FETCH_ASSOC);
            $stats['completed_reviews'] = (int) $result['count'];

            $sql = "SELECT COUNT(*) as count FROM review_assignments WHERE tenant_id = ? AND reviewer_id = ? AND status = 'accepted'";
            $result = \Core\Database::query($sql, [$tenantId, $userId])->fetch(\PDO::FETCH_ASSOC);
            $stats['pending_reviews'] = (int) $result['count'];
        } catch (\Exception $e) {
            // Tables might not exist
        }

        return $stats;
    }
}
