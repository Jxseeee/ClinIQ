<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$errors = [];
$adminPageTitle = 'Add Student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $studentId = trim($_POST['student_id'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');

    if ($studentId === '') $errors[] = 'Student ID is required.';
    elseif (!ctype_digit($studentId)) $errors[] = 'Student ID must be a number.';
    elseif ((int) $studentId < 1 || strlen($studentId) > 10 || (int) $studentId > 2147483647) $errors[] = 'Student ID must be between 1 and 2147483647.';
    else {
        $check = $pdo->prepare("SELECT StudentID FROM Students WHERE StudentID = ?");
        $check->execute([$studentId]);
        if ($check->fetch()) $errors[] = 'This Student ID is already registered.';
    }
    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName === '')  $errors[] = 'Last name is required.';
    validateMaxLengths(
        ['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email],
        ['first_name' => ['First name', 50], 'last_name' => ['Last name', 50], 'email' => ['Email', 100]],
        $errors
    );
    validateEmail($email, $errors);
    $phone = validateMobileNumber($phone, 'Phone (mobile number)', $errors);

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO Students (StudentID, FirstName, LastName, Email, Phone, Password, MustChangePassword) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $studentId,
                $firstName,
                $lastName,
                $email ?: null,
                $phone ?: null,
                password_hash(DEFAULT_STUDENT_PASSWORD, PASSWORD_DEFAULT),
                1,
            ]);

            $_SESSION['flash_temp_password'] = DEFAULT_STUDENT_PASSWORD;
            auditLog(
                $pdo,
                'students',
                'student.created',
                'Created student account #' . $studentId . ' (' . $firstName . ' ' . $lastName . ') with the default password.',
                ['subject_role' => 'student', 'subject_id' => (int) $studentId]
            );

            header("Location: students.php?success=added");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = 'This Student ID or email is already registered.';
            } else {
                error_log('Student creation failed: ' . $e->getMessage());
                $errors[] = 'The student could not be saved. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>
    <div class="dashboard-content-card dashboard-narrow-card">
        <h1>Add New Student</h1>
        <p class="text-muted">New students will use the default password <strong>Password@123!</strong> and must change it on first login.</p>

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
                    <label for="student_id">Student ID *</label>
                    <input type="text" id="student_id" name="student_id"
                           value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>"
                           placeholder="Student's physical ID number" inputmode="numeric" maxlength="10" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name"
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" maxlength="50" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name"
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" maxlength="50" required>
                </div>
                <div class="form-group">
                    <label for="email">Email <span class="text-muted">(@fcat.com.ph only)</span></label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="e.g. juan@fcat.com.ph" maxlength="100">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone"
                           value="<?= htmlspecialchars(mobileInputValue($_POST['phone'] ?? '')) ?>"
                           class="mobile-input" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" title="11 digits starting with 09, e.g. 09171234567" placeholder="09XXXXXXXXX">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Save Student</button>
                    <a href="students.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
