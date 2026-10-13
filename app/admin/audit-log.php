<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$auditViewerRole = 'admin';
$auditViewerId = (int) $_SESSION['user_id'];
$auditFilters = auditLogRequestFilters();
$auditStudentAccounts = auditStudentAccounts($pdo);
$auditQuery = $auditFilters;
$auditSelectedStudentId = null;
if ($auditFilters['account'] === 'mine') {
    $auditQuery['involving_admin_id'] = $auditViewerId;
} elseif (str_starts_with($auditFilters['account'], 'student:')) {
    $auditSelectedStudentId = (int) substr($auditFilters['account'], 8);
    $auditQuery['subject_role'] = 'student';
    $auditQuery['subject_id'] = $auditSelectedStudentId;
}

$auditPerPage = 50;
$auditTotal = countAuditLogs($pdo, $auditQuery);
$auditPageCount = max(1, (int) ceil($auditTotal / $auditPerPage));
$auditPage = min($auditPageCount, max(1, (int) ($_GET['page'] ?? 1)));
$auditEntries = fetchAuditLogs($pdo, $auditQuery, $auditPerPage, ($auditPage - 1) * $auditPerPage);
$auditInstalled = databaseTableExists($pdo, 'AuditLogs');

$adminPageTitle = 'Audit Log';
$adminContentClass = 'reports-content audit-content';
$adminBreadcrumbTrail = ['Audit Log'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log</title>
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>

    <div class="reports-page audit-page">
        <div class="reports-page-heading">
            <div>
                <h1>Audit Log</h1>
                <p>Review who did what, to which account, and when. Entries cannot be edited or deleted.</p>
            </div>
        </div>

        <?php if (!$auditInstalled): ?>
            <div class="alert alert-warning">Audit log storage is not installed. Run <code>database/migrations/add_audit_logs.sql</code> to start recording activity.</div>
        <?php endif; ?>

        <?php include __DIR__ . '/../includes/audit-log-view.php'; ?>
    </div>

    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
