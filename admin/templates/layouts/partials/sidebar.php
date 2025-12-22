<?php
/**
 * Admin Sidebar Navigation
 */
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isSuperAdmin = AdminAuth::isSuperAdmin();
$isImpersonating = AdminAuth::isImpersonating();
$role = AdminAuth::role();

// Get tenant info if not super admin
$tenantName = '';
if (!$isSuperAdmin && AdminAuth::tenantId()) {
    require_once __DIR__ . '/../../../models/Tenant.php';
    $tenant = Tenant::find(AdminAuth::tenantId());
    $tenantName = $tenant['name'] ?? 'Journal';
}
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <?php if ($isSuperAdmin && !$isImpersonating): ?>
            <h2>System Admin</h2>
            <span class="badge badge-primary">Super Admin</span>
        <?php else: ?>
            <h2><?= htmlspecialchars($tenantName ?: 'Journal Admin') ?></h2>
            <span class="badge badge-secondary"><?= ucfirst(str_replace('_', ' ', $role ?? 'User')) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($isImpersonating): ?>
    <div class="impersonation-notice">
        <span>Viewing as <?= htmlspecialchars(Session::get('auth_name')) ?></span>
        <a href="<?= $baseUrl ?>/stop-impersonating" class="btn btn-sm btn-warning">Exit</a>
    </div>
    <?php endif; ?>

    <nav class="sidebar-nav">
        <?php if ($isSuperAdmin && !$isImpersonating): ?>
            <!-- Super Admin Menu -->
            <a href="<?= $baseUrl ?>/superadmin/dashboard"
               class="<?= strpos($currentUri, '/superadmin/dashboard') !== false ? 'active' : '' ?>">
                <span class="icon">&#9673;</span> Dashboard
            </a>
            <a href="<?= $baseUrl ?>/superadmin/tenants"
               class="<?= strpos($currentUri, '/superadmin/tenants') !== false ? 'active' : '' ?>">
                <span class="icon">&#9744;</span> Journals
            </a>
            <a href="<?= $baseUrl ?>/superadmin/settings"
               class="<?= strpos($currentUri, '/superadmin/settings') !== false ? 'active' : '' ?>">
                <span class="icon">&#9881;</span> Settings
            </a>

        <?php elseif ($role === 'editor_in_chief' || $role === 'admin' || $role === 'editor'): ?>
            <!-- Editor-in-Chief Menu -->
            <a href="<?= $baseUrl ?>/editor/dashboard"
               class="<?= strpos($currentUri, '/editor/dashboard') !== false ? 'active' : '' ?>">
                <span class="icon">&#9673;</span> Dashboard
            </a>
            <a href="<?= $baseUrl ?>/editor/articles"
               class="<?= strpos($currentUri, '/editor/articles') !== false ? 'active' : '' ?>">
                <span class="icon">&#9998;</span> Submissions
            </a>
            <a href="<?= $baseUrl ?>/editor/users"
               class="<?= strpos($currentUri, '/editor/users') !== false ? 'active' : '' ?>">
                <span class="icon">&#9787;</span> Users
            </a>
            <a href="<?= $baseUrl ?>/editor/volumes"
               class="<?= strpos($currentUri, '/editor/volumes') !== false ? 'active' : '' ?>">
                <span class="icon">&#9776;</span> Volumes & Issues
            </a>
            <a href="<?= $baseUrl ?>/editor/pages"
               class="<?= strpos($currentUri, '/editor/pages') !== false ? 'active' : '' ?>">
                <span class="icon">&#9782;</span> Pages
            </a>
            <a href="<?= $baseUrl ?>/editor/announcements"
               class="<?= strpos($currentUri, '/editor/announcements') !== false ? 'active' : '' ?>">
                <span class="icon">&#9993;</span> Announcements
            </a>
            <a href="<?= $baseUrl ?>/editor/settings"
               class="<?= strpos($currentUri, '/editor/settings') !== false ? 'active' : '' ?>">
                <span class="icon">&#9881;</span> Settings
            </a>

        <?php elseif ($role === 'author'): ?>
            <!-- Author Menu -->
            <a href="<?= $baseUrl ?>/author/dashboard"
               class="<?= strpos($currentUri, '/author/dashboard') !== false ? 'active' : '' ?>">
                <span class="icon">&#9673;</span> Dashboard
            </a>
            <a href="<?= $baseUrl ?>/author/submissions"
               class="<?= strpos($currentUri, '/author/submissions') !== false && strpos($currentUri, '/create') === false ? 'active' : '' ?>">
                <span class="icon">&#9998;</span> My Submissions
            </a>
            <a href="<?= $baseUrl ?>/author/submissions/create"
               class="<?= strpos($currentUri, '/author/submissions/create') !== false ? 'active' : '' ?>">
                <span class="icon">&#10010;</span> New Submission
            </a>
            <a href="<?= $baseUrl ?>/author/profile"
               class="<?= strpos($currentUri, '/author/profile') !== false ? 'active' : '' ?>">
                <span class="icon">&#9787;</span> Profile
            </a>

        <?php elseif ($role === 'reviewer'): ?>
            <!-- Reviewer Menu -->
            <a href="<?= $baseUrl ?>/reviewer/dashboard"
               class="<?= strpos($currentUri, '/reviewer/dashboard') !== false ? 'active' : '' ?>">
                <span class="icon">&#9673;</span> Dashboard
            </a>
            <a href="<?= $baseUrl ?>/reviewer/assignments"
               class="<?= strpos($currentUri, '/reviewer/assignments') !== false ? 'active' : '' ?>">
                <span class="icon">&#9745;</span> Assignments
            </a>
            <a href="<?= $baseUrl ?>/reviewer/profile"
               class="<?= strpos($currentUri, '/reviewer/profile') !== false ? 'active' : '' ?>">
                <span class="icon">&#9787;</span> Profile
            </a>
        <?php endif; ?>

        <hr>
        <a href="<?= $baseUrl ?>/logout" class="logout-link">
            <span class="icon">&#10140;</span> Logout
        </a>
    </nav>
</aside>
