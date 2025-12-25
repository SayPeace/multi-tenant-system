<?php
/**
 * Submit Review Template (Reviewer)
 */
$errors = Flash::errors();
$old = Flash::oldInput();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Submit Review') ?></title>
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

                <!-- Article Preview -->
                <div class="card">
                    <div class="card-header">
                        <h2>Article Under Review</h2>
                    </div>
                    <div class="card-body">
                        <h3 class="article-title"><?= htmlspecialchars($article['title']) ?></h3>
                        <div class="article-abstract">
                            <strong>Abstract:</strong>
                            <p><?= nl2br(htmlspecialchars(mb_strimwidth($article['abstract'], 0, 500, '...'))) ?></p>
                        </div>
                        <?php if (!empty($article['keywords'])): ?>
                            <div class="keywords">
                                <strong>Keywords:</strong>
                                <?php foreach (explode(',', $article['keywords']) as $keyword): ?>
                                    <span class="keyword-tag"><?= htmlspecialchars(trim($keyword)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Review Form -->
                <div class="card">
                    <div class="card-header">
                        <h2>Submit Your Review</h2>
                        <a href="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>" class="btn btn-secondary btn-sm">
                            &larr; Back to Assignment
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>/review" method="POST">
                            <?= CSRF::field() ?>

                            <div class="form-section">
                                <h3>Recommendation *</h3>
                                <div class="recommendation-options">
                                    <label class="recommendation-option">
                                        <input type="radio" name="recommendation" value="accept"
                                               <?= ($old['recommendation'] ?? '') === 'accept' ? 'checked' : '' ?> required>
                                        <div class="option-content accept">
                                            <span class="option-title">Accept</span>
                                            <span class="option-desc">The article is suitable for publication as is</span>
                                        </div>
                                    </label>

                                    <label class="recommendation-option">
                                        <input type="radio" name="recommendation" value="minor_revision"
                                               <?= ($old['recommendation'] ?? '') === 'minor_revision' ? 'checked' : '' ?>>
                                        <div class="option-content minor">
                                            <span class="option-title">Minor Revision</span>
                                            <span class="option-desc">Small improvements needed before publication</span>
                                        </div>
                                    </label>

                                    <label class="recommendation-option">
                                        <input type="radio" name="recommendation" value="major_revision"
                                               <?= ($old['recommendation'] ?? '') === 'major_revision' ? 'checked' : '' ?>>
                                        <div class="option-content major">
                                            <span class="option-title">Major Revision</span>
                                            <span class="option-desc">Significant changes required, needs re-review</span>
                                        </div>
                                    </label>

                                    <label class="recommendation-option">
                                        <input type="radio" name="recommendation" value="reject"
                                               <?= ($old['recommendation'] ?? '') === 'reject' ? 'checked' : '' ?>>
                                        <div class="option-content reject">
                                            <span class="option-title">Reject</span>
                                            <span class="option-desc">Not suitable for publication</span>
                                        </div>
                                    </label>
                                </div>
                                <?php if (isset($errors['recommendation'])): ?>
                                    <span class="form-error"><?= $errors['recommendation'] ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-section">
                                <h3>Evaluation Scores</h3>
                                <p class="form-text mb-3">Rate each aspect from 1 (Poor) to 10 (Excellent)</p>

                                <div class="scores-grid">
                                    <div class="form-group">
                                        <label for="originality_score">Originality</label>
                                        <input type="number" id="originality_score" name="originality_score" class="form-control"
                                               min="1" max="10" value="<?= $old['originality_score'] ?? '' ?>"
                                               placeholder="1-10">
                                        <span class="form-text">Novelty and contribution to the field</span>
                                    </div>

                                    <div class="form-group">
                                        <label for="methodology_score">Methodology</label>
                                        <input type="number" id="methodology_score" name="methodology_score" class="form-control"
                                               min="1" max="10" value="<?= $old['methodology_score'] ?? '' ?>"
                                               placeholder="1-10">
                                        <span class="form-text">Research design and methods</span>
                                    </div>

                                    <div class="form-group">
                                        <label for="clarity_score">Clarity</label>
                                        <input type="number" id="clarity_score" name="clarity_score" class="form-control"
                                               min="1" max="10" value="<?= $old['clarity_score'] ?? '' ?>"
                                               placeholder="1-10">
                                        <span class="form-text">Writing quality and presentation</span>
                                    </div>

                                    <div class="form-group">
                                        <label for="significance_score">Significance</label>
                                        <input type="number" id="significance_score" name="significance_score" class="form-control"
                                               min="1" max="10" value="<?= $old['significance_score'] ?? '' ?>"
                                               placeholder="1-10">
                                        <span class="form-text">Impact and importance of findings</span>
                                    </div>

                                    <div class="form-group overall-score">
                                        <label for="overall_score">Overall Score *</label>
                                        <input type="number" id="overall_score" name="overall_score" class="form-control"
                                               min="1" max="10" value="<?= $old['overall_score'] ?? '' ?>"
                                               placeholder="1-10" required>
                                        <span class="form-text">Your overall assessment of the article</span>
                                        <?php if (isset($errors['overall_score'])): ?>
                                            <span class="form-error"><?= $errors['overall_score'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Comments to Author *</h3>
                                <p class="form-text mb-3">These comments will be shared with the author to help improve their work.</p>

                                <div class="form-group">
                                    <textarea id="comments_to_author" name="comments_to_author" class="form-control" rows="10"
                                              placeholder="Provide constructive feedback including:
- Summary of the article's main contributions
- Strengths of the work
- Areas that need improvement
- Specific suggestions for revision
- Questions or concerns" required><?= htmlspecialchars($old['comments_to_author'] ?? '') ?></textarea>
                                    <span class="form-text">Minimum 50 characters. Be constructive and specific.</span>
                                    <?php if (isset($errors['comments_to_author'])): ?>
                                        <span class="form-error"><?= $errors['comments_to_author'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Confidential Comments to Editor</h3>
                                <p class="form-text mb-3">These comments are private and will only be seen by the editor.</p>

                                <div class="form-group">
                                    <textarea id="comments_to_editor" name="comments_to_editor" class="form-control" rows="5"
                                              placeholder="Share any confidential concerns or observations with the editor..."><?= htmlspecialchars($old['comments_to_editor'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <div class="form-section">
                                <div class="review-notice">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    <div>
                                        <strong>Before submitting:</strong>
                                        <ul>
                                            <li>Ensure your feedback is constructive and professional</li>
                                            <li>Be specific in your comments to help the author improve</li>
                                            <li>Your recommendation should align with your scores and comments</li>
                                            <li>Once submitted, your review cannot be edited</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">
                                    Submit Review
                                </button>
                                <a href="<?= $baseUrl ?>/reviewer/assignments/<?= $assignment['id'] ?>" class="btn btn-secondary">
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

    <style>
    .article-title {
        font-size: 1.25rem;
        margin-bottom: 16px;
    }
    .article-abstract {
        margin-bottom: 16px;
        line-height: 1.6;
    }
    .keywords {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .keyword-tag {
        background: #e3f2fd;
        color: #1565c0;
        padding: 4px 12px;
        border-radius: 16px;
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
    .recommendation-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
    }
    .recommendation-option {
        display: block;
        cursor: pointer;
    }
    .recommendation-option input {
        display: none;
    }
    .option-content {
        padding: 16px;
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius);
        transition: all 0.2s;
    }
    .recommendation-option input:checked + .option-content {
        border-color: var(--primary-color);
        background: #f0f7ff;
    }
    .option-content.accept { border-left: 4px solid var(--success-color); }
    .option-content.minor { border-left: 4px solid #17a2b8; }
    .option-content.major { border-left: 4px solid var(--warning-color); }
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
    .scores-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
    }
    .overall-score {
        grid-column: 1 / -1;
        max-width: 200px;
    }
    .overall-score input {
        font-size: 1.25rem;
        font-weight: 600;
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
    .review-notice {
        display: flex;
        gap: 16px;
        background: #e3f2fd;
        border: 1px solid #90caf9;
        border-radius: var(--border-radius);
        padding: 16px;
        color: #1565c0;
    }
    .review-notice svg {
        flex-shrink: 0;
        margin-top: 2px;
    }
    .review-notice ul {
        margin: 8px 0 0 0;
        padding-left: 20px;
        font-size: 0.9rem;
    }
    .review-notice li {
        margin-bottom: 4px;
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
