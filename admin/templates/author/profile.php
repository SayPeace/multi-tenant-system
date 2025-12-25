<?php
/**
 * Author Profile Template
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
    <title><?= htmlspecialchars($pageTitle ?? 'My Profile') ?></title>
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

                <div class="card">
                    <div class="card-header">
                        <h2>My Profile</h2>
                    </div>
                    <div class="card-body">
                        <form action="<?= $baseUrl ?>/author/profile" method="POST">
                            <?= CSRF::field() ?>

                            <div class="form-section">
                                <h3>Personal Information</h3>

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
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                           value="<?= htmlspecialchars($getValue('email')) ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <span class="form-error"><?= $errors['email'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Academic Information</h3>

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
                                           placeholder="e.g., University of Example, Department of Computer Science">
                                </div>

                                <div class="form-group">
                                    <label for="orcid">ORCID ID</label>
                                    <input type="text" id="orcid" name="orcid" class="form-control"
                                           value="<?= htmlspecialchars($getValue('orcid')) ?>"
                                           placeholder="e.g., 0000-0001-2345-6789">
                                    <span class="form-text">Your ORCID identifier for author disambiguation.</span>
                                </div>

                                <div class="form-group">
                                    <label for="bio">Biography</label>
                                    <textarea id="bio" name="bio" class="form-control" rows="4"
                                              placeholder="A brief biography about your research interests and background..."><?= htmlspecialchars($getValue('bio')) ?></textarea>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Change Password</h3>
                                <p class="form-text mb-3">Leave blank if you don't want to change your password.</p>

                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control">
                                    <span class="form-text">Required only if you want to change your password.</span>
                                </div>

                                <div class="form-row">
                                    <div class="form-group half">
                                        <label for="password">New Password</label>
                                        <input type="password" id="password" name="password" class="form-control">
                                        <?php if (isset($errors['password'])): ?>
                                            <span class="form-error"><?= $errors['password'] ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group half">
                                        <label for="password_confirmation">Confirm New Password</label>
                                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Account Information</h3>
                                <div class="account-info">
                                    <div class="info-item">
                                        <span class="info-label">Role:</span>
                                        <span class="info-value">
                                            <span class="badge badge-success"><?= ucwords(str_replace('_', ' ', $user['role'] ?? 'Author')) ?></span>
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Member Since:</span>
                                        <span class="info-value"><?= date('F j, Y', strtotime($user['created_at'])) ?></span>
                                    </div>
                                    <?php if (!empty($user['last_login_at'])): ?>
                                        <div class="info-item">
                                            <span class="info-label">Last Login:</span>
                                            <span class="info-value"><?= date('F j, Y \a\t g:i A', strtotime($user['last_login_at'])) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="<?= $baseUrl ?>/author/dashboard" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

            </main>

            <?php include __DIR__ . '/../layouts/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>

    <script>
    // Password confirmation validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        const currentPassword = document.getElementById('current_password').value;

        if (password && password !== confirmation) {
            e.preventDefault();
            alert('New password and confirmation do not match.');
            return false;
        }

        if (password && !currentPassword) {
            e.preventDefault();
            alert('Please enter your current password to change it.');
            return false;
        }
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
    .account-info {
        background: #f8f9fa;
        border-radius: var(--border-radius);
        padding: 16px;
    }
    .info-item {
        display: flex;
        gap: 12px;
        margin-bottom: 8px;
    }
    .info-item:last-child {
        margin-bottom: 0;
    }
    .info-label {
        font-weight: 500;
        color: var(--text-muted);
        min-width: 100px;
    }
    .info-value {
        color: var(--text-color);
    }
    .mb-3 {
        margin-bottom: 12px;
    }
    textarea.form-control {
        resize: vertical;
    }
    </style>
</body>
</html>
