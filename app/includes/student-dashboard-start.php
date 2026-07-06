<?php
require_once __DIR__ . '/helpers.php';

$basePath = getBasePath();
$user = currentUser();
$studentName = $user['name'] ?? 'Student';
$studentInitial = strtoupper(substr($studentName, 0, 1));
$studentNotificationCount = countUnreadNotifications($pdo, 'student', (int) ($user['id'] ?? 0));
$studentNotifications = fetchNotifications($pdo, 'student', (int) ($user['id'] ?? 0));
$currentPage = basename(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
$studentPageTitle = $studentPageTitle ?? 'Dashboard';
$studentContentClass = $studentContentClass ?? '';

$navItems = [
    'index.php'        => ['Dashboard', 'dashboard'],
    'profile.php'      => ['Patient Records', 'records'],
    'appointments.php' => ['Appointments', 'calendar'],
    'messages.php'     => ['Messages', 'message'],
];

if (!function_exists('studentDashboardIcon')) {
    function studentDashboardIcon(string $name): string
    {
        $icons = [
            'dashboard' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 11.5 12 5l8 6.5"></path><path d="M6.5 10.5V19h11v-8.5"></path><path d="M10 19v-5h4v5"></path></svg>',
            'records'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="5" width="12" height="16" rx="2"></rect><path d="M9 5a3 3 0 0 1 6 0"></path><path d="M12 10v5"></path><path d="M9.5 12.5h5"></path></svg>',
            'message'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6h14v10H8l-3 3V6Z"></path><path d="M8.5 10h7"></path><path d="M8.5 13h4"></path></svg>',
            'form'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4h9l3 3v13H6V4Z"></path><path d="M15 4v4h4"></path><path d="M9 12h6"></path><path d="M9 15h5"></path></svg>',
            'settings'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"></circle><path d="M19 12a7 7 0 0 0-.1-1l2-1.5-2-3.4-2.4 1a7.2 7.2 0 0 0-1.8-1L14.4 3h-4.8l-.3 3.1a7.2 7.2 0 0 0-1.8 1l-2.4-1-2 3.4 2 1.5a7 7 0 0 0 0 2l-2 1.5 2 3.4 2.4-1a7.2 7.2 0 0 0 1.8 1l.3 3.1h4.8l.3-3.1a7.2 7.2 0 0 0 1.8-1l2.4 1 2-3.4-2-1.5c.1-.3.1-.7.1-1Z"></path></svg>',
            'logout'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 5H5v14h5"></path><path d="M13 8l4 4-4 4"></path><path d="M17 12H9"></path></svg>',
            'bell'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 10a6 6 0 0 0-12 0c0 7-2 7-2 7h16s-2 0-2-7"></path><path d="M10 20a2 2 0 0 0 4 0"></path></svg>',
            'headset'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13v-1a8 8 0 0 1 16 0v1"></path><path d="M4 13h3v5H4v-5Z"></path><path d="M17 13h3v5h-3v-5Z"></path><path d="M14 20h-2a4 4 0 0 1-4-4"></path></svg>',
            'chat'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6h14v10H9l-4 4V6Z"></path></svg>',
            'plus'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>',
            'send'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12 20 5l-7 16-2-7-7-2Z"></path><path d="M11 14 20 5"></path></svg>',
            'visit'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-5.1 7-11a7 7 0 1 0-14 0c0 5.9 7 11 7 11Z"></path><circle cx="12" cy="10" r="2.5"></circle></svg>',
            'check'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"></path><circle cx="12" cy="12" r="9"></circle></svg>',
            'megaphone' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h4l10 4V7L8 11H4v2Z"></path><path d="M8 13l1 5H6l-1-5"></path><path d="M20 9v6"></path></svg>',
            'file'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h7l4 4v14H7V3Z"></path><path d="M14 3v5h5"></path></svg>',
            'mask'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 10c4-3 10-3 14 0v5c-4 3-10 3-14 0v-5Z"></path><path d="M8 12h8"></path><path d="M8 15h8"></path></svg>',
            'user'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3"></circle><path d="M5 20c1.2-4 12.8-4 14 0"></path></svg>',
            'calendar'  => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2"></rect><path d="M8 3v4"></path><path d="M16 3v4"></path><path d="M4 10h16"></path></svg>',
            'thermo'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 14.5V5a2 2 0 1 1 4 0v9.5a4 4 0 1 1-4 0Z"></path><path d="M12 7v8"></path></svg>',
            'shield'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 19 6v5c0 4.5-2.8 8.2-7 10-4.2-1.8-7-5.5-7-10V6l7-3Z"></path></svg>',
        ];

        return $icons[$name] ?? '';
    }
}
?>
<aside class="student-sidebar">
    <div class="student-sidebar-brand">
        <a href="<?= $basePath ?>/students/index.php" class="student-sidebar-brand-link" aria-label="Go to dashboard">
            <img src="<?= $basePath ?>/public/assets/images/favicon.png" alt="FCAT ClinIQ Logo">
            <span>FCAT ClinIQ</span>
        </a>
        <button type="button" class="student-mobile-nav-toggle" aria-expanded="false" aria-controls="student-mobile-nav">
            Menu
        </button>
    </div>

    <nav class="student-sidebar-nav" id="student-mobile-nav">
        <?php foreach ($navItems as $file => [$label, $icon]): ?>
            <a href="<?= $basePath ?>/students/<?= $file ?>" class="<?= $currentPage === $file ? 'active' : '' ?>">
                <span class="student-nav-icon"><?= studentDashboardIcon($icon) ?></span><?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= $basePath ?>/students/change-password.php" class="student-mobile-only <?= $currentPage === 'change-password.php' ? 'active' : '' ?>">
            <span class="student-nav-icon"><?= studentDashboardIcon('settings') ?></span>Settings
        </a>
        <a href="<?= $basePath ?>/public/logout.php" class="student-mobile-only">
            <span class="student-nav-icon"><?= studentDashboardIcon('logout') ?></span>Log out
        </a>
    </nav>

    <div class="student-sidebar-footer">
        <div class="student-mini-profile">
            <div class="student-photo-placeholder" aria-hidden="true"><?= htmlspecialchars($studentInitial) ?></div>
            <div>
                <strong><?= htmlspecialchars($studentName) ?></strong>
                <small>Student</small>
            </div>
        </div>
        <a href="<?= $basePath ?>/students/change-password.php" class="<?= $currentPage === 'change-password.php' ? 'active' : '' ?>">
            <span class="student-nav-icon"><?= studentDashboardIcon('settings') ?></span>Settings
        </a>
        <a href="<?= $basePath ?>/public/logout.php"><span class="student-nav-icon"><?= studentDashboardIcon('logout') ?></span>Log out</a>
    </div>
</aside>

<main class="student-dashboard-main">
    <header class="student-dashboard-topbar">
        <div class="student-breadcrumb">
            <span class="student-topbar-icon"><?= studentDashboardIcon('dashboard') ?></span>
            <?php if (!empty($studentBreadcrumbTrail) && is_array($studentBreadcrumbTrail)): ?>
                <?php foreach ($studentBreadcrumbTrail as $item): ?>
                    <span><?= htmlspecialchars($item) ?></span>
                <?php endforeach; ?>
            <?php else: ?>
                <span><?= htmlspecialchars($studentPageTitle) ?></span>
            <?php endif; ?>
        </div>
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
                <?php if (!empty($studentNotifications)): ?>
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
    </header>

    <section class="student-dashboard-content <?= htmlspecialchars($studentContentClass) ?>">
