<?php
require_once __DIR__ . '/helpers.php';
$basePath = getBasePath();
$user = currentUser();
$studentUnread = countUnreadChatForStudent($pdo, (int) $user['id']);
?>
<nav class="navbar">
    <div class="nav-container">
        <a href="<?= $basePath ?>/students/index.php" class="nav-brand">Student Portal</a>
        <div class="nav-links">
            <a href="<?= $basePath ?>/students/index.php">Dashboard</a>
            <a href="<?= $basePath ?>/students/messages.php">
                Messages<?php if ($studentUnread > 0): ?><span class="nav-unread-badge"><?= $studentUnread ?></span><?php endif; ?>
            </a>
            <a href="<?= $basePath ?>/students/profile.php">My Profile</a>
            <a href="<?= $basePath ?>/students/edit-profile.php">Clinic Form</a>
            <a href="<?= $basePath ?>/students/change-password.php">Change Password</a>
            <span class="nav-user"><?= htmlspecialchars($user['name']) ?></span>
            <a href="<?= $basePath ?>/public/logout.php" class="btn btn-secondary btn-sm">Logout</a>
        </div>
    </div>
</nav>
