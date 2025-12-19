<?php
/**
 * Distributed Frontend Entry Point
 * Multi-Tenant Journal Management System
 *
 * This is the main entry point for a journal frontend.
 * It fetches data from the Central API and renders pages using themes.
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Load dependencies
require_once __DIR__ . '/ApiClient.php';
require_once __DIR__ . '/Cache.php';

// Load configuration
$config = require __DIR__ . '/config.php';

// Debug mode
if ($config['debug']) {
    ini_set('display_errors', '1');
}

// Initialize API client
$api = new ApiClient($config['api_url'], $config['api_key']);

// Initialize cache
$cache = new Cache($config['cache']['path'], $config['cache']['ttl']);

// Get tenant info (cached)
try {
    $tenant = $cache->remember('tenant', function() use ($api) {
        return $api->getTenant();
    });
} catch (Exception $e) {
    die('Failed to connect to API: ' . $e->getMessage());
}

// Get menu pages (cached)
$menuPages = $cache->remember('menu_pages', function() use ($api) {
    return $api->getMenuPages();
});

// Parse request
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/') {
    $uri = str_replace($basePath, '', $uri);
}
$uri = '/' . trim($uri, '/');

// Theme path
$themePath = __DIR__ . '/themes/' . ($config['theme'] ?? 'default');

// Route request
try {
    routeRequest($uri, $api, $cache, $config, $tenant, $menuPages, $themePath);
} catch (Exception $e) {
    if ($config['debug']) {
        echo '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    } else {
        include $themePath . '/error.php';
    }
}

/**
 * Route request to appropriate handler
 */
function routeRequest($uri, $api, $cache, $config, $tenant, $menuPages, $themePath)
{
    // Home page
    if ($uri === '/' || $uri === '') {
        $recentArticles = $cache->remember('recent_articles', function() use ($api, $config) {
            return $api->getRecentArticles($config['recent_articles_count']);
        });

        $announcements = $cache->remember('announcements', function() use ($api, $config) {
            return $api->getAnnouncements($config['announcements_count']);
        });

        $currentIssue = $cache->remember('current_issue', function() use ($api) {
            return $api->getCurrentIssue();
        });

        include $themePath . '/home.php';
        return;
    }

    // Articles list
    if ($uri === '/articles') {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $cacheKey = "articles_page_$page";

        $result = $cache->remember($cacheKey, function() use ($api, $page, $config) {
            return $api->getArticles($page, $config['articles_per_page']);
        }, 300); // 5 min cache for lists

        $articles = $result['data'];
        $pagination = $result['pagination'];

        include $themePath . '/articles.php';
        return;
    }

    // Single article
    if (preg_match('#^/article/(.+)$#', $uri, $matches)) {
        $slug = $matches[1];
        $cacheKey = "article_$slug";

        $articleData = $cache->remember($cacheKey, function() use ($api, $slug) {
            return $api->getArticle($slug);
        });

        if (!$articleData) {
            http_response_code(404);
            include $themePath . '/404.php';
            return;
        }

        $article = $articleData['article'];
        $authors = $articleData['authors'];

        // Track view (async would be better)
        $api->trackArticleView($article->id);

        include $themePath . '/article-single.php';
        return;
    }

    // Archives (volumes & issues)
    if ($uri === '/archives') {
        $volumes = $cache->remember('volumes_with_issues', function() use ($api) {
            return $api->getVolumesWithIssues();
        });

        include $themePath . '/archives.php';
        return;
    }

    // Single issue
    if (preg_match('#^/issue/(\d+)$#', $uri, $matches)) {
        $issueId = (int) $matches[1];
        $cacheKey = "issue_$issueId";

        $issueData = $cache->remember($cacheKey, function() use ($api, $issueId) {
            return $api->getIssue($issueId);
        });

        if (!$issueData) {
            http_response_code(404);
            include $themePath . '/404.php';
            return;
        }

        $issue = $issueData['issue'];
        $volume = $issueData['volume'];
        $articles = $issueData['articles'];

        include $themePath . '/issue.php';
        return;
    }

    // Editorial board
    if ($uri === '/editorial-board') {
        $board = $cache->remember('editorial_board', function() use ($api) {
            return $api->getEditorialBoard(true);
        });

        include $themePath . '/editorial-board.php';
        return;
    }

    // Search
    if ($uri === '/search') {
        $query = trim($_GET['q'] ?? '');
        $searchResults = null;

        if (!empty($query) && strlen($query) >= 3) {
            $searchResults = $api->search($query);
        }

        include $themePath . '/search.php';
        return;
    }

    // Sitemap
    if ($uri === '/sitemap.xml') {
        header('Content-Type: application/xml');
        $articles = $cache->remember('all_published_articles', function() use ($api) {
            return $api->getArticles(1, 1000)['data'];
        }, 3600);

        include $themePath . '/sitemap.xml.php';
        return;
    }

    // CMS Pages (about, contact, etc.)
    $slug = ltrim($uri, '/');
    $page = null;

    foreach ($menuPages as $menuPage) {
        if ($menuPage->slug === $slug) {
            $page = $api->getPage($slug);
            break;
        }
    }

    if ($page) {
        include $themePath . '/page.php';
        return;
    }

    // 404
    http_response_code(404);
    include $themePath . '/404.php';
}
