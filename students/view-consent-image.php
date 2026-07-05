<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();

$studentId = (int) $_SESSION['user_id'];
$data = fetchStudentFullProfile($pdo, $studentId);
if (!$data || empty($data['student']['ConsentImagePath'])) {
    header('Location: profile.php');
    exit;
}

$relativePath = ltrim($data['student']['ConsentImagePath'], '/');
$fullPath = dirname(__DIR__) . '/' . $relativePath;

if (!is_file($fullPath)) {
    header('Location: profile.php');
    exit;
}

$mime = mime_content_type($fullPath) ?: 'application/octet-stream';
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    http_response_code(415);
    exit('Unsupported file type.');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
exit;
