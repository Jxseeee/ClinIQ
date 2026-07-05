<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$studentId = $_GET['id'] ?? null;
if ($studentId === null || !ctype_digit((string) $studentId)) {
    header('Location: ../../public/index.php');
    exit;
}

$data = fetchStudentFullProfile($pdo, (int) $studentId);
if (!$data || empty($data['student']['ConsentImagePath'])) {
    header('Location: view-student.php?id=' . urlencode((string) $studentId));
    exit;
}

$relativePath = ltrim($data['student']['ConsentImagePath'], '/');
$fullPath = dirname(__DIR__, 2) . '/' . $relativePath;

if (!is_file($fullPath)) {
    header('Location: view-student.php?id=' . urlencode((string) $studentId));
    exit;
}

$mime = mime_content_type($fullPath) ?: 'application/octet-stream';
if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
    http_response_code(415);
    exit('Unsupported file type.');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
readfile($fullPath);
exit;
