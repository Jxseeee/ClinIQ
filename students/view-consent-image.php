<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();

$studentId = (int) $_SESSION['user_id'];
$data = fetchStudentFullProfile($pdo, $studentId);

if (!$data || !streamConsentImage($data['student']['ConsentImagePath'] ?? null)) {
    header('Location: profile.php');
}
exit;
