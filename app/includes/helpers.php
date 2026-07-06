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

function databaseTableExists(PDO $pdo, string $table): bool
{
    static $cache = [];

    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $cache[$table] = (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        $cache[$table] = false;
    }

    return $cache[$table];
}

function createNotification(PDO $pdo, string $targetRole, ?int $targetUserId, string $type, string $title, string $body = '', string $link = ''): void
{
    if (!databaseTableExists($pdo, 'Notifications')) {
        return;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO Notifications (TargetRole, TargetUserID, Type, Title, Body, Link) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$targetRole, $targetUserId, $type, $title, $body ?: null, $link ?: null]);
}

function countUnreadNotifications(PDO $pdo, string $targetRole, ?int $targetUserId = null): int
{
    if (!databaseTableExists($pdo, 'Notifications')) {
        return 0;
    }

    if ($targetRole === 'admin') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Notifications WHERE TargetRole = 'admin' AND IsRead = 0");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Notifications WHERE TargetRole = 'student' AND TargetUserID = ? AND IsRead = 0");
        $stmt->execute([$targetUserId]);
    }
    return (int) $stmt->fetchColumn();
}

function fetchNotifications(PDO $pdo, string $targetRole, ?int $targetUserId = null, int $limit = 8): array
{
    if (!databaseTableExists($pdo, 'Notifications')) {
        return [];
    }

    if ($targetRole === 'admin') {
        $stmt = $pdo->prepare(
            "SELECT * FROM Notifications
             WHERE TargetRole = 'admin'
             ORDER BY CreatedAt DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare(
            "SELECT * FROM Notifications
             WHERE TargetRole = 'student' AND TargetUserID = ?
             ORDER BY CreatedAt DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute([$targetUserId]);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markNotificationsRead(PDO $pdo, string $targetRole, ?int $targetUserId = null): void
{
    if (!databaseTableExists($pdo, 'Notifications')) {
        return;
    }

    if ($targetRole === 'admin') {
        $stmt = $pdo->prepare("UPDATE Notifications SET IsRead = 1 WHERE TargetRole = 'admin' AND IsRead = 0");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("UPDATE Notifications SET IsRead = 1 WHERE TargetRole = 'student' AND TargetUserID = ? AND IsRead = 0");
        $stmt->execute([$targetUserId]);
    }
}

function fetchClinicVisits(PDO $pdo, ?int $studentId = null, int $limit = 0): array
{
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return [];
    }

    $sql = "SELECT v.*, s.FirstName, s.LastName, a.FullName AS AdminName
            FROM ClinicVisits v
            JOIN Students s ON s.StudentID = v.StudentID
            LEFT JOIN Admins a ON a.AdminID = v.AdminID";
    $params = [];

    if ($studentId !== null) {
        $sql .= " WHERE v.StudentID = ?";
        $params[] = $studentId;
    }

    $sql .= " ORDER BY v.CreatedAt DESC";
    if ($limit > 0) {
        $sql .= " LIMIT " . (int) $limit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createClinicVisit(PDO $pdo, int $studentId, int $adminId, array $data): void
{
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO ClinicVisits
         (StudentID, AdminID, Complaint, Vitals, Assessment, Treatment, Status, Disposition, FollowUpDate)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $studentId,
        $adminId,
        trim($data['complaint'] ?? ''),
        trim($data['vitals'] ?? '') ?: null,
        trim($data['assessment'] ?? '') ?: null,
        trim($data['treatment'] ?? '') ?: null,
        $data['status'] ?? 'completed',
        trim($data['disposition'] ?? '') ?: null,
        trim($data['follow_up_date'] ?? '') ?: null,
    ]);

    createNotification(
        $pdo,
        'student',
        $studentId,
        'clinic_visit',
        'Clinic visit recorded',
        'Your clinic visit record has been updated.',
        getBasePath() . '/students/profile.php'
    );
}

function countClinicVisits(PDO $pdo): int
{
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return 0;
    }

    return (int) $pdo->query("SELECT COUNT(*) FROM ClinicVisits")->fetchColumn();
}

function fetchAppointments(PDO $pdo, ?string $status = null, ?int $studentId = null): array
{
    if (!databaseTableExists($pdo, 'Appointments')) {
        return [];
    }

    $sql = "SELECT ap.*, s.FirstName, s.LastName, a.FullName AS AdminName
            FROM Appointments ap
            JOIN Students s ON s.StudentID = ap.StudentID
            LEFT JOIN Admins a ON a.AdminID = ap.HandledByAdminID";
    $where = [];
    $params = [];

    if ($status !== null) {
        $where[] = 'ap.Status = ?';
        $params[] = $status;
    }
    if ($studentId !== null) {
        $where[] = 'ap.StudentID = ?';
        $params[] = $studentId;
    }
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= " ORDER BY ap.RequestedFor DESC, ap.CreatedAt DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countAppointments(PDO $pdo, string $status): int
{
    if (!databaseTableExists($pdo, 'Appointments')) {
        return 0;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Appointments WHERE Status = ?");
    $stmt->execute([$status]);
    return (int) $stmt->fetchColumn();
}

function createAppointment(PDO $pdo, int $studentId, string $requestedFor, string $reason): void
{
    if (!databaseTableExists($pdo, 'Appointments')) {
        return;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO Appointments (StudentID, RequestedFor, Reason) VALUES (?, ?, ?)"
    );
    $stmt->execute([$studentId, $requestedFor, $reason]);

    createNotification(
        $pdo,
        'admin',
        null,
        'appointment',
        'New appointment request',
        'A student requested a clinic appointment.',
        getBasePath() . '/app/admin/appointments.php'
    );
}

function updateAppointmentStatus(PDO $pdo, int $appointmentId, string $status, int $adminId, string $notes = ''): void
{
    if (!databaseTableExists($pdo, 'Appointments')) {
        return;
    }

    $stmt = $pdo->prepare(
        "UPDATE Appointments SET Status = ?, AdminNotes = ?, HandledByAdminID = ? WHERE AppointmentID = ?"
    );
    $stmt->execute([$status, $notes ?: null, $adminId, $appointmentId]);

    $stmt = $pdo->prepare("SELECT StudentID FROM Appointments WHERE AppointmentID = ?");
    $stmt->execute([$appointmentId]);
    $studentId = (int) $stmt->fetchColumn();

    if ($studentId > 0) {
        createNotification(
            $pdo,
            'student',
            $studentId,
            'appointment',
            'Appointment ' . $status,
            'Your clinic appointment request was marked as ' . $status . '.',
            getBasePath() . '/students/appointments.php'
        );
    }
}

function ensureChatConversation(PDO $pdo, int $studentId): void
{
    if (!databaseTableExists($pdo, 'ChatConversations')) {
        return;
    }

    $stmt = $pdo->prepare(
        "INSERT IGNORE INTO ChatConversations (StudentID, Status, LastMessageAt) VALUES (?, 'open', NOW())"
    );
    $stmt->execute([$studentId]);
}

function fetchChatConversation(PDO $pdo, int $studentId): array
{
    if (!databaseTableExists($pdo, 'ChatConversations')) {
        return ['Status' => 'open'];
    }

    ensureChatConversation($pdo, $studentId);
    $stmt = $pdo->prepare("SELECT * FROM ChatConversations WHERE StudentID = ?");
    $stmt->execute([$studentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['Status' => 'open'];
}

function updateChatConversationStatus(PDO $pdo, int $studentId, string $status, ?int $adminId = null): void
{
    if (!databaseTableExists($pdo, 'ChatConversations')) {
        return;
    }

    ensureChatConversation($pdo, $studentId);
    $stmt = $pdo->prepare(
        "UPDATE ChatConversations
         SET Status = ?, ResolvedAt = IF(? = 'resolved', NOW(), NULL), ResolvedByAdminID = IF(? = 'resolved', ?, NULL)
         WHERE StudentID = ?"
    );
    $stmt->execute([$status, $status, $status, $adminId, $studentId]);
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
    ensureChatConversation($pdo, $studentId);

    $stmt = $pdo->prepare(
        "INSERT INTO Messages (StudentID, SenderRole, AdminID, Content) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$studentId, $senderRole, $adminId, $content]);

    if (databaseTableExists($pdo, 'ChatConversations')) {
        $stmt = $pdo->prepare(
            "UPDATE ChatConversations
             SET Status = 'open', LastMessageAt = NOW(), ResolvedAt = NULL, ResolvedByAdminID = NULL
             WHERE StudentID = ?"
        );
        $stmt->execute([$studentId]);
    }

    if ($senderRole === 'student') {
        createNotification(
            $pdo,
            'admin',
            null,
            'chat',
            'New student message',
            'A student sent a new clinic message.',
            getBasePath() . '/app/admin/messages.php?student_id=' . $studentId
        );
    } else {
        createNotification(
            $pdo,
            'student',
            $studentId,
            'chat',
            'New clinic message',
            'The clinic admin sent you a message.',
            getBasePath() . '/students/messages.php'
        );
    }

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
    $hasConversations = databaseTableExists($pdo, 'ChatConversations');
    $statusSelect = $hasConversations ? "COALESCE(c.Status, 'open')" : "'open'";
    $statusJoin = $hasConversations ? "LEFT JOIN ChatConversations c ON c.StudentID = s.StudentID" : "";
    $statusGroup = $hasConversations ? ", c.Status" : "";

    $stmt = $pdo->query(
        "SELECT s.StudentID, s.FirstName, s.LastName,
                SUM(m.SenderRole = 'student' AND m.IsRead = 0) AS UnreadCount,
                MAX(m.CreatedAt) AS LastActivity,
                {$statusSelect} AS ConversationStatus,
                (SELECT Content FROM Messages m2
                 WHERE m2.StudentID = s.StudentID
                 ORDER BY m2.CreatedAt DESC, m2.MessageID DESC LIMIT 1) AS LastMessage
         FROM Messages m
         JOIN Students s ON s.StudentID = m.StudentID
         {$statusJoin}
         GROUP BY s.StudentID, s.FirstName, s.LastName{$statusGroup}
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
