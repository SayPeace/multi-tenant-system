<?php
$pageTitle = 'Editorial Board - ' . $tenant['name'];
$metaDescription = 'Meet the editorial board of ' . $tenant['name'];
include __DIR__ . '/header.php';

$positionLabels = [
    'editor_in_chief' => 'Editor-in-Chief',
    'managing_editor' => 'Managing Editor',
    'associate_editor' => 'Associate Editors',
    'editorial_board' => 'Editorial Board Members',
    'advisory_board' => 'Advisory Board'
];
?>

<h1 style="margin-bottom: 30px;">Editorial Board</h1>

<?php foreach ($positionLabels as $position => $label): ?>
<?php if (!empty($board[$position])): ?>
<div class="card">
    <h2 style="margin-bottom: 20px; color: var(--primary-color);"><?= $label ?></h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php foreach ($board[$position] as $member): ?>
        <div style="display: flex; gap: 15px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
            <?php if (!empty($member['photo_url'])): ?>
            <img src="<?= htmlspecialchars($member['photo_url']) ?>" alt="<?= htmlspecialchars($member['name'] ?? '') ?>"
                 style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
            <?php else: ?>
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #888;">
                <?= strtoupper(substr($member['name'] ?? '', 0, 1)) ?>
            </div>
            <?php endif; ?>

            <div>
                <h3 style="margin-bottom: 5px;">
                    <?php if (!empty($member['title'])): ?>
                    <?= htmlspecialchars($member['title']) ?>
                    <?php endif; ?>
                    <?= htmlspecialchars($member['name'] ?? '') ?>
                </h3>
                <?php if (!empty($member['affiliation'])): ?>
                <p style="color: #666; font-size: 0.9rem;"><?= htmlspecialchars($member['affiliation']) ?></p>
                <?php endif; ?>
                <?php if (!empty($member['country'])): ?>
                <p style="color: #888; font-size: 0.85rem;"><?= htmlspecialchars($member['country']) ?></p>
                <?php endif; ?>
                <?php if (!empty($member['email'])): ?>
                <p style="font-size: 0.85rem; margin-top: 5px;">
                    <a href="mailto:<?= htmlspecialchars($member['email']) ?>"><?= htmlspecialchars($member['email']) ?></a>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<?php if (empty($board) || (empty($board['editor_in_chief']) && empty($board['editorial_board']))): ?>
<div class="card">
    <p>Editorial board information is not available at this time.</p>
</div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
