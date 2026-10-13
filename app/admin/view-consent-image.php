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

if (!$data || !streamConsentImage($data['student']['ConsentImagePath'] ?? null)) {
    header('Location: view-student.php?id=' . urlencode((string) $studentId));
}
exit;
