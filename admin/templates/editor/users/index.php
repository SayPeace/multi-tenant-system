<?php
/**
 * Users List Template (Editor)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Manage Users') ?></title>
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

                <!-- Role Filter Tabs -->
                <div class="filter-tabs">
                    <a href="<?= $baseUrl ?>/editor/users"
                       class="filter-tab <?= empty($roleFilter) ? 'active' : '' ?>">
                        All (<?= $roleCounts['all'] ?>)
                    </a>
                    <a href="<?= $baseUrl ?>/editor/users?role=editor"
                       class="filter-tab <?= $roleFilter === 'editor' ? 'active' : '' ?>">
                        Editors (<?= $roleCounts['editor'] ?>)
                    </a>
                    <a href="<?= $baseUrl ?>/editor/users?role=author"
                       class="filter-tab <?= $roleFilter === 'author' ? 'active' : '' ?>">
                        Authors (<?= $roleCounts['author'] ?>)
                    </a>
                    <a href="<?= $baseUrl ?>/editor/users?role=reviewer"
                       class="filter-tab <?= $roleFilter === 'reviewer' ? 'active' : '' ?>">
                        Reviewers (<?= $roleCounts['reviewer'] ?>)
                    </a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Users</h2>
                        <a href="<?= $baseUrl ?>/editor/users/create" class="btn btn-primary btn-sm">
                            + Add User
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <p class="text-muted text-center">No users found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Last Login</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?></strong>
                                                    <?php if (!empty($user['title'])): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($user['title']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td>
                                                    <?php
                                                    $roleClass = match($user['role']) {
                                                        'editor_in_chief' => 'badge-primary',
                                                        'editor', 'admin' => 'badge-primary',
                                                        'author' => 'badge-success',
                                                        'reviewer' => 'badge-warning',
                                                        default => 'badge-secondary',
                                                    };
                                                    ?>
                                                    <span class="badge <?= $roleClass ?>">
                                                        <?= ucwords(str_replace('_', ' ', $user['role'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($user['is_active']): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                    <?php if (!$user['email_verified']): ?>
                                                        <span class="badge badge-warning">Unverified</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($user['last_login_at'])): ?>
                                                        <?= date('M j, Y', strtotime($user['last_login_at'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Never</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/editor/users/<?= $user['id'] ?>/edit"
                                                       class="btn btn-sm btn-secondary">Edit</a>
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

    <style>
    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .filter-tab {
        padding: 8px 16px;
        background: var(--white);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        text-decoration: none;
        color: var(--text-color);
        font-size: 0.9rem;
    }
    .filter-tab:hover {
        background: #f8f9fa;
        text-decoration: none;
    }
    .filter-tab.active {
        background: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
    }
    </style>
</body>
</html>
