<?php
/**
 * View Article Template (Editor)
 */
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'View Article') ?></title>
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

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2><?= htmlspecialchars($article['title']) ?></h2>
                            <span class="badge <?= $statusClass ?>" style="margin-top: 8px;">
                                <?= ucwords(str_replace('_', ' ', $article['status'])) ?>
                            </span>
                        </div>
                        <div class="header-actions">
                            <a href="<?= $baseUrl ?>/editor/articles" class="btn btn-secondary btn-sm">
                                &larr; Back to Articles
                            </a>
                            <?php if (in_array($article['status'], ['submitted', 'under_review'])): ?>
                                <a href="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>/assign-reviewers"
                                   class="btn btn-primary btn-sm">Assign Reviewers</a>
                            <?php endif; ?>
                            <?php if (!empty($reviews) && in_array($article['status'], ['submitted', 'under_review'])): ?>
                                <a href="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>/decision"
                                   class="btn btn-success btn-sm">Make Decision</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Article Info -->
                        <div class="article-section">
                            <h3>Article Information</h3>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Author</span>
                                    <span class="detail-value">
                                        <?= htmlspecialchars(trim(($article['author_first_name'] ?? '') . ' ' . ($article['author_last_name'] ?? ''))) ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($article['author_email'] ?? '') ?></small>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value">
                                        <span class="badge <?= $statusClass ?>">
                                            <?= ucwords(str_replace('_', ' ', $article['status'])) ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Submitted</span>
                                    <span class="detail-value">
                                        <?= !empty($article['submitted_at']) ? date('F j, Y', strtotime($article['submitted_at'])) : 'Not submitted' ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Revision</span>
                                    <span class="detail-value"><?= $article['current_revision'] ?? 1 ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Abstract -->
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

                        <!-- Quick Status Change -->
                        <div class="article-section">
                            <h3>Quick Actions</h3>
                            <form action="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>/status" method="POST" class="status-form">
                                <?= CSRF::field() ?>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="status">Change Status</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="">Select new status...</option>
                                            <option value="submitted" <?= $article['status'] === 'submitted' ? 'disabled' : '' ?>>Submitted</option>
                                            <option value="under_review" <?= $article['status'] === 'under_review' ? 'disabled' : '' ?>>Under Review</option>
                                            <option value="revision_required" <?= $article['status'] === 'revision_required' ? 'disabled' : '' ?>>Revision Required</option>
                                            <option value="accepted" <?= $article['status'] === 'accepted' ? 'disabled' : '' ?>>Accepted</option>
                                            <option value="rejected" <?= $article['status'] === 'rejected' ? 'disabled' : '' ?>>Rejected</option>
                                            <option value="published" <?= $article['status'] === 'published' ? 'disabled' : '' ?>>Published</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reviewer Assignments -->
                <div class="card">
                    <div class="card-header">
                        <h2>Reviewer Assignments</h2>
                        <?php if (in_array($article['status'], ['submitted', 'under_review'])): ?>
                            <a href="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>/assign-reviewers"
                               class="btn btn-primary btn-sm">+ Assign Reviewers</a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($assignments)): ?>
                            <p class="text-muted text-center">No reviewers assigned yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Reviewer</th>
                                            <th>Status</th>
                                            <th>Assigned</th>
                                            <th>Deadline</th>
                                            <th>Completed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assignments as $assignment): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars(trim($assignment['first_name'] . ' ' . $assignment['last_name'])) ?></strong>
                                                    <br><small class="text-muted"><?= htmlspecialchars($assignment['email']) ?></small>
                                                </td>
                                                <td>
                                                    <?php
                                                    $assignStatusClass = match($assignment['status']) {
                                                        'pending' => 'badge-warning',
                                                        'accepted' => 'badge-primary',
                                                        'completed' => 'badge-success',
                                                        'declined' => 'badge-secondary',
                                                        'cancelled' => 'badge-danger',
                                                        default => 'badge-secondary',
                                                    };
                                                    ?>
                                                    <span class="badge <?= $assignStatusClass ?>">
                                                        <?= ucfirst($assignment['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($assignment['assigned_at'])) ?></td>
                                                <td>
                                                    <?= !empty($assignment['deadline_at']) ? date('M j, Y', strtotime($assignment['deadline_at'])) : '-' ?>
                                                </td>
                                                <td>
                                                    <?= !empty($assignment['completed_at']) ? date('M j, Y', strtotime($assignment['completed_at'])) : '-' ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Reviews -->
                <?php if (!empty($reviews)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Reviews (<?= count($reviews) ?>)</h2>
                        </div>
                        <div class="card-body">
                            <?php foreach ($reviews as $index => $review): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div>
                                            <span class="reviewer-label">
                                                <?= htmlspecialchars(trim(($review['first_name'] ?? 'Reviewer') . ' ' . ($review['last_name'] ?? ($index + 1)))) ?>
                                            </span>
                                            <span class="review-date">
                                                Submitted <?= date('M j, Y', strtotime($review['submitted_at'])) ?>
                                            </span>
                                        </div>
                                        <?php
                                        $recClass = match($review['recommendation'] ?? '') {
                                            'accept' => 'badge-success',
                                            'minor_revision' => 'badge-info',
                                            'major_revision' => 'badge-warning',
                                            'reject' => 'badge-danger',
                                            default => 'badge-secondary',
                                        };
                                        ?>
                                        <span class="badge <?= $recClass ?>">
                                            <?= ucwords(str_replace('_', ' ', $review['recommendation'] ?? 'N/A')) ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($review['overall_score'])): ?>
                                        <div class="review-scores">
                                            <div class="scores-inline">
                                                <?php if (!empty($review['originality_score'])): ?>
                                                    <span class="score-item">Originality: <?= $review['originality_score'] ?>/10</span>
                                                <?php endif; ?>
                                                <?php if (!empty($review['methodology_score'])): ?>
                                                    <span class="score-item">Methodology: <?= $review['methodology_score'] ?>/10</span>
                                                <?php endif; ?>
                                                <?php if (!empty($review['clarity_score'])): ?>
                                                    <span class="score-item">Clarity: <?= $review['clarity_score'] ?>/10</span>
                                                <?php endif; ?>
                                                <?php if (!empty($review['significance_score'])): ?>
                                                    <span class="score-item">Significance: <?= $review['significance_score'] ?>/10</span>
                                                <?php endif; ?>
                                                <span class="score-item overall">Overall: <?= $review['overall_score'] ?>/10</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($review['comments_to_author'])): ?>
                                        <div class="review-comments">
                                            <h4>Comments to Author</h4>
                                            <p><?= nl2br(htmlspecialchars($review['comments_to_author'])) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($review['comments_to_editor'])): ?>
                                        <div class="review-comments confidential">
                                            <h4>Confidential Comments to Editor</h4>
                                            <p><?= nl2br(htmlspecialchars($review['comments_to_editor'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Decision History -->
                <?php if (!empty($article['decision_notes'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Editorial Decision</h2>
                        </div>
                        <div class="card-body">
                            <div class="decision-box">
                                <strong>Decision:</strong>
                                <span class="badge <?= $statusClass ?>">
                                    <?= ucwords(str_replace('_', ' ', $article['status'])) ?>
                                </span>
                                <div class="decision-notes">
                                    <?= nl2br(htmlspecialchars($article['decision_notes'])) ?>
                                </div>
                                <?php if (!empty($article['decision_at'])): ?>
                                    <div class="decision-date">
                                        Decision made on <?= date('F j, Y', strtotime($article['decision_at'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </main>

            <?php include __DIR__ . '/../../layouts/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>

    <style>
    .header-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .article-section {
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--border-color);
    }
    .article-section:last-child {
        border-bottom: none;
    }
    .article-section h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 16px;
        color: var(--text-color);
    }
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
    .abstract-text {
        line-height: 1.7;
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
    .status-form .form-row {
        display: flex;
        gap: 12px;
        align-items: flex-end;
    }
    .status-form .form-group {
        flex: 1;
        max-width: 300px;
    }
    .review-card {
        background: #f8f9fa;
        border-radius: var(--border-radius);
        padding: 20px;
        margin-bottom: 16px;
    }
    .review-card:last-child {
        margin-bottom: 0;
    }
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }
    .reviewer-label {
        font-weight: 600;
        display: block;
    }
    .review-date {
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .review-scores {
        margin-bottom: 16px;
    }
    .scores-inline {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .score-item {
        background: white;
        padding: 4px 10px;
        border-radius: var(--border-radius-sm);
        font-size: 0.85rem;
    }
    .score-item.overall {
        background: var(--primary-color);
        color: white;
        font-weight: 600;
    }
    .review-comments {
        margin-bottom: 12px;
    }
    .review-comments h4 {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-muted);
    }
    .review-comments.confidential {
        background: #fff3e0;
        border: 1px solid #ffcc80;
        border-radius: var(--border-radius);
        padding: 12px;
    }
    .decision-box {
        background: #f8f9fa;
        border-radius: var(--border-radius);
        padding: 20px;
    }
    .decision-notes {
        margin-top: 12px;
        line-height: 1.6;
    }
    .decision-date {
        margin-top: 12px;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    </style>
</body>
</html>
