<?php
require __DIR__ . '/../app/config/auth.php';
requireStudent();

$errors = [];
$forceChange = studentMustChangePassword();
$studentPageTitle = 'Change Password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPass = $_POST['old_password'] ?? '';
    $newPass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT Password, MustChangePassword FROM Students WHERE StudentID = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($oldPass, $student['Password'])) {
        $errors[] = 'Current password is incorrect.';
    }
    if (strlen($newPass) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }
    if (!preg_match('/\d/', $newPass)) {
        $errors[] = 'New password must contain at least one number.';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $newPass)) {
        $errors[] = 'New password must contain at least one special character.';
    }
    if ($newPass === DEFAULT_STUDENT_PASSWORD) {
        $errors[] = 'Please choose a new password different from the default password.';
    }
    if ($newPass !== $confirm) {
        $errors[] = 'New passwords do not match.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE Students SET Password = ?, MustChangePassword = 0 WHERE StudentID = ?");
        $stmt->execute([password_hash($newPass, PASSWORD_DEFAULT), $_SESSION['user_id']]);
        $_SESSION['must_change_password'] = false;
        header("Location: profile.php?password_changed=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="icon" type="image/png" href="../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page">
    <?php include __DIR__ . '/../app/includes/student-dashboard-start.php'; ?>
    <div class="dashboard-content-card dashboard-narrow-card">
        <h1>Change Your Password</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Password changed successfully.</div>
        <?php endif; ?>

        <?php if ($forceChange): ?>
            <div class="alert alert-warning">
                You are using the default password. Please change it before continuing.
            </div>
        <?php endif; ?>

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
                <div class="form-group">
                    <label for="old_password">Current Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="old_password" name="old_password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" data-target="old_password">Show</button>
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password"
                               minlength="8" pattern="(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                               title="At least 8 characters, with at least one number and one special character."
                               autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-target="new_password">Show</button>
                    </div>
                    <p class="text-muted">Use at least 8 characters, one number, and one special character.</p>
                    <ul class="password-checklist" id="password-checklist" aria-live="polite">
                        <li data-rule="length">At least 8 characters</li>
                        <li data-rule="number">At least one number</li>
                        <li data-rule="special">At least one special character</li>
                        <li data-rule="match">Passwords match</li>
                    </ul>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password"
                               minlength="8" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-target="confirm_password">Show</button>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Update Password</button>
                    <?php if (!$forceChange): ?>
                        <a href="profile.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>
    <script src="../public/assets/js/main.js"></script>
</body>
</html>
