<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();

$passwordErrors = [];
$photoErrors = [];
$forceChange = studentMustChangePassword();
$studentPageTitle = 'Settings';
$studentId = (int) $_SESSION['user_id'];
$profileImagePath = fetchProfileImagePath($pdo, 'student', $studentId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_profile_image') {
        try {
            storeProfileImage($pdo, 'student', $studentId, $_FILES['profile_image'] ?? []);
            header('Location: change-password.php?success=photo');
            exit;
        } catch (InvalidArgumentException | RuntimeException $error) {
            $photoErrors[] = $error->getMessage();
        }
    } elseif ($action === 'remove_profile_image') {
        try {
            removeProfileImage($pdo, 'student', $studentId);
            header('Location: change-password.php?success=photo_removed');
            exit;
        } catch (RuntimeException $error) {
            $photoErrors[] = $error->getMessage();
        }
    } elseif ($action === 'change_password') {
        $oldPass = $_POST['old_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare("SELECT Password, MustChangePassword FROM Students WHERE StudentID = ?");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student || !password_verify($oldPass, $student['Password'])) {
            $passwordErrors[] = 'Current password is incorrect.';
        }
        if (strlen($newPass) < 8) {
            $passwordErrors[] = 'New password must be at least 8 characters.';
        }
        if (!preg_match('/\d/', $newPass)) {
            $passwordErrors[] = 'New password must contain at least one number.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $newPass)) {
            $passwordErrors[] = 'New password must contain at least one special character.';
        }
        if ($newPass === DEFAULT_STUDENT_PASSWORD) {
            $passwordErrors[] = 'Please choose a new password different from the default password.';
        }
        if ($newPass !== $confirm) {
            $passwordErrors[] = 'New passwords do not match.';
        }

        if (empty($passwordErrors)) {
            $stmt = $pdo->prepare("UPDATE Students SET Password = ?, MustChangePassword = 0 WHERE StudentID = ?");
            $stmt->execute([password_hash($newPass, PASSWORD_DEFAULT), $studentId]);
            $_SESSION['must_change_password'] = false;
            header($forceChange
                ? 'Location: profile.php?password_changed=1'
                : 'Location: change-password.php?success=password');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="icon" type="image/png" href="../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../app/includes/student-dashboard-start.php'; ?>
    <div class="dashboard-content-card dashboard-narrow-card settings-page">
        <h1>Settings</h1>
        <p class="text-muted settings-intro">Manage your account photo and password.</p>

        <?php if (($_GET['success'] ?? '') === 'password'): ?>
            <div class="alert alert-success">Password changed successfully.</div>
        <?php elseif (($_GET['success'] ?? '') === 'photo'): ?>
            <div class="alert alert-success">Profile photo updated successfully.</div>
        <?php elseif (($_GET['success'] ?? '') === 'photo_removed'): ?>
            <div class="alert alert-success">Profile photo removed.</div>
        <?php endif; ?>

        <?php if ($forceChange): ?>
            <div class="alert alert-warning">
                You are using the default password. Please change it before continuing.
            </div>
        <?php endif; ?>

        <?php
        $profilePhotoRole = 'student';
        $profilePhotoName = $studentName;
        $profilePhotoInitial = $studentInitial;
        include __DIR__ . '/../app/includes/profile-photo-settings.php';
        ?>

        <?php if (!empty($passwordErrors)): ?>
            <div class="alert alert-danger">
                <ul class="settings-error-list">
                    <?php foreach ($passwordErrors as $passwordError): ?>
                        <li><?= htmlspecialchars($passwordError) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="card settings-section password-settings-section">
            <div class="settings-section-heading">
                <span class="settings-section-icon"><?= studentDashboardIcon('settings') ?></span>
                <div>
                    <h2>Change password</h2>
                    <p>Choose a unique password for your clinic account.</p>
                </div>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <input type="hidden" name="action" value="change_password">
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
        </section>
    </div>
    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>
    <script src="../public/assets/js/main.js"></script>
</body>
</html>
