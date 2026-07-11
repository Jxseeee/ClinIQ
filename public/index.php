<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireAdmin();

$user = currentUser();

$students = $pdo->query(
    "SELECT StudentID, FirstName, LastName, Email, Phone, CreatedAt FROM Students ORDER BY CreatedAt DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$totalStudents   = count($students);
$adminUnread     = countUnreadChatForAdmin($pdo);
$pendingAppointments = countAppointments($pdo, 'pending');
$totalVisits = countClinicVisits($pdo);

cleanupChatPresence($pdo);
$queueWaiting = (int) $pdo->query("SELECT COUNT(*) FROM ChatQueue")->fetchColumn();
$estimatedWait = $queueWaiting > 0
    ? (($queueWaiting * 4) + 10) . '–' . (($queueWaiting * 5) + 10) . ' min'
    : 'No wait';

$recentStudents = array_slice($students, 0, 5);
$recentVisits = fetchClinicVisits($pdo, null, 5);
$adminPageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/style.min.css">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../app/includes/admin-dashboard-start.php'; ?>

        <h1>Welcome, <?= htmlspecialchars($user['name'] ?? 'Admin') ?></h1>

        <div class="student-stat-grid">
            <article class="student-stat-card">
                <div>
                    <h2>Total Students</h2>
                    <strong><?= $totalStudents ?></strong>
                    <small>Registered</small>
                </div>
                <span class="stat-icon admin-stat-blue"><?= studentDashboardIcon('user') ?></span>
            </article>

            <article class="student-stat-card">
                <div>
                    <h2>Unread Messages</h2>
                    <strong <?= $adminUnread > 0 ? 'class="status-open"' : '' ?>><?= $adminUnread ?></strong>
                    <small>From students</small>
                </div>
                <span class="stat-icon <?= $adminUnread > 0 ? 'pink' : 'admin-stat-blue' ?>"><?= studentDashboardIcon('message') ?></span>
            </article>

            <article class="student-stat-card">
                <div>
                    <h2>Pending Appointments</h2>
                    <strong><?= $pendingAppointments ?></strong>
                    <small>Needs review</small>
                </div>
                <span class="stat-icon admin-stat-blue"><?= studentDashboardIcon('calendar') ?></span>
            </article>

            <article class="student-stat-card">
                <div>
                    <h2>Clinic Visits</h2>
                    <strong><?= $totalVisits ?></strong>
                    <small>Recorded</small>
                </div>
                <span class="stat-icon green"><?= studentDashboardIcon('records') ?></span>
            </article>
        </div>

        <div class="student-dashboard-grid admin-dashboard-main-grid">

            <!-- Recent Students -->
            <section class="student-announcements-panel">
                <div class="dashboard-section-title">
                    <span><?= studentDashboardIcon('records') ?></span>
                    <h2>Recent Students</h2>
                </div>

                <?php if (!empty($recentStudents)): ?>
                    <div class="dashboard-announcement-list">
                        <?php foreach ($recentStudents as $s): ?>
                            <article class="dashboard-announcement-item">
                                <div class="announcement-round-icon"><?= studentDashboardIcon('user') ?></div>
                                <div>
                                    <h3><?= htmlspecialchars($s['FirstName'] . ' ' . $s['LastName']) ?></h3>
                                    <p>#<?= htmlspecialchars($s['StudentID']) ?><?= !empty($s['Email']) ? ' &mdash; ' . htmlspecialchars($s['Email']) : '' ?></p>
                                </div>
                                <time datetime="<?= htmlspecialchars($s['CreatedAt']) ?>">
                                    <?= date('M d, Y', strtotime($s['CreatedAt'])) ?>
                                    <small><?= date('g:i A', strtotime($s['CreatedAt'])) ?></small>
                                </time>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <a href="../app/admin/students.php" class="dashboard-view-more">View All Students</a>
                <?php else: ?>
                    <div class="dashboard-empty">No students yet.</div>
                <?php endif; ?>
            </section>

            <!-- Queue Status -->
            <section class="student-side-card queue-card-dashboard">
                <div class="dashboard-section-title">
                    <span><?= studentDashboardIcon('headset') ?></span>
                    <h2>Queue Status</h2>
                </div>
                <p>Students currently waiting</p>
                <strong><?= $queueWaiting ?></strong>
                <p>Chat limit: <?= CHAT_ACTIVE_LIMIT ?> active</p>
                <b><?= htmlspecialchars($estimatedWait) ?></b>
            </section>
        </div>

        <section class="admin-table-section student-announcements-panel">
            <div class="dashboard-section-title">
                <span><?= studentDashboardIcon('records') ?></span>
                <h2>Recent Clinic Visits</h2>
            </div>

            <?php if (!empty($recentVisits)): ?>
                <div class="dashboard-announcement-list">
                    <?php foreach ($recentVisits as $visit): ?>
                        <article class="dashboard-announcement-item">
                            <div class="announcement-round-icon"><?= studentDashboardIcon('file') ?></div>
                            <div>
                                <h3><?= htmlspecialchars($visit['FirstName'] . ' ' . $visit['LastName']) ?></h3>
                                <p><?= htmlspecialchars(mb_strimwidth($visit['Complaint'], 0, 120, '...')) ?></p>
                            </div>
                            <time datetime="<?= htmlspecialchars($visit['CreatedAt']) ?>">
                                <?= date('M d, Y', strtotime($visit['CreatedAt'])) ?>
                                <small><?= htmlspecialchars(ucfirst($visit['Status'])) ?></small>
                            </time>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="dashboard-empty">No clinic visits recorded yet.</div>
            <?php endif; ?>
        </section>

    <?php include __DIR__ . '/../app/includes/admin-dashboard-end.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
