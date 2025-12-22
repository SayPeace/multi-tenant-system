<?php
/**
 * Login Page Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Login') ?></title>
    <?= CSRF::meta() ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><?= htmlspecialchars($config['app_name'] ?? 'Journal Admin') ?></h1>
                <p>Sign in to your account</p>
            </div>

            <?php include __DIR__ . '/../layouts/partials/flash.php'; ?>

            <div class="auth-content">
                <form action="<?= $baseUrl ?>/login" method="POST" class="auth-form">
                    <?= CSRF::field() ?>

                    <div class="form-group">
                        <label for="login_type">Account Type</label>
                        <select name="login_type" id="login_type" class="form-control" onchange="toggleTenantField()">
                            <option value="super_admin" <?= Flash::old('login_type') === 'super_admin' ? 'selected' : '' ?>>
                                System Administrator
                            </option>
                            <option value="user" <?= Flash::old('login_type') === 'user' ? 'selected' : '' ?>>
                                Journal User
                            </option>
                        </select>
                    </div>

                    <div class="form-group" id="tenant_field" style="display: none;">
                        <label for="tenant_id">Select Journal</label>
                        <select name="tenant_id" id="tenant_id" class="form-control">
                            <option value="">-- Select Journal --</option>
                            <?php foreach ($tenants as $tenant): ?>
                            <option value="<?= $tenant['id'] ?>" <?= Flash::old('tenant_id') == $tenant['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tenant['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control"
                               value="<?= htmlspecialchars(Flash::old('email')) ?>"
                               placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control"
                               placeholder="Enter your password" required>
                    </div>

                    <div class="form-group form-check">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" value="1">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>

                    <div class="auth-links">
                        <a href="<?= $baseUrl ?>/forgot-password">Forgot your password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleTenantField() {
            var loginType = document.getElementById('login_type').value;
            var tenantField = document.getElementById('tenant_field');
            tenantField.style.display = (loginType === 'user') ? 'block' : 'none';
        }
        // Initialize on page load
        toggleTenantField();
    </script>
    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>
</body>
</html>
