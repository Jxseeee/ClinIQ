<?php
require_once __DIR__ . '/helpers.php';

$basePath = getBasePath();
$user = $user ?? currentUser();
$adminName = $user['name'] ?? 'Admin';
$adminInitial = strtoupper(substr($adminName, 0, 1));
$currentPage = basename(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
$adminActivePage = match ($currentPage) {
    'announcements.php', 'add-announcement.php', 'edit-announcement.php' => 'announcements.php',
    'messages.php' => 'messages.php',
    'change-password.php' => 'settings.php',
    default => 'index.php',
};
$adminPageTitle = $adminPageTitle ?? 'Dashboard';
$adminContentClass = $adminContentClass ?? '';

// Allow the including page to pre-compute $adminUnread to avoid a second query.
$adminUnread = $adminUnread ?? countUnreadChatForAdmin($pdo);

$adminNavItems = [
    'index.php'         => ['Dashboard',     'dashboard', $basePath . '/public/index.php'],
    'announcements.php' => ['Announcements', 'megaphone', $basePath . '/app/admin/announcements.php'],
    'messages.php'      => ['Messages',      'message',   $basePath . '/app/admin/messages.php'],
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
        <img src="<?= $basePath ?>/public/assets/images/favicon.png" alt="FCAT ClinIQ Logo">
        <span>FCAT ClinIQ</span>
    </div>

    <nav class="student-sidebar-nav">
        <?php foreach ($adminNavItems as $file => [$label, $icon, $href]): ?>
            <a href="<?= $href ?>" class="<?= $adminActivePage === $file ? 'active' : '' ?>">
                <span class="student-nav-icon"><?= studentDashboardIcon($icon) ?></span>
                <?= htmlspecialchars($label) ?>
                <?php if ($file === 'messages.php' && $adminUnread > 0): ?>
                    <span class="nav-unread-badge"><?= $adminUnread ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="student-sidebar-footer">
        <div class="student-mini-profile">
            <div class="student-photo-placeholder" aria-hidden="true"><?= htmlspecialchars($adminInitial) ?></div>
            <div>
                <strong><?= htmlspecialchars($adminName) ?></strong>
                <small>Admin</small>
            </div>
        </div>
        <a href="<?= $basePath ?>/app/admin/change-password.php" class="<?= $currentPage === 'change-password.php' ? 'active' : '' ?>">
            <span class="student-nav-icon"><?= studentDashboardIcon('settings') ?></span>Settings
        </a>
        <a href="<?= $basePath ?>/public/logout.php">
            <span class="student-nav-icon"><?= studentDashboardIcon('logout') ?></span>Log out
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
        <div class="student-notification"><?= studentDashboardIcon('bell') ?></div>
    </header>

    <section class="student-dashboard-content <?= htmlspecialchars($adminContentClass) ?>">
