<?php
require_once __DIR__ . '/helpers.php';

$basePath = getBasePath();
$studentUser = currentUser();
$studentName = $studentUser['name'] ?? 'Student';
$studentInitial = strtoupper(substr($studentName, 0, 1));
$studentProfileImagePath = fetchProfileImagePath($pdo, 'student', (int) ($studentUser['id'] ?? 0));
$studentProfileImageUrl = profileImageUrl($studentProfileImagePath, $basePath);
$studentNotificationsAreEnabled = studentNotificationsEnabled($pdo, (int) ($studentUser['id'] ?? 0));
$studentNotificationCount = countUnreadNotifications($pdo, 'student', (int) ($studentUser['id'] ?? 0));
$studentNotifications = fetchNotifications($pdo, 'student', (int) ($studentUser['id'] ?? 0));
$currentPage = basename(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
$studentPageTitle = $studentPageTitle ?? 'Dashboard';
$studentContentClass = $studentContentClass ?? '';

$navItems = [
    'index.php'        => ['Dashboard', 'dashboard'],
    'profile.php'      => ['Patient Records', 'records'],
    'messages.php'     => ['Messages', 'message'],
];
$studentPageIcon = match ($currentPage) {
    'profile.php', 'edit-profile.php' => 'records',
    'messages.php' => 'message',
    'change-password.php' => 'settings',
    default => 'dashboard',
};
?>
<script src="<?= $basePath ?>/public/assets/js/theme.js"></script>
<aside class="student-sidebar">
    <div class="student-sidebar-brand">
        <a href="<?= $basePath ?>/students/index.php" class="student-sidebar-brand-link" aria-label="Go to dashboard">
            <img src="<?= $basePath ?>/public/assets/images/favicon.png" alt="FCAT Clinic Logo">
            <span class="student-sidebar-brand-copy">
                <strong>FCAT Clinic</strong>
                <small>Campus health</small>
            </span>
        </a>
        <button type="button" class="student-mobile-nav-toggle" aria-expanded="false" aria-controls="student-mobile-nav">
            Menu
        </button>
    </div>

    <nav class="student-sidebar-nav" id="student-mobile-nav">
        <?php foreach ($navItems as $file => [$label, $icon]): ?>
            <a href="<?= $basePath ?>/students/<?= $file ?>" class="<?= $currentPage === $file ? 'active' : '' ?>">
                <span class="student-nav-icon"><?= studentDashboardIcon($icon) ?></span>
                <span class="student-nav-label"><?= htmlspecialchars($label) ?></span>
            </a>
        <?php endforeach; ?>
        <a href="<?= $basePath ?>/students/change-password.php" class="student-mobile-only <?= $currentPage === 'change-password.php' ? 'active' : '' ?>">
            <span class="student-nav-icon"><?= studentDashboardIcon('settings') ?></span>
            <span class="student-nav-label">Settings</span>
        </a>
        <a href="<?= $basePath ?>/public/logout.php" class="student-mobile-only">
            <span class="student-nav-icon"><?= studentDashboardIcon('logout') ?></span>
            <span class="student-nav-label">Log out</span>
        </a>
    </nav>

    <div class="student-sidebar-footer">
        <div class="student-mini-profile">
            <?php if ($studentProfileImageUrl): ?>
                <img src="<?= htmlspecialchars($studentProfileImageUrl) ?>"
                     alt="<?= htmlspecialchars($studentName) ?> profile photo"
                     class="student-profile-photo">
            <?php else: ?>
                <div class="student-photo-placeholder" aria-hidden="true"><?= htmlspecialchars($studentInitial) ?></div>
            <?php endif; ?>
            <div>
                <strong><?= htmlspecialchars($studentName) ?></strong>
                <small>Student</small>
            </div>
        </div>
        <a href="<?= $basePath ?>/students/change-password.php" class="<?= $currentPage === 'change-password.php' ? 'active' : '' ?>">
            <span class="student-nav-icon"><?= studentDashboardIcon('settings') ?></span>
            <span class="student-nav-label">Settings</span>
        </a>
        <a href="<?= $basePath ?>/public/logout.php">
            <span class="student-nav-icon"><?= studentDashboardIcon('logout') ?></span>
            <span class="student-nav-label">Log out</span>
        </a>
    </div>
</aside>

<main class="student-dashboard-main">
    <header class="student-dashboard-topbar">
        <div class="student-breadcrumb">
            <span class="student-topbar-icon"><?= studentDashboardIcon($studentPageIcon) ?></span>
            <?php if (!empty($studentBreadcrumbTrail) && is_array($studentBreadcrumbTrail)): ?>
                <?php foreach ($studentBreadcrumbTrail as $item): ?>
                    <span><?= htmlspecialchars($item) ?></span>
                <?php endforeach; ?>
            <?php else: ?>
                <span><?= htmlspecialchars($studentPageTitle) ?></span>
            <?php endif; ?>
        </div>
        <div class="student-topbar-actions">
            <button type="button" class="student-theme-toggle" data-theme-toggle aria-label="Switch to dark mode" title="Switch to dark mode">
                <span data-theme-icon="moon"><?= studentDashboardIcon('moon') ?></span>
                <span data-theme-icon="sun" hidden><?= studentDashboardIcon('sun') ?></span>
            </button>
            <div class="notification-menu"
                 data-notifications-url="<?= $basePath ?>/app/api/notifications.php"
                 data-csrf="<?= htmlspecialchars(csrfToken()) ?>">
                <button type="button" class="student-notification" aria-expanded="false" aria-label="Show notifications">
                    <?= studentDashboardIcon('bell') ?>
                    <?php if ($studentNotificationCount > 0): ?>
                        <span class="notification-count"><?= $studentNotificationCount ?></span>
                    <?php endif; ?>
                </button>
                <div class="notification-dropdown" role="menu">
                    <strong>Notifications</strong>
                    <?php if (!$studentNotificationsAreEnabled): ?>
                        <p>Notifications are disabled. <a href="<?= $basePath ?>/students/change-password.php?tab=notifications">Open Settings</a></p>
                    <?php elseif (!empty($studentNotifications)): ?>
                        <?php foreach ($studentNotifications as $notification): ?>
                            <a href="<?= htmlspecialchars($notification['Link'] ?: '#') ?>" class="<?= (int) $notification['IsRead'] === 0 ? 'is-unread' : '' ?>">
                                <span><?= htmlspecialchars($notification['Title']) ?></span>
                                <?php if (!empty($notification['Body'])): ?>
                                    <small><?= htmlspecialchars($notification['Body']) ?></small>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No notifications yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <section class="student-dashboard-content <?= htmlspecialchars($studentContentClass) ?>">
