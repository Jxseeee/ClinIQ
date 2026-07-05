<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['role'] === 'student' && studentMustChangePassword()) {
    header('Location: ../students/change-password.php?force=1');
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
    <title>Chat Queue</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card queue-card"
             id="chat-queue"
             data-status-url="<?= htmlspecialchars($apiUrl) ?>">
            <h1>Chat Is Full</h1>
            <p class="login-subtitle">
                The chat is limited to <?= CHAT_ACTIVE_LIMIT ?> active users while testing.
            </p>

            <div class="queue-status">
                <p>Active chat users: <strong id="queue-active-count"><?= (int) $status['active_count'] ?></strong> / <?= CHAT_ACTIVE_LIMIT ?></p>
                <p>Your queue position: <strong id="queue-position"><?= (int) $status['position'] ?></strong></p>
            </div>

            <p class="text-muted">
                This page checks every few seconds. You will be moved into chat automatically when a slot opens.
            </p>

            <a href="<?= $_SESSION['role'] === 'admin' ? '../public/index.php' : '../students/index.php' ?>"
               class="btn btn-secondary"
               style="width:100%;padding:12px;text-align:center;">Leave Queue</a>
        </div>
    </div>
    <script src="assets/js/chat-queue.js"></script>
</body>
</html>
