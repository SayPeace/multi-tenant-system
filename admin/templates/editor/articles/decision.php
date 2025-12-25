<?php
/**
 * Editorial Decision Template (Editor)
 */
$errors = Flash::errors();
$old = Flash::oldInput();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Editorial Decision') ?></title>
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

                <!-- Article Summary -->
                <div class="card">
                    <div class="card-header">
                        <h2>Article: <?= htmlspecialchars(mb_strimwidth($article['title'], 0, 80, '...')) ?></h2>
                        <a href="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>" class="btn btn-secondary btn-sm">
                            &larr; Back to Article
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="article-meta">
                            <div class="meta-item">
                                <span class="meta-label">Author:</span>
                                <span class="meta-value">
                                    <?= htmlspecialchars(trim(($article['author_first_name'] ?? '') . ' ' . ($article['author_last_name'] ?? ''))) ?>
                                </span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Current Status:</span>
                                <span class="meta-value">
                                    <?php
                                    $statusClass = match($article['status']) {
                                        'submitted' => 'badge-info',
                                        'under_review' => 'badge-primary',
                                        default => 'badge-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= ucwords(str_replace('_', ' ', $article['status'])) ?>
                                    </span>
                                </span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Reviews:</span>
                                <span class="meta-value"><?= count($reviews) ?> completed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Review Summary -->
                <?php if (!empty($reviews)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Review Summary</h2>
                        </div>
                        <div class="card-body">
                            <?php
                            // Calculate average scores
                            $avgScores = ['originality' => 0, 'methodology' => 0, 'clarity' => 0, 'significance' => 0, 'overall' => 0];
                            $scoreCounts = ['originality' => 0, 'methodology' => 0, 'clarity' => 0, 'significance' => 0, 'overall' => 0];
                            $recommendations = [];

                            foreach ($reviews as $review) {
                                if (!empty($review['originality_score'])) {
                                    $avgScores['originality'] += $review['originality_score'];
                                    $scoreCounts['originality']++;
                                }
                                if (!empty($review['methodology_score'])) {
                                    $avgScores['methodology'] += $review['methodology_score'];
                                    $scoreCounts['methodology']++;
                                }
                                if (!empty($review['clarity_score'])) {
                                    $avgScores['clarity'] += $review['clarity_score'];
                                    $scoreCounts['clarity']++;
                                }
                                if (!empty($review['significance_score'])) {
                                    $avgScores['significance'] += $review['significance_score'];
                                    $scoreCounts['significance']++;
                                }
                                if (!empty($review['overall_score'])) {
                                    $avgScores['overall'] += $review['overall_score'];
                                    $scoreCounts['overall']++;
                                }
                                if (!empty($review['recommendation'])) {
                                    $recommendations[] = $review['recommendation'];
                                }
                            }

                            foreach ($avgScores as $key => $value) {
                                if ($scoreCounts[$key] > 0) {
                                    $avgScores[$key] = round($value / $scoreCounts[$key], 1);
                                }
                            }
                            ?>

                            <div class="summary-grid">
                                <div class="summary-section">
                                    <h4>Recommendations</h4>
                                    <div class="recommendations-list">
                                        <?php foreach ($reviews as $index => $review): ?>
                                            <?php
                                            $recClass = match($review['recommendation'] ?? '') {
                                                'accept' => 'badge-success',
                                                'minor_revision' => 'badge-info',
                                                'major_revision' => 'badge-warning',
                                                'reject' => 'badge-danger',
                                                default => 'badge-secondary',
                                            };
                                            ?>
                                            <div class="rec-item">
                                                <span>Reviewer <?= $index + 1 ?>:</span>
                                                <span class="badge <?= $recClass ?>">
                                                    <?= ucwords(str_replace('_', ' ', $review['recommendation'] ?? 'N/A')) ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="summary-section">
                                    <h4>Average Scores</h4>
                                    <div class="avg-scores">
                                        <?php if ($scoreCounts['originality'] > 0): ?>
                                            <div class="avg-score-item">
                                                <span>Originality</span>
                                                <span class="score"><?= $avgScores['originality'] ?>/10</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($scoreCounts['methodology'] > 0): ?>
                                            <div class="avg-score-item">
                                                <span>Methodology</span>
                                                <span class="score"><?= $avgScores['methodology'] ?>/10</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($scoreCounts['clarity'] > 0): ?>
                                            <div class="avg-score-item">
                                                <span>Clarity</span>
                                                <span class="score"><?= $avgScores['clarity'] ?>/10</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($scoreCounts['significance'] > 0): ?>
                                            <div class="avg-score-item">
                                                <span>Significance</span>
                                                <span class="score"><?= $avgScores['significance'] ?>/10</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($scoreCounts['overall'] > 0): ?>
                                            <div class="avg-score-item overall">
                                                <span>Overall</span>
                                                <span class="score"><?= $avgScores['overall'] ?>/10</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Individual Reviews -->
                            <div class="reviews-detail">
                                <h4>Detailed Feedback</h4>
                                <?php foreach ($reviews as $index => $review): ?>
                                    <div class="review-summary-card">
                                        <div class="review-summary-header">
                                            <strong>Reviewer <?= $index + 1 ?></strong>
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
                                        <?php if (!empty($review['comments_to_author'])): ?>
                                            <div class="review-summary-comments">
                                                <?= nl2br(htmlspecialchars(mb_strimwidth($review['comments_to_author'], 0, 300, '...'))) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($review['comments_to_editor'])): ?>
                                            <div class="review-summary-confidential">
                                                <strong>Confidential:</strong>
                                                <?= nl2br(htmlspecialchars(mb_strimwidth($review['comments_to_editor'], 0, 200, '...'))) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Decision Form -->
                <div class="card">
                    <div class="card-header">
                        <h2>Make Editorial Decision</h2>
                    </div>
                    <div class="card-body">
                        <form action="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>/decision" method="POST">
                            <?= CSRF::field() ?>

                            <div class="form-section">
                                <h3>Decision *</h3>
                                <div class="decision-options">
                                    <label class="decision-option">
                                        <input type="radio" name="decision" value="accepted"
                                               <?= ($old['decision'] ?? '') === 'accepted' ? 'checked' : '' ?> required>
                                        <div class="option-content accept">
                                            <span class="option-title">Accept</span>
                                            <span class="option-desc">The article is ready for publication</span>
                                        </div>
                                    </label>

                                    <label class="decision-option">
                                        <input type="radio" name="decision" value="revision_required"
                                               <?= ($old['decision'] ?? '') === 'revision_required' ? 'checked' : '' ?>>
                                        <div class="option-content revision">
                                            <span class="option-title">Request Revision</span>
                                            <span class="option-desc">Author must address reviewer concerns</span>
                                        </div>
                                    </label>

                                    <label class="decision-option">
                                        <input type="radio" name="decision" value="rejected"
                                               <?= ($old['decision'] ?? '') === 'rejected' ? 'checked' : '' ?>>
                                        <div class="option-content reject">
                                            <span class="option-title">Reject</span>
                                            <span class="option-desc">The article is not suitable for publication</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Decision Notes *</h3>
                                <p class="form-text mb-3">
                                    These notes will be sent to the author explaining your decision.
                                    Summarize the key points from the reviews and provide clear guidance.
                                </p>

                                <div class="form-group">
                                    <textarea id="decision_notes" name="decision_notes" class="form-control" rows="8"
                                              placeholder="Dear Author,

Based on the reviewer feedback, we have decided to...

Key points to address:
1. ...
2. ...

Please submit your revised manuscript within..." required><?= htmlspecialchars($old['decision_notes'] ?? '') ?></textarea>
                                    <?php if (isset($errors['decision_notes'])): ?>
                                        <span class="form-error"><?= $errors['decision_notes'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-section" id="publishSection" style="display: none;">
                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="publish_now" value="1">
                                        Publish immediately after acceptance
                                    </label>
                                    <span class="form-text">If checked, the article will be published right away.</span>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    Submit Decision
                                </button>
                                <a href="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

            </main>

            <?php include __DIR__ . '/../../layouts/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>
    <script>
    // Show/hide publish option based on decision
    document.querySelectorAll('input[name="decision"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.getElementById('publishSection').style.display =
                this.value === 'accepted' ? 'block' : 'none';
        });
    });
    </script>

    <style>
    .article-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
    }
    .meta-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .meta-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 24px;
        margin-bottom: 24px;
    }
    .summary-section h4 {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 12px;
        color: var(--text-muted);
    }
    .recommendations-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .rec-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: var(--border-radius-sm);
    }
    .avg-scores {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .avg-score-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: var(--border-radius-sm);
    }
    .avg-score-item.overall {
        background: var(--primary-color);
        color: white;
    }
    .avg-score-item .score {
        font-weight: 600;
    }
    .reviews-detail h4 {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 12px;
        color: var(--text-muted);
    }
    .review-summary-card {
        background: #f8f9fa;
        border-radius: var(--border-radius);
        padding: 16px;
        margin-bottom: 12px;
    }
    .review-summary-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    .review-summary-comments {
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 8px;
    }
    .review-summary-confidential {
        background: #fff3e0;
        border: 1px solid #ffcc80;
        border-radius: var(--border-radius-sm);
        padding: 8px 12px;
        font-size: 0.85rem;
    }
    .form-section {
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--border-color);
    }
    .form-section:last-of-type {
        border-bottom: none;
    }
    .form-section h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 16px;
        color: var(--text-color);
    }
    .decision-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
    }
    .decision-option {
        display: block;
        cursor: pointer;
    }
    .decision-option input {
        display: none;
    }
    .option-content {
        padding: 16px;
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius);
        transition: all 0.2s;
    }
    .decision-option input:checked + .option-content {
        border-color: var(--primary-color);
        background: #f0f7ff;
    }
    .option-content.accept { border-left: 4px solid var(--success-color); }
    .option-content.revision { border-left: 4px solid var(--warning-color); }
    .option-content.reject { border-left: 4px solid var(--danger-color); }
    .option-title {
        display: block;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .option-desc {
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .form-error {
        color: var(--danger-color);
        font-size: 0.85rem;
        margin-top: 4px;
        display: block;
    }
    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid var(--border-color);
    }
    textarea.form-control {
        resize: vertical;
        min-height: 150px;
    }
    .mb-3 {
        margin-bottom: 12px;
    }
    </style>
</body>
</html>
