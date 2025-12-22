<?php
/**
 * Authentication Layout (Login, Password Reset)
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
            </div>

            <?php include __DIR__ . '/partials/flash.php'; ?>

            <div class="auth-content">
                <?= $content ?? '' ?>
            </div>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>
</body>
</html>
