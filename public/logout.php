<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';

// Logout links carry the session's CSRF token so other sites can't sign users out.
$token = $_POST['csrf_token'] ?? $_GET['token'] ?? '';
$tokenValid = is_string($token) && $token !== '' && hash_equals($_SESSION['csrf_token'] ?? '', $token);

if (isLoggedIn() && !$tokenValid) {
    // Pages redirect here without a token when the signed-in account no longer exists; let those through.
    $accountCheck = $_SESSION['role'] === 'admin'
        ? $pdo->prepare('SELECT 1 FROM Admins WHERE AdminID = ?')
        : $pdo->prepare('SELECT 1 FROM Students WHERE StudentID = ?');
    $accountCheck->execute([$_SESSION['user_id']]);

    if ($accountCheck->fetchColumn()) {
        $basePath = getBasePath();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign out</title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body class="antialiased">
    <div class="login-wrapper">
        <div class="login-card">
            <h1>Sign out?</h1>
            <p class="login-subtitle">Confirm that you want to sign out of the FCAT Clinic portal.</p>
            <form method="POST" action="logout.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Sign out</button>
                    <a href="<?= htmlspecialchars($basePath . ($_SESSION['role'] === 'admin' ? '/public/index.php' : '/students/index.php')) ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
        <?php
        exit;
    }
}

if (isLoggedIn()) {
    auditLog($pdo, 'security', 'auth.logout', 'Signed out.');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $p['path'],
        'domain'   => $p['domain'],
        'secure'   => $p['secure'],
        'httponly' => $p['httponly'],
        'samesite' => $p['samesite'] ?? 'Lax',
    ]);
}
session_destroy();
header('Location: ../?logout=1');
exit;
