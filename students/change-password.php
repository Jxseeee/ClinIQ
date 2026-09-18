<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();

$studentId = (int) $_SESSION['user_id'];
$forceChange = studentMustChangePassword();
$allowedTabs = ['profile', 'account', 'notifications'];
$requestedTab = (string) ($_GET['tab'] ?? 'profile');
$activeTab = $forceChange
    ? 'account'
    : (in_array($requestedTab, $allowedTabs, true) ? $requestedTab : 'profile');
$studentPageTitle = 'Settings';
$studentContentClass = 'student-settings-content';

$stmt = $pdo->prepare(
    "SELECT StudentID, FirstName, LastName, MiddleName, Email, Phone, Course, YearLevel,
            StreetAddress, Municipality, City, Password, MustChangePassword, NotificationsEnabled
     FROM Students WHERE StudentID = ?"
);
$stmt->execute([$studentId]);
$settingsStudent = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$settingsStudent) {
    header('Location: ../public/logout.php');
    exit;
}

$passwordErrors = [];
$photoErrors = [];
$accountErrors = [];
$notificationErrors = [];
$profileImagePath = fetchProfileImagePath($pdo, 'student', $studentId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'upload_profile_image') {
        $activeTab = 'profile';
        try {
            storeProfileImage($pdo, 'student', $studentId, $_FILES['profile_image'] ?? []);
            header('Location: change-password.php?tab=profile&success=photo');
            exit;
        } catch (InvalidArgumentException | RuntimeException $error) {
            $photoErrors[] = $error->getMessage();
        }
    } elseif ($action === 'remove_profile_image') {
        $activeTab = 'profile';
        try {
            removeProfileImage($pdo, 'student', $studentId);
            header('Location: change-password.php?tab=profile&success=photo_removed');
            exit;
        } catch (RuntimeException $error) {
            $photoErrors[] = $error->getMessage();
        }
    } elseif ($action === 'update_account') {
        $activeTab = 'account';
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));

        if ($email === '') {
            $accountErrors[] = 'Email address is required.';
        } else {
            validateEmail($email, $accountErrors);
        }
        if (mb_strlen($phone) > 20) {
            $accountErrors[] = 'Mobile number is too long.';
        }
        if (mb_strlen($address) > 255) {
            $accountErrors[] = 'Address is too long.';
        }

        if (!$accountErrors) {
            $duplicate = $pdo->prepare("SELECT COUNT(*) FROM Students WHERE Email = ? AND StudentID <> ?");
            $duplicate->execute([$email, $studentId]);
            if ((int) $duplicate->fetchColumn() > 0) {
                $accountErrors[] = 'That email address is already in use.';
            }
        }

        if (!$accountErrors) {
            $update = $pdo->prepare("UPDATE Students SET Email = ?, Phone = ?, StreetAddress = ? WHERE StudentID = ?");
            $update->execute([$email, $phone !== '' ? $phone : null, $address !== '' ? $address : null, $studentId]);
            header('Location: change-password.php?tab=account&success=account');
            exit;
        }

        $settingsStudent['Email'] = $email;
        $settingsStudent['Phone'] = $phone;
        $settingsStudent['StreetAddress'] = $address;
    } elseif ($action === 'change_password') {
        $activeTab = 'account';
        $oldPass = (string) ($_POST['old_password'] ?? '');
        $newPass = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if (!password_verify($oldPass, (string) $settingsStudent['Password'])) {
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

        if (!$passwordErrors) {
            $update = $pdo->prepare("UPDATE Students SET Password = ?, MustChangePassword = 0 WHERE StudentID = ?");
            $update->execute([password_hash($newPass, PASSWORD_DEFAULT), $studentId]);
            $_SESSION['must_change_password'] = false;
            header($forceChange
                ? 'Location: profile.php?password_changed=1'
                : 'Location: change-password.php?tab=account&success=password');
            exit;
        }
    } elseif ($action === 'update_notifications') {
        $activeTab = 'notifications';
        if (!databaseColumnExists($pdo, 'Students', 'NotificationsEnabled')) {
            $notificationErrors[] = 'Notification preference storage is not installed.';
        } else {
            $enabled = (int) ($_POST['notifications_enabled'] ?? 0) === 1 ? 1 : 0;
            $update = $pdo->prepare("UPDATE Students SET NotificationsEnabled = ? WHERE StudentID = ?");
            $update->execute([$enabled, $studentId]);
            if ($enabled === 0 && databaseTableExists($pdo, 'Notifications')) {
                $clear = $pdo->prepare(
                    "UPDATE Notifications SET IsRead = 1
                     WHERE TargetRole = 'student' AND TargetUserID = ? AND IsRead = 0"
                );
                $clear->execute([$studentId]);
            }
            header('Location: change-password.php?tab=notifications&success=notifications');
            exit;
        }
    }
}

