<?php
$pageTitle = 'Articles - ' . $tenant['name'];
$metaDescription = 'Browse all published articles in ' . $tenant['name'];
include __DIR__ . '/header.php';
?>

<h1 style="margin-bottom: 30px;">Articles</h1>

<div class="card">
    <?php if (!empty($articles)): ?>
    <div class="article-list">
        <?php foreach ($articles as $article): ?>
        <div class="article-item">
            <h3><a href="<?= $baseUrl ?>/article/<?= htmlspecialchars($article['slug'] ?? '') ?>"><?= htmlspecialchars($article['title'] ?? '') ?></a></h3>
            <div class="article-meta">
                <?php if (!empty($article['published_at'])): ?>
                <span>Published: <?= date('M j, Y', strtotime($article['published_at'])) ?></span>
                <?php endif; ?>
                <span>Views: <?= number_format($article['view_count'] ?? 0) ?></span>
                <?php if (!empty($article['doi'])): ?>
                <span>DOI: <?= htmlspecialchars($article['doi']) ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($article['abstract'])): ?>
            <p class="article-abstract"><?= htmlspecialchars(substr($article['abstract'], 0, 300)) ?>...</p>
            <?php endif; ?>
            <?php if (!empty($article['keywords'])): ?>
            <p style="margin-top: 10px;">
                <strong>Keywords:</strong>
                <span style="color: #666;"><?= htmlspecialchars($article['keywords']) ?></span>
            </p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['last_page'] > 1): ?>
    <div class="pagination">
        <?php if ($pagination['current_page'] > 1): ?>
        <a href="<?= $baseUrl ?>/articles?page=<?= $pagination['current_page'] - 1 ?>">&laquo; Previous</a>
        <?php endif; ?>

        <?php
        $start = max(1, $pagination['current_page'] - 2);
        $end = min($pagination['last_page'], $pagination['current_page'] + 2);

        for ($i = $start; $i <= $end; $i++):
        ?>
            <?php if ($i == $pagination['current_page']): ?>
            <span class="current"><?= $i ?></span>
            <?php else: ?>
            <a href="<?= $baseUrl ?>/articles?page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
        <a href="<?= $baseUrl ?>/articles?page=<?= $pagination['current_page'] + 1 ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <p>No articles published yet.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
