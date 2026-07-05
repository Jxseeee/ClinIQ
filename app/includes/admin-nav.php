<?php
require_once __DIR__ . '/helpers.php';
$basePath = getBasePath();
$user = currentUser();
$adminUnread = countUnreadChatForAdmin($pdo);
?>
<nav class="navbar">
    <div class="nav-container">
        <a href="<?= $basePath ?>/public/index.php" class="nav-brand">Admin Panel</a>
        <div class="nav-links">
            <a href="<?= $basePath ?>/public/index.php">Students</a>
            <a href="<?= $basePath ?>/app/admin/announcements.php">Announcements</a>
            <a href="<?= $basePath ?>/app/admin/messages.php">
                Messages<?php if ($adminUnread > 0): ?><span class="nav-unread-badge"><?= $adminUnread ?></span><?php endif; ?>
            </a>
            <a href="<?= $basePath ?>/app/admin/change-password.php">Change Password</a>
            <span class="nav-user"><?= htmlspecialchars($user['name']) ?></span>
            <a href="<?= $basePath ?>/public/logout.php" class="btn btn-secondary btn-sm">Logout</a>
        </div>
    </div>
</nav>
