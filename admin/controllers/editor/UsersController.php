<?php
/**
 * Editor Users Controller
 * Manages users for a specific journal/tenant
 */

require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Tenant.php';

class UsersController
{
    public string $baseUrl;
    public array $config;
    public array $params;

    /**
     * List all users for the current tenant
     */
    public function index(): void
    {
        $pageTitle = 'Manage Users - Editor';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenantId = AdminAuth::tenantId();

        // Get tenant info
        $tenant = Tenant::find($tenantId);

        // Get filter parameters
        $roleFilter = $_GET['role'] ?? '';

        // Get users
        $users = User::allForTenant($tenantId, $roleFilter ?: null);

        // Count by role
        $roleCounts = [
            'all' => User::countForTenant($tenantId),
            'editor_in_chief' => User::countForTenant($tenantId, 'editor_in_chief'),
            'editor' => User::countForTenant($tenantId, 'editor'),
            'author' => User::countForTenant($tenantId, 'author'),
            'reviewer' => User::countForTenant($tenantId, 'reviewer'),
        ];

        include __DIR__ . '/../../templates/editor/users/index.php';
    }

    /**
     * Show create user form
     */
    public function create(): void
    {
        $pageTitle = 'Add User - Editor';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenantId = AdminAuth::tenantId();

        $tenant = Tenant::find($tenantId);

        include __DIR__ . '/../../templates/editor/users/create.php';
    }

    /**
     * Store new user
     */
    public function store(): void
    {
        $tenantId = AdminAuth::tenantId();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/editor/users/create');
            exit;
        }

        $validator = Validator::make($_POST, [
            'email' => 'required|email',
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'role' => 'required',
            'password' => 'required|min:8|password',
        ]);

        if ($validator->fails()) {
            Flash::setErrors($validator->errors());
            Flash::setOldInput($_POST);
            Flash::error('Please correct the errors below.');
            header('Location: ' . $this->baseUrl . '/editor/users/create');
            exit;
        }

        // Check if email exists for this tenant
        if (User::emailExistsForTenant($_POST['email'], $tenantId)) {
            Flash::error('A user with this email already exists.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/editor/users/create');
            exit;
        }

        // Validate role
        $allowedRoles = ['editor', 'author', 'reviewer'];
        if (!in_array($_POST['role'], $allowedRoles)) {
            Flash::error('Invalid role selected.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/editor/users/create');
            exit;
        }

        // Create user
        $data = [
            'tenant_id' => $tenantId,
            'email' => $_POST['email'],
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'title' => $_POST['title'] ?? null,
            'affiliation' => $_POST['affiliation'] ?? null,
            'role' => $_POST['role'],
            'password' => $_POST['password'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'email_verified' => isset($_POST['email_verified']) ? 1 : 0,
        ];

        $id = User::create($data);

        if ($id) {
            Flash::success('User created successfully.');
            header('Location: ' . $this->baseUrl . '/editor/users');
        } else {
            Flash::error('Failed to create user.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/editor/users/create');
        }
        exit;
    }

    /**
     * Show edit user form
     */
    public function edit(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();

        $user = User::find($id);

        // Verify user belongs to this tenant
        if (!$user || $user['tenant_id'] != $tenantId) {
            Flash::error('User not found.');
            header('Location: ' . $this->baseUrl . '/editor/users');
            exit;
        }

        $pageTitle = 'Edit User - Editor';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();
        $tenant = Tenant::find($tenantId);

        include __DIR__ . '/../../templates/editor/users/edit.php';
    }

    /**
     * Update user
     */
    public function update(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/editor/users/' . $id . '/edit');
            exit;
        }

        $user = User::find($id);

        // Verify user belongs to this tenant
        if (!$user || $user['tenant_id'] != $tenantId) {
            Flash::error('User not found.');
            header('Location: ' . $this->baseUrl . '/editor/users');
            exit;
        }

        $validator = Validator::make($_POST, [
            'email' => 'required|email',
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'role' => 'required',
        ]);

        if ($validator->fails()) {
            Flash::setErrors($validator->errors());
            Flash::setOldInput($_POST);
            Flash::error('Please correct the errors below.');
            header('Location: ' . $this->baseUrl . '/editor/users/' . $id . '/edit');
            exit;
        }

        // Check if email exists for another user in this tenant
        if (User::emailExistsForTenant($_POST['email'], $tenantId, $id)) {
            Flash::error('A user with this email already exists.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/editor/users/' . $id . '/edit');
            exit;
        }

        // Update user
        $data = [
            'email' => $_POST['email'],
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'title' => $_POST['title'] ?? null,
            'affiliation' => $_POST['affiliation'] ?? null,
            'role' => $_POST['role'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'email_verified' => isset($_POST['email_verified']) ? 1 : 0,
        ];

        // Update password if provided
        if (!empty($_POST['password'])) {
            $passwordValidator = Validator::make($_POST, [
                'password' => 'min:8|password',
            ]);

            if ($passwordValidator->fails()) {
                Flash::setErrors($passwordValidator->errors());
                Flash::setOldInput($_POST);
                Flash::error('Please correct the errors below.');
                header('Location: ' . $this->baseUrl . '/editor/users/' . $id . '/edit');
                exit;
            }

            $data['password'] = $_POST['password'];
        }

        $success = User::update($id, $data);

        if ($success) {
            Flash::success('User updated successfully.');
        } else {
            Flash::error('Failed to update user.');
        }

        header('Location: ' . $this->baseUrl . '/editor/users/' . $id . '/edit');
        exit;
    }

    /**
     * Delete user
     */
    public function destroy(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenantId = AdminAuth::tenantId();

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/editor/users');
            exit;
        }

        $user = User::find($id);

        // Verify user belongs to this tenant
        if (!$user || $user['tenant_id'] != $tenantId) {
            Flash::error('User not found.');
            header('Location: ' . $this->baseUrl . '/editor/users');
            exit;
        }

        // Prevent self-deletion
        if ($user['id'] == AdminAuth::id()) {
            Flash::error('You cannot delete your own account.');
            header('Location: ' . $this->baseUrl . '/editor/users');
            exit;
        }

        $success = User::delete($id);

        if ($success) {
            Flash::success('User deleted successfully.');
        } else {
            Flash::error('Failed to delete user.');
        }

        header('Location: ' . $this->baseUrl . '/editor/users');
        exit;
    }
}
