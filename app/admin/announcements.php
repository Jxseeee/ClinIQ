<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$announcements = fetchAnnouncements($pdo);
$adminPageTitle = 'Announcements';
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
<body class="student-dashboard-page admin-dashboard-page">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>
        <h1>Announcements</h1>

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

        <section class="student-announcements-panel admin-full-panel">
            <div class="dashboard-section-title">
                <span><?= studentDashboardIcon('megaphone') ?></span>
                <h2>Clinic Announcements</h2>
                <a href="add-announcement.php" class="btn btn-success admin-panel-action">+ New Announcement</a>
            </div>

            <?php if (count($announcements) > 0): ?>
                <div class="dashboard-announcement-list">
                    <?php foreach ($announcements as $a): ?>
                        <article class="dashboard-announcement-item admin-announcement-item">
                            <div class="announcement-round-icon"><?= studentDashboardIcon('file') ?></div>
                            <div>
                                <h3><?= htmlspecialchars($a['Title']) ?></h3>
                                <p><?= nl2br(htmlspecialchars($a['Content'])) ?></p>
                                <small class="admin-card-meta">
                                    By <?= htmlspecialchars($a['AuthorName'] ?? 'Unknown') ?>
                                </small>
                            </div>
                            <div class="admin-card-actions">
                                <time datetime="<?= htmlspecialchars($a['CreatedAt']) ?>">
                                    <?= date('M d, Y', strtotime($a['CreatedAt'])) ?>
                                    <small><?= date('g:i A', strtotime($a['CreatedAt'])) ?></small>
                                </time>
                                <a href="edit-announcement.php?id=<?= $a['AnnouncementID'] ?>" class="btn btn-warning">Edit</a>
                                <form method="POST" action="delete-announcement.php" class="delete-form">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $a['AnnouncementID'] ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="dashboard-empty">No announcements yet.</div>
            <?php endif; ?>
        </section>
    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js"></script>
</body>
</html>
