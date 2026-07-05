<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$id = $_GET['id'] ?? ($_POST['id'] ?? null);
if (!$id) {
    header("Location: ../../public/index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Students WHERE StudentID = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header("Location: ../../public/index.php?error=notfound");
    exit;
}

$errors = [];
$passwordReset = false;
$adminPageTitle = 'Edit Student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $newPass   = $_POST['new_password'] ?? '';

    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName === '')  $errors[] = 'Last name is required.';
    validateEmail($email, $errors);
    if ($newPass === '') {
        $errors[] = 'New password is required.';
    }
    if ($newPass !== '' && strlen($newPass) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }
    if ($newPass !== '' && !preg_match('/\d/', $newPass)) {
        $errors[] = 'New password must contain at least one number.';
    }
    if ($newPass !== '' && !preg_match('/[^A-Za-z0-9]/', $newPass)) {
        $errors[] = 'New password must contain at least one special character.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE Students SET FirstName = ?, LastName = ?, Email = ?, Phone = ? WHERE StudentID = ?"
            );
            $stmt->execute([$firstName, $lastName, $email ?: null, $phone ?: null, $id]);

            if ($newPass !== '') {
                $stmt = $pdo->prepare("UPDATE Students SET Password = ?, MustChangePassword = 0 WHERE StudentID = ?");
                $stmt->execute([password_hash($newPass, PASSWORD_DEFAULT), $id]);
                $passwordReset = true;
            }

            $suffix = $passwordReset ? 'password_reset' : 'updated';
            header("Location: ../../public/index.php?success=$suffix");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = 'A student with this email already exists.';
            } else {
                $errors[] = 'Database error: ' . $e->getMessage();
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
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page">
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
                <input type="hidden" name="id" value="<?= htmlspecialchars($student['StudentID']) ?>">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name"
                           value="<?= htmlspecialchars($student['FirstName']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name"
                           value="<?= htmlspecialchars($student['LastName']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email <span class="text-muted">(@university.edu.ph only)</span></label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($student['Email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone"
                           value="<?= htmlspecialchars($student['Phone'] ?? '') ?>">
                </div>

                <hr style="margin: 24px 0; border: none; border-top: 1px solid #e2e8f0;">
                <h2>Reset Password</h2>
                <p class="text-muted">Enter a strong password to reset this student's password.</p>
                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password"
                               minlength="8" pattern="(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}"
                               title="At least 8 characters, with at least one number and one special character."
                               placeholder="Enter new password (min 8 chars)" required>
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
                    <a href="../../public/index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js"></script>
</body>
</html>
