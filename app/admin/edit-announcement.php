<?php
require __DIR__ . '/../config/auth.php';
requireAdmin();

$id = $_GET['id'] ?? ($_POST['id'] ?? null);
if (!$id) {
    header("Location: announcements.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Announcements WHERE AnnouncementID = ?");
$stmt->execute([$id]);
$ann = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ann) {
    header("Location: announcements.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '')   $errors[] = 'Title is required.';
    if ($content === '') $errors[] = 'Content is required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE Announcements SET Title = ?, Content = ? WHERE AnnouncementID = ?");
        $stmt->execute([$title, $content, $id]);
        header("Location: announcements.php?success=updated");
        exit;
    }

    $ann['Title']   = $title;
    $ann['Content'] = $content;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Announcement</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-nav.php'; ?>
    <div class="container">
        <h1>Edit Announcement</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin:0; padding-left:18px;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?= htmlspecialchars($ann['AnnouncementID']) ?>">
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title"
                           value="<?= htmlspecialchars($ann['Title']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" rows="6" required><?= htmlspecialchars($ann['Content']) ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Update</button>
                    <a href="announcements.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <script src="../../public/assets/js/main.js"></script>
</body>
</html>
