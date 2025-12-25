<?php
/**
 * View Submission Template (Author)
 */
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'View Submission') ?></title>
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
                <?php if ($submission['status'] === 'revision_required'): ?>
                    <div class="alert alert-warning">
                        <strong>Revision Required:</strong> The editor has requested revisions to your submission.
                        Please review the feedback below and submit your revised manuscript.
                        <div style="margin-top: 12px;">
                            <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>/revise" class="btn btn-warning btn-sm">
                                Submit Revision
                            </a>
                        </div>
                    </div>
                <?php elseif ($submission['status'] === 'accepted'): ?>
                    <div class="alert alert-success">
                        <strong>Congratulations!</strong> Your article has been accepted for publication.
                    </div>
                <?php elseif ($submission['status'] === 'rejected'): ?>
                    <div class="alert alert-danger">
                        <strong>Unfortunately</strong> your article was not accepted for publication at this time.
                        Please review the editor's feedback below.
                    </div>
                <?php elseif ($submission['status'] === 'published'): ?>
                    <div class="alert alert-success">
                        <strong>Published!</strong> Your article has been published and is now available to readers.
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2><?= htmlspecialchars($submission['title']) ?></h2>
                            <span class="badge <?= $statusClass ?>" style="margin-top: 8px;">
                                <?= ucwords(str_replace('_', ' ', $submission['status'])) ?>
                            </span>
                            <?php if (($submission['current_revision'] ?? 1) > 1): ?>
                                <span class="badge badge-secondary">Revision <?= $submission['current_revision'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="header-actions">
                            <a href="<?= $baseUrl ?>/author/submissions" class="btn btn-secondary btn-sm">
                                &larr; Back to Submissions
                            </a>
                            <?php if ($submission['status'] === 'draft'): ?>
                                <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>/edit" class="btn btn-primary btn-sm">
                                    Edit Draft
                                </a>
                            <?php elseif ($submission['status'] === 'revision_required'): ?>
                                <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>/revise" class="btn btn-warning btn-sm">
                                    Submit Revision
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Article Details -->
                        <div class="article-section">
                            <h3>Abstract</h3>
                            <div class="abstract-text">
                                <?= nl2br(htmlspecialchars($submission['abstract'])) ?>
                            </div>
                        </div>

                        <?php if (!empty($submission['keywords'])): ?>
                            <div class="article-section">
                                <h3>Keywords</h3>
                                <div class="keywords">
                                    <?php foreach (explode(',', $submission['keywords']) as $keyword): ?>
                                        <span class="keyword-tag"><?= htmlspecialchars(trim($keyword)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="article-section">
                            <h3>Submission Details</h3>
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value">
                                        <span class="badge <?= $statusClass ?>">
                                            <?= ucwords(str_replace('_', ' ', $submission['status'])) ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Created</span>
                                    <span class="detail-value"><?= date('F j, Y \a\t g:i A', strtotime($submission['created_at'])) ?></span>
                                </div>
                                <?php if (!empty($submission['submitted_at'])): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Submitted</span>
                                        <span class="detail-value"><?= date('F j, Y \a\t g:i A', strtotime($submission['submitted_at'])) ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="detail-item">
                                    <span class="detail-label">Last Updated</span>
                                    <span class="detail-value"><?= date('F j, Y \a\t g:i A', strtotime($submission['updated_at'])) ?></span>
                                </div>
                                <?php if (!empty($submission['volume_id'])): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Target Volume</span>
                                        <span class="detail-value">Volume <?= $submission['volume_id'] ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (($submission['current_revision'] ?? 1) > 1): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Revision</span>
                                        <span class="detail-value"><?= $submission['current_revision'] ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Decision Notes (if any) -->
                        <?php if (!empty($submission['decision_notes'])): ?>
                            <div class="article-section">
                                <h3>Editor's Decision</h3>
                                <div class="decision-box <?= $submission['status'] === 'accepted' ? 'decision-success' : ($submission['status'] === 'rejected' ? 'decision-danger' : 'decision-warning') ?>">
                                    <?= nl2br(htmlspecialchars($submission['decision_notes'])) ?>
                                    <?php if (!empty($submission['decision_at'])): ?>
                                        <div class="decision-date">
                                            Decision made on <?= date('F j, Y', strtotime($submission['decision_at'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Reviews (visible to author after review) -->
                        <?php if (!empty($reviews)): ?>
                            <div class="article-section">
                                <h3>Reviewer Feedback</h3>
                                <?php foreach ($reviews as $index => $review): ?>
                                    <div class="review-card">
                                        <div class="review-header">
                                            <span class="reviewer-label">Reviewer <?= $index + 1 ?></span>
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
                                                <?= ucwords(str_replace('_', ' ', $review['recommendation'] ?? 'Pending')) ?>
                                            </span>
                                        </div>

                                        <?php if (!empty($review['comments_to_author'])): ?>
                                            <div class="review-comments">
                                                <h4>Comments</h4>
                                                <?= nl2br(htmlspecialchars($review['comments_to_author'])) ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($review['overall_score'])): ?>
                                            <div class="review-scores">
                                                <h4>Scores</h4>
                                                <div class="scores-grid">
                                                    <?php if (!empty($review['originality_score'])): ?>
                                                        <div class="score-item">
                                                            <span>Originality</span>
                                                            <span class="score"><?= $review['originality_score'] ?>/10</span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($review['methodology_score'])): ?>
                                                        <div class="score-item">
                                                            <span>Methodology</span>
                                                            <span class="score"><?= $review['methodology_score'] ?>/10</span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($review['clarity_score'])): ?>
                                                        <div class="score-item">
                                                            <span>Clarity</span>
                                                            <span class="score"><?= $review['clarity_score'] ?>/10</span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($review['significance_score'])): ?>
                                                        <div class="score-item">
                                                            <span>Significance</span>
                                                            <span class="score"><?= $review['significance_score'] ?>/10</span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="score-item overall">
                                                        <span>Overall</span>
                                                        <span class="score"><?= $review['overall_score'] ?>/10</span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="review-date">
                                            Submitted <?= date('F j, Y', strtotime($review['submitted_at'])) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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
    .decision-box {
        padding: 16px;
        border-radius: var(--border-radius);
        line-height: 1.6;
    }
    .decision-success {
        background: #e8f5e9;
        border: 1px solid #a5d6a7;
    }
    .decision-warning {
        background: #fff3e0;
        border: 1px solid #ffcc80;
    }
    .decision-danger {
        background: #ffebee;
        border: 1px solid #ef9a9a;
    }
    .decision-date {
        margin-top: 12px;
        font-size: 0.85rem;
        color: var(--text-muted);
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
        align-items: center;
        margin-bottom: 16px;
    }
    .reviewer-label {
        font-weight: 600;
        color: var(--text-color);
    }
    .review-comments {
        margin-bottom: 16px;
    }
    .review-comments h4, .review-scores h4 {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--text-muted);
    }
    .scores-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 12px;
    }
    .score-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        background: white;
        border-radius: var(--border-radius-sm);
        font-size: 0.9rem;
    }
    .score-item.overall {
        background: var(--primary-color);
        color: white;
    }
    .score {
        font-weight: 600;
    }
    .review-date {
        margin-top: 16px;
        font-size: 0.85rem;
        color: var(--text-muted);
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
    .alert-success {
        background: #e8f5e9;
        border: 1px solid #a5d6a7;
        color: #2e7d32;
    }
    .alert-danger {
        background: #ffebee;
        border: 1px solid #ef9a9a;
        color: #c62828;
    }
    </style>
</body>
</html>
