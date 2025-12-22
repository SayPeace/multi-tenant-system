<?php
/**
 * 500 Server Error Page
 */
$config = require __DIR__ . '/../../config/admin.php';
$baseUrl = $config['base_url'] ?? '/multi-tenant-system/admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="error-body">
    <div class="error-container">
        <div class="error-code">500</div>
        <h1>Server Error</h1>
        <p>Something went wrong. Please try again later.</p>
        <a href="<?= $baseUrl ?>" class="btn btn-primary">Go to Dashboard</a>
    </div>
</body>
</html>
