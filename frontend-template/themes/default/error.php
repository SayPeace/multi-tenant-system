<?php
$pageTitle = 'Error - ' . ($tenant['name'] ?? 'Journal');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
        }
        h1 { color: #e74c3c; margin-bottom: 15px; }
        p { color: #666; margin-bottom: 20px; }
        a {
            display: inline-block;
            background: #1a73e8;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
        }
        a:hover { background: #1557b0; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Something Went Wrong</h1>
        <p>We're experiencing technical difficulties. Please try again later.</p>
        <a href="/">Return to Homepage</a>
    </div>
</body>
</html>
