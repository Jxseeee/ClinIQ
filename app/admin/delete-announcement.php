<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
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

$stmt = $pdo->prepare("SELECT AnnouncementID, Title FROM Announcements WHERE AnnouncementID = ?");
$stmt->execute([$id]);
$deletedAnnouncement = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deletedAnnouncement) {
    header("Location: announcements.php");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM Announcements WHERE AnnouncementID = ?");
$stmt->execute([$id]);
auditLog($pdo, 'announcements', 'announcement.deleted', 'Deleted announcement "' . mb_strimwidth($deletedAnnouncement['Title'], 0, 120, '...') . '".', ['subject_role' => null]);

header("Location: announcements.php?success=deleted");
exit;
