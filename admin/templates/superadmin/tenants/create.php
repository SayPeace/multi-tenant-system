<?php
/**
 * Create Tenant Template
 */
$errors = Flash::errors();
$old = Flash::oldInput();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Create Journal') ?></title>
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
                        <h2>Create Journal</h2>
                        <a href="<?= $baseUrl ?>/superadmin/tenants" class="btn btn-secondary btn-sm">
                            &larr; Back to Journals
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="<?= $baseUrl ?>/superadmin/tenants" method="POST" class="tenant-form">
                            <?= CSRF::field() ?>

                            <div class="form-section">
                                <h3>Basic Information</h3>

                                <div class="form-group">
                                    <label for="name">Journal Name *</label>
                                    <input type="text" id="name" name="name" class="form-control"
                                           value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <span class="form-error"><?= $errors['name'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="slug">URL Slug *</label>
                                    <input type="text" id="slug" name="slug" class="form-control"
                                           value="<?= htmlspecialchars($old['slug'] ?? '') ?>"
                                           pattern="[a-z0-9-]+" required>
                                    <span class="form-text">Lowercase letters, numbers, and hyphens only. Used in URLs.</span>
                                    <?php if (isset($errors['slug'])): ?>
                                        <span class="form-error"><?= $errors['slug'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="tagline">Tagline</label>
                                    <input type="text" id="tagline" name="tagline" class="form-control"
                                           value="<?= htmlspecialchars($old['tagline'] ?? '') ?>">
                                    <span class="form-text">A short description or motto for the journal.</span>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Contact Information</h3>

                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                           value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <span class="form-error"><?= $errors['email'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" id="phone" name="phone" class="form-control"
                                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                                </div>

                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea id="address" name="address" class="form-control" rows="2"><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Appearance</h3>

                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="primary_color">Primary Color</label>
                                        <input type="color" id="primary_color" name="primary_color"
                                               value="<?= htmlspecialchars($old['primary_color'] ?? '#1a73e8') ?>" class="form-control-color">
                                    </div>

                                    <div class="form-group half">
                                        <label for="secondary_color">Secondary Color</label>
                                        <input type="color" id="secondary_color" name="secondary_color"
                                               value="<?= htmlspecialchars($old['secondary_color'] ?? '#34a853') ?>" class="form-control-color">
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Status</h3>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="is_active" value="1"
                                               <?= ($old['is_active'] ?? true) ? 'checked' : '' ?>>
                                        Journal is active and accessible
                                    </label>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Create Journal</button>
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
    <script>
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const slug = this.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        document.getElementById('slug').value = slug;
    });
    </script>

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
    </style>
</body>
</html>
