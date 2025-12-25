<?php
/**
 * Assign Reviewers Template (Editor)
 */
$errors = Flash::errors();
$old = Flash::oldInput();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Assign Reviewers') ?></title>
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

                <!-- Article Info -->
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
                                <span class="meta-label">Status:</span>
                                <span class="meta-value">
                                    <?php
                                    $statusClass = match($article['status']) {
                                        'submitted' => 'badge-info',
                                        'under_review' => 'badge-primary',
                                        'revision_required' => 'badge-warning',
                                        default => 'badge-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= ucwords(str_replace('_', ' ', $article['status'])) ?>
                                    </span>
                                </span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Submitted:</span>
                                <span class="meta-value">
                                    <?= !empty($article['submitted_at']) ? date('M j, Y', strtotime($article['submitted_at'])) : 'N/A' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Assignments -->
                <?php if (!empty($assignments)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Current Reviewer Assignments</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Reviewer</th>
                                            <th>Status</th>
                                            <th>Assigned</th>
                                            <th>Deadline</th>
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
                                                    $statusClass = match($assignment['status']) {
                                                        'pending' => 'badge-warning',
                                                        'accepted' => 'badge-primary',
                                                        'completed' => 'badge-success',
                                                        'declined' => 'badge-secondary',
                                                        'cancelled' => 'badge-danger',
                                                        default => 'badge-secondary',
                                                    };
                                                    ?>
                                                    <span class="badge <?= $statusClass ?>">
                                                        <?= ucfirst($assignment['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($assignment['assigned_at'])) ?></td>
                                                <td>
                                                    <?php if (!empty($assignment['deadline_at'])): ?>
                                                        <?= date('M j, Y', strtotime($assignment['deadline_at'])) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not set</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Assign New Reviewers -->
                <div class="card">
                    <div class="card-header">
                        <h2>Assign New Reviewers</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($reviewers)): ?>
                            <div class="empty-state">
                                <p>No reviewers available. Please add users with the reviewer role first.</p>
                                <a href="<?= $baseUrl ?>/editor/users/create" class="btn btn-primary">Add User</a>
                            </div>
                        <?php else: ?>
                            <form action="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>/assign-reviewers" method="POST">
                                <?= CSRF::field() ?>

                                <div class="form-section">
                                    <h3>Select Reviewers</h3>
                                    <p class="form-text mb-3">Choose one or more reviewers to evaluate this article.</p>

                                    <div class="reviewers-grid">
                                        <?php
                                        // Get already assigned reviewer IDs
                                        $assignedIds = array_column(
                                            array_filter($assignments, fn($a) => !in_array($a['status'], ['declined', 'cancelled'])),
                                            'reviewer_id'
                                        );
                                        ?>
                                        <?php foreach ($reviewers as $reviewer): ?>
                                            <?php $isAssigned = in_array($reviewer['id'], $assignedIds); ?>
                                            <label class="reviewer-option <?= $isAssigned ? 'disabled' : '' ?>">
                                                <input type="checkbox" name="reviewers[]" value="<?= $reviewer['id'] ?>"
                                                       <?= $isAssigned ? 'disabled' : '' ?>>
                                                <div class="reviewer-info">
                                                    <span class="reviewer-name">
                                                        <?= htmlspecialchars(trim($reviewer['first_name'] . ' ' . $reviewer['last_name'])) ?>
                                                        <?php if ($isAssigned): ?>
                                                            <span class="badge badge-secondary">Already assigned</span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="reviewer-email"><?= htmlspecialchars($reviewer['email']) ?></span>
                                                    <?php if (!empty($reviewer['title']) || !empty($reviewer['affiliation'])): ?>
                                                        <span class="reviewer-affiliation">
                                                            <?= htmlspecialchars(trim(($reviewer['title'] ?? '') . ' - ' . ($reviewer['affiliation'] ?? ''), ' -')) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h3>Assignment Settings</h3>

                                    <div class="form-row">
                                        <div class="form-group half">
                                            <label for="deadline_days">Review Deadline</label>
                                            <select id="deadline_days" name="deadline_days" class="form-control">
                                                <option value="7">7 days</option>
                                                <option value="14" selected>14 days (Recommended)</option>
                                                <option value="21">21 days</option>
                                                <option value="30">30 days</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="notes">Notes for Reviewers</label>
                                        <textarea id="notes" name="notes" class="form-control" rows="3"
                                                  placeholder="Add any special instructions or notes for the reviewers..."></textarea>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        Assign Selected Reviewers
                                    </button>
                                    <a href="<?= $baseUrl ?>/editor/articles/<?= $article['id'] ?>" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

            </main>

            <?php include __DIR__ . '/../../layouts/partials/footer.php'; ?>
        </div>
    </div>

    <script src="<?= $baseUrl ?>/assets/js/admin.js"></script>

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
    .meta-value {
        color: var(--text-color);
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
    .reviewers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 12px;
    }
    .reviewer-option {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        background: #f8f9fa;
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius);
        cursor: pointer;
        transition: all 0.2s;
    }
    .reviewer-option:hover:not(.disabled) {
        border-color: var(--primary-color);
        background: #fff;
    }
    .reviewer-option.disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .reviewer-option input:checked + .reviewer-info {
        color: var(--primary-color);
    }
    .reviewer-option input:checked ~ .reviewer-option {
        border-color: var(--primary-color);
        background: #f0f7ff;
    }
    .reviewer-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .reviewer-name {
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .reviewer-email {
        font-size: 0.9rem;
        color: var(--text-muted);
    }
    .reviewer-affiliation {
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .form-row {
        display: flex;
        gap: 16px;
    }
    .form-group.half {
        flex: 1;
        max-width: 250px;
    }
    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
        padding-top: 24px;
        border-top: 1px solid var(--border-color);
    }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
    }
    .mb-3 {
        margin-bottom: 12px;
    }
    textarea.form-control {
        resize: vertical;
    }
    </style>
</body>
</html>
