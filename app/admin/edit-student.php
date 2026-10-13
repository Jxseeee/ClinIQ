<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$id = $_GET['id'] ?? ($_POST['id'] ?? null);
if (!$id) {
    header("Location: students.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Students WHERE StudentID = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: students.php?error=notfound");
    exit;
}

$errors = [];
$passwordReset = false;
$adminPageTitle = 'Edit Student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $newPass   = $_POST['new_password'] ?? '';

    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName === '')  $errors[] = 'Last name is required.';
    validateMaxLengths(
        ['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email],
        ['first_name' => ['First name', 50], 'last_name' => ['Last name', 50], 'email' => ['Email', 100]],
        $errors
    );
    validateEmail($email, $errors);
    $phone = validateMobileNumber($phone, 'Phone (mobile number)', $errors);
    if ($newPass !== '') {
        validateNewPassword($newPass, $errors);
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE Students SET FirstName = ?, LastName = ?, Email = ?, Phone = ? WHERE StudentID = ?"
            );
            $stmt->execute([$firstName, $lastName, $email ?: null, $phone ?: null, $id]);

            if ($newPass !== '') {
                // The admin knows this password, so the student must replace it at their next sign-in.
                $stmt = $pdo->prepare("UPDATE Students SET Password = ?, MustChangePassword = 1 WHERE StudentID = ?");
                $stmt->execute([password_hash($newPass, PASSWORD_DEFAULT), $id]);
                $passwordReset = true;
            }

            $changedFields = auditChangedFields(
                $student,
                ['FirstName' => $firstName, 'LastName' => $lastName, 'Email' => $email, 'Phone' => $phone],
                ['FirstName' => 'first name', 'LastName' => 'last name', 'Email' => 'email', 'Phone' => 'phone']
            );
            if ($changedFields) {
                auditLog(
                    $pdo,
                    'students',
                    'student.updated',
                    'Updated student account details: ' . implode(', ', $changedFields) . '.',
                    ['subject_role' => 'student', 'subject_id' => (int) $id]
                );
            }
            if ($passwordReset) {
                auditLog(
                    $pdo,
                    'security',
                    'student.password_reset',
                    "Reset the student's password (must be changed at next sign-in).",
                    ['subject_role' => 'student', 'subject_id' => (int) $id]
                );
            }

            $suffix = $passwordReset ? 'password_reset' : 'updated';
            header("Location: students.php?success=$suffix");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = 'A student with this email already exists.';
            } else {
                error_log('Student update failed: ' . $e->getMessage());
                $errors[] = 'The student could not be updated. Please try again.';
            }
        }
    }

    $student['FirstName'] = $firstName;
    $student['LastName']  = $lastName;
    $student['Email']     = $email;
    $student['Phone']     = $phone;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>
    <div class="dashboard-content-card dashboard-narrow-card">
        <h1>Edit Student #<?= htmlspecialchars($student['StudentID']) ?></h1>

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
                <input type="hidden" name="id" value="<?= htmlspecialchars($student['StudentID']) ?>">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name"
                           value="<?= htmlspecialchars($student['FirstName']) ?>" maxlength="50" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name"
                           value="<?= htmlspecialchars($student['LastName']) ?>" maxlength="50" required>
                </div>
                <div class="form-group">
                    <label for="email">Email <span class="text-muted">(@fcat.com.ph only)</span></label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($student['Email'] ?? '') ?>" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone"
                           value="<?= htmlspecialchars(mobileInputValue($student['Phone'] ?? '')) ?>"
                           class="mobile-input" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" title="11 digits starting with 09, e.g. 09171234567" placeholder="09XXXXXXXXX">
                </div>

                <hr style="margin: 24px 0; border: none; border-top: 1px solid #e2e8f0;">
                <h2>Reset Password</h2>
                <p class="text-muted">Leave blank to keep the student's current password. A reset password must be changed by the student at their next sign-in.</p>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password"
                               minlength="8" pattern="(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                               title="At least 8 characters, with at least one number and one special character."
                               placeholder="Enter new password (min 8 chars)" autocomplete="new-password">
                        <button type="button" class="password-toggle" data-target="new_password">Show</button>
                    </div>
                    <p class="text-muted">Use at least 8 characters, one number, and one special character.</p>
                    <ul class="password-checklist" id="password-checklist" aria-live="polite">
                        <li data-rule="length">At least 8 characters</li>
                        <li data-rule="number">At least one number</li>
                        <li data-rule="special">At least one special character</li>
                    </ul>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Update Student</button>
                    <a href="students.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
