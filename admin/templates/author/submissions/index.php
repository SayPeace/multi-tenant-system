<?php
/**
 * Author Submissions List Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'My Submissions') ?></title>
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
                    <a href="<?= $baseUrl ?>/author/submissions"
                       class="filter-tab <?= empty($statusFilter) ? 'active' : '' ?>">
                        All
                    </a>
                    <a href="<?= $baseUrl ?>/author/submissions?status=draft"
                       class="filter-tab <?= $statusFilter === 'draft' ? 'active' : '' ?>">
                        Drafts
                    </a>
                    <a href="<?= $baseUrl ?>/author/submissions?status=submitted"
                       class="filter-tab <?= $statusFilter === 'submitted' ? 'active' : '' ?>">
                        Submitted
                    </a>
                    <a href="<?= $baseUrl ?>/author/submissions?status=under_review"
                       class="filter-tab <?= $statusFilter === 'under_review' ? 'active' : '' ?>">
                        Under Review
                    </a>
                    <a href="<?= $baseUrl ?>/author/submissions?status=revision_required"
                       class="filter-tab <?= $statusFilter === 'revision_required' ? 'active' : '' ?>">
                        Needs Revision
                    </a>
                    <a href="<?= $baseUrl ?>/author/submissions?status=accepted"
                       class="filter-tab <?= $statusFilter === 'accepted' ? 'active' : '' ?>">
                        Accepted
                    </a>
                    <a href="<?= $baseUrl ?>/author/submissions?status=published"
                       class="filter-tab <?= $statusFilter === 'published' ? 'active' : '' ?>">
                        Published
                    </a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>My Submissions</h2>
                        <a href="<?= $baseUrl ?>/author/submissions/create" class="btn btn-primary btn-sm">
                            + New Submission
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($submissions)): ?>
                            <div class="empty-state">
                                <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="12" y1="18" x2="12" y2="12"></line>
                                    <line x1="9" y1="15" x2="15" y2="15"></line>
                                </svg>
                                <p>No submissions found.</p>
                                <a href="<?= $baseUrl ?>/author/submissions/create" class="btn btn-primary">
                                    Create Your First Submission
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Volume</th>
                                            <th>Submitted</th>
                                            <th>Last Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($submissions as $submission): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>" class="submission-title">
                                                        <?= htmlspecialchars(mb_strimwidth($submission['title'], 0, 80, '...')) ?>
                                                    </a>
                                                    <?php if (!empty($submission['keywords'])): ?>
                                                        <br><small class="text-muted">
                                                            <?= htmlspecialchars(mb_strimwidth($submission['keywords'], 0, 60, '...')) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = match($submission['status']) {
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
                                                        <?= ucwords(str_replace('_', ' ', $submission['status'])) ?>
                                                    </span>
                                                    <?php if (($submission['current_revision'] ?? 1) > 1): ?>
                                                        <br><small class="text-muted">Revision <?= $submission['current_revision'] ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($submission['volume_id'])): ?>
                                                        <span class="text-muted">Vol. <?= $submission['volume_id'] ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($submission['submitted_at'])): ?>
                                                        <?= date('M j, Y', strtotime($submission['submitted_at'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not submitted</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= date('M j, Y', strtotime($submission['updated_at'])) ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>"
                                                           class="btn btn-sm btn-secondary">View</a>
                                                        <?php if ($submission['status'] === 'draft'): ?>
                                                            <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>/edit"
                                                               class="btn btn-sm btn-primary">Edit</a>
                                                        <?php elseif ($submission['status'] === 'revision_required'): ?>
                                                            <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>/revise"
                                                               class="btn btn-sm btn-warning">Revise</a>
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
    .submission-title {
        color: var(--text-color);
        text-decoration: none;
        font-weight: 500;
    }
    .submission-title:hover {
        color: var(--primary-color);
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
    .empty-state p {
        margin-bottom: 20px;
    }
    .btn-group {
        display: flex;
        gap: 4px;
    }
    </style>
</body>
</html>
