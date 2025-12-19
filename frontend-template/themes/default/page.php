<?php
$pageTitle = ($page['title'] ?? 'Page') . ' - ' . $tenant['name'];
$metaDescription = $page['meta_description'] ?? substr(strip_tags($page['content'] ?? ''), 0, 160);
include __DIR__ . '/header.php';
?>

<div class="card">
    <h1 style="margin-bottom: 25px;"><?= htmlspecialchars($page['title'] ?? 'Page') ?></h1>

    <div class="page-content">
        <?php
        // Output page content (HTML allowed for CMS pages)
        // In production, sanitize this with a library like HTML Purifier
        echo $page['content'] ?? '<p>Content not available.</p>';
        ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
