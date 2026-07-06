<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$errors = [];
$validStatuses = ['pending', 'approved', 'declined', 'completed', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $notes = trim($_POST['admin_notes'] ?? '');

    if ($appointmentId <= 0 || !in_array($status, $validStatuses, true)) {
        $errors[] = 'Invalid appointment update.';
    } else {
        updateAppointmentStatus($pdo, $appointmentId, $status, (int) $_SESSION['user_id'], $notes);
        header('Location: appointments.php?success=updated');
        exit;
    }
}

$filter = $_GET['status'] ?? null;
if ($filter !== null && !in_array($filter, $validStatuses, true)) {
    $filter = null;
}

$appointments = fetchAppointments($pdo, $filter);
$adminPageTitle = 'Appointments';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments</title>
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.min.css">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>
    <div class="dashboard-content-card">
        <h1>Appointments</h1>

        <?php if (($_GET['success'] ?? '') === 'updated'): ?>
            <div class="alert alert-success">Appointment updated successfully.</div>
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
                <h2>Clinic Requests</h2>
            </div>

            <div class="appointment-filter-row">
                <a href="appointments.php" class="btn btn-secondary">All</a>
                <?php foreach ($validStatuses as $status): ?>
                    <a href="appointments.php?status=<?= urlencode($status) ?>" class="btn appointment-filter-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></a>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($appointments)): ?>
                <div class="appointment-card-list">
                    <?php foreach ($appointments as $appointment): ?>
                        <article class="appointment-card">
                            <div>
                                <h3><?= htmlspecialchars($appointment['FirstName'] . ' ' . $appointment['LastName']) ?></h3>
                                <p><?= htmlspecialchars($appointment['Reason']) ?></p>
                                <small>Requested for <?= date('M d, Y g:i A', strtotime($appointment['RequestedFor'])) ?></small>
                            </div>
                            <form method="POST" class="appointment-status-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="appointment_id" value="<?= (int) $appointment['AppointmentID'] ?>">
                                <label>
                                    Status
                                    <select name="status">
                                        <?php foreach ($validStatuses as $status): ?>
                                            <option value="<?= $status ?>" <?= $appointment['Status'] === $status ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(ucfirst($status)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>
                                    Notes
                                    <textarea name="admin_notes" rows="2"><?= htmlspecialchars($appointment['AdminNotes'] ?? '') ?></textarea>
                                </label>
                                <button type="submit" class="btn btn-success">Update</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="dashboard-empty">No appointment requests found.</div>
            <?php endif; ?>
        </section>
    </div>
    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js"></script>
</body>
</html>
