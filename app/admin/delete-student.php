<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: students.php");
    exit;
}
verifyCsrfToken();

$id = $_POST['id'] ?? null;
if (!$id) {
    header("Location: students.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Students WHERE StudentID = ?");
$stmt->execute([$id]);
$deletedStudent = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deletedStudent) {
    header("Location: students.php?error=notfound");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM Students WHERE StudentID = ?");
$stmt->execute([$id]);

// Don't leave the student's uploaded personal data (guardian ID, profile photo) behind on disk.
deleteStoredConsentImage($deletedStudent['ConsentImagePath'] ?? null);
$profilePhotoPath = trim((string) ($deletedStudent['ProfileImagePath'] ?? ''));
if ($profilePhotoPath !== '') {
    deleteStoredProfileImage($profilePhotoPath);
}

$deletedName = $deletedStudent['FirstName'] . ' ' . $deletedStudent['LastName'];
auditLog(
    $pdo,
    'students',
    'student.deleted',
    'Deleted student account #' . (int) $deletedStudent['StudentID'] . ' (' . $deletedName . '), its clinic records, and uploaded files.',
    ['subject_role' => 'student', 'subject_id' => (int) $deletedStudent['StudentID'], 'subject_name' => $deletedName]
);

header("Location: students.php?success=deleted");
exit;
