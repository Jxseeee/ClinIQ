<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();

$studentId = (int) $_SESSION['user_id'];
$user = currentUser();
$data = fetchStudentFullProfile($pdo, $studentId);
$student = $data['student'] ?? [];
$medHistory = $data['medHistory'] ?? [];
$announcements = fetchAnnouncements($pdo);
$studentAppointments = fetchAppointments($pdo, null, $studentId);
$latestVisit = fetchClinicVisits($pdo, $studentId, 1)[0] ?? null;

cleanupChatPresence($pdo);
$queueWaiting = (int) $pdo->query("SELECT COUNT(*) FROM ChatQueue")->fetchColumn();
$estimatedWait = $queueWaiting > 0
    ? (($queueWaiting * 4) + 10) . '-' . (($queueWaiting * 5) + 10) . ' minutes'
    : 'No wait';

$totalVisits = count($medHistory);
$lastVisit = !empty($student['CreatedAt']) ? date('F j, Y', strtotime($student['CreatedAt'])) : 'No record';
$lastVisit = $latestVisit ? date('F j, Y', strtotime($latestVisit['CreatedAt'])) : $lastVisit;
$lastIssue = $medHistory[0]['Illness'] ?? 'No record';
$lastIssue = $latestVisit['Complaint'] ?? $lastIssue;
$profileComplete = !empty($student['Course']) && !empty($student['Gender']) && !empty($student['DateOfBirth']) && !empty($student['StreetAddress']);
$clinicStatus = $profileComplete ? 'TREATED' : 'PENDING';
$studentPageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../public/assets/css/style.min.css">
    <link rel="icon" type="image/png" href="../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
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
                        <strong class="status-open">OPEN</strong>
                        <small>8:00 AM - 5:00 PM</small>
                    </div>
                    <span class="stat-icon green"><?= studentDashboardIcon('check') ?></span>
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
                        <span><?= studentDashboardIcon('user') ?></span>
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
                    <a href="profile.php" class="dashboard-view-more">View Report</a>
                </section>

                <section class="student-side-card queue-card-dashboard">
                    <div class="dashboard-section-title">
                        <span><?= studentDashboardIcon('calendar') ?></span>
                        <h2>Appointment Status</h2>
                    </div>
                    <?php if (!empty($studentAppointments)): $latestAppointment = $studentAppointments[0]; ?>
                        <p>Latest request</p>
                        <strong style="font-size:2.6rem;"><?= htmlspecialchars(ucfirst($latestAppointment['Status'])) ?></strong>
                        <p><?= date('M d, Y g:i A', strtotime($latestAppointment['RequestedFor'])) ?></p>
                    <?php else: ?>
                        <p>No appointment requests yet</p>
                        <strong style="font-size:2.6rem;">0</strong>
                        <p>Submit one when you need clinic support.</p>
                    <?php endif; ?>
                    <a href="appointments.php" class="dashboard-view-more">Manage Appointments</a>
                </section>
            </div>
    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>

    <script src="../public/assets/js/main.js"></script>
</body>
</html>
