<?php
require_once __DIR__ . '/helpers.php';

$basePath = getBasePath();
$adminUser = currentUser();
$adminName = 'Admin';
$adminInitial = strtoupper(substr($adminName, 0, 1));
$adminProfileImagePath = fetchProfileImagePath($pdo, 'admin', (int) ($adminUser['id'] ?? 0));
$adminProfileImageUrl = profileImageUrl($adminProfileImagePath, $basePath);
$currentPage = basename(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
$adminActivePage = match ($currentPage) {
    'students.php', 'add-student.php', 'edit-student.php', 'view-student.php' => 'students.php',
    'reports.php' => 'reports.php',
    'announcements.php', 'add-announcement.php', 'edit-announcement.php' => 'announcements.php',
    'messages.php' => 'messages.php',
    'change-password.php' => 'settings.php',
    default => 'index.php',
};
$adminPageTitle = $adminPageTitle ?? 'Dashboard';
$adminContentClass = $adminContentClass ?? '';

// Allow the including page to pre-compute $adminUnread to avoid a second query.
$adminUnread = $adminUnread ?? countUnreadChatForAdmin($pdo);
$adminNotificationCount = countUnreadNotifications($pdo, 'admin');
$adminNotifications = fetchNotifications($pdo, 'admin');

$adminNavItems = [
    'index.php'         => ['Dashboard',     'dashboard', $basePath . '/public/index.php'],
    'reports.php'       => ['Reports',       'report',    $basePath . '/app/admin/reports.php'],
    'students.php'      => ['Students',      'records',   $basePath . '/app/admin/students.php'],
    'announcements.php' => ['Announcements', 'megaphone', $basePath . '/app/admin/announcements.php'],
    'messages.php'      => ['Messages',      'message',   $basePath . '/app/admin/messages.php'],
];
?>
<script src="<?= $basePath ?>/public/assets/js/theme.js"></script>
<aside class="student-sidebar">
    <div class="student-sidebar-brand">
        <a href="<?= $basePath ?>/public/index.php" class="student-sidebar-brand-link" aria-label="Go to dashboard">
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
        <?php foreach ($adminNavItems as $file => [$label, $icon, $href]): ?>
            <a href="<?= $href ?>" class="<?= $adminActivePage === $file ? 'active' : '' ?>">
                <span class="student-nav-icon"><?= studentDashboardIcon($icon) ?></span>
                <span class="student-nav-label"><?= htmlspecialchars($label) ?></span>
                <?php if ($file === 'messages.php' && $adminUnread > 0): ?>
                    <span class="nav-unread-badge"><?= $adminUnread ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= $basePath ?>/app/admin/change-password.php" class="student-mobile-only <?= $currentPage === 'change-password.php' ? 'active' : '' ?>">
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
            <?php if ($adminProfileImageUrl): ?>
                <img src="<?= htmlspecialchars($adminProfileImageUrl) ?>"
                     alt="<?= htmlspecialchars($adminName) ?> profile photo"
                     class="student-profile-photo">
            <?php else: ?>
                <div class="student-photo-placeholder" aria-hidden="true"><?= htmlspecialchars($adminInitial) ?></div>
            <?php endif; ?>
            <div>
                <strong><?= htmlspecialchars($adminName) ?></strong>
                <small>Admin</small>
            </div>
        </div>
        <a href="<?= $basePath ?>/app/admin/change-password.php" class="<?= $currentPage === 'change-password.php' ? 'active' : '' ?>">
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
            <span class="student-topbar-icon"><?= studentDashboardIcon('dashboard') ?></span>
            <?php if (!empty($adminBreadcrumbTrail) && is_array($adminBreadcrumbTrail)): ?>
                <?php foreach ($adminBreadcrumbTrail as $item): ?>
                    <span><?= htmlspecialchars($item) ?></span>
                <?php endforeach; ?>
            <?php else: ?>
                <span><?= htmlspecialchars($adminPageTitle) ?></span>
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
                    <?php if ($adminNotificationCount > 0): ?>
                        <span class="notification-count"><?= $adminNotificationCount ?></span>
                    <?php endif; ?>
                </button>
                <div class="notification-dropdown" role="menu">
                    <strong>Notifications</strong>
                    <?php if (!empty($adminNotifications)): ?>
                        <?php foreach ($adminNotifications as $notification): ?>
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

    <section class="student-dashboard-content <?= htmlspecialchars($adminContentClass) ?>">
