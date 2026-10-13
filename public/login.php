<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';

$basePath = getBasePath();
$assetsPath = $basePath . '/public/assets';

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ' . $basePath . '/public/index.php');
    } elseif (studentMustChangePassword()) {
        header('Location: ' . $basePath . '/students/change-password.php?force=1');
    } else {
        header('Location: ' . $basePath . '/students/index.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM Admins WHERE Username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        // Student IDs are digits only; without this check MySQL would match "10001abc" to student 10001.
        $student = null;
        if (ctype_digit($username) && strlen($username) <= 10) {
            $stmt = $pdo->prepare("SELECT * FROM Students WHERE StudentID = ?");
            $stmt->execute([$username]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $targetRole = $admin ? 'admin' : ($student ? 'student' : null);
        $targetId = $admin ? (int) $admin['AdminID'] : ($student ? (int) $student['StudentID'] : null);
        $throttled = loginIsThrottled($pdo, $targetRole, $targetId);

        if ($throttled) {
            $error = 'Too many failed sign-in attempts. Please wait ' . LOGIN_LOCKOUT_MINUTES . ' minutes and try again.';
        } elseif ($admin && password_verify($password, $admin['Password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $admin['AdminID'];
            $_SESSION['role']      = 'admin';
            $_SESSION['user_name'] = 'Admin';
            auditLog($pdo, 'security', 'auth.login', 'Signed in as administrator.');
            header('Location: ' . $basePath . '/public/index.php');
            exit;
        } elseif ($student && password_verify($password, $student['Password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $student['StudentID'];
            $_SESSION['role']      = 'student';
            $_SESSION['user_name'] = $student['FirstName'] . ' ' . $student['LastName'];
            $_SESSION['must_change_password'] = (bool) ($student['MustChangePassword'] ?? false);
            auditLog($pdo, 'security', 'auth.login', 'Signed in to the student portal.');

            if ($_SESSION['must_change_password']) {
                header('Location: ' . $basePath . '/students/change-password.php?force=1');
            } else {
                header('Location: ' . $basePath . '/students/index.php');
            }
            exit;
        } else {
            if ($targetRole === null) {
                // Do the same bcrypt work as a real check so response time doesn't reveal which IDs exist.
                password_verify($password, '$2y$10$wmDKaC1keu.3.gLKoiMgGe6.o41USYZRn1TU3N6WpbeNW4HERwB46');
            }
            // Record the failed attempt against the targeted account so its owner can see it.
            auditLog(
                $pdo,
                'security',
                'auth.login_failed',
                $targetRole !== null
                    ? 'Failed sign-in attempt (wrong password).'
                    : 'Failed sign-in attempt for unknown ID/username "' . mb_substr($username, 0, 60) . '".',
                ['actor_role' => 'guest', 'actor_id' => null, 'subject_role' => $targetRole, 'subject_id' => $targetId]
            );
            $error = 'Invalid credentials. Please check your Student ID / username and password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetsPath) ?>/css/tailwind.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetsPath) ?>/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($assetsPath) ?>/images/favicon.png">
</head>
<body class="login-page antialiased selection:bg-green-200 selection:text-green-950">
    <main class="portal-login-shell">
        <section class="portal-brand-panel" aria-labelledby="portal-title">
            <nav class="portal-brand-nav" aria-label="FCAT HealthWard">
                <a href="<?= htmlspecialchars($basePath . '/') ?>" class="portal-brand-link">
                    <img src="<?= htmlspecialchars($assetsPath) ?>/images/fcat-clinic-logo.2.png" alt="">
                    <span>FCAT HealthWard</span>
                </a>
                <span class="portal-founded">Est. 1993</span>
            </nav>

            <div class="portal-brand-content">
                <p class="portal-campus-name">Fernandez College of Arts and Technology</p>
                <h1 class="portal-hero-title" id="portal-title">FCAT HealthWard</h1>
                <p class="portal-brand-copy">Campus health records and clinic support, handled with clarity and care.</p>

                <div class="portal-seal-stage">
                    <img class="portal-logo" src="<?= htmlspecialchars($assetsPath) ?>/images/fcat-clinic-logo.png" alt="Fernandez College of Arts and Technology seal">
                </div>
            </div>

            <div class="portal-marquee" aria-hidden="true">
                <div class="portal-marquee-track">
                    <span>Student health</span><i>&middot;</i><span>Clinic records</span><i>&middot;</i><span>Private messaging</span><i>&middot;</i><span>FCAT HealthWard</span><i>&middot;</i>
                    <span>Student health</span><i>&middot;</i><span>Clinic records</span><i>&middot;</i><span>Private messaging</span><i>&middot;</i><span>FCAT HealthWard</span><i>&middot;</i>
                </div>
            </div>
        </section>

        <section class="portal-actions-panel" aria-labelledby="login-title">
            <div class="portal-actions-inner">
                <div class="portal-signin-heading">
                    <img class="modal-logo" src="<?= htmlspecialchars($assetsPath) ?>/images/fcat-clinic-logo.2.png" alt="FCAT HealthWard Logo">
                    <div>
                        <p>Secure portal access</p>
                        <h2 id="login-title">Sign in to your account</h2>
                    </div>
                </div>

                <p class="portal-intro">Use your Student ID or administrator username to continue.</p>

                <?php if (isset($_GET['logout']) && !$error): ?>
                    <div class="alert alert-success">You have been logged out.</div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger modal-login-alert"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= htmlspecialchars($basePath . '/') ?>" class="modal-login-form portal-signin-form">
                    <div class="form-group">
                        <label for="username">Student ID or username</label>
                        <input type="text" id="username" name="username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               placeholder="Enter your ID or username" autocomplete="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password"
                                   placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" data-target="password">Show</button>
                        </div>
                    </div>
                    <button type="submit" class="btn modal-login-submit">Sign in</button>
                </form>

                <div class="portal-access-lanes">
                    <article>
                        <strong>Student access</strong>
                        <span>Use the ID issued by FCAT.</span>
                    </article>
                    <article>
                        <strong>New account</strong>
                        <span>Request one from the clinic administrator.</span>
                    </article>
                </div>

                <footer class="portal-login-footer">
                    Clinic System &middot; Fernandez College of Arts and Technology
                </footer>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js"></script>
    <script src="<?= htmlspecialchars($assetsPath) ?>/js/motion.js"></script>
    <script src="<?= htmlspecialchars($assetsPath) ?>/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
