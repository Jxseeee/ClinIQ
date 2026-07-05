<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$announcements = fetchAnnouncements($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-nav.php'; ?>
    <div class="container">
        <div class="header-row">
            <h1>Announcements</h1>
            <a href="add-announcement.php" class="btn btn-primary">+ New Announcement</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php
                    $msg = match ($_GET['success']) {
                        'created' => 'Announcement created.',
                        'updated' => 'Announcement updated.',
                        'deleted' => 'Announcement deleted.',
                        default   => 'Done.',
                    };
                    echo htmlspecialchars($msg);
                ?>
            </div>
        <?php endif; ?>

        <?php if (count($announcements) > 0): ?>
            <?php foreach ($announcements as $a): ?>
                <div class="announcement-card">
                    <div class="announcement-header">
                        <h2><?= htmlspecialchars($a['Title']) ?></h2>
                        <div class="actions">
                            <a href="edit-announcement.php?id=<?= $a['AnnouncementID'] ?>" class="btn btn-warning">Edit</a>
                            <form method="POST" action="delete-announcement.php" class="delete-form">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $a['AnnouncementID'] ?>">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                    <p class="announcement-meta">
                        By <?= htmlspecialchars($a['AuthorName'] ?? 'Unknown') ?>
                        on <?= date('M d, Y \a\t g:i A', strtotime($a['CreatedAt'])) ?>
                    </p>
                    <div class="announcement-body"><?= nl2br(htmlspecialchars($a['Content'])) ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card empty-state">
                <p>No announcements yet. Click <strong>+ New Announcement</strong> to create one.</p>
            </div>
        <?php endif; ?>
    </div>
    <script src="../../public/assets/js/main.js"></script>
</body>
</html>
