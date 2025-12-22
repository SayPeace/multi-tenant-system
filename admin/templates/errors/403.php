<?php
/**
 * 403 Forbidden Error Page
 */
$config = require __DIR__ . '/../../config/admin.php';
$baseUrl = $config['base_url'] ?? '/multi-tenant-system/admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="error-body">
    <div class="error-container">
        <div class="error-code">403</div>
        <h1>Access Denied</h1>
        <p>You don't have permission to access this resource.</p>
        <a href="<?= $baseUrl ?>" class="btn btn-primary">Go to Dashboard</a>
    </div>
</body>
</html>
