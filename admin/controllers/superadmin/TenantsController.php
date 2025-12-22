<?php
/**
 * Tenants Controller (Super Admin)
 * Manages journals/tenants
 */

require_once __DIR__ . '/../../models/Tenant.php';
require_once __DIR__ . '/../../models/SuperAdmin.php';
require_once __DIR__ . '/../../models/User.php';

class TenantsController
{
    public string $baseUrl;
    public array $config;
    public array $params;

    /**
     * Super Admin Dashboard
     */
    public function dashboard(): void
    {
        $pageTitle = 'Dashboard - Super Admin';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();

        // Get statistics
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::countActive(),
            'total_super_admins' => SuperAdmin::count(),
        ];

        // Get total users across all tenants
        require_once __DIR__ . '/../../../core/Database.php';
        $userCount = \Core\Database::query("SELECT COUNT(*) as count FROM users")->fetch(\PDO::FETCH_ASSOC);
        $stats['total_users'] = (int) $userCount['count'];

        // Get total articles across all tenants
        $articleCount = \Core\Database::query("SELECT COUNT(*) as count FROM articles")->fetch(\PDO::FETCH_ASSOC);
        $stats['total_articles'] = (int) $articleCount['count'];

        // Get recent tenants
        $recentTenants = Tenant::allWithStats();
        $recentTenants = array_slice($recentTenants, 0, 5);

        include __DIR__ . '/../../templates/superadmin/dashboard.php';
    }

    /**
     * List all tenants
     */
    public function index(): void
    {
        $pageTitle = 'Manage Journals - Super Admin';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();

        $tenants = Tenant::allWithStats();

        include __DIR__ . '/../../templates/superadmin/tenants/index.php';
    }

    /**
     * Show create tenant form
     */
    public function create(): void
    {
        $pageTitle = 'Create Journal - Super Admin';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();

        include __DIR__ . '/../../templates/superadmin/tenants/create.php';
    }

    /**
     * Store new tenant
     */
    public function store(): void
    {
        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants/create');
            exit;
        }

        $validator = Validator::make($_POST, [
            'name' => 'required|min:3|max:255',
            'slug' => 'required|alpha_dash|min:3|max:50',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            Flash::setErrors($validator->errors());
            Flash::setOldInput($_POST);
            Flash::error('Please correct the errors below.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants/create');
            exit;
        }

        // Check if slug exists
        if (Tenant::slugExists($_POST['slug'])) {
            Flash::error('This slug is already in use.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/superadmin/tenants/create');
            exit;
        }

        // Create tenant
        $data = [
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'tagline' => $_POST['tagline'] ?? null,
            'description' => $_POST['description'] ?? null,
            'email' => $_POST['email'],
            'phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'primary_color' => $_POST['primary_color'] ?? '#1a73e8',
            'secondary_color' => $_POST['secondary_color'] ?? '#34a853',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        $id = Tenant::create($data);

        if ($id) {
            Flash::success('Journal created successfully.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants');
        } else {
            Flash::error('Failed to create journal.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/superadmin/tenants/create');
        }
        exit;
    }

    /**
     * Show edit tenant form
     */
    public function edit(): void
    {
        $id = (int) ($this->params['id'] ?? 0);
        $tenant = Tenant::findWithStats($id);

        if (!$tenant) {
            Flash::error('Journal not found.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants');
            exit;
        }

        $pageTitle = 'Edit Journal - Super Admin';
        $baseUrl = $this->baseUrl;
        $currentUser = AdminAuth::user();

        include __DIR__ . '/../../templates/superadmin/tenants/edit.php';
    }

    /**
     * Update tenant
     */
    public function update(): void
    {
        $id = (int) ($this->params['id'] ?? 0);

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants/' . $id . '/edit');
            exit;
        }

        $tenant = Tenant::find($id);

        if (!$tenant) {
            Flash::error('Journal not found.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants');
            exit;
        }

        $validator = Validator::make($_POST, [
            'name' => 'required|min:3|max:255',
            'slug' => 'required|alpha_dash|min:3|max:50',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            Flash::setErrors($validator->errors());
            Flash::setOldInput($_POST);
            Flash::error('Please correct the errors below.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants/' . $id . '/edit');
            exit;
        }

        // Check if slug exists (excluding current tenant)
        if (Tenant::slugExists($_POST['slug'], $id)) {
            Flash::error('This slug is already in use.');
            Flash::setOldInput($_POST);
            header('Location: ' . $this->baseUrl . '/superadmin/tenants/' . $id . '/edit');
            exit;
        }

        // Update tenant
        $data = [
            'name' => $_POST['name'],
            'slug' => $_POST['slug'],
            'tagline' => $_POST['tagline'] ?? null,
            'description' => $_POST['description'] ?? null,
            'email' => $_POST['email'],
            'phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'primary_color' => $_POST['primary_color'] ?? '#1a73e8',
            'secondary_color' => $_POST['secondary_color'] ?? '#34a853',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        // Regenerate API key if requested
        if (isset($_POST['regenerate_api_key'])) {
            $data['api_key'] = Tenant::generateApiKey();
            $data['api_key_created_at'] = date('Y-m-d H:i:s');
        }

        $success = Tenant::update($id, $data);

        if ($success) {
            Flash::success('Journal updated successfully.');
        } else {
            Flash::error('Failed to update journal.');
        }

        header('Location: ' . $this->baseUrl . '/superadmin/tenants/' . $id . '/edit');
        exit;
    }

    /**
     * Delete tenant
     */
    public function destroy(): void
    {
        $id = (int) ($this->params['id'] ?? 0);

        try {
            CSRF::check();
        } catch (Exception $e) {
            Flash::error('Invalid security token. Please try again.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants');
            exit;
        }

        $tenant = Tenant::find($id);

        if (!$tenant) {
            Flash::error('Journal not found.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants');
            exit;
        }

        $success = Tenant::delete($id);

        if ($success) {
            Flash::success('Journal deleted successfully.');
        } else {
            Flash::error('Failed to delete journal.');
        }

        header('Location: ' . $this->baseUrl . '/superadmin/tenants');
        exit;
    }

    /**
     * Impersonate a journal admin
     */
    public function impersonate(): void
    {
        $id = (int) ($this->params['id'] ?? 0);

        $tenant = Tenant::find($id);

        if (!$tenant) {
            Flash::error('Journal not found.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants');
            exit;
        }

        // Find the first editor/admin for this tenant
        $editors = User::getEditors($id);

        if (empty($editors)) {
            Flash::error('No editor found for this journal. Please create an editor first.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants');
            exit;
        }

        $editor = $editors[0];

        $success = AdminAuth::impersonate($editor['id']);

        if ($success) {
            Flash::info('You are now viewing as: ' . ($editor['first_name'] ?? '') . ' ' . ($editor['last_name'] ?? '') . ' (' . $tenant['name'] . ')');
            header('Location: ' . $this->baseUrl . '/editor/dashboard');
        } else {
            Flash::error('Failed to impersonate user.');
            header('Location: ' . $this->baseUrl . '/superadmin/tenants');
        }
        exit;
    }
}
