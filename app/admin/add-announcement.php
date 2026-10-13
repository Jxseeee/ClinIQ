<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireClinicStaff();

$errors = [];
$adminPageTitle = 'New Announcement';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '')   $errors[] = 'Title is required.';
    if ($content === '') $errors[] = 'Content is required.';
    validateMaxLengths(
        ['title' => $title, 'content' => $content],
        ['title' => ['Title', 200], 'content' => ['Content', 10000]],
        $errors
    );

    if (empty($errors)) {
        try {
            // An admin is recorded in AdminID; a nurse in NurseID (AdminID only references admins).
            if (isNurse()) {
                if (!databaseColumnExists($pdo, 'Announcements', 'NurseID')) {
                    throw new PDOException('Announcements.NurseID is missing; run add_nurse_accounts.sql.');
                }
                $stmt = $pdo->prepare("INSERT INTO Announcements (Title, Content, AdminID, NurseID) VALUES (?, ?, NULL, ?)");
            } else {
                $stmt = $pdo->prepare("INSERT INTO Announcements (Title, Content, AdminID) VALUES (?, ?, ?)");
            }
            $stmt->execute([$title, $content, (int) $_SESSION['user_id']]);
            auditLog($pdo, 'announcements', 'announcement.created', 'Posted announcement "' . mb_strimwidth($title, 0, 120, '...') . '".', ['subject_role' => null]);
            header("Location: announcements.php?success=created");
            exit;
        } catch (PDOException $error) {
            error_log('Announcement creation failed: ' . $error->getMessage());
            $errors[] = 'The announcement could not be published. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Announcement</title>
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>
    <div class="dashboard-content-card dashboard-narrow-card">
        <h1>New Announcement</h1>

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
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title"
                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" maxlength="200" required>
                </div>
                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" rows="6" maxlength="10000" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Publish</button>
                    <a href="announcements.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
