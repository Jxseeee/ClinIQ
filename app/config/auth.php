<?php
if (session_status() === PHP_SESSION_NONE) {
    // Harden the session cookie: no JavaScript access, not sent on cross-site requests,
    // HTTPS-only when the site is served over HTTPS, and reject session IDs the server didn't issue.
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
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
    $role = $_SESSION['role'] ?? '';

    return isLoggedIn() ? [
        'id'   => $_SESSION['user_id'],
        'role' => $role,
        'name' => $role === 'admin' ? 'Admin' : ($_SESSION['user_name'] ?? ''),
    ] : null;
}

function requireAdmin(): void
{
    if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
        header('Location: ' . getBasePath() . '/');
        exit;
    }
}

function requireStudent(): void
{
    if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
        header('Location: ' . getBasePath() . '/');
        exit;
    }

    if (!isStudentPasswordChangePage() && studentMustChangePassword()) {
        header('Location: ' . getBasePath() . '/students/change-password.php?force=1');
        exit;
    }

    if (!studentMustChangePassword() && !isStudentClinicFormPage() && !studentClinicFormComplete()) {
        header('Location: ' . getBasePath() . '/students/edit-profile.php?required=1');
        exit;
    }
}

function isStudentPasswordChangePage(): bool
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return str_ends_with($script, '/students/change-password.php');
}

// Pages a student may open before the required clinic form is saved.
function isStudentClinicFormPage(): bool
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return str_ends_with($script, '/students/edit-profile.php')
        || str_ends_with($script, '/students/download-consent-form.php');
}

// Adds Students.PrivacyConsentAt if the migration was never run, so the required form can't be bypassed.
function ensurePrivacyConsentColumn(): bool
{
    global $pdo;
    static $ready = null;

    if ($ready === null) {
        try {
            $ready = (bool) $pdo->query("SHOW COLUMNS FROM Students LIKE 'PrivacyConsentAt'")->fetch();
            if (!$ready) {
                $pdo->exec("ALTER TABLE Students ADD COLUMN PrivacyConsentAt DATETIME NULL");
                $ready = true;
            }
        } catch (PDOException $error) {
            error_log('Could not add Students.PrivacyConsentAt: ' . $error->getMessage());
            $ready = false;
        }
    }
    return $ready;
}

// Complete = saved with Data Privacy Act consent AND every required field still filled in.
// Checked on every request (not cached) so the gate can't be bypassed with a stale session.
function studentClinicFormComplete(): bool
{
    global $pdo;

    if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
        return true;
    }
    if (!ensurePrivacyConsentColumn()) {
        // Consent can't be stored on this database; only a form saved in this session counts.
        return !empty($_SESSION['clinic_form_saved']);
    }

    $stmt = $pdo->prepare(
        "SELECT s.PrivacyConsentAt IS NOT NULL
                AND TRIM(COALESCE(s.Course, '')) <> '' AND TRIM(COALESCE(s.YearLevel, '')) <> ''
                AND TRIM(COALESCE(s.Department, '')) <> '' AND s.DateOfBirth IS NOT NULL
                AND TRIM(COALESCE(s.Gender, '')) <> '' AND TRIM(COALESCE(s.StreetAddress, '')) <> ''
                AND TRIM(COALESCE(s.Municipality, '')) <> '' AND TRIM(COALESCE(s.City, '')) <> ''
                AND TRIM(COALESCE(g.LastName, '')) <> '' AND TRIM(COALESCE(g.FirstName, '')) <> ''
                AND TRIM(COALESCE(g.MobileNumber, '')) <> ''
         FROM Students s
         LEFT JOIN Guardians g ON g.StudentID = s.StudentID AND g.GuardianType = 'guardian1'
         WHERE s.StudentID = ?
         LIMIT 1"
    );
    $stmt->execute([$_SESSION['user_id']]);
    return (bool) $stmt->fetchColumn();
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

// Cache-busting version for files under public/assets so browsers pick up CSS/JS changes.
function assetVersion(string $relativePath): int
{
    $path = dirname(__DIR__, 2) . '/public/assets/' . ltrim($relativePath, '/');
    return is_file($path) ? (int) filemtime($path) : 0;
}

function getBasePath(): string
{
    $root = dirname(__DIR__, 2);
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $root = rtrim(str_replace('\\', '/', $root), '/');
    return str_replace($docRoot, '', $root);
}
