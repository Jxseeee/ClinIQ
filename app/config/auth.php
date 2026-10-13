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
// How many students can have a chat page open at once; more wait in the chat queue. Admins and nurses
// are never queued and don't count toward it. Set CHAT_ACTIVE_LIMIT in .env to change it (default 5).
define('CHAT_ACTIVE_LIMIT', max(1, (int) ($_ENV['CHAT_ACTIVE_LIMIT'] ?? 5)));
const CHAT_SESSION_TIMEOUT_SECONDS = 60;
// Nurse accounts created by an admin start with the same default password as new students.
const DEFAULT_NURSE_PASSWORD = DEFAULT_STUDENT_PASSWORD;
// Roles that use the clinic (admin-side) pages of the portal.
const CLINIC_STAFF_ROLES = ['admin', 'nurse'];

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

function isAdmin(): bool
{
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function isNurse(): bool
{
    return isLoggedIn() && $_SESSION['role'] === 'nurse';
}

/**
 * Pages shared by admins and nurses: the dashboard, Check In, read-only student records, Reports and
 * the Nurses directory. Admin-only pages keep using requireAdmin(). A nurse still on the default
 * password is sent to Settings until they replace it, the same way a student is.
 */
function requireClinicStaff(): void
{
    if (!isLoggedIn() || !in_array($_SESSION['role'], CLINIC_STAFF_ROLES, true)) {
        header('Location: ' . getBasePath() . '/');
        exit;
    }
    if ($_SESSION['role'] !== 'nurse') {
        return;
    }
    if (currentNurseAccount() === null) {
        // The nurse account was deleted (or nurse accounts aren't installed) while signed in.
        header('Location: ' . getBasePath() . '/public/logout.php');
        exit;
    }
    if (!isStaffPasswordChangePage() && nurseMustChangePassword()) {
        header('Location: ' . getBasePath() . '/app/admin/change-password.php?force=1');
        exit;
    }
}

function isStaffPasswordChangePage(): bool
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    return str_ends_with($script, '/app/admin/change-password.php');
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

/**
 * Installs everything nurse accounts need if add_nurse_accounts.sql was never run: the sign-in columns,
 * NurseID on visits, chat messages and announcements, and the nurse role in the audit log and chat queue.
 * One information_schema read per request; ALTERs only run for what is missing.
 */
function ensureNurseAccountSchema(): bool
{
    global $pdo;
    static $ready = null;

    if ($ready !== null) {
        return $ready;
    }
    try {
        $columns = [];
        $rows = $pdo->query(
            "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME IN ('Nurses', 'ClinicVisits', 'Messages', 'Announcements', 'AuditLogs', 'ActiveChatSessions', 'ChatQueue',
                                  'Notifications', 'NurseNotificationReads')"
        );
        foreach ($rows as $row) {
            $columns[strtolower($row['TABLE_NAME'])][strtolower($row['COLUMN_NAME'])] = strtolower($row['COLUMN_TYPE']);
        }
        if (!isset($columns['nurses'])) {
            return $ready = false;
        }
        $missing = static fn(string $table, string $column): bool
            => isset($columns[$table]) && !isset($columns[$table][$column]);
        $lacksNurseRole = static fn(string $table, string $column): bool
            => isset($columns[$table][$column]) && !str_contains($columns[$table][$column], "'nurse'");

        if ($missing('nurses', 'username')) {
            $pdo->exec("ALTER TABLE Nurses ADD COLUMN Username VARCHAR(50) NULL UNIQUE AFTER LicenseNumber");
        }
        if ($missing('nurses', 'password')) {
            $pdo->exec("ALTER TABLE Nurses ADD COLUMN Password VARCHAR(255) NULL AFTER Username");
        }
        if ($missing('nurses', 'mustchangepassword')) {
            $pdo->exec("ALTER TABLE Nurses ADD COLUMN MustChangePassword TINYINT(1) NOT NULL DEFAULT 1 AFTER Password");
        }
        if ($missing('nurses', 'profileimagepath')) {
            $pdo->exec("ALTER TABLE Nurses ADD COLUMN ProfileImagePath VARCHAR(255) NULL AFTER MustChangePassword");
        }
        // Work a nurse does is credited to the nurse, since the existing AdminID columns only reference admins.
        $nurseKeys = [
            'clinicvisits' => ['ClinicVisits', 'AdminID', 'fk_clinic_visits_nurse'],
            'messages' => ['Messages', 'AdminID', 'fk_messages_nurse'],
            'announcements' => ['Announcements', 'AdminID', 'fk_announcements_nurse'],
        ];
        foreach ($nurseKeys as $key => [$table, $after, $constraint]) {
            if ($missing($key, 'nurseid')) {
                $pdo->exec(
                    "ALTER TABLE {$table}
                     ADD COLUMN NurseID INT NULL AFTER {$after},
                     ADD CONSTRAINT {$constraint} FOREIGN KEY (NurseID) REFERENCES Nurses(NurseID) ON DELETE SET NULL"
                );
            }
        }
        if ($lacksNurseRole('auditlogs', 'actorrole')) {
            $pdo->exec(
                "ALTER TABLE AuditLogs
                 MODIFY ActorRole ENUM('admin', 'student', 'nurse', 'guest') NOT NULL,
                 MODIFY SubjectRole ENUM('admin', 'student', 'nurse') NULL"
            );
        }
        // Nurses get the clinic staff notifications (e.g. new student messages) with their own read state.
        if (isset($columns['notifications']) && !isset($columns['nursenotificationreads'])) {
            $pdo->exec(
                "CREATE TABLE NurseNotificationReads (
                    NotificationID INT NOT NULL,
                    NurseID INT NOT NULL,
                    ReadAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (NotificationID, NurseID),
                    FOREIGN KEY (NotificationID) REFERENCES Notifications(NotificationID) ON DELETE CASCADE,
                    FOREIGN KEY (NurseID) REFERENCES Nurses(NurseID) ON DELETE CASCADE
                )"
            );
        }
        // Nurses take chat slots like admins do.
        foreach (['activechatsessions' => 'ActiveChatSessions', 'chatqueue' => 'ChatQueue'] as $key => $table) {
            if ($lacksNurseRole($key, 'userrole')) {
                $pdo->exec("ALTER TABLE {$table} MODIFY UserRole ENUM('student', 'admin', 'nurse') NOT NULL");
            }
        }
        $ready = true;
    } catch (PDOException $error) {
        error_log('Could not install nurse accounts: ' . $error->getMessage());
        $ready = false;
    }
    return $ready;
}

/** True for a nurse session that may not use the portal yet: the account is gone, or still on the default password. */
function nurseAccessBlocked(): bool
{
    return isNurse() && (currentNurseAccount() === null || nurseMustChangePassword());
}
/**
 * The signed-in nurse's row, read once per request (never cached in the session) so an admin's
 * password reset or deletion of the account takes effect on the nurse's very next page load.
 */
function currentNurseAccount(bool $refresh = false): ?array
{
    global $pdo;
    static $account = false;

    if ($account === false || $refresh) {
        $account = null;
        if (isLoggedIn() && $_SESSION['role'] === 'nurse' && ensureNurseAccountSchema()) {
            $stmt = $pdo->prepare(
                "SELECT NurseID, FullName, Position, LicenseNumber, Username, Password, MustChangePassword, CreatedAt
                 FROM Nurses WHERE NurseID = ?"
            );
            $stmt->execute([$_SESSION['user_id']]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }
    }
    return $account;
}

function nurseMustChangePassword(): bool
{
    $nurse = currentNurseAccount();
    return $nurse !== null && (int) $nurse['MustChangePassword'] === 1;
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
