<?php
require __DIR__ . '/../app/config/auth.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: index.php');
    } elseif (studentMustChangePassword()) {
        header('Location: ../students/change-password.php?force=1');
    } else {
        header('Location: ../students/index.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Admins WHERE Username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['Password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $admin['AdminID'];
            $_SESSION['role']      = 'admin';
            $_SESSION['user_name'] = $admin['FullName'];
            header('Location: index.php');
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM Students WHERE StudentID = ?");
        $stmt->execute([$username]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student && password_verify($password, $student['Password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $student['StudentID'];
            $_SESSION['role']      = 'student';
            $_SESSION['user_name'] = $student['FirstName'] . ' ' . $student['LastName'];
            $_SESSION['must_change_password'] = (bool) ($student['MustChangePassword'] ?? false);

            if ($_SESSION['must_change_password']) {
                header('Location: ../students/change-password.php?force=1');
            } else {
                header('Location: ../students/index.php');
            }
            exit;
        }

        $error = 'Invalid credentials. Please check your Student ID / username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body class="login-page">
    <div class="portal-login-shell">
        <section class="portal-actions-panel">
            <div class="portal-actions-inner">
                <h1>Your Campus Health Portal</h1>
                <p class="portal-intro">Providing secure and efficient healthcare services for the FCAT community.</p>

                <?php if (isset($_GET['logout']) && !$error): ?>
                    <div class="alert alert-success">You have been logged out.</div>
                <?php endif; ?>

                <div class="portal-action-card muted">
                    <span class="portal-action-icon">+</span>
                    <span>
                        <strong>Get Started</strong>
                        <small>Contact the admin to create your account</small>
                    </span>
                    <b>&rsaquo;</b>
                </div>

                <button type="button" class="portal-action-card portal-login-trigger" id="open-login-modal">
                        <span class="portal-action-icon">&rarr;</span>
                        <span>
                            <strong>Already have an account?</strong>
                            <small>Click here if you are already a member</small>
                        </span>
                        <b>&rsaquo;</b>
                </button>

                <div class="portal-action-card muted">
                    <span class="portal-action-icon">i</span>
                    <span>
                        <strong>About</strong>
                        <small>Clinic records, announcements, and messaging</small>
                    </span>
                    <b>&rsaquo;</b>
                </div>
            </div>
        </section>

        <section class="portal-brand-panel">
            <div class="portal-brand-content">
                <h2>FCAT ClinIQ<br>Management System</h2>
                <div class="portal-title-rule"></div>
                <p>A secured system to manage patient records and services.</p>

                <img class="portal-logo" src="assets/images/fcat-clinic-logo.png" alt="FCAT ClinIQ Logo">

                <div class="portal-feature-row">
                    <div class="portal-feature">
                        <span class="portal-feature-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="6" y="5" width="12" height="16" rx="2"></rect>
                                <path d="M9 5a3 3 0 0 1 6 0"></path>
                                <path d="M9 4h6v3H9z"></path>
                                <path d="M12 9v4"></path>
                                <path d="M10 11h4"></path>
                                <path d="M9 15.5h6"></path>
                            </svg>
                        </span>
                        <strong>Health Records</strong>
                        <small>Access and manage your medical records securely</small>
                    </div>
                    <div class="portal-feature-divider"></div>
                    <div class="portal-feature">
                        <span class="portal-feature-icon">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2.5 20.5 6v5.8c0 5-3.4 8.9-8.5 10.4-5.1-1.5-8.5-5.4-8.5-10.4V6L12 2.5Z"></path>
                                <path d="M9.5 12v-1.4a2.5 2.5 0 0 1 5 0V12"></path>
                                <rect x="8.5" y="12" width="7" height="4.8" rx="1"></rect>
                                <path d="M12 14v1"></path>
                            </svg>
                        </span>
                        <strong>Secure and Confidential</strong>
                        <small>Your information is protected with the highest security</small>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="login-modal <?= $error ? 'is-open' : '' ?>" id="login-modal" aria-hidden="<?= $error ? 'false' : 'true' ?>">
        <div class="login-modal-card" role="dialog" aria-modal="true" aria-labelledby="login-modal-title">
            <button type="button" class="login-modal-back" id="close-login-modal">&larr; Back</button>
            <img class="modal-logo" src="assets/images/favicon.png" alt="FCAT ClinIQ Logo">
            <h2 id="login-modal-title"><span>Login to</span> FCAT ClinIQ</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger modal-login-alert"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="modal-login-form">
                <div class="form-group">
                    <label for="username">Student ID / Username</label>
                    <input type="text" id="username" name="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           placeholder="Enter Student ID or username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" data-target="password">Show</button>
                    </div>
                </div>
                <div class="modal-login-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" value="1">
                        Remember Me
                    </label>
                </div>
                <button type="submit" class="btn modal-login-submit">Sign in</button>
            </form>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
