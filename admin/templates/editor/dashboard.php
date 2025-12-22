<?php
/**
 * Editor Dashboard Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
    <?= CSRF::meta() ?>
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <?php include __DIR__ . '/../layouts/partials/sidebar.php'; ?>

        <div class="admin-main">
            <?php include __DIR__ . '/../layouts/partials/header.php'; ?>

            <main class="admin-content">
                <?php include __DIR__ . '/../layouts/partials/flash.php'; ?>

                <!-- Journal Info -->
                <?php if ($tenant): ?>
                <div class="journal-info-bar">
                    <h2><?= htmlspecialchars($tenant['name']) ?></h2>
                    <?php if (!empty($tenant['tagline'])): ?>
                        <p><?= htmlspecialchars($tenant['tagline']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #1a73e8;">&#9998;</div>
                        <div class="stat-content">
                            <h3><?= number_format($stats['total_articles']) ?></h3>
                            <p>Total Articles</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #fbbc05;">&#9993;</div>
                        <div class="stat-content">
                            <h3><?= number_format($stats['pending_submissions']) ?></h3>
                            <p>Pending Submissions</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #4285f4;">&#9881;</div>
                        <div class="stat-content">
                            <h3><?= number_format($stats['under_review']) ?></h3>
                            <p>Under Review</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #34a853;">&#10003;</div>
                        <div class="stat-content">
                            <h3><?= number_format($stats['published']) ?></h3>
                            <p>Published</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="<?= $baseUrl ?>/editor/articles" class="btn btn-primary">
                                View All Submissions
                            </a>
                            <a href="<?= $baseUrl ?>/editor/users" class="btn btn-secondary">
                                Manage Users
                            </a>
                            <a href="<?= $baseUrl ?>/editor/volumes" class="btn btn-secondary">
                                Manage Volumes
                            </a>
                            <a href="<?= $baseUrl ?>/editor/settings" class="btn btn-secondary">
                                Journal Settings
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Submissions -->
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Submissions</h2>
                        <a href="<?= $baseUrl ?>/editor/articles" class="btn btn-sm btn-secondary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentSubmissions)): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSubmissions as $article): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($article['title'] ?? 'Untitled') ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($article['first_name']) || !empty($article['last_name'])): ?>
                                            <?= htmlspecialchars(trim(($article['first_name'] ?? '') . ' ' . ($article['last_name'] ?? ''))) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Unknown</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($article['status'] ?? 'draft') {
                                            'draft' => 'badge-secondary',
                                            'submitted' => 'badge-warning',
                                            'under_review' => 'badge-primary',
                                            'revision_required' => 'badge-warning',
                                            'accepted' => 'badge-success',
                                            'rejected' => 'badge-danger',
                                            'published' => 'badge-success',
                                            default => 'badge-secondary',
                                        };
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= ucwords(str_replace('_', ' ', $article['status'] ?? 'draft')) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= date('M j, Y', strtotime($article['created_at'])) ?>
                                    </td>
                                    <td>
                                        <a href="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>"
                                           class="btn btn-sm btn-secondary">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-muted">No submissions yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </main>

            <?php include __DIR__ . '/../layouts/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>

    <style>
    .journal-info-bar {
        background: var(--white);
        padding: 20px 24px;
        border-radius: var(--border-radius);
        margin-bottom: 24px;
        box-shadow: var(--shadow);
    }
    .journal-info-bar h2 {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0 0 4px 0;
        color: var(--primary-color);
    }
    .journal-info-bar p {
        margin: 0;
        color: var(--text-muted);
    }
    </style>
</body>
</html>
