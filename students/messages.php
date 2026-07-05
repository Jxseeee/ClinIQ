<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/config/pusher.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();
requireChatCapacity($pdo);

$studentId = (int) $_SESSION['user_id'];
$activeChatCount = activeChatCount($pdo);
markChatMessagesRead($pdo, $studentId, 'student');

$rows = fetchChatMessages($pdo, $studentId);
$messages = array_map(fn($row) => formatChatMessage($row, 'student'), $rows);

$basePath = getBasePath();
$apiBase = $basePath . '/app/api';
$pusher = pusherPublicConfig();
$studentPageTitle = 'Messages';
$studentContentClass = 'student-message-content';
$studentBreadcrumbTrail = ['Dashboard', 'Reports', 'Patient Records', 'Messages'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="icon" type="image/png" href="../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page student-messages-body">
    <?php include __DIR__ . '/../app/includes/student-dashboard-start.php'; ?>
    <div class="student-messages-page">
        <div class="student-messages-header">
            <h1>Messages</h1>
            <p>You can message the clinic admin for any questions or concerns.</p>

            <div class="student-chat-contact">
                <span class="student-chat-contact-icon"><?= studentDashboardIcon('headset') ?></span>
                <div>
                    <strong>Clinic Admin</strong>
                    <small>We typically reply within 24 hours</small>
                </div>
            </div>
        </div>

        <?php if (!pusherConfigured()): ?>
            <div class="alert alert-warning student-chat-warning">
                Real-time updates are disabled until Pusher credentials are set in your <code>.env</code> file.
                You can still send messages; refresh the page to see new replies.
            </div>
        <?php endif; ?>

        <div class="student-chat-stage">
            <div id="chat-app"
                 class="student-chat-app"
                 data-api-base="<?= htmlspecialchars($apiBase) ?>"
                 data-presence-url="<?= htmlspecialchars($apiBase . '/chat-presence.php') ?>"
                 data-queue-url="<?= htmlspecialchars($basePath . '/public/chat-queue.php') ?>"
                 data-auth-endpoint="<?= htmlspecialchars($apiBase . '/pusher-auth.php') ?>"
                 data-pusher-key="<?= htmlspecialchars($pusher['key']) ?>"
                 data-pusher-cluster="<?= htmlspecialchars($pusher['cluster']) ?>"
                 data-channel="<?= htmlspecialchars(chatChannelName($studentId)) ?>"
                 data-csrf="<?= htmlspecialchars(csrfToken()) ?>"
                 data-student-id="<?= $studentId ?>"
                 data-role="student">

                <div id="chat-messages" class="chat-messages student-chat-messages <?= empty($messages) ? 'is-empty' : 'has-messages' ?>">
                    <?php if (empty($messages)): ?>
                        <div class="student-chat-empty" id="chat-empty">
                            <span><?= studentDashboardIcon('chat') ?></span>
                            <strong>No messages yet</strong>
                            <p>Send a message to the clinic admin.</p>
                        </div>
                    <?php else: ?>
                        <?php include __DIR__ . '/../app/includes/chat-messages.php'; ?>
                    <?php endif; ?>
                </div>

                <form id="chat-form" class="chat-compose student-chat-compose" method="POST" action="">
                    <textarea id="chat-input" name="content" rows="2"
                              placeholder="Send a message" maxlength="2000" required></textarea>
                    <button type="submit" class="student-chat-send" aria-label="Send message">
                        <?= studentDashboardIcon('send') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>
    <script src="../public/assets/js/main.js"></script>
    <script src="../public/assets/js/chat.js"></script>
</body>
</html>
