<?php
$pageTitle = $tenant['name'] . ' - Home';
$metaDescription = $tenant['tagline'] ?? $tenant['description'] ?? '';
include __DIR__ . '/header.php';
?>

<div class="two-column">
    <div class="main-content">
        <!-- Current Issue -->
        <?php if ($currentIssue): ?>
        <div class="card">
            <h2>Current Issue</h2>
            <p style="margin-bottom: 15px;">
                <strong>Volume <?= $currentIssue['volume']['volume_number'] ?? '' ?>, Issue <?= $currentIssue['issue']['issue_number'] ?? '' ?></strong>
                <?php if (!empty($currentIssue['issue']['month'])): ?>
                (<?= htmlspecialchars($currentIssue['issue']['month']) ?> <?= $currentIssue['volume']['year'] ?? '' ?>)
                <?php endif; ?>
            </p>

            <?php if (!empty($currentIssue['articles'])): ?>
            <div class="article-list">
                <?php foreach (array_slice($currentIssue['articles'], 0, 5) as $article): ?>
                <div class="article-item">
                    <h3><a href="<?= $baseUrl ?>/article/<?= htmlspecialchars($article['slug'] ?? '') ?>"><?= htmlspecialchars($article['title'] ?? '') ?></a></h3>
                    <div class="article-meta">
                        <?php if (!empty($article['published_at'])): ?>
                        <span>Published: <?= date('M j, Y', strtotime($article['published_at'])) ?></span>
                        <?php endif; ?>
                        <span>Views: <?= number_format($article['view_count'] ?? 0) ?></span>
                    </div>
                    <?php if (!empty($article['abstract'])): ?>
                    <p class="article-abstract"><?= htmlspecialchars(substr($article['abstract'], 0, 200)) ?>...</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 20px;">
                <a href="<?= $baseUrl ?>/issue/<?= $currentIssue['issue']['id'] ?? '' ?>" class="btn btn-primary">View Full Issue</a>
            </div>
            <?php else: ?>
            <p>No articles in this issue yet.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Recent Articles -->
        <div class="card">
            <h2>Recent Articles</h2>

            <?php if (!empty($recentArticles)): ?>
            <div class="article-list">
                <?php foreach ($recentArticles as $article): ?>
                <div class="article-item">
                    <h3><a href="<?= $baseUrl ?>/article/<?= htmlspecialchars($article['slug'] ?? '') ?>"><?= htmlspecialchars($article['title'] ?? '') ?></a></h3>
                    <div class="article-meta">
                        <?php if (!empty($article['published_at'])): ?>
                        <span>Published: <?= date('M j, Y', strtotime($article['published_at'])) ?></span>
                        <?php endif; ?>
                        <span>Views: <?= number_format($article['view_count'] ?? 0) ?></span>
                    </div>
                    <?php if (!empty($article['abstract'])): ?>
                    <p class="article-abstract"><?= htmlspecialchars(substr($article['abstract'], 0, 200)) ?>...</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 20px;">
                <a href="<?= $baseUrl ?>/articles" class="btn btn-primary">View All Articles</a>
            </div>
            <?php else: ?>
            <p>No articles published yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <aside class="sidebar">
        <!-- Search -->
        <div class="card">
            <h3>Search</h3>
            <form action="<?= $baseUrl ?>/search" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Search articles..." required>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <!-- Announcements -->
        <?php if (!empty($announcements)): ?>
        <div class="card">
            <h3>Announcements</h3>
            <?php foreach ($announcements as $announcement): ?>
            <div class="announcement">
                <h4><?= htmlspecialchars($announcement['title'] ?? '') ?></h4>
                <p><?= htmlspecialchars(substr($announcement['content'] ?? '', 0, 150)) ?>...</p>
                <small style="color: #666;"><?= date('M j, Y', strtotime($announcement['published_at'] ?? 'now')) ?></small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- About -->
        <div class="card">
            <h3>About the Journal</h3>
            <?php if (!empty($tenant['description'])): ?>
            <p><?= htmlspecialchars(substr($tenant['description'], 0, 300)) ?></p>
            <?php else: ?>
            <p><?= htmlspecialchars($tenant['tagline'] ?? 'Welcome to our journal.') ?></p>
            <?php endif; ?>
            <p style="margin-top: 10px;"><a href="<?= $baseUrl ?>/about">Read more &rarr;</a></p>
        </div>
    </aside>
</div>

<?php include __DIR__ . '/footer.php'; ?>
