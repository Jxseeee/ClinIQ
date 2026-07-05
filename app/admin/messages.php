<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/pusher.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();
requireChatCapacity($pdo);

$activeChatCount = activeChatCount($pdo);
$threads = fetchChatThreads($pdo);
$selectedId = isset($_GET['student_id']) && ctype_digit((string) $_GET['student_id'])
    ? (int) $_GET['student_id']
    : null;

if ($selectedId === null && !empty($threads)) {
    $selectedId = (int) $threads[0]['StudentID'];
}

$messages = [];
$selectedStudent = null;

if ($selectedId !== null) {
    $stmt = $pdo->prepare('SELECT StudentID, FirstName, LastName FROM Students WHERE StudentID = ?');
    $stmt->execute([$selectedId]);
    $selectedStudent = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selectedStudent) {
        markChatMessagesRead($pdo, $selectedId, 'admin');
        $rows = fetchChatMessages($pdo, $selectedId);
        $messages = array_map(fn($row) => formatChatMessage($row, 'admin'), $rows);
    } else {
        $selectedId = null;
    }
}

$basePath = getBasePath();
$apiBase = $basePath . '/app/api';
$pusher = pusherPublicConfig();
$adminPageTitle = 'Messages';
$adminContentClass = 'student-message-content';
$adminBreadcrumbTrail = ['Dashboard', 'Messages'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page student-messages-body admin-dashboard-page">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>
        <div class="student-messages-page">
            <div class="student-messages-header">
                <div>
                    <h1>Messages</h1>
                    <p>Reply to student inquiries in real time.</p>
                </div>
                <div class="student-chat-contact admin-chat-contact">
                    <span class="student-chat-contact-icon"><?= studentDashboardIcon('headset') ?></span>
                    <div>
                        <strong>Student Conversations</strong>
                        <small>Manage clinic chat requests</small>
                        <small class="student-chat-capacity">Active chats: <?= $activeChatCount ?> / <?= CHAT_ACTIVE_LIMIT ?></small>
                    </div>
                </div>
            </div>

        <div id="chat-presence"
             data-presence-url="<?= htmlspecialchars($apiBase . '/chat-presence.php') ?>"
             data-queue-url="<?= htmlspecialchars($basePath . '/public/chat-queue.php') ?>"
             data-csrf="<?= htmlspecialchars(csrfToken()) ?>"></div>

        <?php if (!pusherConfigured()): ?>
            <div class="alert alert-warning">
                Real-time updates are disabled until Pusher credentials are set in your <code>.env</code> file.
            </div>
        <?php endif; ?>

        <div class="chat-layout card admin-chat-card">
            <aside class="chat-sidebar" id="chat-sidebar">
                <h2>Conversations</h2>
                <?php if (empty($threads)): ?>
                    <p class="chat-sidebar-empty">No conversations yet.</p>
                <?php else: ?>
                    <ul class="chat-thread-list" id="chat-thread-list">
                        <?php foreach ($threads as $t): ?>
                        <li>
                            <a href="messages.php?student_id=<?= (int) $t['StudentID'] ?>"
                               class="chat-thread-item <?= $selectedId === (int) $t['StudentID'] ? 'active' : '' ?>"
                               data-student-id="<?= (int) $t['StudentID'] ?>">
                                <span class="chat-thread-name">
                                    <?= htmlspecialchars($t['FirstName'] . ' ' . $t['LastName']) ?>
                                    <small>#<?= (int) $t['StudentID'] ?></small>
                                </span>
                                <?php if ((int) $t['UnreadCount'] > 0): ?>
                                    <span class="chat-unread-badge"><?= (int) $t['UnreadCount'] ?></span>
                                <?php endif; ?>
                                <?php if (!empty($t['LastMessage'])): ?>
                                    <span class="chat-thread-preview">
                                        <?= htmlspecialchars(mb_strimwidth($t['LastMessage'], 0, 60, '…')) ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </aside>

            <section class="chat-main">
                <?php if ($selectedStudent): ?>
                    <div class="chat-main-header">
                        <h2><?= htmlspecialchars($selectedStudent['FirstName'] . ' ' . $selectedStudent['LastName']) ?></h2>
                        <span class="text-muted">Student ID #<?= (int) $selectedStudent['StudentID'] ?></span>
                    </div>

                    <div id="chat-app"
                         class="student-chat-app admin-chat-app"
                         data-api-base="<?= htmlspecialchars($apiBase) ?>"
                         data-presence-url="<?= htmlspecialchars($apiBase . '/chat-presence.php') ?>"
                         data-queue-url="<?= htmlspecialchars($basePath . '/public/chat-queue.php') ?>"
                         data-auth-endpoint="<?= htmlspecialchars($apiBase . '/pusher-auth.php') ?>"
                         data-pusher-key="<?= htmlspecialchars($pusher['key']) ?>"
                         data-pusher-cluster="<?= htmlspecialchars($pusher['cluster']) ?>"
                         data-channel="<?= htmlspecialchars(chatChannelName($selectedId)) ?>"
                         data-inbox-channel="<?= htmlspecialchars(adminInboxChannel()) ?>"
                         data-csrf="<?= htmlspecialchars(csrfToken()) ?>"
                         data-student-id="<?= $selectedId ?>"
                         data-role="admin">

                        <div id="chat-messages" class="chat-messages student-chat-messages admin-chat-messages <?= empty($messages) ? 'is-empty' : 'has-messages' ?>">
                            <?php if (empty($messages)): ?>
                                <div class="chat-empty" id="chat-empty">No messages in this conversation yet.</div>
                            <?php else: ?>
                                <?php include __DIR__ . '/../includes/chat-messages.php'; ?>
                            <?php endif; ?>
                        </div>

                        <form id="chat-form" class="chat-compose student-chat-compose" method="POST" action="">
                            <textarea id="chat-input" name="content" rows="2"
                                      placeholder="Type your reply…" maxlength="2000" required></textarea>
                            <button type="submit" class="student-chat-send" aria-label="Send message">
                                <?= studentDashboardIcon('send') ?>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="chat-placeholder">
                        <?php if (empty($threads)): ?>
                            <p>When a student sends a message, their conversation will appear here.</p>
                        <?php else: ?>
                            <p>Select a conversation from the left to view and reply.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
        </div>
    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js"></script>
    <script src="../../public/assets/js/chat.js"></script>
</body>
</html>
