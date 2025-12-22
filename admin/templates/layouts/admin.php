<?php
/**
 * Main Admin Layout
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Dashboard') ?></title>
    <?= CSRF::meta() ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <div class="admin-main">
            <?php include __DIR__ . '/partials/header.php'; ?>

            <main class="admin-content">
                <?php include __DIR__ . '/partials/flash.php'; ?>
                <?= $content ?? '' ?>
            </main>

            <?php include __DIR__ . '/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>
</body>
</html>
