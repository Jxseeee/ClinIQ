<?php
// Settings page for a signed-in nurse, included by app/admin/change-password.php.
// A nurse can change their profile photo, full name and password. Their username, position and
// license number stay in the nurse directory, where only an admin can change them.
// A nurse still on the default password is kept on the Account tab until they replace it.
if (!function_exists('isNurse') || !isNurse()) {
    http_response_code(404);
    exit;
}

$nurseAccount = currentNurseAccount();
$nurseId = (int) $nurseAccount['NurseID'];
$forceChange = (int) $nurseAccount['MustChangePassword'] === 1;
$allowedTabs = ['profile', 'account'];
$requestedTab = (string) ($_GET['tab'] ?? 'profile');
$activeTab = $forceChange ? 'account' : (in_array($requestedTab, $allowedTabs, true) ? $requestedTab : 'profile');
$adminPageTitle = 'Settings';
$adminContentClass = 'student-settings-content';
$adminBreadcrumbTrail = ['Settings'];
$passwordErrors = [];
$photoErrors = [];
$accountErrors = [];
$profileImagePath = fetchProfileImagePath($pdo, 'nurse', $nurseId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = (string) ($_POST['action'] ?? '');

    if ($forceChange && $action !== 'change_password') {
        // Nothing else can be changed until the default password is replaced.
        header('Location: change-password.php?force=1');
        exit;
    }

    if ($action === 'upload_profile_image') {
        $activeTab = 'profile';
        try {
            storeProfileImage($pdo, 'nurse', $nurseId, $_FILES['profile_image'] ?? []);
            auditLog($pdo, 'account', 'account.photo_updated', 'Updated profile photo.');
            header('Location: change-password.php?tab=profile&success=photo');
            exit;
        } catch (InvalidArgumentException | RuntimeException $error) {
            $photoErrors[] = $error->getMessage();
        }
    } elseif ($action === 'remove_profile_image') {
        $activeTab = 'profile';
        try {
            removeProfileImage($pdo, 'nurse', $nurseId);
            auditLog($pdo, 'account', 'account.photo_removed', 'Removed profile photo.');
            header('Location: change-password.php?tab=profile&success=photo_removed');
            exit;
        } catch (RuntimeException $error) {
            $photoErrors[] = $error->getMessage();
        }
    } elseif ($action === 'update_account') {
        $activeTab = 'account';
        $fullName = trim((string) ($_POST['full_name'] ?? ''));

        if ($fullName === '') {
            $accountErrors[] = 'Full name is required.';
        } elseif (mb_strlen($fullName) > 120) {
            $accountErrors[] = 'Full name must not exceed 120 characters.';
        }

        if (!$accountErrors) {
            $previousName = trim((string) $nurseAccount['FullName']);
            $update = $pdo->prepare('UPDATE Nurses SET FullName = ? WHERE NurseID = ?');
            $update->execute([$fullName, $nurseId]);
            $_SESSION['user_name'] = $fullName;
            if ($fullName !== $previousName) {
                auditLog($pdo, 'account', 'account.details_updated', 'Changed account full name from "' . $previousName . '" to "' . $fullName . '".');
            }
            header('Location: change-password.php?tab=account&success=account');
            exit;
        }

        $nurseAccount['FullName'] = $fullName;
    } elseif ($action === 'change_password') {
        $activeTab = 'account';
        $oldPass = (string) ($_POST['old_password'] ?? '');
        $newPass = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if (!password_verify($oldPass, (string) $nurseAccount['Password'])) {
            $passwordErrors[] = 'Current password is incorrect.';
        }
        validateNewPassword($newPass, $passwordErrors);
        if ($newPass === DEFAULT_NURSE_PASSWORD) {
            $passwordErrors[] = 'Please choose a new password different from the default password.';
        } elseif ($newPass !== '' && $newPass === $oldPass) {
            $passwordErrors[] = 'Please choose a new password different from your current password.';
        }
        if ($newPass !== $confirm) {
            $passwordErrors[] = 'New passwords do not match.';
        }

        if (!$passwordErrors) {
            $update = $pdo->prepare('UPDATE Nurses SET Password = ?, MustChangePassword = 0 WHERE NurseID = ?');
            $update->execute([password_hash($newPass, PASSWORD_DEFAULT), $nurseId]);
            session_regenerate_id(true);
            auditLog(
                $pdo,
                'security',
                'account.password_changed',
                $forceChange ? 'Replaced the temporary password on sign-in.' : 'Changed account password.'
            );
            header('Location: change-password.php?tab=account&success=' . ($forceChange ? 'password_set' : 'password'));
            exit;
        }
    }
}

