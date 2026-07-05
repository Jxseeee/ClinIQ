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

$user = currentUser();
$role = $user['role'];
$userId = (int) $user['id'];

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

    if ($action === 'threads' && $role === 'admin') {
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

    markChatMessagesRead($pdo, $studentId, $role);
    $rows = fetchChatMessages($pdo, $studentId);
    $messages = array_map(fn($row) => formatChatMessage($row, $role), $rows);

    echo json_encode([
        'messages'  => $messages,
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

    $adminId = $role === 'admin' ? $userId : null;
    $row = sendChatMessage($pdo, $studentId, $role, $adminId, $content);
    $message = formatChatMessage($row, $role);

    broadcastChatMessage($studentId, $message);

    echo json_encode(['message' => $message]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
