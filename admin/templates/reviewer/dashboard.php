<?php
/**
 * Reviewer Dashboard Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Reviewer Dashboard') ?></title>
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

                <div class="dashboard-header">
                    <h1>Welcome, <?= htmlspecialchars($currentUser['first_name'] ?? 'Reviewer') ?>!</h1>
                    <p class="text-muted"><?= htmlspecialchars($tenant['name'] ?? '') ?></p>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?= $stats['pending'] ?? 0 ?></span>
                            <span class="stat-label">Pending Invitations</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?= $stats['accepted'] ?? 0 ?></span>
                            <span class="stat-label">Active Reviews</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-success">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?= $stats['completed'] ?? 0 ?></span>
                            <span class="stat-label">Completed Reviews</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-secondary">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?= $stats['declined'] ?? 0 ?></span>
                            <span class="stat-label">Declined</span>
                        </div>
                    </div>
                </div>

                <!-- Pending Invitations -->
                <?php if (!empty($pendingAssignments)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Pending Invitations</h2>
                            <a href="<?= $baseUrl ?>/reviewer/assignments?status=pending" class="btn btn-secondary btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Article</th>
                                            <th>Assigned</th>
                                            <th>Deadline</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingAssignments as $assignment): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars(mb_strimwidth($assignment['article_title'], 0, 60, '...')) ?></strong>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($assignment['assigned_at'])) ?></td>
                                                <td>
                                                    <?php if (!empty($assignment['deadline_at'])): ?>
                                                        <?php
                                                        $deadline = strtotime($assignment['deadline_at']);
                                                        $now = time();
                                                        $daysLeft = ceil(($deadline - $now) / 86400);
                                                        $urgentClass = $daysLeft <= 3 ? 'text-danger' : ($daysLeft <= 7 ? 'text-warning' : '');
                                                        ?>
                                                        <span class="<?= $urgentClass ?>">
                                                            <?= date('M j, Y', $deadline) ?>
                                                            <?php if ($daysLeft > 0): ?>
                                                                (<?= $daysLeft ?> days)
                                                            <?php else: ?>
                                                                (Overdue)
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not set</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>"
                                                       class="btn btn-sm btn-primary">View & Respond</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Active Reviews -->
                <?php if (!empty($activeAssignments)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Active Reviews</h2>
                            <a href="<?= $baseUrl ?>/reviewer/assignments?status=accepted" class="btn btn-secondary btn-sm">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Article</th>
                                            <th>Accepted</th>
                                            <th>Deadline</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($activeAssignments as $assignment): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars(mb_strimwidth($assignment['article_title'], 0, 60, '...')) ?></strong>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($assignment['responded_at'] ?? $assignment['assigned_at'])) ?></td>
                                                <td>
                                                    <?php if (!empty($assignment['deadline_at'])): ?>
                                                        <?php
                                                        $deadline = strtotime($assignment['deadline_at']);
                                                        $now = time();
                                                        $daysLeft = ceil(($deadline - $now) / 86400);
                                                        $urgentClass = $daysLeft <= 3 ? 'text-danger' : ($daysLeft <= 7 ? 'text-warning' : '');
                                                        ?>
                                                        <span class="<?= $urgentClass ?>">
                                                            <?= date('M j, Y', $deadline) ?>
                                                            <?php if ($daysLeft > 0): ?>
                                                                (<?= $daysLeft ?> days left)
                                                            <?php else: ?>
                                                                (Overdue!)
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not set</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>/review"
                                                       class="btn btn-sm btn-success">Submit Review</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Empty State -->
                <?php if (empty($pendingAssignments) && empty($activeAssignments)): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-state">
                                <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p>You have no pending or active review assignments.</p>
                                <a href="<?= $baseUrl ?>/reviewer/assignments" class="btn btn-secondary">View All Assignments</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </main>

            <?php include __DIR__ . '/../layouts/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>

    <style>
    .dashboard-header {
        margin-bottom: 24px;
    }
    .dashboard-header h1 {
        margin: 0 0 8px 0;
        font-size: 1.75rem;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: var(--white);
        border-radius: var(--border-radius);
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-sm);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    .stat-icon.bg-primary { background: var(--primary-color); }
    .stat-icon.bg-secondary { background: #6c757d; }
    .stat-icon.bg-success { background: var(--success-color); }
    .stat-icon.bg-warning { background: var(--warning-color); }
    .stat-icon.bg-danger { background: var(--danger-color); }
    .stat-content {
        display: flex;
        flex-direction: column;
    }
    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-color);
    }
    .stat-label {
        font-size: 0.85rem;
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
    .text-danger { color: var(--danger-color); }
    .text-warning { color: var(--warning-color); }
    </style>
</body>
</html>
