<?php
/**
 * Editor Articles List Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Manage Articles') ?></title>
    <?= CSRF::meta() ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include __DIR__ . '/../../layouts/partials/sidebar.php'; ?>

        <div class="admin-main">
            <?php include __DIR__ . '/../../layouts/partials/header.php'; ?>

            <main class="admin-content">
                <?php include __DIR__ . '/../../layouts/partials/flash.php'; ?>

                <!-- Status Filter Tabs -->
                <div class="filter-tabs">
                    <a href="<?= $baseUrl ?>/editor/articles"
                       class="filter-tab <?= empty($statusFilter) ? 'active' : '' ?>">
                        All (<?= $statusCounts['all'] ?>)
                    </a>
                    <a href="<?= $baseUrl ?>/editor/articles?status=submitted"
                       class="filter-tab <?= $statusFilter === 'submitted' ? 'active' : '' ?>">
                        Submitted (<?= $statusCounts['submitted'] ?>)
                    </a>
                    <a href="<?= $baseUrl ?>/editor/articles?status=under_review"
                       class="filter-tab <?= $statusFilter === 'under_review' ? 'active' : '' ?>">
                        Under Review (<?= $statusCounts['under_review'] ?>)
                    </a>
                    <a href="<?= $baseUrl ?>/editor/articles?status=revision_required"
                       class="filter-tab <?= $statusFilter === 'revision_required' ? 'active' : '' ?>">
                        Revision Required (<?= $statusCounts['revision_required'] ?>)
                    </a>
                    <a href="<?= $baseUrl ?>/editor/articles?status=accepted"
                       class="filter-tab <?= $statusFilter === 'accepted' ? 'active' : '' ?>">
                        Accepted (<?= $statusCounts['accepted'] ?>)
                    </a>
                    <a href="<?= $baseUrl ?>/editor/articles?status=published"
                       class="filter-tab <?= $statusFilter === 'published' ? 'active' : '' ?>">
                        Published (<?= $statusCounts['published'] ?>)
                    </a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Articles</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($articles)): ?>
                            <div class="empty-state">
                                <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p>No articles found.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Status</th>
                                            <th>Reviews</th>
                                            <th>Submitted</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($articles as $article): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>" class="article-title">
                                                        <?= htmlspecialchars(mb_strimwidth($article['title'], 0, 60, '...')) ?>
                                                    </a>
                                                    <?php if (!empty($article['keywords'])): ?>
                                                        <br><small class="text-muted">
                                                            <?= htmlspecialchars(mb_strimwidth($article['keywords'], 0, 40, '...')) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars(trim(($article['author_first_name'] ?? '') . ' ' . ($article['author_last_name'] ?? ''))) ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = match($article['status']) {
                                                        'draft' => 'badge-secondary',
                                                        'submitted' => 'badge-info',
                                                        'under_review' => 'badge-primary',
                                                        'revision_required' => 'badge-warning',
                                                        'accepted' => 'badge-success',
                                                        'rejected' => 'badge-danger',
                                                        'published' => 'badge-success',
                                                        default => 'badge-secondary',
                                                    };
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= ucwords(str_replace('_', ' ', $article['status'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="review-count">
                                                        <?= $article['review_count'] ?>/<?= $article['assignment_count'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($article['submitted_at'])): ?>
                                                        <?= date('M j, Y', strtotime($article['submitted_at'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>"
                                                           class="btn btn-sm btn-secondary">View</a>
                                                        <?php if (in_array($article['status'], ['submitted', 'under_review'])): ?>
                                                            <a href="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>/assign-reviewers"
                                                               class="btn btn-sm btn-primary">Assign</a>
                                                        <?php endif; ?>
                                                        <?php if ($article['review_count'] > 0 && in_array($article['status'], ['under_review', 'submitted'])): ?>
                                                            <a href="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>/decision"
                                                               class="btn btn-sm btn-success">Decide</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </main>

            <?php include __DIR__ . '/../../layouts/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>

    <style>
    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .filter-tab {
        padding: 8px 16px;
        background: var(--white);
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius-sm);
        text-decoration: none;
        color: var(--text-color);
        font-size: 0.9rem;
    }
    .filter-tab:hover {
        background: #f8f9fa;
        text-decoration: none;
    }
    .filter-tab.active {
        background: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
    }
    .article-title {
        color: var(--text-color);
        text-decoration: none;
        font-weight: 500;
    }
    .article-title:hover {
        color: var(--primary-color);
    }
    .review-count {
        font-weight: 600;
        color: var(--text-muted);
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-muted);
    }
    .empty-state svg {
        margin-bottom: 16px;
        opacity: 0.5;
    }
    .btn-group {
        display: flex;
        gap: 4px;
    }
    </style>
</body>
</html>
