<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/pusher.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
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
