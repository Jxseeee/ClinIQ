<?php
require __DIR__ . '/../config/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../public/index.php");
    exit;
}
verifyCsrfToken();

$id = $_POST['id'] ?? null;
if (!$id) {
    header("Location: ../../public/index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT StudentID FROM Students WHERE StudentID = ?");
$stmt->execute([$id]);

if (!$stmt->fetch()) {
    header("Location: ../../public/index.php?error=notfound");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM Students WHERE StudentID = ?");
$stmt->execute([$id]);

header("Location: ../../public/index.php?success=deleted");
exit;
