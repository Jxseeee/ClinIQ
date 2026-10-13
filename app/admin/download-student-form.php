<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/student-form-pdf.php';
requireAdmin();

$studentId = $_GET['id'] ?? null;
$form = (string) ($_GET['form'] ?? '');
if ($studentId === null || !ctype_digit((string) $studentId) || !in_array($form, ['clinic', 'consent'], true)) {
    header('Location: students.php');
    exit;
}

$data = fetchStudentFullProfile($pdo, (int) $studentId);
if (!$data) {
    header('Location: students.php?error=notfound');
    exit;
}

$html = $form === 'clinic'
    ? studentClinicFormHtml($data['student'], $data['g1'], $data['g2'], $data['medHistory'])
    : studentConsentFormHtml($data['student'], $data['g1']);

auditLog(
    $pdo,
    'clinic_form',
    'form.pdf_downloaded',
    $form === 'clinic'
        ? "Downloaded the student's clinic form PDF (Form 01)."
        : "Downloaded the student's consent form PDF (Form 02).",
    ['subject_role' => 'student', 'subject_id' => (int) $studentId]
);
downloadStudentPdf($html, $form . '-form-' . (int) $studentId . '.pdf');
