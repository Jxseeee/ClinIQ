<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();

$studentId = (int) $_SESSION['user_id'];
$errors = [];
$studentPageTitle = 'Appointments';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $requestedDate = trim($_POST['requested_date'] ?? '');
    $requestedTime = trim($_POST['requested_time'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if ($requestedDate === '' || $requestedTime === '') {
        $errors[] = 'Requested date and time are required.';
    }
    if ($reason === '') {
        $errors[] = 'Reason is required.';
    }

    if (empty($errors)) {
        createAppointment($pdo, $studentId, $requestedDate . ' ' . $requestedTime . ':00', $reason);
        header('Location: appointments.php?success=requested');
        exit;
    }
}

$appointments = fetchAppointments($pdo, null, $studentId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments</title>
    <link rel="stylesheet" href="../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../public/assets/css/style.min.css">
    <link rel="icon" type="image/png" href="../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../app/includes/student-dashboard-start.php'; ?>
    <div class="dashboard-content-card">
        <h1>Appointments</h1>

        <?php if (($_GET['success'] ?? '') === 'requested'): ?>
            <div class="alert alert-success">Appointment request submitted.</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin:0; padding-left:18px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="student-announcements-panel admin-full-panel">
            <div class="dashboard-section-title">
                <span><?= studentDashboardIcon('calendar') ?></span>
                <h2>Request a Clinic Visit</h2>
            </div>

            <form method="POST" class="appointment-request-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label for="requested_date">Preferred Date *</label>
                        <input type="date" id="requested_date" name="requested_date" required>
                    </div>
                    <div class="form-group">
                        <label for="requested_time">Preferred Time *</label>
                        <input type="time" id="requested_time" name="requested_time" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="reason">Reason *</label>
                    <textarea id="reason" name="reason" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-success">Submit Request</button>
            </form>
        </section>

        <section class="student-announcements-panel admin-full-panel">
            <div class="dashboard-section-title">
                <span><?= studentDashboardIcon('records') ?></span>
                <h2>Your Requests</h2>
            </div>

            <?php if (!empty($appointments)): ?>
                <div class="appointment-card-list">
                    <?php foreach ($appointments as $appointment): ?>
                        <article class="appointment-card">
                            <div>
                                <h3><?= date('M d, Y g:i A', strtotime($appointment['RequestedFor'])) ?></h3>
                                <p><?= htmlspecialchars($appointment['Reason']) ?></p>
                                <?php if (!empty($appointment['AdminNotes'])): ?>
                                    <small>Admin notes: <?= htmlspecialchars($appointment['AdminNotes']) ?></small>
                                <?php endif; ?>
                            </div>
                            <strong><?= htmlspecialchars(ucfirst($appointment['Status'])) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="dashboard-empty">You have not requested an appointment yet.</div>
            <?php endif; ?>
        </section>
    </div>
    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>
    <script src="../public/assets/js/main.js"></script>
</body>
</html>
