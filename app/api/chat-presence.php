<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SESSION['role'] === 'student' && studentMustChangePassword()) {
    http_response_code(403);
    echo json_encode(['error' => 'Password change required']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
}

if ($action === 'heartbeat' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isCurrentChatSessionActive($pdo)) {
        touchChatSession($pdo);
        echo json_encode(chatQueueStatus($pdo));
        exit;
    }

    http_response_code(409);
    echo json_encode(chatQueueStatus($pdo));
    exit;
}

if ($action === 'status') {
    echo json_encode(chatQueueStatus($pdo));
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
