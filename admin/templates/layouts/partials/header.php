<?php
/**
 * Admin Header
 */
$userName = Session::get('auth_name') ?? 'User';
$userEmail = Session::get('auth_email') ?? '';
?>
<header class="admin-header">
    <div class="header-left">
        <button class="sidebar-toggle" onclick="document.body.classList.toggle('sidebar-collapsed')">
            &#9776;
        </button>
        <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
    </div>

    <div class="header-right">
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($userName) ?></span>
            <span class="user-email"><?= htmlspecialchars($userEmail) ?></span>
        </div>
    </div>
</header>
