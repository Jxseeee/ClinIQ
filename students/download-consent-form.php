<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
require_once __DIR__ . '/../app/includes/student-form-pdf.php';
requireStudent();

$studentId = (int) $_SESSION['user_id'];
$data = fetchStudentFullProfile($pdo, $studentId);
if (!$data) {
    header('Location: ../public/logout.php');
    exit;
}

downloadStudentPdf(
    studentConsentFormHtml($data['student'], $data['g1']),
    'consent-form-' . $studentId . '.pdf'
);
