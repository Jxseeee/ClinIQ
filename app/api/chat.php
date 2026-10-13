<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/pusher.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Nurses answer chats like admins, once they have replaced the default password.
if (nurseAccessBlocked()) {
    http_response_code(403);
    echo json_encode(['error' => 'Change your default password before using chat.']);
    exit;
}

// Students must change their default password and complete the clinic form before using chat.
if ($_SESSION['role'] === 'student' && (studentMustChangePassword() || !studentClinicFormComplete())) {
    http_response_code(403);
    echo json_encode(['error' => 'Complete your clinic form before using this feature.']);
    exit;
}

$user = currentUser();
$role = $user['role'];
$userId = (int) $user['id'];
$isClinicStaff = in_array($role, CLINIC_STAFF_ROLES, true);

function resolveStudentId(string $role, int $userId): ?int
{
    if ($role === 'student') {
        return $userId;
    }

    $studentId = $_GET['student_id'] ?? $_POST['student_id'] ?? null;
    if ($studentId === null || $studentId === '' || !ctype_digit((string) $studentId)) {
        return null;
    }
    return (int) $studentId;
}

function studentExists(PDO $pdo, int $studentId): bool
{
    $stmt = $pdo->prepare('SELECT StudentID FROM Students WHERE StudentID = ?');
    $stmt->execute([$studentId]);
    return (bool) $stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'messages';

    if ($action === 'threads' && $isClinicStaff) {
        echo json_encode(['threads' => fetchChatThreads($pdo)]);
        exit;
    }

    $studentId = resolveStudentId($role, $userId);
    if ($studentId === null) {
        http_response_code(400);
        echo json_encode(['error' => 'student_id is required']);
        exit;
    }

    if (!studentExists($pdo, $studentId)) {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found']);
        exit;
    }

    if ($role === 'student' && $studentId !== $userId) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    // Only the chat page's own fetches (which send this header) mark messages read; other sites can't add it.
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        markChatMessagesRead($pdo, $studentId, $role);
    }
    $rows = fetchChatMessages($pdo, $studentId);
    $messages = array_map(fn($row) => formatChatMessage($row, $role), $rows);
    $conversation = fetchChatConversation($pdo, $studentId);

    echo json_encode([
        'messages'  => $messages,
        'conversation_status' => $conversation['Status'] ?? 'open',
        'pusher'    => pusherPublicConfig(),
        'channel'   => chatChannelName($studentId),
        'student_id'=> $studentId,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $content = trim($_POST['content'] ?? '');
    if ($content === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Message cannot be empty']);
        exit;
    }
    if (mb_strlen($content) > 2000) {
        http_response_code(400);
        echo json_encode(['error' => 'Message is too long (max 2000 characters)']);
        exit;
    }

    $studentId = resolveStudentId($role, $userId);
    if ($studentId === null) {
        http_response_code(400);
        echo json_encode(['error' => 'student_id is required']);
        exit;
    }

    if (!studentExists($pdo, $studentId)) {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found']);
        exit;
    }

    if ($role === 'student' && $studentId !== $userId) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    // Admins and nurses both write from the clinic side ('admin'); the nurse is recorded in NurseID.
    $senderRole = $isClinicStaff ? 'admin' : 'student';
    $adminId = $role === 'admin' ? $userId : null;
    $nurseId = $role === 'nurse' ? $userId : null;
    $autoReplyRow = null;
    $row = sendChatMessage($pdo, $studentId, $senderRole, $adminId, $content, $nurseId, $autoReplyRow);
    $message = formatChatMessage($row, $role);
    $autoReply = $autoReplyRow !== null ? formatChatMessage($autoReplyRow, $role) : null;
    $conversation = fetchChatConversation($pdo, $studentId);

    broadcastChatMessage($studentId, $message);
    if ($autoReply !== null) {
        broadcastChatMessage($studentId, $autoReply);
    }

    echo json_encode([
        'message' => $message,
        'auto_reply' => $autoReply,
        'conversation_status' => $conversation['Status'] ?? 'open',
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