$profilePhotoUrl = profileImageUrl($profileImagePath, getBasePath());
$settingsFullName = trim((string) $nurseAccount['FullName']);
$settingsInitial = strtoupper(substr($settingsFullName !== '' ? $settingsFullName : 'N', 0, 1));
$successMessages = [
    'photo' => 'Profile photo updated successfully.',
    'photo_removed' => 'Profile photo removed.',
    'account' => 'Your account information was updated.',
    'password_set' => 'Your new password is saved. You can now use the clinic portal.',
    'password' => 'Password changed successfully.',
];
$successMessage = $successMessages[(string) ($_GET['success'] ?? '')] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page student-settings-page admin-settings-page <?= $activeTab === 'profile' ? 'student-settings-static-view' : '' ?> antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/admin-dashboard-start.php'; ?>
    <div class="student-settings-workspace">
        <div class="student-settings-layout">
            <aside class="student-settings-rail">
                <nav class="student-settings-tabs" aria-label="Nurse settings sections">
                    <?php $settingsTabs = ['profile' => ['Profile', 'user'], 'account' => ['Account', 'settings']]; ?>
                    <?php foreach ($settingsTabs as $tabKey => [$tabLabel, $tabIcon]): ?>
                        <?php $tabDisabled = $forceChange && $tabKey !== 'account'; ?>
                        <a href="change-password.php?tab=<?= $tabDisabled ? 'account' : $tabKey ?>"
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
                    <h1><?= $activeTab === 'profile' ? 'My Profile' : 'Account' ?></h1>
                    <p><?= $activeTab === 'profile'
                        ? 'Review your nurse information and manage your profile photo.'
                        : 'Update your name and the password you use to sign in.' ?></p>
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
                                <input type="file" id="nurse_settings_avatar" name="profile_image" accept="image/jpeg,image/png,image/webp" required>
                                <label for="nurse_settings_avatar" class="btn btn-success settings-file-picker student-avatar-picker">Edit Avatar</label>
                                <span class="settings-profile-file-name" aria-live="polite">No file selected</span>
                                <button type="submit" class="btn btn-success settings-profile-submit">Save Avatar</button>
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
                            <label><span>Full Name</span><input type="text" value="<?= htmlspecialchars($nurseAccount['FullName']) ?>" readonly></label>
                            <label><span>Username</span><input type="text" value="<?= htmlspecialchars((string) $nurseAccount['Username']) ?>" readonly></label>
                            <label><span>Position</span><input type="text" value="<?= htmlspecialchars($nurseAccount['Position']) ?>" readonly></label>
                            <label><span>License Number</span><input type="text" value="<?= htmlspecialchars($nurseAccount['LicenseNumber'] ?: '—') ?>" readonly></label>
                            <label><span>Role</span><input type="text" value="Nurse" readonly></label>
                            <label><span>Account Created</span><input type="text" value="<?= htmlspecialchars(date('F d, Y', strtotime((string) $nurseAccount['CreatedAt']))) ?>" readonly></label>
                        </div>
                    </section>
                <?php else: ?>
                    <?php if (!$forceChange): ?>
                        <section class="student-settings-panel">
                            <div class="student-settings-panel-heading">
                                <h2>Nurse information</h2>
                                <p>Your username, position and license number are set by an administrator.</p>
                            </div>
                            <?php if ($accountErrors): ?>
                                <div class="alert alert-danger"><ul class="settings-error-list"><?php foreach ($accountErrors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div>
                            <?php endif; ?>
                            <form method="POST" class="student-account-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="action" value="update_account">
                                <div class="student-settings-form-grid">
                                    <label for="nurse_full_name"><span>Full Name</span><input type="text" id="nurse_full_name" name="full_name" value="<?= htmlspecialchars($nurseAccount['FullName']) ?>" maxlength="120" required></label>
                                    <label for="nurse_username"><span>Username</span><input type="text" id="nurse_username" value="<?= htmlspecialchars((string) $nurseAccount['Username']) ?>" readonly></label>
                                    <label for="nurse_position"><span>Position</span><input type="text" id="nurse_position" value="<?= htmlspecialchars($nurseAccount['Position']) ?>" readonly></label>
                                    <label for="nurse_license"><span>License Number</span><input type="text" id="nurse_license" value="<?= htmlspecialchars($nurseAccount['LicenseNumber'] ?: '—') ?>" readonly></label>
                                </div>
                                <div class="student-settings-actions"><button type="submit" class="btn btn-success">Save Changes</button><a href="change-password.php?tab=profile" class="btn btn-secondary">Cancel</a></div>
                            </form>
                        </section>
                    <?php endif; ?>

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
                <?php endif; ?>
            </main>
        </div>
    </div>
    <?php include __DIR__ . '/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
