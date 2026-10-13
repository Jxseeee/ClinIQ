<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();

$studentId = (int) $_SESSION['user_id'];
$user = currentUser();
$data = fetchStudentFullProfile($pdo, $studentId);
$student = $data['student'] ?? [];
$announcements = fetchAnnouncements($pdo);
$latestVisit = fetchClinicVisits($pdo, $studentId, 1)[0] ?? null;
$clinicOccupancy = fetchClinicOccupancy($pdo);
// The clinic is open only while at least one nurse is switched on duty (on the Nurses page).
$clinicIsOpen = (int) $clinicOccupancy['NursesOnDuty'] > 0;

cleanupChatPresence($pdo);
$queueWaiting = (int) $pdo->query("SELECT COUNT(*) FROM ChatQueue")->fetchColumn();
$estimatedWait = $queueWaiting > 0
    ? (($queueWaiting * 4) + 10) . '-' . (($queueWaiting * 5) + 10) . ' minutes'
    : 'No wait';

$totalVisits = countClinicVisits($pdo, $studentId);
// The Health Snapshot reflects actual clinic visits only; a student with no visits shows "No record"
// (not the account creation date or illnesses from the clinic form's past medical history).
$lastVisit = $latestVisit ? date('F j, Y', strtotime($latestVisit['TimeIn'] ?? $latestVisit['CreatedAt'])) : 'No record';
$lastIssue = $latestVisit ? (trim((string) $latestVisit['Complaint']) ?: 'No record') : 'No record';
$clinicStatus = match ($latestVisit['Status'] ?? null) {
    'follow-up' => 'FOLLOW UP',
    'completed' => 'TREATED',
    'open' => 'IN CLINIC',
    default => 'No record',
};
$studentPageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../public/assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page dashboard-home-body antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../app/includes/student-dashboard-start.php'; ?>
            <h1>Welcome, <?= htmlspecialchars($student['FirstName'] ?? $user['name']) ?></h1>

            <div class="student-stat-grid">
                <article class="student-stat-card">
                    <div>
                        <h2>Your Total Visits</h2>
                        <strong><?= $totalVisits ?></strong>
                        <small>All time</small>
                    </div>
                    <span class="stat-icon pink"><?= studentDashboardIcon('visit') ?></span>
                </article>

                <article class="student-stat-card">
                    <div>
                        <h2>Clinic Status</h2>
                        <?php if ($clinicIsOpen): ?>
                            <strong class="status-open">OPEN</strong>
                            <small>8:00 AM - 5:00 PM</small>
                        <?php else: ?>
                            <strong class="status-closed">CLOSED</strong>
                            <small>No nurse is on duty right now</small>
                        <?php endif; ?>
                    </div>
                    <span class="stat-icon <?= $clinicIsOpen ? 'green' : 'pink' ?>"><?= studentDashboardIcon($clinicIsOpen ? 'check' : 'moon') ?></span>
                </article>

                <article class="student-stat-card">
                    <div>
                        <h2>Nurses on duty</h2>
                        <strong><?= (int) $clinicOccupancy['NursesOnDuty'] ?></strong>
                    </div>
                    <span class="stat-icon admin-stat-blue"><?= studentDashboardIcon('nurse') ?></span>
                </article>

                <article class="student-stat-card">
                    <div>
                        <h2>People in clinic</h2>
                        <strong><?= (int) $clinicOccupancy['StudentsInClinic'] ?></strong>
                    </div>
                    <span class="stat-icon pink"><?= studentDashboardIcon('people') ?></span>
                </article>
            </div>

            <div class="student-dashboard-grid">
                <section class="student-announcements-panel">
                    <div class="dashboard-section-title">
                        <span><?= studentDashboardIcon('megaphone') ?></span>
                        <h2>Announcements</h2>
                    </div>

                    <?php if (count($announcements) > 0): ?>
                        <div class="dashboard-announcement-list">
                            <?php foreach ($announcements as $a): ?>
                                <article class="dashboard-announcement-item">
                                    <div class="announcement-round-icon"><?= studentDashboardIcon($a['Title'] === 'Wear a Face Mask' ? 'mask' : 'file') ?></div>
                                    <div>
                                        <h3><?= htmlspecialchars($a['Title']) ?></h3>
                                        <p><?= htmlspecialchars(mb_strimwidth($a['Content'], 0, 120, '...')) ?></p>
                                    </div>
                                    <time datetime="<?= htmlspecialchars($a['CreatedAt']) ?>">
                                        <?= date('M d, Y', strtotime($a['CreatedAt'])) ?>
                                        <small><?= date('g:i A', strtotime($a['CreatedAt'])) ?></small>
                                    </time>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="dashboard-empty">No announcements at this time.</div>
                    <?php endif; ?>
                </section>

                <section class="student-side-card health-card">
                    <div class="dashboard-section-title">
                        <span><?= studentDashboardIcon('pulse') ?></span>
                        <h2>Health Snapshot</h2>
                    </div>
                    <div class="health-row">
                        <span><?= studentDashboardIcon('calendar') ?></span>
                        <div><small>Last Visit</small><strong><?= htmlspecialchars($lastVisit) ?></strong></div>
                    </div>
                    <div class="health-row">
                        <span><?= studentDashboardIcon('thermo') ?></span>
                        <div><small>Last Issue</small><strong><?= htmlspecialchars($lastIssue) ?></strong></div>
                    </div>
                    <div class="health-row">
                        <span><?= studentDashboardIcon('shield') ?></span>
                        <div><small>Status</small><strong><?= htmlspecialchars($clinicStatus) ?></strong></div>
                    </div>
                </section>

            </div>

            <?php include __DIR__ . '/../app/includes/nurse-campus-availability.php'; ?>
    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>

    <script src="../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
