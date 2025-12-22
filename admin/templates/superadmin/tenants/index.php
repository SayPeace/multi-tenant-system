<?php
/**
 * Tenants List Template
 */
$errors = Flash::errors();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Manage Journals') ?></title>
    <?= CSRF::meta() ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include __DIR__ . '/../../layouts/partials/sidebar.php'; ?>

        <div class="admin-main">
            <?php include __DIR__ . '/../../layouts/partials/header.php'; ?>

            <main class="admin-content">
                <?php include __DIR__ . '/../../layouts/partials/flash.php'; ?>

                <div class="card">
                    <div class="card-header">
                        <h2>All Journals</h2>
                        <a href="<?= $baseUrl ?>/superadmin/tenants/create" class="btn btn-primary btn-sm">
                            + Create Journal
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($tenants)): ?>
                            <p class="text-muted text-center">No journals found. Create your first journal to get started.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Slug</th>
                                            <th>Email</th>
                                            <th>Users</th>
                                            <th>Articles</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tenants as $tenant): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($tenant['name']) ?></strong>
                                                    <?php if (!empty($tenant['tagline'])): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($tenant['tagline']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><code><?= htmlspecialchars($tenant['slug']) ?></code></td>
                                                <td><?= htmlspecialchars($tenant['email']) ?></td>
                                                <td><?= (int)($tenant['user_count'] ?? 0) ?></td>
                                                <td><?= (int)($tenant['article_count'] ?? 0) ?></td>
                                                <td>
                                                    <?php if ($tenant['is_active']): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="<?= $baseUrl ?>/superadmin/tenants/<?= $tenant['id'] ?>/edit"
                                                           class="btn btn-sm btn-secondary" title="Edit">
                                                            Edit
                                                        </a>
                                                        <?php if (($tenant['user_count'] ?? 0) > 0): ?>
                                                        <a href="<?= $baseUrl ?>/superadmin/tenants/<?= $tenant['id'] ?>/impersonate"
                                                           class="btn btn-sm btn-warning" title="Login as Editor">
                                                            Impersonate
                                                        </a>
                                                        <?php endif; ?>
                                                        <form action="<?= $baseUrl ?>/superadmin/tenants/<?= $tenant['id'] ?>/delete"
                                                              method="POST" style="display: inline;"
                                                              onsubmit="return confirm('Are you sure you want to delete this journal? This action cannot be undone.');">
                                                            <?= CSRF::field() ?>
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </main>

            <?php include __DIR__ . '/../../layouts/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>
</body>
</html>
