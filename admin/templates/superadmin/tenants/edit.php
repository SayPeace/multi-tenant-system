<?php
/**
 * Edit Tenant Template
 */
$errors = Flash::errors();
$old = Flash::oldInput();
// Use old input if available, otherwise use tenant data
$getValue = function($field) use ($old, $tenant) {
    return $old[$field] ?? $tenant[$field] ?? '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Edit Journal') ?></title>
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
                        <h2>Edit Journal: <?= htmlspecialchars($tenant['name']) ?></h2>
                        <a href="<?= $baseUrl ?>/superadmin/tenants" class="btn btn-secondary btn-sm">
                            &larr; Back to Journals
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="<?= $baseUrl ?>/superadmin/tenants/<?= $tenant['id'] ?>" method="POST" class="tenant-form">
                            <?= CSRF::field() ?>

                            <div class="form-section">
                                <h3>Basic Information</h3>

                                <div class="form-group">
                                    <label for="name">Journal Name *</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                           value="<?= htmlspecialchars($getValue('name')) ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <span class="form-error"><?= $errors['name'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="slug">URL Slug *</label>
                                    <input type="text" id="slug" name="slug" class="form-control"
                                           value="<?= htmlspecialchars($getValue('slug')) ?>"
                                           pattern="[a-z0-9-]+" required>
                                    <span class="form-text">Lowercase letters, numbers, and hyphens only. Changing this will break existing URLs.</span>
                                    <?php if (isset($errors['slug'])): ?>
                                        <span class="form-error"><?= $errors['slug'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="tagline">Tagline</label>
                                    <input type="text" id="tagline" name="tagline" class="form-control"
                                           value="<?= htmlspecialchars($getValue('tagline')) ?>">
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($getValue('description')) ?></textarea>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Contact Information</h3>

                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                           value="<?= htmlspecialchars($getValue('email')) ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <span class="form-error"><?= $errors['email'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" id="phone" name="phone" class="form-control"
                                           value="<?= htmlspecialchars($getValue('phone')) ?>">
                                </div>

                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea id="address" name="address" class="form-control" rows="2"><?= htmlspecialchars($getValue('address')) ?></textarea>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Appearance</h3>

                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="primary_color">Primary Color</label>
                                        <input type="color" id="primary_color" name="primary_color"
                                               value="<?= htmlspecialchars($getValue('primary_color') ?: '#1a73e8') ?>" class="form-control-color">
                                    </div>

                                    <div class="form-group half">
                                        <label for="secondary_color">Secondary Color</label>
                                        <input type="color" id="secondary_color" name="secondary_color"
                                               value="<?= htmlspecialchars($getValue('secondary_color') ?: '#34a853') ?>" class="form-control-color">
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Status</h3>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="is_active" value="1"
                                               <?= ($getValue('is_active')) ? 'checked' : '' ?>>
                                        Journal is active and accessible
                                    </label>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>API Access</h3>

                                <div class="form-group">
                                    <label>API Key</label>
                                    <div class="api-key-display">
                                        <code><?= htmlspecialchars($tenant['api_key'] ?? 'Not generated') ?></code>
                                    </div>
                                    <?php if (!empty($tenant['api_key_created_at'])): ?>
                                        <span class="form-text">Created: <?= date('M j, Y g:i A', strtotime($tenant['api_key_created_at'])) ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="regenerate_api_key" value="1">
                                        Regenerate API key (this will invalidate the current key)
                                    </label>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Statistics</h3>

                                <div class="stats-mini">
                                    <div class="stat-mini">
                                        <span class="stat-value"><?= (int)($tenant['user_count'] ?? 0) ?></span>
                                        <span class="stat-label">Users</span>
                                    </div>
                                    <div class="stat-mini">
                                        <span class="stat-value"><?= (int)($tenant['article_count'] ?? 0) ?></span>
                                        <span class="stat-label">Articles</span>
                                    </div>
                                    <div class="stat-mini">
                                        <span class="stat-value"><?= date('M j, Y', strtotime($tenant['created_at'])) ?></span>
                                        <span class="stat-label">Created</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Update Journal</button>
                                <a href="<?= $baseUrl ?>/superadmin/tenants" class="btn btn-secondary">Cancel</a>
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
    .form-control-color {
        width: 60px;
        height: 40px;
        padding: 4px;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        cursor: pointer;
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
    .api-key-display {
        padding: 12px;
        background: #f8f9fa;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        font-family: monospace;
        word-break: break-all;
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
        font-size: 1.25rem;
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
