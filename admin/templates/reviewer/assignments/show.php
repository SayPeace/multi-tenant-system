<?php
/**
 * View Assignment Template (Reviewer)
 */
$statusClass = match($assignment['status']) {
    'pending' => 'badge-warning',
    'accepted' => 'badge-primary',
    'completed' => 'badge-success',
    'declined' => 'badge-secondary',
    'cancelled' => 'badge-danger',
    default => 'badge-secondary',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'View Assignment') ?></title>
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

                <!-- Status Banner -->
                <?php if ($assignment['status'] === 'pending'): ?>
                    <div class="alert alert-warning">
                        <strong>Invitation Pending:</strong> Please review the article details below and accept or decline this review request.
                    </div>
                <?php elseif ($assignment['status'] === 'accepted'): ?>
                    <div class="alert alert-info">
                        <strong>Review In Progress:</strong> You have accepted this review. Please submit your review before the deadline.
                        <div style="margin-top: 12px;">
                            <a href="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>/review" class="btn btn-success btn-sm">
                                Submit Review
                            </a>
                        </div>
                    </div>
                <?php elseif ($assignment['status'] === 'completed'): ?>
                    <div class="alert alert-success">
                        <strong>Review Completed:</strong> Thank you for submitting your review.
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2><?= htmlspecialchars($article['title']) ?></h2>
                            <span class="badge <?= $statusClass ?>" style="margin-top: 8px;">
                                <?= ucfirst($assignment['status']) ?>
                            </span>
                        </div>
                        <div class="header-actions">
                            <a href="<?= $baseUrl ?>/reviewer/assignments" class="btn btn-secondary btn-sm">
                                &larr; Back to Assignments
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Assignment Details -->
                        <div class="article-section">
                            <h3>Assignment Details</h3>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value">
                                        <span class="badge <?= $statusClass ?>"><?= ucfirst($assignment['status']) ?></span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Assigned</span>
                                    <span class="detail-value"><?= date('F j, Y', strtotime($assignment['assigned_at'])) ?></span>
                                </div>
                                <?php if (!empty($assignment['deadline_at'])): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Deadline</span>
                                        <?php
                                        $deadline = strtotime($assignment['deadline_at']);
                                        $now = time();
                                        $daysLeft = ceil(($deadline - $now) / 86400);
                                        $urgentClass = '';
                                        if ($assignment['status'] === 'accepted' || $assignment['status'] === 'pending') {
                                            $urgentClass = $daysLeft <= 3 ? 'text-danger' : ($daysLeft <= 7 ? 'text-warning' : '');
                                        }
                                        ?>
                                        <span class="detail-value <?= $urgentClass ?>">
                                            <?= date('F j, Y', $deadline) ?>
                                            <?php if ($assignment['status'] !== 'completed' && $assignment['status'] !== 'declined'): ?>
                                                <?php if ($daysLeft > 0): ?>
                                                    (<?= $daysLeft ?> days remaining)
                                                <?php else: ?>
                                                    (Overdue!)
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($assignment['responded_at'])): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Responded</span>
                                        <span class="detail-value"><?= date('F j, Y', strtotime($assignment['responded_at'])) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($assignment['completed_at'])): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Completed</span>
                                        <span class="detail-value"><?= date('F j, Y', strtotime($assignment['completed_at'])) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Article Abstract -->
                        <div class="article-section">
                            <h3>Abstract</h3>
                            <div class="abstract-text">
                                <?= nl2br(htmlspecialchars($article['abstract'])) ?>
                            </div>
                        </div>

                        <?php if (!empty($article['keywords'])): ?>
                            <div class="article-section">
                                <h3>Keywords</h3>
                                <div class="keywords">
                                    <?php foreach (explode(',', $article['keywords']) as $keyword): ?>
                                        <span class="keyword-tag"><?= htmlspecialchars(trim($keyword)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Editor Notes -->
                        <?php if (!empty($assignment['notes'])): ?>
                            <div class="article-section">
                                <h3>Notes from Editor</h3>
                                <div class="editor-notes">
                                    <?= nl2br(htmlspecialchars($assignment['notes'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Actions -->
                        <?php if ($assignment['status'] === 'pending'): ?>
                            <div class="article-section">
                                <h3>Respond to Invitation</h3>
                                <div class="action-buttons">
                                    <form action="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>/accept" method="POST" style="display: inline;">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="btn btn-success">
                                            Accept & Review
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-secondary" onclick="showDeclineForm()">
                                        Decline
                                    </button>
                                </div>

                                <div id="declineForm" style="display: none; margin-top: 20px;">
                                    <form action="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>/decline" method="POST">
                                        <?= CSRF::field() ?>
                                        <div class="form-group">
                                            <label for="decline_reason">Reason for declining (optional)</label>
                                            <textarea id="decline_reason" name="decline_reason" class="form-control" rows="3"
                                                      placeholder="Please let us know why you cannot review this article..."></textarea>
                                        </div>
                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-danger">Confirm Decline</button>
                                            <button type="button" class="btn btn-secondary" onclick="hideDeclineForm()">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php elseif ($assignment['status'] === 'accepted'): ?>
                            <div class="article-section">
                                <h3>Submit Your Review</h3>
                                <p>Please carefully review the article and submit your evaluation.</p>
                                <a href="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>/review" class="btn btn-success">
                                    Submit Review
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

            </main>

            <?php include __DIR__ . '/../../layouts/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>
    <script>
    function showDeclineForm() {
        document.getElementById('declineForm').style.display = 'block';
    }
    function hideDeclineForm() {
        document.getElementById('declineForm').style.display = 'none';
    }
    </script>

    <style>
    .header-actions {
        display: flex;
        gap: 8px;
    }
    .article-section {
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--border-color);
    }
    .article-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .article-section h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 16px;
        color: var(--text-color);
    }
    .abstract-text {
        line-height: 1.7;
        color: var(--text-color);
    }
    .keywords {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .keyword-tag {
        background: #e3f2fd;
        color: #1565c0;
        padding: 4px 12px;
        border-radius: 16px;
        font-size: 0.85rem;
    }
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .detail-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
    }
    .detail-value {
        color: var(--text-color);
    }
    .editor-notes {
        background: #f8f9fa;
        border-radius: var(--border-radius);
        padding: 16px;
        line-height: 1.6;
    }
    .action-buttons {
        display: flex;
        gap: 12px;
    }
    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 16px;
    }
    .alert {
        padding: 16px 20px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
    }
    .alert-warning {
        background: #fff3e0;
        border: 1px solid #ffcc80;
        color: #e65100;
    }
    .alert-info {
        background: #e3f2fd;
        border: 1px solid #90caf9;
        color: #1565c0;
    }
    .alert-success {
        background: #e8f5e9;
        border: 1px solid #a5d6a7;
        color: #2e7d32;
    }
    .text-danger { color: var(--danger-color); }
    .text-warning { color: var(--warning-color); }
    </style>
</body>
</html>
