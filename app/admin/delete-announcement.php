<?php
require __DIR__ . '/../config/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: announcements.php");
    exit;
}
verifyCsrfToken();

$id = $_POST['id'] ?? null;
if (!$id) {
    header("Location: announcements.php");
    exit;
}

$stmt = $pdo->prepare("SELECT AnnouncementID FROM Announcements WHERE AnnouncementID = ?");
$stmt->execute([$id]);

if (!$stmt->fetch()) {
    header("Location: announcements.php");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM Announcements WHERE AnnouncementID = ?");
$stmt->execute([$id]);

header("Location: announcements.php?success=deleted");
exit;
