<?php
/**
 * Reset Password Page Template
 */
$token = $this->params['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Reset Password') ?></title>
    <?= CSRF::meta() ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><?= htmlspecialchars($config['app_name'] ?? 'Journal Admin') ?></h1>
                <p>Create a new password</p>
            </div>

            <?php include __DIR__ . '/../layouts/partials/flash.php'; ?>

            <div class="auth-content">
                <form action="<?= $baseUrl ?>/reset-password" method="POST" class="auth-form">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" name="password" id="password" class="form-control"
                               placeholder="Enter new password" required minlength="8">
                        <small class="form-text">
                            Password must be at least 8 characters with uppercase, lowercase, and a number.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control" placeholder="Confirm new password" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>

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
