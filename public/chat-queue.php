<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';

if (!isLoggedIn()) {
    header('Location: ' . getBasePath() . '/');
    exit;
}

// A nurse still on the default password must replace it before taking a chat slot.
if (nurseAccessBlocked()) {
    header('Location: ../app/admin/change-password.php?force=1');
    exit;
}

if ($_SESSION['role'] === 'student' && studentMustChangePassword()) {
    header('Location: ../students/change-password.php?force=1');
    exit;
}

// Students can't take a chat slot until their required clinic form is complete.
if ($_SESSION['role'] === 'student' && !studentClinicFormComplete()) {
    header('Location: ../students/edit-profile.php?required=1');
    exit;
}

$status = chatQueueStatus($pdo);
if ($status['allowed']) {
    header('Location: ' . $status['return_url']);
    exit;
}

$basePath = getBasePath();
$apiUrl = $basePath . '/app/api/chat-presence.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <noscript><meta http-equiv="refresh" content="5"></noscript>
    <title>Chat Queue</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body class="antialiased selection:bg-green-200 selection:text-green-950">
    <div class="login-wrapper">
        <div class="login-card queue-card"
             id="chat-queue"
             data-status-url="<?= htmlspecialchars($apiUrl) ?>">
            <h1>Chat Is Full</h1>
            <p class="login-subtitle">
                Up to <?= CHAT_ACTIVE_LIMIT ?> students can chat at once. You'll be let in automatically when a spot opens.
            </p>

            <div class="queue-status">
                <p>Students chatting: <strong id="queue-active-count"><?= (int) $status['active_count'] ?></strong> / <?= CHAT_ACTIVE_LIMIT ?></p>
                <p>Your queue position: <strong id="queue-position"><?= (int) $status['position'] ?></strong></p>
            </div>

            <p class="text-muted">
                This page refreshes every 5 seconds. You will be moved into chat automatically when a slot opens.
            </p>

            <a href="<?= in_array($_SESSION['role'], CLINIC_STAFF_ROLES, true) ? '../public/index.php' : '../students/index.php' ?>"
               class="btn btn-secondary"
               style="width:100%;padding:12px;text-align:center;">Leave Queue</a>
        </div>
    </div>
    <script src="assets/js/chat-queue.js"></script>
</body>
</html>
