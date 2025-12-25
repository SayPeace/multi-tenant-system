<?php
/**
 * Author Dashboard Template
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Author Dashboard') ?></title>
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
                    <h1>Welcome, <?= htmlspecialchars($currentUser['first_name'] ?? 'Author') ?>!</h1>
                    <p class="text-muted"><?= htmlspecialchars($tenant['name'] ?? '') ?></p>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?= $stats['total'] ?? 0 ?></span>
                            <span class="stat-label">Total Submissions</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-secondary">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?= $stats['draft'] ?? 0 ?></span>
                            <span class="stat-label">Drafts</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-info">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?= ($stats['submitted'] ?? 0) + ($stats['under_review'] ?? 0) ?></span>
                            <span class="stat-label">In Review</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-warning">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?= $stats['revision_required'] ?? 0 ?></span>
                            <span class="stat-label">Needs Revision</span>
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
                            <span class="stat-number"><?= $stats['accepted'] ?? 0 ?></span>
                            <span class="stat-label">Accepted</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon bg-primary">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-number"><?= $stats['published'] ?? 0 ?></span>
                            <span class="stat-label">Published</span>
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
                            <a href="<?= $baseUrl ?>/author/submissions/create" class="action-card">
                                <div class="action-icon">
                                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </div>
                                <span>New Submission</span>
                            </a>
                            <a href="<?= $baseUrl ?>/author/submissions" class="action-card">
                                <div class="action-icon">
                                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="8" y1="6" x2="21" y2="6"></line>
                                        <line x1="8" y1="12" x2="21" y2="12"></line>
                                        <line x1="8" y1="18" x2="21" y2="18"></line>
                                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                    </svg>
                                </div>
                                <span>My Submissions</span>
                            </a>
                            <a href="<?= $baseUrl ?>/author/profile" class="action-card">
                                <div class="action-icon">
                                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <span>My Profile</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Submissions -->
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Submissions</h2>
                        <a href="<?= $baseUrl ?>/author/submissions" class="btn btn-secondary btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentSubmissions)): ?>
                            <div class="empty-state">
                                <p>You haven't submitted any articles yet.</p>
                                <a href="<?= $baseUrl ?>/author/submissions/create" class="btn btn-primary">
                                    Submit Your First Article
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Submitted</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentSubmissions as $submission): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars(mb_strimwidth($submission['title'], 0, 60, '...')) ?></strong>
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
                                                </td>
                                                <td>
                                                    <?php if (!empty($submission['submitted_at'])): ?>
                                                        <?= date('M j, Y', strtotime($submission['submitted_at'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not submitted</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>"
                                                       class="btn btn-sm btn-secondary">View</a>
                                                    <?php if ($submission['status'] === 'draft'): ?>
                                                        <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>/edit"
                                                           class="btn btn-sm btn-primary">Edit</a>
                                                    <?php elseif ($submission['status'] === 'revision_required'): ?>
                                                        <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>/revise"
                                                           class="btn btn-sm btn-warning">Revise</a>
                                                    <?php endif; ?>
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
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
    .stat-icon.bg-info { background: #17a2b8; }
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
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
    }
    .action-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 24px;
        background: #f8f9fa;
        border-radius: var(--border-radius);
        text-decoration: none;
        color: var(--text-color);
        transition: all 0.2s;
    }
    .action-card:hover {
        background: var(--primary-color);
        color: white;
        text-decoration: none;
    }
    .action-icon {
        margin-bottom: 12px;
    }
    .empty-state {
        text-align: center;
        padding: 40px;
        color: var(--text-muted);
    }
    .empty-state .btn {
        margin-top: 16px;
    }
    </style>
</body>
</html>
