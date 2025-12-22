<?php
// Get the base URL (protocol + domain)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];
?>
<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Homepage -->
    <url>
        <loc><?= $baseUrl ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <!-- Articles List -->
    <url>
        <loc><?= $baseUrl ?>/articles</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    <!-- Archives -->
    <url>
        <loc><?= $baseUrl ?>/archives</loc>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Editorial Board -->
    <url>
        <loc><?= $baseUrl ?>/editorial-board</loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>

    <!-- Menu Pages -->
    <?php foreach ($menuPages as $menuPage): ?>
    <url>
        <loc><?= $baseUrl ?>/<?= htmlspecialchars($menuPage['slug'] ?? '') ?></loc>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    <?php endforeach; ?>

    <!-- Individual Articles -->
    <?php foreach ($articles as $article): ?>
    <url>
        <loc><?= $baseUrl ?>/article/<?= htmlspecialchars($article['slug'] ?? '') ?></loc>
        <?php if (!empty($article['updated_at'])): ?>
        <lastmod><?= date('Y-m-d', strtotime($article['updated_at'])) ?></lastmod>
        <?php elseif (!empty($article['published_at'])): ?>
        <lastmod><?= date('Y-m-d', strtotime($article['published_at'])) ?></lastmod>
        <?php endif; ?>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
</urlset>
