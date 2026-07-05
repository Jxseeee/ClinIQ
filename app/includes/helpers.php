<?php

function fetchStudentFullProfile(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM Students WHERE StudentID = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) return null;

    $stmt = $pdo->prepare("SELECT * FROM Guardians WHERE StudentID = ? AND GuardianType = 'guardian1'");
    $stmt->execute([$id]);
    $g1 = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $stmt = $pdo->prepare("SELECT * FROM Guardians WHERE StudentID = ? AND GuardianType = 'guardian2'");
    $stmt->execute([$id]);
    $g2 = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $stmt = $pdo->prepare("SELECT * FROM MedicalHistory WHERE StudentID = ?");
    $stmt->execute([$id]);
    $medHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $medMap = [];
    foreach ($medHistory as $row) {
        $medMap[$row['Illness']] = $row;
    }

    return [
        'student'    => $student,
        'g1'         => $g1,
        'g2'         => $g2,
        'medHistory' => $medHistory,
        'medMap'     => $medMap,
    ];
}

function fetchAnnouncements(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT a.*, ad.FullName AS AuthorName
         FROM Announcements a
         LEFT JOIN Admins ad ON a.AdminID = ad.AdminID
         ORDER BY a.CreatedAt DESC"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function validateEmail(string $email, array &$errors): void
{
    $allowed_domain = 'university.edu.ph';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } elseif ($email !== '' && !str_ends_with(strtolower($email), '@' . $allowed_domain)) {
        $errors[] = "Email must be a @$allowed_domain address.";
    }
}

function generateTempPassword(int $length = 10): string
{
    // Unambiguous characters only (no 0/O, 1/l/I) so the password is easy to relay.
    $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $out;
}

function displayValue(?string $val): string
{
    return htmlspecialchars($val ?? '') ?: '—';
}

function formatChatMessage(array $row, string $viewerRole): array
{
    $senderName = $row['SenderRole'] === 'admin'
        ? ($row['AdminName'] ?? 'Admin')
        : trim(($row['StudentFirstName'] ?? '') . ' ' . ($row['StudentLastName'] ?? ''));

    return [
        'message_id'  => (int) $row['MessageID'],
        'student_id'  => (int) $row['StudentID'],
        'sender_role' => $row['SenderRole'],
        'sender_name' => $senderName ?: ($row['SenderRole'] === 'admin' ? 'Admin' : 'Student'),
        'content'     => $row['Content'],
        'created_at'  => $row['CreatedAt'],
        'is_mine'     => $row['SenderRole'] === $viewerRole,
    ];
}

