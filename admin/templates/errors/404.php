<?php
/**
 * 404 Not Found Error Page
 */
$config = require __DIR__ . '/../../config/admin.php';
$baseUrl = $config['base_url'] ?? '/multi-tenant-system/admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="error-body">
    <div class="error-container">
        <div class="error-code">404</div>
        <h1>Page Not Found</h1>
        <p>The page you are looking for doesn't exist or has been moved.</p>
        <a href="<?= $baseUrl ?>" class="btn btn-primary">Go to Dashboard</a>
    </div>
</body>
</html>
