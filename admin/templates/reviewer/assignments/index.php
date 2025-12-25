<?php
/**
 * Reviewer Assignments List Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'My Assignments') ?></title>
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
                    <a href="<?= $baseUrl ?>/reviewer/assignments"
                       class="filter-tab <?= empty($statusFilter) ? 'active' : '' ?>">
                        All
                    </a>
                    <a href="<?= $baseUrl ?>/reviewer/assignments?status=pending"
                       class="filter-tab <?= $statusFilter === 'pending' ? 'active' : '' ?>">
                        Pending
                    </a>
                    <a href="<?= $baseUrl ?>/reviewer/assignments?status=accepted"
                       class="filter-tab <?= $statusFilter === 'accepted' ? 'active' : '' ?>">
                        Accepted
                    </a>
                    <a href="<?= $baseUrl ?>/reviewer/assignments?status=completed"
                       class="filter-tab <?= $statusFilter === 'completed' ? 'active' : '' ?>">
                        Completed
                    </a>
                    <a href="<?= $baseUrl ?>/reviewer/assignments?status=declined"
                       class="filter-tab <?= $statusFilter === 'declined' ? 'active' : '' ?>">
                        Declined
                    </a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>My Assignments</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assignments)): ?>
                            <div class="empty-state">
                                <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p>No assignments found.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Article</th>
                                            <th>Status</th>
                                            <th>Assigned</th>
                                            <th>Deadline</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assignments as $assignment): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>" class="assignment-title">
                                                        <?= htmlspecialchars(mb_strimwidth($assignment['article_title'], 0, 70, '...')) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = match($assignment['status']) {
                                                        'pending' => 'badge-warning',
                                                        'accepted' => 'badge-primary',
                                                        'completed' => 'badge-success',
                                                        'declined' => 'badge-secondary',
                                                        'cancelled' => 'badge-danger',
                                                        default => 'badge-secondary',
                                                    };
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= ucfirst($assignment['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($assignment['assigned_at'])) ?></td>
                                                <td>
                                                    <?php if (!empty($assignment['deadline_at'])): ?>
                                                        <?php
                                                        $deadline = strtotime($assignment['deadline_at']);
                                                        $now = time();
                                                        $daysLeft = ceil(($deadline - $now) / 86400);
                                                        $urgentClass = '';
                                                        if ($assignment['status'] === 'accepted' || $assignment['status'] === 'pending') {
                                                            $urgentClass = $daysLeft <= 3 ? 'text-danger' : ($daysLeft <= 7 ? 'text-warning' : '');
                                                        }
                                                        ?>
                                                        <span class="<?= $urgentClass ?>">
                                                            <?= date('M j, Y', $deadline) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not set</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>"
                                                           class="btn btn-sm btn-secondary">View</a>
                                                        <?php if ($assignment['status'] === 'accepted'): ?>
                                                            <a href="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>/review"
                                                               class="btn btn-sm btn-success">Review</a>
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
    .assignment-title {
        color: var(--text-color);
        text-decoration: none;
        font-weight: 500;
    }
    .assignment-title:hover {
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
    .btn-group {
        display: flex;
        gap: 4px;
    }
    .text-danger { color: var(--danger-color); }
    .text-warning { color: var(--warning-color); }
    </style>
</body>
</html>
