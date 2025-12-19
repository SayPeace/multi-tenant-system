<?php
$pageTitle = 'Page Not Found - ' . $tenant['name'];
include __DIR__ . '/header.php';
?>

<div class="card" style="text-align: center; padding: 60px 20px;">
    <h1 style="font-size: 6rem; color: #ddd; margin-bottom: 20px;">404</h1>
    <h2 style="margin-bottom: 15px;">Page Not Found</h2>
    <p style="color: #666; margin-bottom: 30px;">
        The page you're looking for doesn't exist or has been moved.
    </p>
    <div>
        <a href="/" class="btn btn-primary">Go to Homepage</a>
        <a href="/articles" class="btn" style="margin-left: 10px; border: 1px solid var(--primary-color); color: var(--primary-color);">Browse Articles</a>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
