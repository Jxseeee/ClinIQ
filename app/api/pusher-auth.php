<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/pusher.php';

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

$socketId    = $_POST['socket_id'] ?? '';
$channelName = $_POST['channel_name'] ?? '';

if ($socketId === '' || $channelName === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing socket_id or channel_name']);
    exit;
}

$user = currentUser();
if (!canAccessChatChannel($channelName, $user['role'], (int) $user['id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$pusher = pusherClient();
if (!$pusher) {
    http_response_code(503);
    echo json_encode(['error' => 'Pusher is not configured']);
    exit;
}

echo $pusher->authorizeChannel($channelName, $socketId);
