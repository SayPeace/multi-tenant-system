<?php
/**
 * Forgot Password Page Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Forgot Password') ?></title>
    <?= CSRF::meta() ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><?= htmlspecialchars($config['app_name'] ?? 'Journal Admin') ?></h1>
                <p>Reset your password</p>
            </div>

            <?php include __DIR__ . '/../layouts/partials/flash.php'; ?>

            <div class="auth-content">
                <p class="auth-description">
                    Enter your email address and we'll send you a link to reset your password.
                </p>

                <form action="<?= $baseUrl ?>/forgot-password" method="POST" class="auth-form">
                    <?= CSRF::field() ?>

                    <div class="form-group">
                        <label for="account_type">Account Type</label>
                        <select name="account_type" id="account_type" class="form-control">
                            <option value="super_admin">System Administrator</option>
                            <option value="user">Journal User</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control"
                               placeholder="Enter your email" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>

                    <div class="auth-links">
                        <a href="<?= $baseUrl ?>/login">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>
</body>
</html>
