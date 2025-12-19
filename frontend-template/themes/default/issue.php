<?php
$pageTitle = "Volume {$volume->volume_number}, Issue {$issue->issue_number} - " . $tenant['name'];
$metaDescription = "Articles published in Volume {$volume->volume_number}, Issue {$issue->issue_number} of " . $tenant['name'];
include __DIR__ . '/header.php';
?>

<div style="margin-bottom: 20px;">
    <a href="/archives">&larr; Back to Archives</a>
</div>

<div class="card">
    <h1 style="margin-bottom: 10px;">
        Volume <?= $volume->volume_number ?>, Issue <?= $issue->issue_number ?>
    </h1>

    <div style="margin-bottom: 20px; color: #666;">
        <?php if (!empty($issue->month)): ?>
        <span><?= htmlspecialchars($issue->month) ?> <?= $volume->year ?></span>
        <?php else: ?>
        <span><?= $volume->year ?></span>
        <?php endif; ?>

        <?php if ($issue->is_special_issue && !empty($issue->title)): ?>
        <span style="margin-left: 15px; font-style: italic;">Special Issue: <?= htmlspecialchars($issue->title) ?></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($issue->description)): ?>
    <p style="margin-bottom: 20px;"><?= htmlspecialchars($issue->description) ?></p>
    <?php endif; ?>

    <?php if (!empty($issue->cover_image)): ?>
    <div style="margin-bottom: 20px;">
        <img src="<?= htmlspecialchars($issue->cover_image) ?>" alt="Issue Cover" style="max-width: 200px; border: 1px solid #ddd;">
    </div>
    <?php endif; ?>

    <h2 style="margin-bottom: 20px; border-bottom: 2px solid var(--primary-color); padding-bottom: 10px;">
        Articles in this Issue
    </h2>

    <?php if (!empty($articles)): ?>
    <div class="article-list">
        <?php foreach ($articles as $article): ?>
        <div class="article-item">
            <h3><a href="/article/<?= htmlspecialchars($article->slug) ?>"><?= htmlspecialchars($article->title) ?></a></h3>
            <div class="article-meta">
                <?php if (!empty($article->pages)): ?>
                <span>Pages: <?= htmlspecialchars($article->pages) ?></span>
                <?php endif; ?>
                <?php if (!empty($article->doi)): ?>
                <span>DOI: <a href="https://doi.org/<?= htmlspecialchars($article->doi) ?>" target="_blank"><?= htmlspecialchars($article->doi) ?></a></span>
                <?php endif; ?>
                <span>Views: <?= number_format($article->view_count) ?></span>
            </div>
            <?php if (!empty($article->abstract)): ?>
            <p class="article-abstract"><?= htmlspecialchars(substr($article->abstract, 0, 200)) ?>...</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p>No articles in this issue yet.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
