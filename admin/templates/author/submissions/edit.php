<?php
/**
 * Edit Submission Template (Author)
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
    <title><?= htmlspecialchars($pageTitle ?? 'Edit Submission') ?></title>
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
                        <h2>Edit Draft Submission</h2>
                        <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>" class="btn btn-secondary btn-sm">
                            &larr; Back to Submission
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="draft-notice">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            <span>This is a draft. You can edit it freely until you submit it for review.</span>
                        </div>

                        <form action="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>" method="POST">
                            <?= CSRF::field() ?>

                            <div class="form-section">
                                <h3>Article Information</h3>

                                <div class="form-group">
                                    <label for="title">Title *</label>
                                    <input type="text" id="title" name="title" class="form-control"
                                           value="<?= htmlspecialchars($getValue('title')) ?>"
                                           placeholder="Enter the full title of your article" required>
                                    <span class="form-text">Minimum 10 characters</span>
                                    <?php if (isset($errors['title'])): ?>
                                        <span class="form-error"><?= $errors['title'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="abstract">Abstract *</label>
                                    <textarea id="abstract" name="abstract" class="form-control" rows="8"
                                              placeholder="Provide a comprehensive abstract of your article" required><?= htmlspecialchars($getValue('abstract')) ?></textarea>
                                    <span class="form-text">Minimum 100 characters. Summarize the purpose, methodology, results, and conclusions.</span>
                                    <?php if (isset($errors['abstract'])): ?>
                                        <span class="form-error"><?= $errors['abstract'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="keywords">Keywords</label>
                                    <input type="text" id="keywords" name="keywords" class="form-control"
                                           value="<?= htmlspecialchars($getValue('keywords')) ?>"
                                           placeholder="e.g., machine learning, data analysis, neural networks">
                                    <span class="form-text">Separate keywords with commas. These help readers find your article.</span>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Publication Details</h3>

                                <div class="form-group">
                                    <label for="volume_id">Target Volume/Issue</label>
                                    <select id="volume_id" name="volume_id" class="form-control">
                                        <option value="">No specific volume (Editor's choice)</option>
                                        <?php foreach ($volumes as $volume): ?>
                                            <option value="<?= $volume['id'] ?>"
                                                    <?= $getValue('volume_id') == $volume['id'] ? 'selected' : '' ?>>
                                                Vol. <?= $volume['volume_number'] ?>, Issue <?= $volume['issue_number'] ?>
                                                (<?= $volume['year'] ?>)
                                                <?= $volume['title'] ? ' - ' . htmlspecialchars($volume['title']) : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="form-text">Select a specific issue or leave for editor to assign.</span>
                                </div>
                            </div>

                            <div class="form-section">
                                <h3>Draft Information</h3>
                                <div class="draft-info">
                                    <div class="info-item">
                                        <span class="info-label">Created:</span>
                                        <span class="info-value"><?= date('F j, Y \a\t g:i A', strtotime($submission['created_at'])) ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Last Updated:</span>
                                        <span class="info-value"><?= date('F j, Y \a\t g:i A', strtotime($submission['updated_at'])) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="submit_now" class="btn btn-primary">
                                    Submit for Review
                                </button>
                                <button type="submit" name="save_draft" class="btn btn-secondary">
                                    Save Draft
                                </button>
                                <a href="<?= $baseUrl ?>/author/submissions/<?= $submission['id'] ?>" class="btn btn-link">Cancel</a>
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
    .draft-notice {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #e3f2fd;
        border: 1px solid #90caf9;
        border-radius: var(--border-radius);
        padding: 12px 16px;
        margin-bottom: 24px;
        color: #1565c0;
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
    .draft-info {
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
        min-width: 100px;
    }
    .info-value {
        color: var(--text-color);
    }
    textarea.form-control {
        resize: vertical;
        min-height: 150px;
    }
    .btn-link {
        background: none;
        border: none;
        color: var(--text-muted);
        text-decoration: none;
        padding: 8px 16px;
    }
    .btn-link:hover {
        color: var(--text-color);
        text-decoration: underline;
    }
    </style>
</body>
</html>
