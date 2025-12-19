<?php
$pageTitle = 'Archives - ' . $tenant['name'];
$metaDescription = 'Browse all volumes and issues of ' . $tenant['name'];
include __DIR__ . '/header.php';
?>

<h1 style="margin-bottom: 30px;">Archives</h1>

<?php if (!empty($volumes)): ?>
    <?php foreach ($volumes as $volumeData): ?>
    <?php $volume = $volumeData['volume']; ?>
    <div class="card">
        <h2 style="margin-bottom: 15px;">
            Volume <?= $volume->volume_number ?> (<?= $volume->year ?>)
            <?php if (!empty($volume->title)): ?>
            - <?= htmlspecialchars($volume->title) ?>
            <?php endif; ?>
        </h2>

        <?php if (!empty($volume->description)): ?>
        <p style="margin-bottom: 15px; color: #666;"><?= htmlspecialchars($volume->description) ?></p>
        <?php endif; ?>

        <?php if (!empty($volumeData['issues'])): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
            <?php foreach ($volumeData['issues'] as $issue): ?>
            <div style="padding: 15px; background: #f9f9f9; border-radius: 4px;">
                <h3 style="margin-bottom: 8px;">
                    <a href="/issue/<?= $issue->id ?>">
                        Issue <?= $issue->issue_number ?>
                        <?php if (!empty($issue->month)): ?>
                        (<?= htmlspecialchars($issue->month) ?>)
                        <?php endif; ?>
                    </a>
                </h3>
                <?php if ($issue->is_special_issue && !empty($issue->title)): ?>
                <p style="font-style: italic; color: #666; margin-bottom: 5px;">
                    Special Issue: <?= htmlspecialchars($issue->title) ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($issue->published_at)): ?>
                <p style="font-size: 0.9rem; color: #888;">
                    Published: <?= date('M j, Y', strtotime($issue->published_at)) ?>
                </p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p>No issues in this volume yet.</p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php else: ?>
<div class="card">
    <p>No volumes published yet.</p>
</div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
