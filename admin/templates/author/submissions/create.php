<?php
/**
 * Create Submission Template (Author)
 */
$errors = Flash::errors();
$old = Flash::oldInput();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'New Submission') ?></title>
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
                        <h2>New Submission</h2>
                        <a href="<?= $baseUrl ?>/author/submissions" class="btn btn-secondary btn-sm">
                            &larr; Back to Submissions
                        </a>
                    </div>
                    <div class="card-body">
                        <form action="<?= $baseUrl ?>/author/submissions" method="POST">
                            <?= CSRF::field() ?>

                            <div class="form-section">
                                <h3>Article Information</h3>

                                <div class="form-group">
                                    <label for="title">Title *</label>
                                    <input type="text" id="title" name="title" class="form-control"
                                           value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                                           placeholder="Enter the full title of your article" required>
                                    <span class="form-text">Minimum 10 characters</span>
                                    <?php if (isset($errors['title'])): ?>
                                        <span class="form-error"><?= $errors['title'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="abstract">Abstract *</label>
                                    <textarea id="abstract" name="abstract" class="form-control" rows="8"
                                              placeholder="Provide a comprehensive abstract of your article" required><?= htmlspecialchars($old['abstract'] ?? '') ?></textarea>
                                    <span class="form-text">Minimum 100 characters. Summarize the purpose, methodology, results, and conclusions.</span>
                                    <?php if (isset($errors['abstract'])): ?>
                                        <span class="form-error"><?= $errors['abstract'] ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="keywords">Keywords</label>
                                    <input type="text" id="keywords" name="keywords" class="form-control"
                                           value="<?= htmlspecialchars($old['keywords'] ?? '') ?>"
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
                                                    <?= ($old['volume_id'] ?? '') == $volume['id'] ? 'selected' : '' ?>>
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
                                <h3>Submission Guidelines</h3>
                                <div class="guidelines-box">
                                    <p><strong>Before submitting, please ensure:</strong></p>
                                    <ul>
                                        <li>Your manuscript follows the journal's formatting guidelines</li>
                                        <li>All co-authors have approved the submission</li>
                                        <li>The work is original and has not been published elsewhere</li>
                                        <li>All references are properly cited</li>
                                        <li>Any required ethical approvals have been obtained</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="submit_article" class="btn btn-primary">
                                    Submit for Review
                                </button>
                                <button type="submit" name="save_draft" class="btn btn-secondary">
                                    Save as Draft
                                </button>
                                <a href="<?= $baseUrl ?>/author/submissions" class="btn btn-link">Cancel</a>
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
    .guidelines-box {
        background: #f8f9fa;
        border-radius: var(--border-radius);
        padding: 20px;
        font-size: 0.9rem;
    }
    .guidelines-box ul {
        margin: 12px 0 0 0;
        padding-left: 20px;
    }
    .guidelines-box li {
        margin-bottom: 8px;
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