$profilePhotoUrl = profileImageUrl($profileImagePath, getBasePath());
$settingsFullName = trim($settingsStudent['FirstName'] . ' ' . $settingsStudent['LastName']);
$settingsInitial = strtoupper(substr($settingsStudent['FirstName'] ?: 'S', 0, 1));
$profileAddress = implode(', ', array_filter([
    trim((string) ($settingsStudent['StreetAddress'] ?? '')),
    trim((string) ($settingsStudent['Municipality'] ?? '')),
    trim((string) ($settingsStudent['City'] ?? '')),
]));
$notificationEnabled = (bool) ($settingsStudent['NotificationsEnabled'] ?? 1);
$successMessages = [
    'photo' => 'Profile photo updated successfully.',
    'photo_removed' => 'Profile photo removed.',
    'account' => 'Account information updated.',
    'password' => 'Password changed successfully.',
    'notifications' => 'Notification preference updated.',
];
$successMessage = $successMessages[(string) ($_GET['success'] ?? '')] ?? '';
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
<body class="student-dashboard-page student-settings-page <?= $activeTab !== 'account' ? 'student-settings-static-view' : '' ?> antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../app/includes/student-dashboard-start.php'; ?>
    <div class="student-settings-workspace">
        <div class="student-settings-layout">
            <aside class="student-settings-rail">
                <nav class="student-settings-tabs" aria-label="Student settings sections">
                <?php
                $settingsTabs = [
                    'profile' => ['Profile', 'user'],
                    'account' => ['Account', 'settings'],
                    'notifications' => ['Notification', 'bell'],
                ];
                ?>
                <?php foreach ($settingsTabs as $tabKey => [$tabLabel, $tabIcon]): ?>
                    <?php $tabDisabled = $forceChange && $tabKey !== 'account'; ?>
                    <a href="<?= $tabDisabled ? 'change-password.php?tab=account' : 'change-password.php?tab=' . $tabKey ?>"
                       class="student-settings-tab <?= $activeTab === $tabKey ? 'is-active' : '' ?> <?= $tabDisabled ? 'is-disabled' : '' ?>"
                       <?= $activeTab === $tabKey ? 'aria-current="page"' : '' ?>
                       <?= $tabDisabled ? 'aria-disabled="true" tabindex="-1"' : '' ?>>
                        <span><?= studentDashboardIcon($tabIcon) ?></span>
                        <?= htmlspecialchars($tabLabel) ?>
                    </a>
                <?php endforeach; ?>
                </nav>
            </aside>

            <main class="student-settings-main">
                <header class="student-settings-heading">
                    <h1><?= $activeTab === 'profile' ? 'My Profile' : ($activeTab === 'account' ? 'Account' : 'Notifications') ?></h1>
                    <p><?= $activeTab === 'profile'
                        ? 'Review your student information and manage your profile photo.'
                        : ($activeTab === 'account'
                            ? 'Update your contact details, address, and account password.'
                            : 'Choose whether the clinic may send notifications to your account.') ?></p>
                </header>

                <?php if ($successMessage !== ''): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
                <?php endif; ?>

                <?php if ($forceChange): ?>
                    <div class="alert alert-warning">You are using the default password. Change it before continuing.</div>
                <?php endif; ?>

                <?php if ($activeTab === 'profile'): ?>
                    <section class="student-settings-panel student-profile-avatar-panel">
                        <div class="student-settings-avatar" data-profile-preview>
                            <?php if ($profilePhotoUrl): ?>
                                <img src="<?= htmlspecialchars($profilePhotoUrl) ?>" alt="<?= htmlspecialchars($settingsFullName) ?> profile photo">
                            <?php else: ?>
                                <span aria-hidden="true"><?= htmlspecialchars($settingsInitial) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="student-settings-avatar-controls">
                            <?php if ($photoErrors): ?>
                                <div class="alert alert-danger student-settings-inline-alert"><?= htmlspecialchars($photoErrors[0]) ?></div>
                            <?php endif; ?>
                            <form method="POST" enctype="multipart/form-data" class="profile-photo-form student-avatar-upload-form" data-profile-photo-form>
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="action" value="upload_profile_image">
                                <input type="file" id="student_settings_avatar" name="profile_image" accept="image/jpeg,image/png,image/webp" required>
                                <label for="student_settings_avatar" class="btn btn-success settings-file-picker student-avatar-picker">Edit Avatar</label>
                                <span class="settings-profile-file-name" aria-live="polite">No file selected</span>
                                <button type="submit" class="btn btn-success settings-profile-submit" disabled>Save Avatar</button>
                            </form>
                            <?php if ($profilePhotoUrl): ?>
                                <form method="POST" class="profile-photo-remove-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                    <input type="hidden" name="action" value="remove_profile_image">
                                    <button type="submit" class="btn btn-danger">Remove Picture</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="student-settings-panel">
                        <div class="student-settings-readonly-grid">
                            <label><span>First Name</span><input type="text" value="<?= htmlspecialchars($settingsStudent['FirstName']) ?>" readonly></label>
                            <label><span>Last Name</span><input type="text" value="<?= htmlspecialchars($settingsStudent['LastName']) ?>" readonly></label>
                            <label><span>Student ID</span><input type="text" value="<?= (int) $settingsStudent['StudentID'] ?>" readonly></label>
                            <label><span>Course / Year Level</span><input type="text" value="<?= htmlspecialchars(trim(($settingsStudent['Course'] ?? '') . ' ' . ($settingsStudent['YearLevel'] ?? ''))) ?>" readonly></label>
                            <label><span>Email Address</span><input type="text" value="<?= htmlspecialchars($settingsStudent['Email'] ?? '—') ?>" readonly></label>
                            <label><span>Mobile Number</span><input type="text" value="<?= htmlspecialchars($settingsStudent['Phone'] ?? '—') ?>" readonly></label>
                            <label class="is-wide"><span>Address</span><input type="text" value="<?= htmlspecialchars($profileAddress !== '' ? $profileAddress : '—') ?>" readonly></label>
                        </div>
                    </section>
                <?php elseif ($activeTab === 'account'): ?>
                    <section class="student-settings-panel">
                        <div class="student-settings-panel-heading">
                            <h2>Contact information</h2>
                            <p>Only FCAT email addresses are accepted.</p>
                        </div>
                        <?php if ($accountErrors): ?>
                            <div class="alert alert-danger"><ul class="settings-error-list"><?php foreach ($accountErrors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div>
                        <?php endif; ?>
                        <form method="POST" class="student-account-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                            <input type="hidden" name="action" value="update_account">
                            <div class="student-settings-form-grid">
                                <label for="settings_email"><span>Email Address</span><input type="email" id="settings_email" name="email" value="<?= htmlspecialchars($settingsStudent['Email'] ?? '') ?>" pattern="^[^\s@]+@fcat\.com\.ph$" title="Use your @fcat.com.ph email address." required></label>
                                <label for="settings_phone"><span>Mobile Number</span><input type="tel" id="settings_phone" name="phone" value="<?= htmlspecialchars($settingsStudent['Phone'] ?? '') ?>" maxlength="20"></label>
                                <label for="settings_address" class="is-wide"><span>Address</span><input type="text" id="settings_address" name="address" value="<?= htmlspecialchars($settingsStudent['StreetAddress'] ?? '') ?>" maxlength="255"></label>
                            </div>
                            <div class="student-settings-actions"><button type="submit" class="btn btn-success">Save Changes</button><a href="change-password.php?tab=profile" class="btn btn-secondary">Cancel</a></div>
                        </form>
                    </section>

                    <section class="student-settings-panel">
                        <div class="student-settings-panel-heading">
                            <h2>Change password</h2>
                            <p>Use at least 8 characters, one number, and one special character.</p>
                        </div>
                        <?php if ($passwordErrors): ?>
                            <div class="alert alert-danger"><ul class="settings-error-list"><?php foreach ($passwordErrors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div>
                        <?php endif; ?>
                        <form method="POST" class="student-password-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group"><label for="old_password">Current Password *</label><div class="password-wrapper"><input type="password" id="old_password" name="old_password" autocomplete="current-password" required><button type="button" class="password-toggle" data-target="old_password">Show</button></div></div>
                            <div class="form-group"><label for="new_password">New Password *</label><div class="password-wrapper"><input type="password" id="new_password" name="new_password" minlength="8" pattern="(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}" autocomplete="new-password" required><button type="button" class="password-toggle" data-target="new_password">Show</button></div><ul class="password-checklist" id="password-checklist" aria-live="polite"><li data-rule="length">At least 8 characters</li><li data-rule="number">At least one number</li><li data-rule="special">At least one special character</li><li data-rule="match">Passwords match</li></ul></div>
                            <div class="form-group"><label for="confirm_password">Confirm Password *</label><div class="password-wrapper"><input type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required><button type="button" class="password-toggle" data-target="confirm_password">Show</button></div></div>
                            <div class="student-settings-actions"><button type="submit" class="btn btn-success">Update Password</button></div>
                        </form>
                    </section>
                <?php else: ?>
                    <section class="student-settings-panel student-notification-panel">
                        <div class="student-settings-panel-heading">
                            <h2>Clinic notifications</h2>
                            <p>Control whether clinic updates appear in the notification bell.</p>
                        </div>
                        <?php if ($notificationErrors): ?><div class="alert alert-danger"><?= htmlspecialchars($notificationErrors[0]) ?></div><?php endif; ?>
                        <form method="POST" class="student-notification-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                            <input type="hidden" name="action" value="update_notifications">
                            <label class="student-notification-preference">
                                <input type="checkbox" name="notifications_enabled" value="1" <?= $notificationEnabled ? 'checked' : '' ?>>
                                <span class="student-notification-switch" aria-hidden="true"><span></span></span>
                                <span class="student-notification-copy"><strong>Receive notifications</strong><small>When disabled, no new clinic notifications will appear in your bell.</small></span>
                            </label>
                            <p class="student-notification-state">Notifications are currently <strong><?= $notificationEnabled ? 'enabled' : 'disabled' ?></strong>.</p>
                            <div class="student-settings-actions"><button type="submit" class="btn btn-success">Save Preference</button></div>
                        </form>
                    </section>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>
    <script src="../public/assets/js/main.js"></script>
</body>
</html>
