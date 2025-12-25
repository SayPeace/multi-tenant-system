<?php
/**
 * Edit User Template (Editor)
 */
$errors = Flash::errors();
$old = Flash::oldInput();
$getValue = function($field) use ($old, $user) {
    return $old[$field] ?? $user[$field] ?? '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Edit User') ?></title>
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
                        <h2>Edit User: <?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?></h2>
                        <a href="<?= $baseUrl ?>/editor/users" class="btn btn-secondary btn-sm">
                            &larr; Back to Users
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="<?= $baseUrl ?>/editor/users/<?= $user['id'] ?>" method="POST">
                            <?= CSRF::field() ?>

                            <div class="form-section">
                                <h3>Account Information</h3>

                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                           value="<?= htmlspecialchars($getValue('email')) ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <span class="form-error"><?= $errors['email'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="first_name">First Name *</label>
                                        <input type="text" id="first_name" name="first_name" class="form-control"
                                               value="<?= htmlspecialchars($getValue('first_name')) ?>" required>
                                        <?php if (isset($errors['first_name'])): ?>
                                            <span class="form-error"><?= $errors['first_name'] ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group half">
                                        <label for="last_name">Last Name *</label>
                                        <input type="text" id="last_name" name="last_name" class="form-control"
                                               value="<?= htmlspecialchars($getValue('last_name')) ?>" required>
                                        <?php if (isset($errors['last_name'])): ?>
                                            <span class="form-error"><?= $errors['last_name'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input type="password" id="password" name="password" class="form-control">
                                    <span class="form-text">Leave blank to keep current password. Minimum 8 characters if changing.</span>
                                    <?php if (isset($errors['password'])): ?>
                                        <span class="form-error"><?= $errors['password'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Profile Information</h3>

                                <div class="form-group">
                                    <label for="title">Title/Position</label>
                                    <input type="text" id="title" name="title" class="form-control"
                                           value="<?= htmlspecialchars($getValue('title')) ?>"
                                           placeholder="e.g., Professor, Dr., Research Associate">
                                </div>

                                <div class="form-group">
                                    <label for="affiliation">Affiliation</label>
                                    <input type="text" id="affiliation" name="affiliation" class="form-control"
                                           value="<?= htmlspecialchars($getValue('affiliation')) ?>"
                                           placeholder="e.g., University of Example">
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Role & Permissions</h3>

                                <div class="form-group">
                                    <label for="role">Role *</label>
                                    <select id="role" name="role" class="form-control" required>
                                        <option value="editor_in_chief" <?= $getValue('role') === 'editor_in_chief' ? 'selected' : '' ?>>
                                            Editor-in-Chief - Full journal management
                                        </option>
                                        <option value="editor" <?= $getValue('role') === 'editor' ? 'selected' : '' ?>>
                                            Editor - Can manage articles and assign reviewers
                                        </option>
                                        <option value="author" <?= $getValue('role') === 'author' ? 'selected' : '' ?>>
                                            Author - Can submit articles
                                        </option>
                                        <option value="reviewer" <?= $getValue('role') === 'reviewer' ? 'selected' : '' ?>>
                                            Reviewer - Can review assigned articles
                                        </option>
                                    </select>
                                    <?php if (isset($errors['role'])): ?>
                                        <span class="form-error"><?= $errors['role'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="is_active" value="1"
                                               <?= $getValue('is_active') ? 'checked' : '' ?>>
                                        Account is active
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="email_verified" value="1"
                                               <?= $getValue('email_verified') ? 'checked' : '' ?>>
                                        Email is verified
                                    </label>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Account Statistics</h3>

                                <div class="stats-mini">
                                    <div class="stat-mini">
                                        <span class="stat-value"><?= date('M j, Y', strtotime($user['created_at'])) ?></span>
                                        <span class="stat-label">Joined</span>
                                    </div>
                                    <div class="stat-mini">
                                        <span class="stat-value">
                                            <?= !empty($user['last_login_at']) ? date('M j, Y', strtotime($user['last_login_at'])) : 'Never' ?>
                                        </span>
                                        <span class="stat-label">Last Login</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update User</button>
                                <a href="<?= $baseUrl ?>/editor/users" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

            </main>

            <?php include __DIR__ . '/../../layouts/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>

    <style>
    .form-section {
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--border-color);
    }
    .form-section:last-of-type {
        border-bottom: none;
    }
    .form-section h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 16px;
        color: var(--text-color);
    }
    .form-row {
        display: flex;
        gap: 16px;
    }
    .form-group.half {
        flex: 1;
    }
    .form-error {
        color: var(--danger-color);
        font-size: 0.85rem;
        margin-top: 4px;
        display: block;
    }
    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }
    .stats-mini {
        display: flex;
        gap: 32px;
    }
    .stat-mini {
        text-align: center;
    }
    .stat-value {
        display: block;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary-color);
    }
    .stat-label {
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    </style>
</body>
</html>
