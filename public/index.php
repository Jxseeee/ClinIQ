<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireAdmin();

$user = currentUser();
$totalStudents   = (int) $pdo->query("SELECT COUNT(*) FROM Students")->fetchColumn();
$recentStudents  = $pdo->query(
    "SELECT StudentID, FirstName, LastName, Email, CreatedAt FROM Students ORDER BY CreatedAt DESC LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);
$totalVisits = countClinicVisits($pdo);
$clinicOccupancy = fetchClinicOccupancy($pdo);

cleanupChatPresence($pdo);
$queueWaiting = (int) $pdo->query("SELECT COUNT(*) FROM ChatQueue")->fetchColumn();
$estimatedWait = $queueWaiting > 0
    ? (($queueWaiting * 4) + 10) . '–' . (($queueWaiting * 5) + 10) . ' min'
    : 'No wait';

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
    <link rel="stylesheet" href="assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page dashboard-home-body antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../app/includes/admin-dashboard-start.php'; ?>

        <h1>Welcome, Admin</h1>

        <div class="student-stat-grid admin-dashboard-stat-grid">
            <article class="student-stat-card admin-primary-stat">
                <div>
                    <h2>Total Students</h2>
                    <strong><?= $totalStudents ?></strong>
                    <small>Registered</small>
                </div>
                <span class="stat-icon admin-stat-blue"><?= studentDashboardIcon('user') ?></span>
            </article>

            <article class="student-stat-card admin-primary-stat">
                <div>
                    <h2>Clinic Visits</h2>
                    <strong><?= $totalVisits ?></strong>
                    <small>Recorded</small>
                </div>
                <span class="stat-icon green"><?= studentDashboardIcon('medical') ?></span>
            </article>

            <article class="student-stat-card admin-occupancy-stat">
                <div class="nurses-dashboard-stat-copy">
                    <h2>Nurses on duty</h2>
                    <strong><?= (int) $clinicOccupancy['NursesOnDuty'] ?></strong>
                </div>
                <span class="stat-icon admin-stat-blue"><?= studentDashboardIcon('nurse') ?></span>
            </article>

            <article class="student-stat-card admin-occupancy-stat">
                <div class="nurses-dashboard-stat-copy">
                    <h2>People in clinic</h2>
                    <strong><?= (int) $clinicOccupancy['StudentsInClinic'] ?></strong>
                </div>
                <span class="stat-icon pink"><?= studentDashboardIcon('people') ?></span>
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
                <span><?= studentDashboardIcon('medical') ?></span>
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
    <script src="assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
