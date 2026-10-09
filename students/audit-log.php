<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();

$auditViewerRole = 'student';
$auditViewerId = (int) $_SESSION['user_id'];
$auditFilters = auditLogRequestFilters();
$auditFilters['account'] = '';
// Students only ever see entries about their own account; the account comes from the session, never the request.
$auditQuery = array_merge($auditFilters, ['subject_role' => 'student', 'subject_id' => $auditViewerId]);
unset($auditQuery['involving_admin_id']);

$auditPerPage = 50;
$auditTotal = countAuditLogs($pdo, $auditQuery);
$auditPageCount = max(1, (int) ceil($auditTotal / $auditPerPage));
$auditPage = min($auditPageCount, max(1, (int) ($_GET['page'] ?? 1)));
$auditEntries = fetchAuditLogs($pdo, $auditQuery, $auditPerPage, ($auditPage - 1) * $auditPerPage);

$studentPageTitle = 'Audit Log';
$studentContentClass = 'reports-content audit-content';
$studentBreadcrumbTrail = ['Audit Log'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log</title>
    <link rel="stylesheet" href="../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../public/assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../app/includes/student-dashboard-start.php'; ?>

    <div class="reports-page audit-page">
        <div class="reports-page-heading">
            <div>
                <h1>Audit Log</h1>
                <p>A timestamped history of everything that happened on your account. If you see activity you don't recognize, change your password and contact the clinic.</p>
            </div>
        </div>

        <?php include __DIR__ . '/../app/includes/audit-log-view.php'; ?>
    </div>

    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>
    <script src="../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
