<?php
require __DIR__ . '/../app/config/auth.php';

if (isLoggedIn()) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'index.php' : '../students/index.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Unavailable</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <h1>Registration Unavailable</h1>
            <p class="login-subtitle">
                Student accounts are created by the administrator only.
            </p>
            <div class="alert alert-warning">
                Please contact the admin office to create your student account.
                Your first login will use the default password provided by the admin.
            </div>
            <a href="login.php" class="btn btn-primary" style="width:100%;padding:12px;text-align:center;">Back to Login</a>
        </div>
    </div>
</body>
</html>
