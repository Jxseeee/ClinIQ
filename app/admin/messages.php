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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Messages</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-nav.php'; ?>
    <div class="container container-wide">
        <h1>Student Messages</h1>
        <p class="text-muted">Reply to student inquiries in real time.</p>
        <div class="chat-capacity">
            Active chat users: <strong><?= $activeChatCount ?></strong> / <?= CHAT_ACTIVE_LIMIT ?>
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

        <div class="chat-layout card">
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

                        <div id="chat-messages" class="chat-messages">
                            <?php if (empty($messages)): ?>
                                <div class="chat-empty" id="chat-empty">No messages in this conversation yet.</div>
                            <?php else: ?>
                                <?php include __DIR__ . '/../includes/chat-messages.php'; ?>
                            <?php endif; ?>
                        </div>

                        <form id="chat-form" class="chat-compose" method="POST" action="">
                            <textarea id="chat-input" name="content" rows="2"
                                      placeholder="Type your reply…" maxlength="2000" required></textarea>
                            <button type="submit" class="btn btn-primary">Send</button>
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
    <script src="../../public/assets/js/main.js"></script>
    <script src="../../public/assets/js/chat.js"></script>
</body>
</html>
