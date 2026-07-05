<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

const DEFAULT_STUDENT_PASSWORD = 'Password@123!';
const CHAT_ACTIVE_LIMIT = 5;
const CHAT_SESSION_TIMEOUT_SECONDS = 60;

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

function currentUser(): ?array
{
    return isLoggedIn() ? [
        'id'   => $_SESSION['user_id'],
        'role' => $_SESSION['role'],
        'name' => $_SESSION['user_name'] ?? '',
    ] : null;
}

function requireAdmin(): void
{
    if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
        header('Location: ' . getBasePath() . '/public/login.php');
        exit;
    }
}

function requireStudent(): void
{
    if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
        header('Location: ' . getBasePath() . '/public/login.php');
        exit;
    }

    if (!isStudentPasswordChangePage() && studentMustChangePassword()) {
        header('Location: ' . getBasePath() . '/students/change-password.php?force=1');
        exit;
    }
}

function isStudentPasswordChangePage(): bool
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return str_ends_with($script, '/students/change-password.php');
}

function studentMustChangePassword(): bool
{
    global $pdo;

    if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
        return false;
    }

    if (!array_key_exists('must_change_password', $_SESSION)) {
        $stmt = $pdo->prepare("SELECT MustChangePassword FROM Students WHERE StudentID = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $_SESSION['must_change_password'] = (bool) $stmt->fetchColumn();
    }

    return (bool) $_SESSION['must_change_password'];
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid or missing security token. Please go back and try again.');
    }
}

function getBasePath(): string
{
    $root = dirname(__DIR__, 2);
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $root = rtrim(str_replace('\\', '/', $root), '/');
    return str_replace($docRoot, '', $root);
}
