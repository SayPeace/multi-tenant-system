<?php
/**
 * Flash Messages Display
 */
$messages = Flash::get();
$errors = Flash::getErrors();
?>
<?php if (!empty($messages)): ?>
<div class="flash-messages">
    <?php foreach ($messages as $msg): ?>
    <div class="alert alert-<?= htmlspecialchars($msg['type']) ?>">
        <span class="alert-message"><?= htmlspecialchars($msg['message']) ?></span>
        <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
<div class="flash-messages">
    <div class="alert alert-error">
        <ul class="error-list">
            <?php foreach ($errors as $field => $fieldErrors): ?>
                <?php foreach ($fieldErrors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
</div>
<?php endif; ?>
