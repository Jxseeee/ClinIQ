<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/pusher.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();
requireChatCapacity($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_chat_status') {
    verifyCsrfToken();
    $studentIdForStatus = $_POST['student_id'] ?? '';
    $status = $_POST['status'] ?? '';

    if (ctype_digit((string) $studentIdForStatus) && in_array($status, ['open', 'resolved'], true)) {
        updateChatConversationStatus($pdo, (int) $studentIdForStatus, $status, (int) $_SESSION['user_id']);
    }

    header('Location: messages.php?student_id=' . urlencode((string) $studentIdForStatus));
    exit;
}

$activeChatCount = activeChatCount($pdo);
$threads = fetchChatThreads($pdo);
$selectedId = isset($_GET['student_id']) && ctype_digit((string) $_GET['student_id'])
    ? (int) $_GET['student_id']
    : null;

$messages = [];
$selectedStudent = null;
$conversation = ['Status' => 'open'];

if ($selectedId !== null) {
    $stmt = $pdo->prepare('SELECT StudentID, FirstName, LastName FROM Students WHERE StudentID = ?');
    $stmt->execute([$selectedId]);
    $selectedStudent = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($selectedStudent) {
        markChatMessagesRead($pdo, $selectedId, 'admin');
        $rows = fetchChatMessages($pdo, $selectedId);
        $messages = array_map(fn($row) => formatChatMessage($row, 'admin'), $rows);
        $conversation = fetchChatConversation($pdo, $selectedId);
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
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.min.css">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page student-messages-body admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
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

        <div class="chat-layout card admin-chat-card <?= $selectedStudent ? 'has-selected-chat' : 'is-conversation-list' ?>">
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
                                <span class="chat-status-mini"><?= htmlspecialchars(ucfirst($t['ConversationStatus'] ?? 'open')) ?></span>
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
                        <a href="messages.php" class="admin-chat-back">&larr; Back</a>
                        <div>
                            <h2><?= htmlspecialchars($selectedStudent['FirstName'] . ' ' . $selectedStudent['LastName']) ?></h2>
                            <span class="text-muted">Student ID #<?= (int) $selectedStudent['StudentID'] ?></span>
                        </div>
                        <form method="POST" class="chat-status-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                            <input type="hidden" name="action" value="update_chat_status">
                            <input type="hidden" name="student_id" value="<?= (int) $selectedStudent['StudentID'] ?>">
                            <input type="hidden" name="status" value="<?= ($conversation['Status'] ?? 'open') === 'resolved' ? 'open' : 'resolved' ?>">
                            <button type="submit" class="btn btn-success">
                                <?= ($conversation['Status'] ?? 'open') === 'resolved' ? 'Reopen' : 'Mark Resolved' ?>
                            </button>
                            <span class="chat-status-pill"><?= htmlspecialchars(ucfirst($conversation['Status'] ?? 'open')) ?></span>
                        </form>
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
