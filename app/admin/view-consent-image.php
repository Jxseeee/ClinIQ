<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireClinicStaff();

$studentId = $_GET['id'] ?? null;
if ($studentId === null || !ctype_digit((string) $studentId)) {
    header('Location: ../../public/index.php');
    exit;
}

$data = fetchStudentFullProfile($pdo, (int) $studentId);
$download = ($_GET['download'] ?? '') === '1';
$downloadName = $download ? 'guardian-id-' . (int) $studentId : null;

if (!$data || !streamConsentImage($data['student']['ConsentImagePath'] ?? null, $downloadName)) {
    header('Location: view-student.php?id=' . urlencode((string) $studentId));
    exit;
}

if ($download) {
    auditLog(
        $pdo,
        'clinic_form',
        'form.guardian_id_downloaded',
        "Downloaded the student's uploaded parent/guardian ID image.",
        ['subject_role' => 'student', 'subject_id' => (int) $studentId]
    );
}
exit;
