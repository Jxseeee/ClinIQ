<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

verifyCsrfToken();

$user = currentUser();
$role = $user['role'];
$userId = (int) $user['id'];

markNotificationsRead($pdo, $role, $role === 'student' ? $userId : null);

echo json_encode(['ok' => true]);
exit;
