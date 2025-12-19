<?php
$pageTitle = 'Search - ' . $tenant['name'];
$metaDescription = 'Search articles in ' . $tenant['name'];
include __DIR__ . '/header.php';
?>

<h1 style="margin-bottom: 30px;">Search Articles</h1>

<div class="card">
    <form action="/search" method="GET" style="margin-bottom: 30px;">
        <div class="search-form">
            <input type="text" name="q" value="<?= htmlspecialchars($query ?? '') ?>"
                   placeholder="Enter search terms..." required minlength="3"
                   style="flex: 1; padding: 15px; font-size: 1.1rem;">
            <button type="submit" class="btn btn-primary" style="padding: 15px 30px;">Search</button>
        </div>
        <p style="margin-top: 10px; color: #666; font-size: 0.9rem;">
            Search by title, abstract, or keywords. Minimum 3 characters required.
        </p>
    </form>

    <?php if ($searchResults !== null): ?>
        <?php if (!empty($query)): ?>
        <h2 style="margin-bottom: 20px;">
            Search Results for "<?= htmlspecialchars($query) ?>"
            <span style="font-weight: normal; color: #666;">(<?= $searchResults['count'] ?> results)</span>
        </h2>
        <?php endif; ?>

        <?php if (!empty($searchResults['articles'])): ?>
        <div class="article-list">
            <?php foreach ($searchResults['articles'] as $article): ?>
            <div class="article-item">
                <h3><a href="/article/<?= htmlspecialchars($article->slug) ?>"><?= htmlspecialchars($article->title) ?></a></h3>
                <div class="article-meta">
                    <?php if (!empty($article->published_at)): ?>
                    <span>Published: <?= date('M j, Y', strtotime($article->published_at)) ?></span>
                    <?php endif; ?>
                    <span>Views: <?= number_format($article->view_count) ?></span>
                </div>
                <?php if (!empty($article->abstract)): ?>
                <p class="article-abstract"><?= htmlspecialchars(substr($article->abstract, 0, 200)) ?>...</p>
                <?php endif; ?>
                <?php if (!empty($article->keywords)): ?>
                <p style="margin-top: 8px; font-size: 0.9rem;">
                    <strong>Keywords:</strong> <?= htmlspecialchars($article->keywords) ?>
                </p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="text-align: center; padding: 40px; color: #666;">
            No articles found matching your search.
            <br><br>
            Try using different keywords or broader terms.
        </p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
