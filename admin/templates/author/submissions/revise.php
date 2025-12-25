<?php
/**
 * Submit Revision Template (Author)
 */
$errors = Flash::errors();
$old = Flash::oldInput();
$getValue = function($field) use ($old, $submission) {
    return $old[$field] ?? $submission[$field] ?? '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Submit Revision') ?></title>
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

                <div class="revision-notice">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div>
                        <strong>Revision Required</strong>
                        <p>The editor has requested revisions to your submission. Please review the feedback below and update your manuscript accordingly.</p>
                    </div>
                </div>

                <!-- Reviewer Feedback -->
                <?php if (!empty($reviews)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Reviewer Feedback</h2>
                        </div>
                        <div class="card-body">
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
                                            <?= nl2br(htmlspecialchars($review['comments_to_author'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">No specific comments provided.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Editor's Notes -->
                <?php if (!empty($submission['decision_notes'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Editor's Notes</h2>
                        </div>
                        <div class="card-body">
                            <div class="editor-notes">
                                <?= nl2br(htmlspecialchars($submission['decision_notes'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Revision Form -->
                <div class="card">
                    <div class="card-header">
                        <h2>Submit Revised Manuscript</h2>
                        <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>" class="btn btn-secondary btn-sm">
                            &larr; Back to Submission
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>/revise" method="POST">
                            <?= CSRF::field() ?>

                            <div class="form-section">
                                <h3>Updated Article</h3>

                                <div class="form-group">
                                    <label for="title">Title *</label>
                                    <input type="text" id="title" name="title" class="form-control"
                                           value="<?= htmlspecialchars($getValue('title')) ?>" required>
                                    <?php if (isset($errors['title'])): ?>
                                        <span class="form-error"><?= $errors['title'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="abstract">Abstract *</label>
                                    <textarea id="abstract" name="abstract" class="form-control" rows="8" required><?= htmlspecialchars($getValue('abstract')) ?></textarea>
                                    <?php if (isset($errors['abstract'])): ?>
                                        <span class="form-error"><?= $errors['abstract'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="keywords">Keywords</label>
                                    <input type="text" id="keywords" name="keywords" class="form-control"
                                           value="<?= htmlspecialchars($getValue('keywords')) ?>">
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Response to Reviewers *</h3>
                                <p class="form-text mb-3">
                                    Please explain how you have addressed each of the reviewer's comments.
                                    This helps the editor and reviewers understand the changes you've made.
                                </p>

                                <div class="form-group">
                                    <label for="revision_notes">Revision Notes *</label>
                                    <textarea id="revision_notes" name="revision_notes" class="form-control" rows="10"
                                              placeholder="Describe the changes you've made in response to the reviewer feedback..."
                                              required><?= htmlspecialchars($old['revision_notes'] ?? '') ?></textarea>
                                    <span class="form-text">Minimum 50 characters. Be specific about what changes were made.</span>
                                    <?php if (isset($errors['revision_notes'])): ?>
                                        <span class="form-error"><?= $errors['revision_notes'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Revision Information</h3>
                                <div class="revision-info">
                                    <div class="info-item">
                                        <span class="info-label">Current Revision:</span>
                                        <span class="info-value"><?= $submission['current_revision'] ?? 1 ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Next Revision:</span>
                                        <span class="info-value"><?= ($submission['current_revision'] ?? 1) + 1 ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Originally Submitted:</span>
                                        <span class="info-value">
                                            <?= !empty($submission['submitted_at']) ? date('F j, Y', strtotime($submission['submitted_at'])) : 'N/A' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    Submit Revision
                                </button>
                                <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>" class="btn btn-secondary">
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
    .revision-notice {
        display: flex;
        gap: 16px;
        background: #fff3e0;
        border: 1px solid #ffcc80;
        border-radius: var(--border-radius);
        padding: 20px;
        margin-bottom: 24px;
        color: #e65100;
    }
    .revision-notice svg {
        flex-shrink: 0;
        margin-top: 2px;
    }
    .revision-notice p {
        margin: 4px 0 0 0;
        font-size: 0.9rem;
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
        margin-bottom: 12px;
    }
    .reviewer-label {
        font-weight: 600;
        color: var(--text-color);
    }
    .review-comments {
        line-height: 1.6;
        color: var(--text-color);
    }
    .editor-notes {
        background: #e3f2fd;
        border-radius: var(--border-radius);
        padding: 16px;
        line-height: 1.6;
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
    .revision-info {
        background: #f8f9fa;
        border-radius: var(--border-radius);
        padding: 16px;
    }
    .info-item {
        display: flex;
        gap: 12px;
        margin-bottom: 8px;
    }
    .info-item:last-child {
        margin-bottom: 0;
    }
    .info-label {
        font-weight: 500;
        color: var(--text-muted);
        min-width: 140px;
    }
    .info-value {
        color: var(--text-color);
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
