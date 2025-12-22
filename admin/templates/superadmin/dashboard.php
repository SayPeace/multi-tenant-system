<?php
/**
 * Super Admin Dashboard Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
    <?= CSRF::meta() ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include __DIR__ . '/../layouts/partials/sidebar.php'; ?>

        <div class="admin-main">
            <?php include __DIR__ . '/../layouts/partials/header.php'; ?>

            <main class="admin-content">
                <?php include __DIR__ . '/../layouts/partials/flash.php'; ?>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #1a73e8;">&#9744;</div>
                        <div class="stat-content">
                            <h3><?= number_format($stats['total_tenants']) ?></h3>
                            <p>Total Journals</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #34a853;">&#10003;</div>
                        <div class="stat-content">
                            <h3><?= number_format($stats['active_tenants']) ?></h3>
                            <p>Active Journals</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #ea4335;">&#9787;</div>
                        <div class="stat-content">
                            <h3><?= number_format($stats['total_users']) ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #fbbc05;">&#9998;</div>
                        <div class="stat-content">
                            <h3><?= number_format($stats['total_articles']) ?></h3>
                            <p>Total Articles</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="<?= $baseUrl ?>/superadmin/tenants/create" class="btn btn-primary">
                                + Create New Journal
                            </a>
                            <a href="<?= $baseUrl ?>/superadmin/tenants" class="btn btn-secondary">
                                Manage Journals
                            </a>
                            <a href="<?= $baseUrl ?>/superadmin/settings" class="btn btn-secondary">
                                System Settings
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Journals -->
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Journals</h2>
                        <a href="<?= $baseUrl ?>/superadmin/tenants" class="btn btn-sm btn-secondary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentTenants)): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Users</th>
                                    <th>Articles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTenants as $tenant): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($tenant['name']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($tenant['slug']) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($tenant['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($tenant['stats']['users'] ?? 0) ?></td>
                                    <td><?= number_format($tenant['stats']['articles'] ?? 0) ?></td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/superadmin/tenants/<?= $tenant['id'] ?>/edit"
                                           class="btn btn-sm btn-secondary">Edit</a>
                                        <?php if ($tenant['stats']['users'] > 0): ?>
                                        <a href="<?= $baseUrl ?>/superadmin/tenants/<?= $tenant['id'] ?>/impersonate"
                                           class="btn btn-sm btn-warning">View As</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-muted">No journals created yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </main>

            <?php include __DIR__ . '/../layouts/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>
</body>
</html>