function fetchChatMessages(PDO $pdo, int $studentId): array
{
    $stmt = $pdo->prepare(
        "SELECT m.*, s.FirstName AS StudentFirstName, s.LastName AS StudentLastName, a.FullName AS AdminName
         FROM Messages m
         JOIN Students s ON s.StudentID = m.StudentID
         LEFT JOIN Admins a ON a.AdminID = m.AdminID
         WHERE m.StudentID = ?
         ORDER BY m.CreatedAt ASC, m.MessageID ASC"
    );
    $stmt->execute([$studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markChatMessagesRead(PDO $pdo, int $studentId, string $readerRole): void
{
    if ($readerRole === 'student') {
        $stmt = $pdo->prepare(
            "UPDATE Messages SET IsRead = 1 WHERE StudentID = ? AND SenderRole = 'admin' AND IsRead = 0"
        );
    } else {
        $stmt = $pdo->prepare(
            "UPDATE Messages SET IsRead = 1 WHERE StudentID = ? AND SenderRole = 'student' AND IsRead = 0"
        );
    }
    $stmt->execute([$studentId]);
}

function sendChatMessage(PDO $pdo, int $studentId, string $senderRole, ?int $adminId, string $content): array
{
    $stmt = $pdo->prepare(
        "INSERT INTO Messages (StudentID, SenderRole, AdminID, Content) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$studentId, $senderRole, $adminId, $content]);

    $messageId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare(
        "SELECT m.*, s.FirstName AS StudentFirstName, s.LastName AS StudentLastName, a.FullName AS AdminName
         FROM Messages m
         JOIN Students s ON s.StudentID = m.StudentID
         LEFT JOIN Admins a ON a.AdminID = m.AdminID
         WHERE m.MessageID = ?"
    );
    $stmt->execute([$messageId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function fetchChatThreads(PDO $pdo): array
{
    $stmt = $pdo->query(
        "SELECT s.StudentID, s.FirstName, s.LastName,
                SUM(m.SenderRole = 'student' AND m.IsRead = 0) AS UnreadCount,
                MAX(m.CreatedAt) AS LastActivity,
                (SELECT Content FROM Messages m2
                 WHERE m2.StudentID = s.StudentID
                 ORDER BY m2.CreatedAt DESC, m2.MessageID DESC LIMIT 1) AS LastMessage
         FROM Messages m
         JOIN Students s ON s.StudentID = m.StudentID
         GROUP BY s.StudentID, s.FirstName, s.LastName
         ORDER BY LastActivity DESC"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countUnreadChatForStudent(PDO $pdo, int $studentId): int
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM Messages WHERE StudentID = ? AND SenderRole = 'admin' AND IsRead = 0"
    );
    $stmt->execute([$studentId]);
    return (int) $stmt->fetchColumn();
}

function countUnreadChatForAdmin(PDO $pdo): int
{
    $stmt = $pdo->query(
        "SELECT COUNT(*) FROM Messages WHERE SenderRole = 'student' AND IsRead = 0"
    );
    return (int) $stmt->fetchColumn();
}

function currentChatSessionId(): string
{
    return session_id();
}

function currentChatUser(): array
{
    $user = currentUser();
    return [
        'role' => $user['role'],
        'id'   => (int) $user['id'],
    ];
}

function chatReturnPath(): string
{
    return ($_SESSION['role'] ?? '') === 'admin'
        ? getBasePath() . '/app/admin/messages.php'
        : getBasePath() . '/students/messages.php';
}

function cleanupChatPresence(PDO $pdo): void
{
    $timeout = CHAT_SESSION_TIMEOUT_SECONDS;
    $pdo->exec(
        "DELETE FROM ActiveChatSessions
         WHERE LastSeenAt < (NOW() - INTERVAL {$timeout} SECOND)"
    );
    $pdo->exec(
        "DELETE q FROM ChatQueue q
         INNER JOIN ActiveChatSessions a ON a.SessionID = q.SessionID"
    );
    $pdo->exec(
        "DELETE FROM ChatQueue
         WHERE LastSeenAt < (NOW() - INTERVAL {$timeout} SECOND)"
    );
}

function activeChatCount(PDO $pdo): int
{
    cleanupChatPresence($pdo);
    return (int) $pdo->query("SELECT COUNT(*) FROM ActiveChatSessions")->fetchColumn();
}

function isCurrentChatSessionActive(PDO $pdo): bool
{
    cleanupChatPresence($pdo);
    $stmt = $pdo->prepare("SELECT 1 FROM ActiveChatSessions WHERE SessionID = ?");
    $stmt->execute([currentChatSessionId()]);
    return (bool) $stmt->fetchColumn();
}

function touchChatSession(PDO $pdo): void
{
    $chatUser = currentChatUser();
    $stmt = $pdo->prepare(
        "INSERT INTO ActiveChatSessions (SessionID, UserRole, UserID, LastSeenAt)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE UserRole = VALUES(UserRole), UserID = VALUES(UserID), LastSeenAt = NOW()"
    );
    $stmt->execute([currentChatSessionId(), $chatUser['role'], $chatUser['id']]);

    $stmt = $pdo->prepare("DELETE FROM ChatQueue WHERE SessionID = ?");
    $stmt->execute([currentChatSessionId()]);
}

function addCurrentChatSessionToQueue(PDO $pdo): void
{
    $chatUser = currentChatUser();
    $stmt = $pdo->prepare(
        "INSERT INTO ChatQueue (SessionID, UserRole, UserID, LastSeenAt)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE UserRole = VALUES(UserRole), UserID = VALUES(UserID), LastSeenAt = NOW()"
    );
    $stmt->execute([currentChatSessionId(), $chatUser['role'], $chatUser['id']]);
}

function chatQueuePosition(PDO $pdo): ?int
{
    cleanupChatPresence($pdo);
    $stmt = $pdo->prepare("SELECT QueueID FROM ChatQueue WHERE SessionID = ?");
    $stmt->execute([currentChatSessionId()]);
    $queueId = $stmt->fetchColumn();

    if (!$queueId) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ChatQueue WHERE QueueID <= ?");
    $stmt->execute([$queueId]);
    return (int) $stmt->fetchColumn();
}

function isCurrentChatSessionFirstInQueue(PDO $pdo): bool
{
    cleanupChatPresence($pdo);
    $stmt = $pdo->query("SELECT SessionID FROM ChatQueue ORDER BY QueueID ASC LIMIT 1");
    return $stmt->fetchColumn() === currentChatSessionId();
}

function admitCurrentChatSessionIfPossible(PDO $pdo): bool
{
    cleanupChatPresence($pdo);

    if (isCurrentChatSessionActive($pdo)) {
        touchChatSession($pdo);
        return true;
    }

    if (activeChatCount($pdo) < CHAT_ACTIVE_LIMIT) {
        $position = chatQueuePosition($pdo);
        if ($position === null || isCurrentChatSessionFirstInQueue($pdo)) {
            touchChatSession($pdo);
            return true;
        }
    }

    addCurrentChatSessionToQueue($pdo);
    return false;
}

function requireChatCapacity(PDO $pdo): void
{
    if (admitCurrentChatSessionIfPossible($pdo)) {
        return;
    }

    header('Location: ' . getBasePath() . '/public/chat-queue.php');
    exit;
}

function chatQueueStatus(PDO $pdo): array
{
    $allowed = admitCurrentChatSessionIfPossible($pdo);
    return [
        'allowed'      => $allowed,
        'active_count' => activeChatCount($pdo),
        'limit'        => CHAT_ACTIVE_LIMIT,
        'position'     => $allowed ? null : chatQueuePosition($pdo),
        'return_url'   => chatReturnPath(),
    ];
}
