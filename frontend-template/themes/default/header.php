<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $tenant['name']) ?></title>

    <?php if (isset($metaDescription)): ?>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <?php endif; ?>

    <?php if (isset($canonical)): ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <?php endif; ?>

    <?php if (!empty($tenant['branding']['favicon_url'])): ?>
    <link rel="icon" href="<?= htmlspecialchars($tenant['branding']['favicon_url']) ?>">
    <?php endif; ?>

    <style>
        :root {
            --primary-color: <?= htmlspecialchars($tenant['branding']['primary_color'] ?? '#1a73e8') ?>;
            --secondary-color: <?= htmlspecialchars($tenant['branding']['secondary_color'] ?? '#34a853') ?>;
            --text-color: #333;
            --bg-color: #f5f5f5;
            --white: #fff;
            --border-color: #ddd;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: var(--bg-color);
        }

        a {
            color: var(--primary-color);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: var(--white);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 0;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo img {
            max-height: 60px;
        }

        .logo-text h1 {
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .logo-text .tagline {
            font-size: 0.9rem;
            color: #666;
        }

        /* Navigation */
        .nav {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .nav a {
            color: var(--text-color);
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .nav a:hover {
            background: var(--bg-color);
            text-decoration: none;
        }

        .nav a.active {
            color: var(--primary-color);
            background: rgba(26, 115, 232, 0.1);
        }

        /* Main Content */
        .main {
            padding: 40px 0;
        }

        /* Footer */
        .footer {
            background: #333;
            color: #fff;
            padding: 40px 0 20px;
            margin-top: 40px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .footer h3 {
            margin-bottom: 15px;
            color: var(--secondary-color);
        }

        .footer a {
            color: #ccc;
        }

        .footer a:hover {
            color: #fff;
        }

        .footer-bottom {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #444;
            text-align: center;
            color: #888;
        }

        /* Cards */
        .card {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card h2, .card h3 {
            margin-bottom: 10px;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: #1557b0;
            text-decoration: none;
        }

        /* Article List */
        .article-item {
            border-bottom: 1px solid var(--border-color);
            padding: 20px 0;
        }

        .article-item:last-child {
            border-bottom: none;
        }

        .article-item h3 {
            margin-bottom: 8px;
        }

        .article-meta {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .article-meta span {
            margin-right: 15px;
        }

        .article-abstract {
            color: #555;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination a, .pagination span {
            padding: 8px 15px;
            border-radius: 4px;
            background: var(--white);
            border: 1px solid var(--border-color);
        }

        .pagination a:hover {
            background: var(--bg-color);
            text-decoration: none;
        }

        .pagination .current {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
        }

        /* Sidebar */
        .two-column {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        @media (max-width: 900px) {
            .two-column {
                grid-template-columns: 1fr;
            }
        }

        .sidebar .card {
            margin-bottom: 20px;
        }

        /* Announcements */
        .announcement {
            padding: 15px;
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            margin-bottom: 15px;
        }

        .announcement h4 {
            color: #b45309;
            margin-bottom: 5px;
        }

        /* Search */
        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-form input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
        }

        /* Page Content */
        .page-content {
            line-height: 1.8;
        }

        .page-content h2 {
            margin: 25px 0 15px;
            color: var(--primary-color);
        }

        .page-content h3 {
            margin: 20px 0 10px;
        }

        .page-content ul, .page-content ol {
            margin-left: 25px;
            margin-bottom: 15px;
        }

        .page-content p {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">
                    <?php if (!empty($tenant['branding']['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($tenant['branding']['logo_url']) ?>" alt="<?= htmlspecialchars($tenant['name']) ?>">
                    <?php endif; ?>
                    <div class="logo-text">
                        <h1><?= htmlspecialchars($tenant['name']) ?></h1>
                        <?php if (!empty($tenant['tagline'])): ?>
                        <div class="tagline"><?= htmlspecialchars($tenant['tagline']) ?></div>
                        <?php endif; ?>
                    </div>
                </a>

                <nav class="nav">
                    <a href="/" <?= $uri === '/' ? 'class="active"' : '' ?>>Home</a>
                    <a href="/articles" <?= $uri === '/articles' ? 'class="active"' : '' ?>>Articles</a>
                    <a href="/archives" <?= $uri === '/archives' ? 'class="active"' : '' ?>>Archives</a>
                    <a href="/editorial-board" <?= $uri === '/editorial-board' ? 'class="active"' : '' ?>>Editorial Board</a>
                    <?php foreach ($menuPages as $menuPage): ?>
                    <a href="/<?= htmlspecialchars($menuPage->slug) ?>" <?= $uri === '/'.$menuPage->slug ? 'class="active"' : '' ?>><?= htmlspecialchars($menuPage->title) ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
