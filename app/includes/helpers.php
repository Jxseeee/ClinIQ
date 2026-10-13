<?php

function studentDashboardIcon(string $name): string
{
    static $icons = [
        'dashboard' => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"></rect><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"></rect><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"></rect><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"></rect>',
        'records'   => '<rect x="5" y="4.5" width="14" height="16" rx="2"></rect><path d="M9 4.5V3h6v1.5"></path><path d="M8.5 9h7"></path><path d="M8.5 13h7"></path><path d="M8.5 17h4.5"></path>',
        'report'    => '<path d="M5 3.5h14v17H5v-17Z"></path><path d="M8.5 16v-3"></path><path d="M12 16V9"></path><path d="M15.5 16v-5"></path><path d="M8.5 6.5h7"></path>',
        'medicine'  => '<path d="M8 3.5h8"></path><path d="M9 3.5v3l-2 2v10a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-10l-2-2v-3"></path><path d="M7 11h10"></path><path d="M10 15h4"></path>',
        'warning'   => '<path d="M12 3 21 20H3L12 3Z"></path><path d="M12 9v5"></path><path d="M12 17h.01"></path>',
        'message'   => '<path d="M4 5.5h16v11H9l-5 4v-15Z"></path><path d="M8 10h8"></path><path d="M8 13h5"></path>',
        'form'      => '<path d="M6 3.5h8l4 4v13H6v-17Z"></path><path d="M14 3.5v4h4"></path><path d="M9 12h6"></path><path d="M9 16h4"></path>',
        'settings'  => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.09a2 2 0 0 1 1 1.74v.5a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.38a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2Z"></path><circle cx="12" cy="12" r="3"></circle>',
        'logout'    => '<path d="M10 4.5H5v15h5"></path><path d="M13 8.5 16.5 12 13 15.5"></path><path d="M16.5 12H9"></path>',
        'bell'      => '<path d="M18 10a6 6 0 0 0-12 0c0 5-2 7-2 7h16s-2-2-2-7"></path><path d="M9.5 20h5"></path>',
        'moon'      => '<path d="M20.5 15.25A8.5 8.5 0 0 1 8.75 3.5 8.5 8.5 0 1 0 20.5 15.25Z"></path>',
        'sun'       => '<circle cx="12" cy="12" r="4"></circle><path d="M12 2.5v2"></path><path d="M12 19.5v2"></path><path d="m5.3 5.3 1.4 1.4"></path><path d="m17.3 17.3 1.4 1.4"></path><path d="M2.5 12h2"></path><path d="M19.5 12h2"></path><path d="m5.3 18.7 1.4-1.4"></path><path d="m17.3 6.7 1.4-1.4"></path>',
        'headset'   => '<path d="M4 13v-1a8 8 0 0 1 16 0v1"></path><path d="M4 13h3v5H4v-5Z"></path><path d="M17 13h3v5h-3v-5Z"></path><path d="M17 18c0 2-1.5 3-4 3h-1"></path>',
        'chat'      => '<path d="M4 5.5h16v11H9l-5 4v-15Z"></path><circle cx="8.5" cy="11" r="0.75" fill="currentColor" stroke="none"></circle><circle cx="12" cy="11" r="0.75" fill="currentColor" stroke="none"></circle><circle cx="15.5" cy="11" r="0.75" fill="currentColor" stroke="none"></circle>',
        'camera'    => '<path d="M4 7.5h3l1.5-2h7l1.5 2h3v11H4v-11Z"></path><circle cx="12" cy="13" r="3.25"></circle><path d="M17.5 10h.01"></path>',
        'plus'      => '<path d="M12 5v14"></path><path d="M5 12h14"></path>',
        'close'     => '<path d="m6 6 12 12"></path><path d="m18 6-12 12"></path>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"></path>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"></path>',
        'pin'       => '<path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z"></path><circle cx="12" cy="10" r="2.4"></circle>',
        'audit'     => '<rect x="4.5" y="4" width="13" height="16.5" rx="2"></rect><path d="M8.5 4V2.75h5V4"></path><path d="M8 9h6"></path><path d="M8 12.5h3.5"></path><circle cx="17" cy="16.5" r="3.75"></circle><path d="M17 14.75v1.75l1.25 1"></path>',
        'archive'   => '<rect x="3.5" y="4" width="17" height="4.5" rx="1"></rect><path d="M5 8.5v10a1.5 1.5 0 0 0 1.5 1.5h11a1.5 1.5 0 0 0 1.5-1.5v-10"></path><path d="M10 12.5h4"></path>',
        'restore'   => '<path d="M4 12a8 8 0 1 0 2.35-5.65"></path><path d="M4 4.5v4h4"></path><path d="M12 8v4l2.5 2"></path>',
        'trash'     =>'<path d="M4.5 7h15"></path><path d="M9 3.5h6L16 7H8l1-3.5Z"></path><path d="m7 7 .75 13.5h8.5L17 7"></path><path d="M10 11v5.5"></path><path d="M14 11v5.5"></path>',
        'send'      => '<path d="m3.5 11.5 17-7-6.75 16-2.25-6.75-8-2.25Z"></path><path d="m11.5 13.75 9-9.25"></path>',
        'medical'   => '<rect x="3.5" y="7" width="17" height="13.5" rx="2"></rect><path d="M9 7V4.5h6V7"></path><path d="M12 10.5v6.5"></path><path d="M8.75 13.75h6.5"></path>',
        'visit'     => '<rect x="3.5" y="7" width="17" height="13.5" rx="2"></rect><path d="M9 7V4.5h6V7"></path><path d="M12 10.5v6.5"></path><path d="M8.75 13.75h6.5"></path>',
        'check'     => '<circle cx="12" cy="12" r="8.5"></circle><path d="m8 12 2.5 2.5L16.5 8.5"></path>',
        'megaphone' => '<path d="M4 10.5v3h4l9 4V6.5l-9 4H4Z"></path><path d="m8 13.5 1 5H6l-1-5"></path><path d="M20 9v6"></path>',
        'file'      => '<path d="M6 3.5h8l4 4v13H6v-17Z"></path><path d="M14 3.5v4h4"></path><path d="M9 12h6"></path><path d="M9 16h4"></path>',
        'mask'      => '<path d="M4.5 9.5c4.5-3 10.5-3 15 0v6c-4.5 3-10.5 3-15 0v-6Z"></path><path d="M8 11.5h8"></path><path d="M8 14.5h8"></path><path d="m4.5 11-2-1"></path><path d="m19.5 11 2-1"></path>',
        'user'      => '<circle cx="12" cy="8" r="3.25"></circle><path d="M5 20c.8-4.25 3.15-6.25 7-6.25s6.2 2 7 6.25"></path>',
        'people'    => '<circle cx="9" cy="8" r="3"></circle><circle cx="17" cy="9" r="2.25"></circle><path d="M3.5 20c.7-4 2.6-6 5.5-6s4.8 2 5.5 6"></path><path d="M15 14.5c2.9 0 4.7 1.7 5.5 5.5"></path>',
        'nurse'     => '<circle cx="12" cy="8" r="3.25"></circle><path d="M5 20c.8-4.25 3.15-6.25 7-6.25s6.2 2 7 6.25"></path><path d="M9.5 3.5h5"></path><path d="M12 2v3"></path>',
        'calendar'  => '<rect x="3.5" y="5" width="17" height="15.5" rx="2"></rect><path d="M8 3v4"></path><path d="M16 3v4"></path><path d="M3.5 9.5h17"></path><path d="M8 13h2"></path><path d="M14 13h2"></path><path d="M8 17h2"></path>',
        'thermo'    => '<path d="M9 14.5V6a3 3 0 0 1 6 0v8.5a5 5 0 1 1-6 0Z"></path><path d="M12 8v8"></path><path d="M17.5 7h2"></path><path d="M17.5 10h1.5"></path>',
        'shield'    => '<path d="M12 3 19 6v5c0 4.5-2.8 8.2-7 10-4.2-1.8-7-5.5-7-10V6l7-3Z"></path><path d="m8.5 12 2.25 2.25 4.75-5"></path>',
        'pulse'     => '<path d="M20.5 8.5c0 5.5-8.5 11-8.5 11s-8.5-5.5-8.5-11A4.5 4.5 0 0 1 12 6a4.5 4.5 0 0 1 8.5 2.5Z"></path><path d="M5 12h3l1.5-3 2.5 6 1.5-3H19"></path>',
    ];

    if (!isset($icons[$name])) {
        return '';
    }

    return '<svg class="dashboard-icon dashboard-icon-' . $name . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'
        . $icons[$name]
        . '</svg>';
}

function fetchStudentFullProfile(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM Students WHERE StudentID = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) return null;

    $stmt = $pdo->prepare("SELECT * FROM Guardians WHERE StudentID = ? AND GuardianType = 'guardian1'");
    $stmt->execute([$id]);
    $g1 = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $stmt = $pdo->prepare("SELECT * FROM Guardians WHERE StudentID = ? AND GuardianType = 'guardian2'");
    $stmt->execute([$id]);
    $g2 = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $stmt = $pdo->prepare("SELECT * FROM MedicalHistory WHERE StudentID = ?");
    $stmt->execute([$id]);
    $medHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $medMap = [];
    foreach ($medHistory as $row) {
        $medMap[$row['Illness']] = $row;
    }

    return [
        'student'    => $student,
        'g1'         => $g1,
        'g2'         => $g2,
        'medHistory' => $medHistory,
        'medMap'     => $medMap,
    ];
}

function fetchAnnouncements(PDO $pdo): array
{
    // Announcements are posted by an admin (AdminID) or a nurse (NurseID).
    $byNurse = databaseColumnExists($pdo, 'Announcements', 'NurseID');
    $stmt = $pdo->query(
        "SELECT a.*, " . ($byNurse ? 'COALESCE(ad.FullName, n.FullName)' : 'ad.FullName') . " AS AuthorName
         FROM Announcements a
         LEFT JOIN Admins ad ON a.AdminID = ad.AdminID
         " . ($byNurse ? 'LEFT JOIN Nurses n ON a.NurseID = n.NurseID' : '') . "
         ORDER BY a.CreatedAt DESC"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function databaseTableExists(PDO $pdo, string $table): bool
{
    static $cache = [];

    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    try {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $cache[$table] = (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        $cache[$table] = false;
    }

    return $cache[$table];
}

function databaseColumnExists(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $cacheKey = $table . '.' . $column;

    if (array_key_exists($cacheKey, $cache)) {
        return $cache[$cacheKey];
    }

    try {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
        );
        $stmt->execute([$table, $column]);
        $cache[$cacheKey] = (bool) $stmt->fetchColumn();
    } catch (PDOException $e) {
        $cache[$cacheKey] = false;
    }

    return $cache[$cacheKey];
}

function profileImageAccountConfig(string $role): array
{
    return match ($role) {
        'admin' => ['Admins', 'AdminID'],
        'nurse' => ['Nurses', 'NurseID'],
        'student' => ['Students', 'StudentID'],
        default => throw new InvalidArgumentException('Unsupported account role.'),
    };
}

function profileImageAbsolutePath(string $relativePath): ?string
{
    $normalizedPath = str_replace('\\', '/', ltrim($relativePath, '/'));
    if (!str_starts_with($normalizedPath, 'public/uploads/profiles/')) {
        return null;
    }

    return dirname(__DIR__, 2) . '/' . $normalizedPath;
}

function fetchProfileImagePath(PDO $pdo, string $role, int $userId): ?string
{
    [$table, $idColumn] = profileImageAccountConfig($role);
    if (!databaseColumnExists($pdo, $table, 'ProfileImagePath')) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT ProfileImagePath FROM {$table} WHERE {$idColumn} = ?");
    $stmt->execute([$userId]);
    $relativePath = trim((string) $stmt->fetchColumn());
    $absolutePath = profileImageAbsolutePath($relativePath);

    return $relativePath !== '' && $absolutePath !== null && is_file($absolutePath)
        ? $relativePath
        : null;
}

function profileImageUrl(?string $relativePath, string $basePath): ?string
{
    if ($relativePath === null || profileImageAbsolutePath($relativePath) === null) {
        return null;
    }

    return rtrim($basePath, '/') . '/' . ltrim($relativePath, '/');
}

/** Select expression for an account's profile photo path ($alias.ProfileImagePath), or NULL before that column exists. */
function profileImageSelectSql(PDO $pdo, string $table, string $alias): string
{
    return databaseColumnExists($pdo, $table, 'ProfileImagePath') ? $alias . '.ProfileImagePath' : 'NULL';
}

/**
 * Round avatar for a student or staff member: their uploaded profile photo, or the same
 * initial-letter placeholder the sidebar shows when no photo has been uploaded.
 */
function personAvatarHtml(?string $relativePath, string $name, string $extraClass = ''): string
{
    $class = trim('person-avatar ' . $extraClass);
    $relativePath = trim((string) $relativePath);
    $absolutePath = $relativePath !== '' ? profileImageAbsolutePath($relativePath) : null;

    if ($absolutePath !== null && is_file($absolutePath)) {
        return '<span class="' . htmlspecialchars($class) . '" aria-hidden="true">'
            . '<img src="' . htmlspecialchars((string) profileImageUrl($relativePath, getBasePath())) . '" alt="" loading="lazy">'
            . '</span>';
    }

    $initial = mb_strtoupper(mb_substr(trim($name) !== '' ? trim($name) : '?', 0, 1));
    return '<span class="' . htmlspecialchars($class . ' is-placeholder') . '" aria-hidden="true">' . htmlspecialchars($initial) . '</span>';
}

function deleteStoredProfileImage(?string $relativePath): void
{
    if ($relativePath === null) {
        return;
    }

    $absolutePath = profileImageAbsolutePath($relativePath);
    if ($absolutePath !== null && is_file($absolutePath)) {
        unlink($absolutePath);
    }
}

function storeProfileImage(PDO $pdo, string $role, int $userId, array $file): string
{
    [$table, $idColumn] = profileImageAccountConfig($role);
    if (!databaseColumnExists($pdo, $table, 'ProfileImagePath')) {
        throw new RuntimeException('Profile photo storage is not installed. Run the profile image migration first.');
    }

    $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        throw new InvalidArgumentException('Please choose an image to upload.');
    }
    if ($uploadError !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The image upload failed. Please try again.');
    }

    $temporaryPath = (string) ($file['tmp_name'] ?? '');
    $fileSize = (int) ($file['size'] ?? 0);
    if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
        throw new InvalidArgumentException('The selected upload is not valid.');
    }
    if ($fileSize <= 0 || $fileSize > 3 * 1024 * 1024) {
        throw new InvalidArgumentException('Profile photos must be 3MB or smaller.');
    }

    $mimeType = class_exists('finfo')
        ? (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath)
        : mime_content_type($temporaryPath);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!is_string($mimeType) || !isset($extensions[$mimeType])) {
        throw new InvalidArgumentException('Only JPG, PNG, or WEBP profile photos are allowed.');
    }

    $imageInfo = getimagesize($temporaryPath);
    if ($imageInfo === false || $imageInfo[0] < 1 || $imageInfo[1] < 1) {
        throw new InvalidArgumentException('The selected file is not a readable image.');
    }
    if ($imageInfo[0] > 5000 || $imageInfo[1] > 5000) {
        throw new InvalidArgumentException('Profile photos cannot exceed 5000 by 5000 pixels.');
    }

    $uploadDirectory = dirname(__DIR__, 2) . '/public/uploads/profiles';
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
        throw new RuntimeException('The profile photo directory could not be created.');
    }

    $filename = $role . '_' . $userId . '_' . bin2hex(random_bytes(12)) . '.' . $extensions[$mimeType];
    $absolutePath = $uploadDirectory . '/' . $filename;
    if (!move_uploaded_file($temporaryPath, $absolutePath)) {
        throw new RuntimeException('The profile photo could not be saved.');
    }

    $relativePath = 'public/uploads/profiles/' . $filename;
    $oldPath = fetchProfileImagePath($pdo, $role, $userId);

    try {
        $stmt = $pdo->prepare("UPDATE {$table} SET ProfileImagePath = ? WHERE {$idColumn} = ?");
        $stmt->execute([$relativePath, $userId]);
    } catch (Throwable $error) {
        deleteStoredProfileImage($relativePath);
        throw $error;
    }

    if ($oldPath !== $relativePath) {
        deleteStoredProfileImage($oldPath);
    }

    return $relativePath;
}

function removeProfileImage(PDO $pdo, string $role, int $userId): void
{
    [$table, $idColumn] = profileImageAccountConfig($role);
    if (!databaseColumnExists($pdo, $table, 'ProfileImagePath')) {
        throw new RuntimeException('Profile photo storage is not installed. Run the profile image migration first.');
    }

    $oldPath = fetchProfileImagePath($pdo, $role, $userId);
    $stmt = $pdo->prepare("UPDATE {$table} SET ProfileImagePath = NULL WHERE {$idColumn} = ?");
    $stmt->execute([$userId]);
    deleteStoredProfileImage($oldPath);
}

function studentNotificationsEnabled(PDO $pdo, int $studentId): bool
{
    if ($studentId <= 0 || !databaseColumnExists($pdo, 'Students', 'NotificationsEnabled')) {
        return true;
    }

    $stmt = $pdo->prepare("SELECT NotificationsEnabled FROM Students WHERE StudentID = ?");
    $stmt->execute([$studentId]);
    $value = $stmt->fetchColumn();
    return $value === false ? true : (bool) $value;
}

function createNotification(PDO $pdo, string $targetRole, ?int $targetUserId, string $type, string $title, string $body = '', string $link = ''): void
{
    if (!databaseTableExists($pdo, 'Notifications')) {
        return;
    }
    if ($targetRole === 'student' && $targetUserId !== null && !studentNotificationsEnabled($pdo, $targetUserId)) {
        return;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO Notifications (TargetRole, TargetUserID, Type, Title, Body, Link) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$targetRole, $targetUserId, $type, $title, $body ?: null, $link ?: null]);
}

/**
 * Nurses see the clinic staff feed (notifications sent to TargetRole 'admin', such as new student messages)
 * with their own read state in NurseNotificationReads. Only notifications from after the nurse's account
 * was created count, so a new nurse doesn't start with every older notification unread.
 */
function nurseNotificationsReady(PDO $pdo): bool
{
    return databaseTableExists($pdo, 'Notifications')
        && ensureNurseAccountSchema()
        && databaseTableExists($pdo, 'NurseNotificationReads');
}

const NURSE_NOTIFICATION_SCOPE = "n.TargetRole = 'admin' AND n.CreatedAt >= (SELECT CreatedAt FROM Nurses WHERE NurseID = ?)";

function countUnreadNotifications(PDO $pdo, string $targetRole, ?int $targetUserId = null): int
{
    if ($targetRole === 'nurse') {
        if ($targetUserId === null || !nurseNotificationsReady($pdo)) {
            return 0;
        }
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM Notifications n
             WHERE " . NURSE_NOTIFICATION_SCOPE . " AND n.IsRead = 0
               AND NOT EXISTS (SELECT 1 FROM NurseNotificationReads r WHERE r.NotificationID = n.NotificationID AND r.NurseID = ?)"
        );
        $stmt->execute([$targetUserId, $targetUserId]);
        return (int) $stmt->fetchColumn();
    }
    if (!databaseTableExists($pdo, 'Notifications') || !in_array($targetRole, ['admin', 'student'], true)) {
        return 0;
    }
    if ($targetRole === 'student' && $targetUserId !== null && !studentNotificationsEnabled($pdo, $targetUserId)) {
        return 0;
    }

    if ($targetRole === 'admin' && $targetUserId !== null && databaseTableExists($pdo, 'AdminNotificationReads')) {
        // Admin notifications are shared; each admin's read state is tracked separately.
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM Notifications n
             WHERE n.TargetRole = 'admin' AND n.IsRead = 0
               AND NOT EXISTS (SELECT 1 FROM AdminNotificationReads r WHERE r.NotificationID = n.NotificationID AND r.AdminID = ?)"
        );
        $stmt->execute([$targetUserId]);
    } elseif ($targetRole === 'admin') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Notifications WHERE TargetRole = 'admin' AND IsRead = 0");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Notifications WHERE TargetRole = 'student' AND TargetUserID = ? AND IsRead = 0");
        $stmt->execute([$targetUserId]);
    }
    return (int) $stmt->fetchColumn();
}

function fetchNotifications(PDO $pdo, string $targetRole, ?int $targetUserId = null, int $limit = 8): array
{
    if ($targetRole === 'nurse') {
        if ($targetUserId === null || !nurseNotificationsReady($pdo)) {
            return [];
        }
        $stmt = $pdo->prepare(
            "SELECT n.NotificationID, n.TargetRole, n.TargetUserID, n.Type, n.Title, n.Body, n.Link, n.CreatedAt,
                    (n.IsRead = 1 OR r.NurseID IS NOT NULL) AS IsRead
             FROM Notifications n
             LEFT JOIN NurseNotificationReads r ON r.NotificationID = n.NotificationID AND r.NurseID = ?
             WHERE " . NURSE_NOTIFICATION_SCOPE . "
             ORDER BY n.CreatedAt DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute([$targetUserId, $targetUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    if (!databaseTableExists($pdo, 'Notifications') || !in_array($targetRole, ['admin', 'student'], true)) {
        return [];
    }
    if ($targetRole === 'student' && $targetUserId !== null && !studentNotificationsEnabled($pdo, $targetUserId)) {
        return [];
    }

    if ($targetRole === 'admin' && $targetUserId !== null && databaseTableExists($pdo, 'AdminNotificationReads')) {
        $stmt = $pdo->prepare(
            "SELECT n.NotificationID, n.TargetRole, n.TargetUserID, n.Type, n.Title, n.Body, n.Link, n.CreatedAt,
                    (n.IsRead = 1 OR r.AdminID IS NOT NULL) AS IsRead
             FROM Notifications n
             LEFT JOIN AdminNotificationReads r ON r.NotificationID = n.NotificationID AND r.AdminID = ?
             WHERE n.TargetRole = 'admin'
             ORDER BY n.CreatedAt DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute([$targetUserId]);
    } elseif ($targetRole === 'admin') {
        $stmt = $pdo->prepare(
            "SELECT * FROM Notifications
             WHERE TargetRole = 'admin'
             ORDER BY CreatedAt DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare(
            "SELECT * FROM Notifications
             WHERE TargetRole = 'student' AND TargetUserID = ?
             ORDER BY CreatedAt DESC
             LIMIT " . (int) $limit
        );
        $stmt->execute([$targetUserId]);
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markNotificationsRead(PDO $pdo, string $targetRole, ?int $targetUserId = null): void
{
    if ($targetRole === 'nurse') {
        // Mark read for this nurse only.
        if ($targetUserId !== null && nurseNotificationsReady($pdo)) {
            $stmt = $pdo->prepare(
                "INSERT IGNORE INTO NurseNotificationReads (NotificationID, NurseID)
                 SELECT n.NotificationID, ? FROM Notifications n WHERE " . NURSE_NOTIFICATION_SCOPE . " AND n.IsRead = 0"
            );
            $stmt->execute([$targetUserId, $targetUserId]);
        }
        return;
    }
    // An unknown role must never fall through to the student query.
    if (!databaseTableExists($pdo, 'Notifications') || !in_array($targetRole, ['admin', 'student'], true)) {
        return;
    }

    if ($targetRole === 'admin' && $targetUserId !== null && databaseTableExists($pdo, 'AdminNotificationReads')) {
        // Mark read for this admin only.
        $stmt = $pdo->prepare(
            "INSERT IGNORE INTO AdminNotificationReads (NotificationID, AdminID)
             SELECT NotificationID, ? FROM Notifications WHERE TargetRole = 'admin' AND IsRead = 0"
        );
        $stmt->execute([$targetUserId]);
    } elseif ($targetRole === 'admin') {
        $stmt = $pdo->prepare("UPDATE Notifications SET IsRead = 1 WHERE TargetRole = 'admin' AND IsRead = 0");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("UPDATE Notifications SET IsRead = 1 WHERE TargetRole = 'student' AND TargetUserID = ? AND IsRead = 0");
        $stmt->execute([$targetUserId]);
    }
}

// Time in/out columns for visit queries; visits recorded before check-ins existed fall back to CreatedAt.
function clinicVisitCheckInSql(PDO $pdo): array
{
    if (!databaseTableExists($pdo, 'ClinicCheckIns')) {
        return ["v.CreatedAt AS TimeIn, NULL AS TimeOut, 0 AS IsInClinic", ''];
    }

    return [
        "COALESCE(ci.CheckedInAt, v.CreatedAt) AS TimeIn,
         ci.CheckedOutAt AS TimeOut,
         (ci.CheckInID IS NOT NULL AND ci.CheckedOutAt IS NULL) AS IsInClinic",
        'LEFT JOIN ClinicCheckIns ci ON ci.VisitID = v.VisitID',
    ];
}

/** "Consulted by" for visit queries: the admin who recorded the visit, or the nurse when a nurse did. */
function clinicVisitConsultedBySql(PDO $pdo): array
{
    if (!databaseColumnExists($pdo, 'ClinicVisits', 'NurseID')) {
        return ['a.FullName AS AdminName', ''];
    }
    return ['COALESCE(a.FullName, cn.FullName) AS AdminName', 'LEFT JOIN Nurses cn ON cn.NurseID = v.NurseID'];
}

function visitTimesHtml(array $visit): string
{
    $timeIn = !empty($visit['TimeIn']) ? strtotime($visit['TimeIn']) : false;
    $timeOut = !empty($visit['TimeOut']) ? strtotime($visit['TimeOut']) : false;

    $inLabel = $timeIn ? date('g:i A', $timeIn) : '—';
    if ($timeOut) {
        // Show the date too when a visit runs past midnight.
        $outLabel = $timeIn && date('Y-m-d', $timeIn) !== date('Y-m-d', $timeOut)
            ? date('M d, g:i A', $timeOut)
            : date('g:i A', $timeOut);
    } else {
        $outLabel = (int) ($visit['IsInClinic'] ?? 0) === 1 ? 'Still in clinic' : '—';
    }

    return '<span class="visit-time-row">'
        . '<span class="visit-time is-in"><b>Time in</b>'
        . ($timeIn ? '<time datetime="' . date('c', $timeIn) . '">' . $inLabel . '</time>' : $inLabel)
        . '</span>'
        . '<span class="visit-time is-out' . (!$timeOut && (int) ($visit['IsInClinic'] ?? 0) === 1 ? ' is-pending' : '') . '"><b>Time out</b>'
        . ($timeOut ? '<time datetime="' . date('c', $timeOut) . '">' . $outLabel . '</time>' : $outLabel)
        . '</span>'
        . '</span>';
}

// Joins that give visit queries the patient (student or staff) and the admin who recorded the visit.
const CLINIC_VISIT_PEOPLE_JOINS = "LEFT JOIN Students s ON s.StudentID = v.StudentID
            LEFT JOIN Admins staff ON staff.AdminID = v.StaffID
            LEFT JOIN Admins a ON a.AdminID = v.AdminID";

/** Whether nurses can be checked in as patients (ClinicVisits/ClinicCheckIns.PatientNurseID exist). */
function nursePatientsSupported(PDO $pdo): bool
{
    static $supported = null;
    if ($supported === null) {
        // Install first, so the column checks below see the result on a database that is being upgraded.
        $supported = ensureNurseAccountSchema()
            && databaseColumnExists($pdo, 'ClinicVisits', 'PatientNurseID')
            && databaseColumnExists($pdo, 'ClinicCheckIns', 'PatientNurseID');
    }
    return $supported;
}

/** Whether faculty, non-teaching staff and visitors can be checked in (OtherPatients table + OtherPatientID). */
function otherPatientsSupported(PDO $pdo): bool
{
    static $supported = null;
    if ($supported === null) {
        $supported = ensureNurseAccountSchema()
            && databaseTableExists($pdo, 'OtherPatients')
            && databaseColumnExists($pdo, 'ClinicVisits', 'OtherPatientID')
            && databaseColumnExists($pdo, 'ClinicCheckIns', 'OtherPatientID');
    }
    return $supported;
}

/** Kinds of patient that can be added at Check In when they aren't a student, nurse or admin. */
function otherPatientTypes(): array
{
    return ['Faculty', 'Non-teaching staff', 'Visitor', 'Other'];
}

/** The ClinicVisits / ClinicCheckIns column that holds each kind of patient. */
function clinicPatientColumn(string $personType): string
{
    return match ($personType) {
        'staff' => 'StaffID',
        'nurse' => 'PatientNurseID',
        'other' => 'OtherPatientID',
        default => 'StudentID',
    };
}

function otherPatientName(PDO $pdo, int $otherPatientId): ?string
{
    if ($otherPatientId <= 0 || !otherPatientsSupported($pdo)) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT FullName FROM OtherPatients WHERE OtherPatientID = ?');
    $stmt->execute([$otherPatientId]);
    $name = $stmt->fetchColumn();
    return $name === false ? null : (string) $name;
}

/**
 * Adds a patient who isn't a student, nurse or admin (faculty, non-teaching staff, visitor) so they can be
 * checked in and their visits logged. Returns the new OtherPatientID.
 */
function createOtherPatient(PDO $pdo, array $data): int
{
    if (!otherPatientsSupported($pdo)) {
        throw new RuntimeException('Adding other patients is not installed yet. Run database/migrations/add_other_patients.sql.');
    }
    $fullName = preg_replace('/\s+/u', ' ', trim((string) ($data['full_name'] ?? '')));
    $patientType = trim((string) ($data['patient_type'] ?? ''));
    $department = trim((string) ($data['department'] ?? ''));
    $errors = [];
    if ($fullName === '') {
        $errors[] = "Enter the patient's full name.";
    } elseif (mb_strlen($fullName) > 120) {
        $errors[] = 'Full name must not exceed 120 characters.';
    }
    if (!in_array($patientType, otherPatientTypes(), true)) {
        $errors[] = 'Choose whether the patient is faculty, non-teaching staff, a visitor, or other.';
    }
    if (mb_strlen($department) > 100) {
        $errors[] = 'Department or office must not exceed 100 characters.';
    }
    $contactNumber = validateMobileNumber((string) ($data['contact_number'] ?? ''), 'Contact number', $errors);
    if ($errors) {
        throw new InvalidArgumentException($errors[0]);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO OtherPatients (FullName, PatientType, Department, ContactNumber) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$fullName, $patientType, $department !== '' ? $department : null, $contactNumber !== '' ? $contactNumber : null]);
    $otherPatientId = (int) $pdo->lastInsertId();
    auditLog(
        $pdo,
        'visits',
        'patient.added',
        'Added ' . mb_strtolower($patientType) . ' patient ' . $fullName . ($department !== '' ? ' (' . $department . ')' : '') . ' for clinic check-in.',
        ['subject_role' => null]
    );
    return $otherPatientId;
}

/** CLINIC_VISIT_PEOPLE_JOINS plus the nurse (pn) and other (op) patients when supported. */
function clinicVisitPeopleJoins(PDO $pdo): string
{
    return CLINIC_VISIT_PEOPLE_JOINS
        . (nursePatientsSupported($pdo) ? "\n            LEFT JOIN Nurses pn ON pn.NurseID = v.PatientNurseID" : '')
        . (otherPatientsSupported($pdo) ? "\n            LEFT JOIN OtherPatients op ON op.OtherPatientID = v.OtherPatientID" : '');
}

/** Extra patient name columns for the visit search, matching the joins in clinicVisitPeopleJoins(). */
function clinicVisitExtraNameColumns(PDO $pdo): string
{
    return (nursePatientsSupported($pdo) ? ', pn.FullName' : '') . (otherPatientsSupported($pdo) ? ', op.FullName' : '');
}

/**
 * WHERE clause shared by the clinic visit list and count queries. $filters (optional):
 * 'search' matches patient, complaint, treatment, medicine, or staff name;
 * 'status' is open / completed / follow-up; 'date' (Y-m-d) keeps only visits on that day.
 */
function clinicVisitWhereSql(?int $studentId, array $filters, string $extraNameColumns = ''): array
{
    $where = [];
    $params = [];

    if ($studentId !== null) {
        $where[] = 'v.StudentID = ?';
        $params[] = $studentId;
    }
    $search = trim((string) ($filters['search'] ?? ''));
    if ($search !== '') {
        // $extraNameColumns comes from clinicVisitExtraNameColumns(), so nurse and other patients are searchable.
        $where[] = "(CONCAT_WS(' ', s.FirstName, s.LastName, staff.FullName{$extraNameColumns}) LIKE ?
                     OR v.Complaint LIKE ? OR v.Treatment LIKE ? OR v.MedicineGiven LIKE ? OR a.FullName LIKE ?)";
        $term = '%' . $search . '%';
        array_push($params, $term, $term, $term, $term, $term);
    }
    if (in_array($filters['status'] ?? '', ['open', 'completed', 'follow-up'], true)) {
        $where[] = 'v.Status = ?';
        $params[] = $filters['status'];
    }
    $date = (string) ($filters['date'] ?? '');
    $day = $date !== '' ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
    if ($day && $day->format('Y-m-d') === $date) {
        // From midnight to the next midnight in the clinic's time zone (the DB session uses it too).
        $where[] = 'v.CreatedAt >= ? AND v.CreatedAt < ?';
        array_push($params, $day->format('Y-m-d 00:00:00'), $day->modify('+1 day')->format('Y-m-d 00:00:00'));
    }

    return [$where ? ' WHERE ' . implode(' AND ', $where) : '', $params];
}

/** Clinic visits, newest first. See clinicVisitWhereSql() for $filters; $limit 0 means no limit. */
function fetchClinicVisits(PDO $pdo, ?int $studentId = null, int $limit = 0, array $filters = [], int $offset = 0): array
{
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return [];
    }

    [$checkInSelect, $checkInJoin] = clinicVisitCheckInSql($pdo);
    $nursePatients = nursePatientsSupported($pdo);
    $otherPatients = otherPatientsSupported($pdo);
    [$whereSql, $params] = clinicVisitWhereSql($studentId, $filters, clinicVisitExtraNameColumns($pdo));
    $studentPhoto = profileImageSelectSql($pdo, 'Students', 's');
    $staffPhoto = profileImageSelectSql($pdo, 'Admins', 'staff');
    [$consultedSelect, $consultedJoin] = clinicVisitConsultedBySql($pdo);
    // A patient is a student, an admin (staff) or, when supported, a nurse (pn) or another patient (op).
    $patientName = ($nursePatients ? 'WHEN v.PatientNurseID IS NOT NULL THEN pn.FullName ' : '')
        . ($otherPatients ? 'WHEN v.OtherPatientID IS NOT NULL THEN op.FullName ' : '');
    $patientType = ($nursePatients ? "WHEN v.PatientNurseID IS NOT NULL THEN 'nurse' " : '')
        . ($otherPatients ? "WHEN v.OtherPatientID IS NOT NULL THEN 'other' " : '');
    // Other patients have no photo; the ELSE branch is NULL for them because no admin is joined.
    $nursePhoto = $nursePatients ? 'WHEN v.PatientNurseID IS NOT NULL THEN ' . profileImageSelectSql($pdo, 'Nurses', 'pn') . ' ' : '';
    $personId = 'COALESCE(v.StudentID, v.StaffID' . ($nursePatients ? ', v.PatientNurseID' : '') . ($otherPatients ? ', v.OtherPatientID' : '') . ')';
    $otherPatientType = $otherPatients ? 'op.PatientType' : 'NULL';
    $sql = "SELECT v.*,
               CASE WHEN v.StudentID IS NOT NULL THEN s.FirstName {$patientName}ELSE staff.FullName END AS FirstName,
               CASE WHEN v.StudentID IS NOT NULL THEN s.LastName ELSE '' END AS LastName,
               CASE WHEN v.StudentID IS NOT NULL THEN 'student' {$patientType}ELSE 'staff' END AS PersonType,
               CASE WHEN v.StudentID IS NOT NULL THEN {$studentPhoto} {$nursePhoto}ELSE {$staffPhoto} END AS PersonPhotoPath,
               {$otherPatientType} AS OtherPatientType,
               {$personId} AS PersonID,
               {$consultedSelect},
               {$checkInSelect}
            FROM ClinicVisits v
            " . clinicVisitPeopleJoins($pdo) . "
            {$consultedJoin}
            {$checkInJoin}
            {$whereSql}
            ORDER BY v.CreatedAt DESC, v.VisitID DESC";
    if ($limit > 0) {
        $sql .= ' LIMIT ' . (int) $limit . ' OFFSET ' . max(0, $offset);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countClinicVisits(PDO $pdo, ?int $studentId = null, array $filters = []): int
{
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return 0;
    }

    [$whereSql, $params] = clinicVisitWhereSql($studentId, $filters, clinicVisitExtraNameColumns($pdo));
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ClinicVisits v " . clinicVisitPeopleJoins($pdo) . $whereSql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/**
 * The most frequent visit complaints in the last $days days (today included), for the admin dashboard.
 * Complaints are free text, so they are grouped case-insensitively after trimming.
 * Returns ['total' => visits with a complaint, 'items' => [['label', 'cases', 'share' (% of total)], ...]].
 */
function fetchCommonComplaints(PDO $pdo, int $days = 30, int $limit = 3): array
{
    $result = ['total' => 0, 'items' => []];
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return $result;
    }

    $since = (new DateTimeImmutable('today'))->modify('-' . max(0, $days - 1) . ' days')->format('Y-m-d 00:00:00');
    $complaintWhere = "WHERE CreatedAt >= ? AND TRIM(COALESCE(Complaint, '')) <> ''";

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ClinicVisits {$complaintWhere}");
    $stmt->execute([$since]);
    $result['total'] = (int) $stmt->fetchColumn();
    if ($result['total'] === 0) {
        return $result;
    }

    $stmt = $pdo->prepare(
        "SELECT LOWER(TRIM(Complaint)) AS ComplaintKey, MIN(TRIM(Complaint)) AS Label, COUNT(*) AS Cases
         FROM ClinicVisits {$complaintWhere}
         GROUP BY ComplaintKey
         ORDER BY Cases DESC, ComplaintKey ASC
         LIMIT " . max(1, $limit)
    );
    $stmt->execute([$since]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $label = (string) $row['Label'];
        $result['items'][] = [
            'label' => mb_strtoupper(mb_substr($label, 0, 1)) . mb_substr($label, 1),
            'cases' => (int) $row['Cases'],
            'share' => round((int) $row['Cases'] / $result['total'] * 100, 1),
        ];
    }

    return $result;
}

/** Visit totals per status ('open', 'completed', 'follow-up') for the same filters. */
function countClinicVisitsByStatus(PDO $pdo, array $filters = []): array
{
    $counts = ['open' => 0, 'completed' => 0, 'follow-up' => 0];
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return $counts;
    }

    [$whereSql, $params] = clinicVisitWhereSql(null, $filters, clinicVisitExtraNameColumns($pdo));
    $stmt = $pdo->prepare(
        "SELECT v.Status, COUNT(*) AS Total FROM ClinicVisits v " . clinicVisitPeopleJoins($pdo) . $whereSql . " GROUP BY v.Status"
    );
    $stmt->execute($params);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (array_key_exists($row['Status'], $counts)) {
            $counts[$row['Status']] = (int) $row['Total'];
        }
    }
    return $counts;
}

function searchCheckInPeople(PDO $pdo, string $query, int $limit = 10): array
{
    $query = trim($query);
    $studentWhere = "WHERE NOT EXISTS (
        SELECT 1 FROM ClinicCheckIns c
        WHERE c.StudentID = s.StudentID AND c.CheckedOutAt IS NULL
    )";
    $staffWhere = "WHERE NOT EXISTS (
        SELECT 1 FROM ClinicCheckIns c
        WHERE c.StaffID = staff.AdminID AND c.CheckedOutAt IS NULL
    )";
    $nurseWhere = "WHERE NOT EXISTS (
        SELECT 1 FROM ClinicCheckIns c
        WHERE c.PatientNurseID = pn.NurseID AND c.CheckedOutAt IS NULL
    )";
    $otherWhere = "WHERE NOT EXISTS (
        SELECT 1 FROM ClinicCheckIns c
        WHERE c.OtherPatientID = op.OtherPatientID AND c.CheckedOutAt IS NULL
    )";
    $nursePatients = nursePatientsSupported($pdo);
    $otherPatients = otherPatientsSupported($pdo);
    $params = [];

    if ($query !== '') {
        $term = '%' . $query . '%';
        $studentWhere .= " AND (CONCAT(s.FirstName, ' ', s.LastName) LIKE ? OR CAST(s.StudentID AS CHAR) LIKE ?)";
        $staffWhere .= ' AND staff.FullName LIKE ?';
        $nurseWhere .= ' AND pn.FullName LIKE ?';
        $otherWhere .= ' AND op.FullName LIKE ?';
        // One value per placeholder, in the order the UNION parts appear below.
        $params = array_merge([$term, $term, $term], $nursePatients ? [$term] : [], $otherPatients ? [$term] : []);
    }

    $studentPhoto = profileImageSelectSql($pdo, 'Students', 's');
    $staffPhoto = profileImageSelectSql($pdo, 'Admins', 'staff');
    // Nurses can be patients too; they are listed with their position.
    $nurseUnion = $nursePatients
        ? "UNION ALL
            SELECT 'nurse' AS PersonType,
                   pn.NurseID AS PersonID,
                   pn.FullName AS DisplayName,
                   pn.Position AS Details,
                   CONCAT_WS(' ', pn.FullName, pn.Position) AS SearchText,
                   " . profileImageSelectSql($pdo, 'Nurses', 'pn') . " AS PhotoPath,
                   'Nurse' AS TypeLabel
            FROM Nurses pn
            {$nurseWhere}"
        : '';
    // Faculty, non-teaching staff and visitors added at Check In; listed with their type and office.
    $otherUnion = $otherPatients
        ? "UNION ALL
            SELECT 'other' AS PersonType,
                   op.OtherPatientID AS PersonID,
                   op.FullName AS DisplayName,
                   COALESCE(op.Department, '') AS Details,
                   CONCAT_WS(' ', op.FullName, op.PatientType, op.Department) AS SearchText,
                   NULL AS PhotoPath,
                   op.PatientType AS TypeLabel
            FROM OtherPatients op
            {$otherWhere}"
        : '';
    $sql = "SELECT 'student' AS PersonType,
                   s.StudentID AS PersonID,
                   CONCAT(s.FirstName, ' ', s.LastName) AS DisplayName,
                   CONCAT('#', s.StudentID) AS Details,
                   CONCAT_WS(' ', s.FirstName, s.LastName, s.StudentID) AS SearchText,
                   {$studentPhoto} AS PhotoPath,
                   'Student' AS TypeLabel
            FROM Students s
            {$studentWhere}
            UNION ALL
            SELECT 'staff' AS PersonType,
                   staff.AdminID AS PersonID,
                   staff.FullName AS DisplayName,
                   '' AS Details,
                   staff.FullName AS SearchText,
                   {$staffPhoto} AS PhotoPath,
                   'Staff' AS TypeLabel
            FROM Admins staff
            {$staffWhere}
            {$nurseUnion}
            {$otherUnion}
            ORDER BY DisplayName ASC";
    if ($limit > 0) {
        $sql .= ' LIMIT ' . (int) $limit;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function fetchCheckInPerson(PDO $pdo, string $personType, int $personId): ?array
{
    $personTypes = array_merge(
        ['student', 'staff'],
        nursePatientsSupported($pdo) ? ['nurse'] : [],
        otherPatientsSupported($pdo) ? ['other'] : []
    );
    if ($personId <= 0 || !in_array($personType, $personTypes, true)) {
        return null;
    }

    if ($personType === 'other') {
        $stmt = $pdo->prepare(
            "SELECT 'other' AS PersonType, OtherPatientID AS PersonID, FullName AS DisplayName,
                    CONCAT_WS(' · ', NULLIF(Department, ''), NULLIF(ContactNumber, '')) AS Details,
                    NULL AS PhotoPath, PatientType AS TypeLabel
             FROM OtherPatients WHERE OtherPatientID = ?"
        );
    } elseif ($personType === 'nurse') {
        $photo = profileImageSelectSql($pdo, 'Nurses', 'Nurses');
        $stmt = $pdo->prepare(
            "SELECT 'nurse' AS PersonType, NurseID AS PersonID, FullName AS DisplayName,
                    Position AS Details, {$photo} AS PhotoPath, 'Nurse' AS TypeLabel
             FROM Nurses WHERE NurseID = ?"
        );
    } elseif ($personType === 'student') {
        $photo = profileImageSelectSql($pdo, 'Students', 'Students');
        $stmt = $pdo->prepare(
            "SELECT 'student' AS PersonType, StudentID AS PersonID,
                    CONCAT(FirstName, ' ', LastName) AS DisplayName,
                    CONCAT('#', StudentID) AS Details, {$photo} AS PhotoPath, 'Student' AS TypeLabel
             FROM Students WHERE StudentID = ?"
        );
    } else {
        $photo = profileImageSelectSql($pdo, 'Admins', 'Admins');
        $stmt = $pdo->prepare(
            "SELECT 'staff' AS PersonType, AdminID AS PersonID, FullName AS DisplayName,
                    '' AS Details, {$photo} AS PhotoPath, 'Staff' AS TypeLabel
             FROM Admins WHERE AdminID = ?"
        );
    }
    $stmt->execute([$personId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function fetchClinicVisitsForPerson(PDO $pdo, string $personType, int $personId): array
{
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return [];
    }
    $column = clinicPatientColumn($personType);
    if (($personType === 'nurse' && !nursePatientsSupported($pdo)) || ($personType === 'other' && !otherPatientsSupported($pdo))) {
        return [];
    }
    [$checkInSelect, $checkInJoin] = clinicVisitCheckInSql($pdo);
    [$consultedSelect, $consultedJoin] = clinicVisitConsultedBySql($pdo);
    $stmt = $pdo->prepare(
        "SELECT v.*, {$consultedSelect}, {$checkInSelect}
         FROM ClinicVisits v
         LEFT JOIN Admins a ON a.AdminID = v.AdminID
         {$consultedJoin}
         {$checkInJoin}
         WHERE v.{$column} = ?
         ORDER BY v.CreatedAt DESC"
    );
    $stmt->execute([$personId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countActiveClinicCheckIns(PDO $pdo): int
{
    if (!databaseTableExists($pdo, 'ClinicCheckIns')) {
        return 0;
    }
    return (int) $pdo->query("SELECT COUNT(*) FROM ClinicCheckIns WHERE CheckedOutAt IS NULL")->fetchColumn();
}

function fetchActiveClinicCheckIns(PDO $pdo): array
{
    if (!databaseTableExists($pdo, 'ClinicCheckIns')) {
        return [];
    }
    $studentPhoto = profileImageSelectSql($pdo, 'Students', 's');
    $staffPhoto = profileImageSelectSql($pdo, 'Admins', 'staff');
    // Nurse (pn) and other (op) patients are included when supported; other patients have no photo,
    // and the ELSE photo branch is NULL for them because no admin is joined.
    $nursePatients = nursePatientsSupported($pdo);
    $otherPatients = otherPatientsSupported($pdo);
    $nurseName = ($nursePatients ? ', pn.FullName' : '') . ($otherPatients ? ', op.FullName' : '');
    $nurseDetails = ($nursePatients ? "WHEN c.PersonType = 'nurse' THEN CONCAT(pn.Position, ' · Nurse') " : '')
        . ($otherPatients ? "WHEN c.PersonType = 'other' THEN CONCAT_WS(' · ', NULLIF(op.Department, ''), op.PatientType) " : '');
    $nursePhoto = $nursePatients ? "WHEN c.PersonType = 'nurse' THEN " . profileImageSelectSql($pdo, 'Nurses', 'pn') . ' ' : '';
    $nurseJoin = ($nursePatients ? 'LEFT JOIN Nurses pn ON pn.NurseID = c.PatientNurseID' : '')
        . ($otherPatients ? "\n         LEFT JOIN OtherPatients op ON op.OtherPatientID = c.OtherPatientID" : '');
    return $pdo->query(
        "SELECT c.*, v.Complaint, v.Status,
                COALESCE(CONCAT(s.FirstName, ' ', s.LastName), staff.FullName{$nurseName}) AS DisplayName,
                CASE WHEN c.PersonType = 'student'
                     THEN CONCAT('#', s.StudentID, ' · Student')
                     {$nurseDetails}ELSE CONCAT('@', staff.Username, ' · Staff') END AS Details,
                CASE WHEN c.PersonType = 'student' THEN {$studentPhoto} {$nursePhoto}ELSE {$staffPhoto} END AS PhotoPath
         FROM ClinicCheckIns c
         JOIN ClinicVisits v ON v.VisitID = c.VisitID
         LEFT JOIN Students s ON s.StudentID = c.StudentID
         LEFT JOIN Admins staff ON staff.AdminID = c.StaffID
         {$nurseJoin}
         WHERE c.CheckedOutAt IS NULL
         ORDER BY c.CheckedInAt ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Exactly one of $adminId / $nurseId is the staff member recording the visit. AdminID and
 * CheckedInByAdminID only reference admins, so a nurse's visit is stored in ClinicVisits.NurseID.
 */
function createClinicCheckIn(PDO $pdo, string $personType, int $personId, ?int $adminId, array $data, ?int $nurseId = null): int
{
    if (!databaseTableExists($pdo, 'ClinicCheckIns')) {
        throw new RuntimeException('Clinic check-in storage is not installed.');
    }
    $person = fetchCheckInPerson($pdo, $personType, $personId);
    if (!$person) {
        throw new InvalidArgumentException('Choose an existing student, nurse or staff member.');
    }

    $complaint = trim((string) ($data['complaint'] ?? ''));
    if ($complaint === '') {
        throw new InvalidArgumentException('Complaint is required.');
    }
    $lengthErrors = [];
    validateMaxLengths($data, [
        'complaint' => ['Complaint', 2000], 'vitals' => ['Vitals', 255], 'assessment' => ['Assessment', 5000],
        'treatment' => ['Treatment', 5000], 'disposition' => ['Disposition', 100],
    ], $lengthErrors);
    if ($lengthErrors) {
        throw new InvalidArgumentException($lengthErrors[0]);
    }
    // A new check-in is Open or Follow-up; an Open visit becomes Completed when the patient checks out.
    $status = trim((string) ($data['status'] ?? '')) ?: 'open';
    if (!in_array($status, ['open', 'follow-up'], true)) {
        throw new InvalidArgumentException('A new check-in can only be Open or Follow-up. Open visits are completed when the patient checks out.');
    }
    $medicineIdValue = trim((string) ($data['medicine_id'] ?? ''));
    $medicineId = $medicineIdValue === '' ? null : filter_var($medicineIdValue, FILTER_VALIDATE_INT);
    if ($medicineId === false || ($medicineId !== null && $medicineId <= 0)) {
        throw new InvalidArgumentException('Choose a valid medicine.');
    }
    $medicineDosage = trim((string) ($data['medicine_dosage'] ?? '')) ?: null;
    $medicineQuantityValue = trim((string) ($data['medicine_quantity'] ?? ''));
    $medicineQuantity = null;
    if ($medicineQuantityValue !== '') {
        $validatedQuantity = filter_var(
            $medicineQuantityValue,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 999999]]
        );
        if ($validatedQuantity === false) {
            throw new InvalidArgumentException('Medicine quantity must be a whole number greater than zero.');
        }
        $medicineQuantity = $validatedQuantity;
    }
    if ($medicineDosage !== null && mb_strlen($medicineDosage) > 120) {
        throw new InvalidArgumentException('Medicine dosage is too long.');
    }
    if ($medicineId !== null && $medicineDosage === null) {
        throw new InvalidArgumentException('Enter the dosage given for the selected medicine.');
    }
    if ($medicineId !== null && $medicineQuantity === null) {
        throw new InvalidArgumentException('Enter the quantity given for the selected medicine.');
    }
    if ($medicineId === null && ($medicineDosage !== null || $medicineQuantity !== null)) {
        throw new InvalidArgumentException('Choose a medicine before entering a dosage or quantity.');
    }

    // Joins a transaction the caller already started (e.g. adding a new patient and their visit together).
    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) {
        $pdo->beginTransaction();
    }
    try {
        // fetchCheckInPerson() above only accepts 'nurse' and 'other' when their columns exist.
        $personColumn = clinicPatientColumn($personType);
        $duplicate = $pdo->prepare(
            "SELECT COUNT(*) FROM ClinicCheckIns WHERE {$personColumn} = ? AND CheckedOutAt IS NULL FOR UPDATE"
        );
        $duplicate->execute([$personId]);
        if ((int) $duplicate->fetchColumn() > 0) {
            throw new InvalidArgumentException($person['DisplayName'] . ' is already checked in.');
        }

        $medicineName = null;
        if ($medicineId !== null) {
            $medicine = $pdo->prepare(
                "SELECT MedicineID, Name, Stock FROM Medicines WHERE MedicineID = ?"
                . (medicineArchiveInstalled($pdo) ? ' AND IsArchived = 0' : '')
                . ' FOR UPDATE'
            );
            $medicine->execute([$medicineId]);
            $medicineRow = $medicine->fetch(PDO::FETCH_ASSOC);
            if (!$medicineRow) {
                throw new InvalidArgumentException('The selected medicine was not found.');
            }
            if ((int) $medicineRow['Stock'] < $medicineQuantity) {
                throw new InvalidArgumentException(
                    'Only ' . (int) $medicineRow['Stock'] . ' unit(s) of ' . $medicineRow['Name'] . ' are available.'
                );
            }
            $medicineName = $medicineRow['Name'];
        }

        $studentId = $personType === 'student' ? $personId : null;
        $staffId = $personType === 'staff' ? $personId : null;
        // A nurse patient (PatientNurseID, not NurseID, which is the nurse recording the visit) or another
        // patient (OtherPatientID) gets one extra column on both the visit and the check-in.
        $extraPatientColumn = in_array($personType, ['nurse', 'other'], true) ? clinicPatientColumn($personType) : null;
        $patientColumn = $extraPatientColumn !== null ? $extraPatientColumn . ', ' : '';
        $patientPlaceholder = $extraPatientColumn !== null ? '?, ' : '';
        $patientValue = $extraPatientColumn !== null ? [$personId] : [];
        $recordNurse = $nurseId !== null && databaseColumnExists($pdo, 'ClinicVisits', 'NurseID');
        $nurseColumn = $recordNurse ? 'NurseID, ' : '';
        $nursePlaceholder = $recordNurse ? '?, ' : '';
        $visit = $pdo->prepare(
            "INSERT INTO ClinicVisits
             (StudentID, StaffID, {$patientColumn}AdminID, {$nurseColumn}Complaint, Vitals, Assessment, Treatment,
              MedicineGiven, MedicineID, MedicineDosage, MedicineQuantity, Status, Disposition, FollowUpDate)
             VALUES (?, ?, {$patientPlaceholder}?, {$nursePlaceholder}?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $visit->execute(array_merge([$studentId, $staffId], $patientValue, [$adminId], $recordNurse ? [$nurseId] : [], [
            $complaint,
            trim((string) ($data['vitals'] ?? '')) ?: null,
            trim((string) ($data['assessment'] ?? '')) ?: null,
            trim((string) ($data['treatment'] ?? '')) ?: null,
            $medicineName,
            $medicineId,
            $medicineDosage,
            $medicineQuantity,
            $status,
            trim((string) ($data['disposition'] ?? '')) ?: null,
            // Only follow-up visits keep a follow-up date.
            $status === 'follow-up' ? (trim((string) ($data['follow_up_date'] ?? '')) ?: null) : null,
        ]));
        $visitId = (int) $pdo->lastInsertId();

        $checkIn = $pdo->prepare(
            "INSERT INTO ClinicCheckIns
             (PersonType, StudentID, StaffID, {$patientColumn}VisitID, CheckedInByAdminID, CheckedInAt)
             VALUES (?, ?, ?, {$patientPlaceholder}?, ?, NOW())"
        );
        $checkIn->execute(array_merge(
            [$personType, $studentId, $staffId],
            $patientValue,
            [$visitId, $adminId]
        ));
        $checkInId = (int) $pdo->lastInsertId();

        if ($medicineId !== null) {
            $deductStock = $pdo->prepare(
                "UPDATE Medicines SET Stock = Stock - ?
                 WHERE MedicineID = ? AND Stock >= ?"
            );
            $deductStock->execute([$medicineQuantity, $medicineId, $medicineQuantity]);
            if ($deductStock->rowCount() !== 1) {
                throw new RuntimeException('Medicine stock changed before the check-in was saved. Please try again.');
            }
        }

        if ($studentId !== null) {
            createNotification(
                $pdo,
                'student',
                $studentId,
                'clinic_visit',
                'Clinic visit recorded',
                'Your clinic visit record has been updated.',
                getBasePath() . '/students/profile.php'
            );
        }
        auditLog(
            $pdo,
            'visits',
            'visit.checked_in',
            'Checked in ' . $person['DisplayName'] . ' (' . mb_strtolower((string) ($person['TypeLabel'] ?? $personType)) . ') to the clinic. Complaint: '
                . mb_strimwidth($complaint, 0, 160, '...')
                . ($medicineName !== null ? '. Medicine given: ' . $medicineName . ' x' . $medicineQuantity : '') . '.',
            ($adminId !== null ? ['actor_role' => 'admin', 'actor_id' => $adminId] : [])
                + match ($personType) {
                    'nurse' => nurseAuditSubject($personId),
                    // Other patients have no account; the entry is clinic-wide and names them in the description.
                    'other' => ['subject_role' => null],
                    'staff' => ['subject_role' => 'admin', 'subject_id' => $personId],
                    default => ['subject_role' => 'student', 'subject_id' => $personId],
                }
        );
        if ($ownsTransaction) {
            $pdo->commit();
        }
        return $checkInId;
    } catch (Throwable $error) {
        // A caller's transaction is rolled back by that caller.
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

// Returns the recorded check-out timestamp.
function checkOutClinicCheckIn(PDO $pdo, int $checkInId): string
{
    if (!databaseTableExists($pdo, 'ClinicCheckIns') || $checkInId <= 0) {
        throw new InvalidArgumentException('Choose a valid active check-in.');
    }
    $pdo->beginTransaction();
    try {
        $visit = $pdo->prepare(
            "SELECT * FROM ClinicCheckIns
             WHERE CheckInID = ? AND CheckedOutAt IS NULL FOR UPDATE"
        );
        $visit->execute([$checkInId]);
        $checkIn = $visit->fetch(PDO::FETCH_ASSOC) ?: [];
        $visitId = (int) ($checkIn['VisitID'] ?? 0);
        if ($visitId <= 0) {
            throw new InvalidArgumentException('This person is no longer checked in.');
        }
        $checkedOutAt = (string) $pdo->query('SELECT NOW()')->fetchColumn();
        $stmt = $pdo->prepare("UPDATE ClinicCheckIns SET CheckedOutAt = ? WHERE CheckInID = ?");
        $stmt->execute([$checkedOutAt, $checkInId]);
        $closeVisit = $pdo->prepare("UPDATE ClinicVisits SET Status = 'completed' WHERE VisitID = ? AND Status = 'open'");
        $closeVisit->execute([$visitId]);
        [$subjectRole, $subjectId, $typeLabel, $fallbackName] = match ($checkIn['PersonType'] ?? '') {
            'staff' => ['admin', (int) $checkIn['StaffID'], 'staff', 'a staff member'],
            'nurse' => ['nurse', (int) ($checkIn['PatientNurseID'] ?? 0), 'nurse', 'a nurse'],
            // Other patients have no account, so the entry is clinic-wide and the name comes from OtherPatients.
            'other' => [null, null, 'other patient', otherPatientName($pdo, (int) ($checkIn['OtherPatientID'] ?? 0)) ?? 'a patient'],
            default => ['student', (int) $checkIn['StudentID'], 'student', 'a student'],
        };
        auditLog(
            $pdo,
            'visits',
            'visit.checked_out',
            'Checked out ' . (auditAccountName($pdo, $subjectRole, $subjectId) ?? $fallbackName)
                . ' (' . $typeLabel . ') from the clinic.',
            ['subject_role' => $subjectRole, 'subject_id' => $subjectId]
        );
        $pdo->commit();
        return $checkedOutAt;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

/**
 * Changes the status of an open or follow-up visit; completed visits are final and can't be changed.
 * Follow-up needs a date from today to 30 days ahead (rescheduling a follow-up is allowed). Reopening
 * clears the follow-up date; completing keeps it as a record of the scheduled follow-up.
 */
function updateClinicVisitStatus(PDO $pdo, int $visitId, string $status, string $followUpDate): void
{
    $labels = ['open' => 'Open', 'completed' => 'Completed', 'follow-up' => 'Follow-up'];
    if ($visitId <= 0) {
        throw new InvalidArgumentException('Choose a valid visit.');
    }
    if (!isset($labels[$status])) {
        throw new InvalidArgumentException('Choose a valid status.');
    }

    $newFollowUp = null;
    if ($status === 'follow-up') {
        $followUpDate = trim($followUpDate);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $followUpDate);
        $today = new DateTimeImmutable('today');
        if (!$date || $date->format('Y-m-d') !== $followUpDate) {
            throw new InvalidArgumentException('Choose a follow-up date for a follow-up visit.');
        }
        if ($date < $today || $date > $today->modify('+30 days')) {
            throw new InvalidArgumentException('Follow-up date must be between today and 30 days ahead.');
        }
        $newFollowUp = $date->format('Y-m-d');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT VisitID, StudentID, StaffID, Status, FollowUpDate FROM ClinicVisits WHERE VisitID = ? FOR UPDATE');
        $stmt->execute([$visitId]);
        $visit = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$visit) {
            throw new InvalidArgumentException('The selected visit was not found.');
        }
        if ($visit['Status'] === 'completed') {
            throw new InvalidArgumentException("Completed visits are final and can't be changed.");
        }
        if (databaseTableExists($pdo, 'ClinicCheckIns')) {
            $active = $pdo->prepare('SELECT COUNT(*) FROM ClinicCheckIns WHERE VisitID = ? AND CheckedOutAt IS NULL');
            $active->execute([$visitId]);
            if ((int) $active->fetchColumn() > 0) {
                throw new InvalidArgumentException('This patient is still in the clinic. Check them out on the Check In page before changing the visit status.');
            }
        }
        if ($status === $visit['Status'] && ($status !== 'follow-up' || $newFollowUp === $visit['FollowUpDate'])) {
            throw new InvalidArgumentException('Choose a different status or follow-up date.');
        }

        $followUpToSave = match ($status) {
            'follow-up' => $newFollowUp,
            'completed' => $visit['FollowUpDate'],
            default => null,
        };
        $update = $pdo->prepare("UPDATE ClinicVisits SET Status = ?, FollowUpDate = ? WHERE VisitID = ? AND Status <> 'completed'");
        $update->execute([$status, $followUpToSave, $visitId]);

        $isStaff = $visit['StudentID'] === null;
        $subjectRole = $isStaff ? 'admin' : 'student';
        $subjectId = (int) ($isStaff ? $visit['StaffID'] : $visit['StudentID']);
        $followUpNote = $newFollowUp !== null ? ' (follow-up on ' . date('M j, Y', strtotime($newFollowUp)) . ')' : '';
        auditLog(
            $pdo,
            'visits',
            'visit.status_changed',
            'Changed the clinic visit status for ' . (auditAccountName($pdo, $subjectRole, $subjectId) ?? 'a patient')
                . ' from ' . ($labels[$visit['Status']] ?? $visit['Status']) . ' to ' . $labels[$status] . $followUpNote . '.',
            ['subject_role' => $subjectRole, 'subject_id' => $subjectId]
        );
        if (!$isStaff) {
            createNotification(
                $pdo,
                'student',
                $subjectId,
                'clinic_visit',
                'Clinic visit updated',
                'Your clinic visit is now marked ' . $labels[$status] . $followUpNote . '.',
                getBasePath() . '/students/profile.php'
            );
        }
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

// Live counts for the dashboards: nurses toggled on duty and people currently checked in.
function fetchClinicOccupancy(PDO $pdo): array
{
    return [
        'NursesOnDuty' => countNursesOnDuty($pdo),
        'StudentsInClinic' => countActiveClinicCheckIns($pdo),
    ];
}

/** FCAT campuses a nurse can be on duty at. */
function clinicCampuses(): array
{
    return ['FCAT - Main', 'FCAT - Re Chico', 'FCAT - Pinagbarilan', 'FCAT - La Rosa'];
}

// Adds Nurses.DutyCampus if the migration was never run, so duty toggles work on any copy of the database.
function ensureNurseDutyCampusColumn(PDO $pdo): bool
{
    static $ready = null;
    if ($ready === null) {
        try {
            $ready = databaseColumnExists($pdo, 'Nurses', 'DutyCampus');
            if (!$ready && databaseTableExists($pdo, 'Nurses')) {
                $pdo->exec("ALTER TABLE Nurses ADD COLUMN DutyCampus VARCHAR(50) NULL AFTER IsOnDuty");
                $ready = true;
            }
        } catch (PDOException $error) {
            error_log('Could not add Nurses.DutyCampus: ' . $error->getMessage());
            $ready = false;
        }
    }
    return $ready;
}

/** Returns the campus if it is one of FCAT's campuses, otherwise throws. */
function validateDutyCampus(?string $campus): string
{
    $campus = trim((string) $campus);
    if (!in_array($campus, clinicCampuses(), true)) {
        throw new InvalidArgumentException('Choose the campus where the nurse is on duty.');
    }
    return $campus;
}

function fetchNurses(PDO $pdo): array
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        return [];
    }

    $campusColumn = ensureNurseDutyCampusColumn($pdo) ? 'DutyCampus' : 'NULL AS DutyCampus';
    $accountColumns = ensureNurseAccountSchema()
        ? 'Username, MustChangePassword, Password IS NOT NULL AS HasPassword, ProfileImagePath'
        : 'NULL AS Username, NULL AS MustChangePassword, 0 AS HasPassword, NULL AS ProfileImagePath';
    return $pdo->query(
        "SELECT NurseID, FullName, Position, LicenseNumber, IsOnDuty, {$campusColumn}, {$accountColumns}, CreatedAt, UpdatedAt
         FROM Nurses
         ORDER BY IsOnDuty DESC, FullName ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

function countNursesOnDuty(PDO $pdo): int
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        return 0;
    }

    return (int) $pdo->query("SELECT COUNT(*) FROM Nurses WHERE IsOnDuty = 1")->fetchColumn();
}

function validateNurseData(array $data): array
{
    $fullName = trim((string) ($data['full_name'] ?? ''));
    $position = trim((string) ($data['position'] ?? '')) ?: 'Clinic Nurse';
    $licenseNumber = trim((string) ($data['license_number'] ?? ''));

    if ($fullName === '') {
        throw new InvalidArgumentException('Nurse name is required.');
    }
    if (mb_strlen($fullName) > 120 || mb_strlen($position) > 100 || mb_strlen($licenseNumber) > 80) {
        throw new InvalidArgumentException('Nurse details exceed the allowed length.');
    }

    return [$fullName, $position, $licenseNumber !== '' ? $licenseNumber : null];
}

/**
 * Nurse sign-in name: 3-50 characters, starting with a letter (so it can never be mistaken for a
 * Student ID), then letters, digits, dots, underscores or dashes. Must not belong to an admin or
 * another nurse. Returns null when left blank.
 */
function validateNurseUsername(PDO $pdo, ?string $username, ?int $excludeNurseId = null): ?string
{
    $username = trim((string) $username);
    if ($username === '') {
        return null;
    }
    if (!preg_match('/^[A-Za-z][A-Za-z0-9._-]{2,49}$/', $username)) {
        throw new InvalidArgumentException('Username must be 3-50 characters, start with a letter, and use only letters, numbers, dots, underscores, or dashes.');
    }
    $admin = $pdo->prepare('SELECT COUNT(*) FROM Admins WHERE LOWER(Username) = LOWER(?)');
    $admin->execute([$username]);
    $nurse = $pdo->prepare('SELECT COUNT(*) FROM Nurses WHERE LOWER(Username) = LOWER(?) AND NurseID <> ?');
    $nurse->execute([$username, (int) $excludeNurseId]);
    if ((int) $admin->fetchColumn() > 0 || (int) $nurse->fetchColumn() > 0) {
        throw new InvalidArgumentException('That username is already taken. Choose a different one.');
    }
    return $username;
}

/** Audit context pointing at a nurse account (a clinic-wide entry if nurse accounts aren't installed). */
function nurseAuditSubject(int $nurseId, ?string $name = null): array
{
    if (!ensureNurseAccountSchema()) {
        return ['subject_role' => null];
    }
    $context = ['subject_role' => 'nurse', 'subject_id' => $nurseId];
    if ($name !== null) {
        $context['subject_name'] = $name;
    }
    return $context;
}

/** Gives a nurse the default password and requires them to replace it at their next sign-in. */
function resetNursePassword(PDO $pdo, int $nurseId): void
{
    $stmt = $pdo->prepare('UPDATE Nurses SET Password = ?, MustChangePassword = 1 WHERE NurseID = ?');
    $stmt->execute([password_hash(DEFAULT_NURSE_PASSWORD, PASSWORD_DEFAULT), $nurseId]);
}

function createNurse(PDO $pdo, array $data, int $adminId): int
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        throw new RuntimeException('Nurse directory storage is not installed.');
    }

    [$fullName, $position, $licenseNumber] = validateNurseData($data);
    $isOnDuty = (int) ($data['is_on_duty'] ?? 0) === 1 ? 1 : 0;
    // A nurse added as on duty must be assigned to a campus.
    $campus = $isOnDuty ? validateDutyCampus($data['duty_campus'] ?? null) : null;
    // Every new nurse gets a sign-in: their username plus the default password, which they must change first.
    $accountsReady = ensureNurseAccountSchema();
    $username = $accountsReady ? validateNurseUsername($pdo, $data['username'] ?? null) : null;
    if ($accountsReady && $username === null) {
        throw new InvalidArgumentException('Choose the username this nurse will sign in with.');
    }

    $columns = ['FullName', 'Position', 'LicenseNumber', 'IsOnDuty', 'CreatedByAdminID'];
    $values = [$fullName, $position, $licenseNumber, $isOnDuty, $adminId];
    if (ensureNurseDutyCampusColumn($pdo)) {
        $columns[] = 'DutyCampus';
        $values[] = $campus;
    }
    if ($accountsReady) {
        array_push($columns, 'Username', 'Password', 'MustChangePassword');
        array_push($values, $username, password_hash(DEFAULT_NURSE_PASSWORD, PASSWORD_DEFAULT), 1);
    }
    $stmt = $pdo->prepare(
        'INSERT INTO Nurses (' . implode(', ', $columns) . ') VALUES (' . implode(', ', array_fill(0, count($columns), '?')) . ')'
    );
    $stmt->execute($values);
    $nurseId = (int) $pdo->lastInsertId();
    auditLog(
        $pdo,
        'nurses',
        'nurse.added',
        'Added nurse ' . $fullName . ' (' . $position . ')' . ($isOnDuty ? ', on duty at ' . $campus : '')
            . ($username !== null ? ', with sign-in username "' . $username . '" and the default password' : '') . '.',
        nurseAuditSubject($nurseId)
    );
    return $nurseId;
}

/** Returns ['password_reset' => bool] so the page can say whether the default password was issued. */
function updateNurse(PDO $pdo, int $nurseId, array $data, int $adminId): array
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        throw new RuntimeException('Nurse directory storage is not installed.');
    }
    if ($nurseId <= 0) {
        throw new InvalidArgumentException('Choose a valid nurse to update.');
    }

    [$fullName, $position, $licenseNumber] = validateNurseData($data);
    $accountsReady = ensureNurseAccountSchema();
    $current = $pdo->prepare(
        'SELECT FullName, ' . ($accountsReady ? 'Username, Password' : 'NULL AS Username, NULL AS Password')
        . ' FROM Nurses WHERE NurseID = ?'
    );
    $current->execute([$nurseId]);
    $existing = $current->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        throw new InvalidArgumentException('The selected nurse was not found.');
    }
    // Validate the username before writing anything, so a taken name doesn't half-save the form.
    $username = $accountsReady ? validateNurseUsername($pdo, $data['username'] ?? null, $nurseId) : null;
    if ($accountsReady && $username === null && $existing['Username'] !== null) {
        throw new InvalidArgumentException('A nurse who can sign in must keep a username.');
    }
    $resetRequested = (int) ($data['reset_password'] ?? 0) === 1;
    if ($resetRequested && $username === null) {
        throw new InvalidArgumentException('Give this nurse a username before resetting their password.');
    }

    $stmt = $pdo->prepare('UPDATE Nurses SET FullName = ?, Position = ?, LicenseNumber = ? WHERE NurseID = ?');
    $stmt->execute([$fullName, $position, $licenseNumber, $nurseId]);
    if ($stmt->rowCount() > 0) {
        auditLog($pdo, 'nurses', 'nurse.updated', 'Updated nurse details for ' . $fullName . '.', nurseAuditSubject($nurseId));
    }

    $passwordReset = false;
    if ($accountsReady && $username !== null) {
        if ($username !== $existing['Username']) {
            $pdo->prepare('UPDATE Nurses SET Username = ? WHERE NurseID = ?')->execute([$username, $nurseId]);
            auditLog(
                $pdo,
                'security',
                'nurse.username_changed',
                'Set the sign-in username for nurse ' . $fullName . ' to "' . $username . '".',
                nurseAuditSubject($nurseId)
            );
        }
        // A nurse added before accounts existed gets the default password together with their first username.
        if ($existing['Password'] === null || $resetRequested) {
            resetNursePassword($pdo, $nurseId);
            $passwordReset = true;
            auditLog(
                $pdo,
                'security',
                'nurse.password_reset',
                "Set the nurse's password to the default (must be changed at next sign-in).",
                nurseAuditSubject($nurseId)
            );
        }
    }
    return ['password_reset' => $passwordReset];
}
/** Turning a nurse on duty requires a campus; turning them off clears it. Returns the on-duty count. */
function setNurseDutyStatus(PDO $pdo, int $nurseId, bool $isOnDuty, int $adminId, ?string $campus = null): int
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        throw new RuntimeException('Nurse directory storage is not installed.');
    }
    if ($nurseId <= 0) {
        throw new InvalidArgumentException('Choose a valid nurse to update.');
    }
    $campus = $isOnDuty ? validateDutyCampus($campus) : null;

    if (ensureNurseDutyCampusColumn($pdo)) {
        $stmt = $pdo->prepare("UPDATE Nurses SET IsOnDuty = ?, DutyCampus = ? WHERE NurseID = ?");
        $stmt->execute([$isOnDuty ? 1 : 0, $campus, $nurseId]);
    } else {
        $stmt = $pdo->prepare("UPDATE Nurses SET IsOnDuty = ? WHERE NurseID = ?");
        $stmt->execute([$isOnDuty ? 1 : 0, $nurseId]);
    }
    if ($stmt->rowCount() === 0) {
        $exists = $pdo->prepare("SELECT COUNT(*) FROM Nurses WHERE NurseID = ?");
        $exists->execute([$nurseId]);
        if (!(bool) $exists->fetchColumn()) {
            throw new InvalidArgumentException('The selected nurse was not found.');
        }
    } else {
        $nurseName = $pdo->prepare('SELECT FullName FROM Nurses WHERE NurseID = ?');
        $nurseName->execute([$nurseId]);
        $isSelf = ($_SESSION['role'] ?? '') === 'nurse' && (int) ($_SESSION['user_id'] ?? 0) === $nurseId;
        auditLog(
            $pdo,
            'nurses',
            'nurse.duty_changed',
            $isSelf
                ? ($isOnDuty ? 'Went on duty at ' . $campus . '.' : 'Went off duty.')
                : 'Set nurse ' . $nurseName->fetchColumn() . ($isOnDuty ? ' on duty at ' . $campus . '.' : ' off duty.'),
            nurseAuditSubject($nurseId)
        );
    }
    return countNursesOnDuty($pdo);
}

/**
 * On-duty nurses grouped by campus, for the dashboards: one entry per FCAT campus
 * with the nurses currently on duty there (an empty list means no nurse is available).
 */
function fetchNurseAvailabilityByCampus(PDO $pdo): array
{
    $availability = array_fill_keys(clinicCampuses(), []);
    if (!databaseTableExists($pdo, 'Nurses') || !ensureNurseDutyCampusColumn($pdo)) {
        return $availability;
    }

    $rows = $pdo->query(
        "SELECT FullName, Position, DutyCampus FROM Nurses
         WHERE IsOnDuty = 1
         ORDER BY FullName ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        // Nurses put on duty before campuses were recorded are listed separately rather than hidden.
        $campus = array_key_exists((string) $row['DutyCampus'], $availability) ? $row['DutyCampus'] : 'Campus not set';
        $availability[$campus][] = $row;
    }
    return $availability;
}

function deleteNurse(PDO $pdo, int $nurseId, int $adminId): void
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        throw new RuntimeException('Nurse directory storage is not installed.');
    }
    if ($nurseId <= 0) {
        throw new InvalidArgumentException('Choose a valid nurse to delete.');
    }

    $nurseName = $pdo->prepare('SELECT FullName FROM Nurses WHERE NurseID = ?');
    $nurseName->execute([$nurseId]);
    $deletedNurseName = (string) $nurseName->fetchColumn();
    $photoPath = ensureNurseAccountSchema() ? fetchProfileImagePath($pdo, 'nurse', $nurseId) : null;
    $stmt = $pdo->prepare("DELETE FROM Nurses WHERE NurseID = ?");
    $stmt->execute([$nurseId]);
    if ($stmt->rowCount() === 0) {
        throw new InvalidArgumentException('The selected nurse was not found.');
    }
    // Deleting a nurse also deletes their profile photo, as deleting a student does.
    deleteStoredProfileImage($photoPath);
    auditLog($pdo, 'nurses', 'nurse.deleted', 'Removed nurse ' . $deletedNurseName . ' from the directory.', nurseAuditSubject($nurseId, $deletedNurseName));
}

function medicineArchiveInstalled(PDO $pdo): bool
{
    return databaseColumnExists($pdo, 'Medicines', 'IsArchived');
}

function medicineInventorySummary(PDO $pdo): array
{
    if (!databaseTableExists($pdo, 'Medicines')) {
        return ['TotalMedicines' => 0, 'LowStockMedicines' => 0, 'ArchivedMedicines' => 0];
    }

    $archiveInstalled = medicineArchiveInstalled($pdo);
    $row = $pdo->query(
        $archiveInstalled
            ? "SELECT SUM(IsArchived = 0) AS TotalMedicines,
                      SUM(IsArchived = 0 AND Stock <= LowStockThreshold) AS LowStockMedicines,
                      SUM(IsArchived = 1) AS ArchivedMedicines
               FROM Medicines"
            : "SELECT COUNT(*) AS TotalMedicines,
                      SUM(Stock <= LowStockThreshold) AS LowStockMedicines,
                      0 AS ArchivedMedicines
               FROM Medicines"
    )->fetch(PDO::FETCH_ASSOC);

    return [
        'TotalMedicines' => (int) ($row['TotalMedicines'] ?? 0),
        'LowStockMedicines' => (int) ($row['LowStockMedicines'] ?? 0),
        'ArchivedMedicines' => (int) ($row['ArchivedMedicines'] ?? 0),
    ];
}

function fetchMedicines(PDO $pdo, string $search = '', bool $lowStockOnly = false, bool $archived = false): array
{
    if (!databaseTableExists($pdo, 'Medicines')) {
        return [];
    }

    $where = [];
    $params = [];
    if (medicineArchiveInstalled($pdo)) {
        $where[] = 'IsArchived = ?';
        $params[] = $archived ? 1 : 0;
    } elseif ($archived) {
        return [];
    }
    if ($search !== '') {
        $where[] = '(Name LIKE ? OR Dosage LIKE ? OR Description LIKE ?)';
        $term = '%' . $search . '%';
        array_push($params, $term, $term, $term);
    }
    if ($lowStockOnly) {
        $where[] = 'Stock <= LowStockThreshold';
    }

    $sql = 'SELECT * FROM Medicines';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY Stock ASC, Name ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Spacing and letter case are ignored so "Para cetamol" and "paracetamol" count as the same medicine.
function medicineNameKey(string $name): string
{
    return mb_strtolower(preg_replace('/\s+/u', '', $name));
}

function findMedicineByName(PDO $pdo, string $name): ?array
{
    $columns = medicineArchiveInstalled($pdo) ? 'MedicineID, Name, IsArchived' : 'MedicineID, Name';
    $key = medicineNameKey($name);
    foreach ($pdo->query("SELECT {$columns} FROM Medicines")->fetchAll(PDO::FETCH_ASSOC) as $medicine) {
        if (medicineNameKey($medicine['Name']) === $key) {
            return $medicine;
        }
    }
    return null;
}

function createMedicine(PDO $pdo, array $data, int $adminId): void
{
    if (!databaseTableExists($pdo, 'Medicines')) {
        throw new RuntimeException('Medicine inventory storage is not installed.');
    }

    $name = preg_replace('/\s+/u', ' ', trim((string) ($data['name'] ?? '')));
    $stock = filter_var($data['stock'] ?? null, FILTER_VALIDATE_INT);
    $threshold = filter_var($data['low_stock_threshold'] ?? null, FILTER_VALIDATE_INT);
    if ($name === '') {
        throw new InvalidArgumentException('Medicine name is required.');
    }
    if ($stock === false || $stock < 0 || $stock > 999999) {
        throw new InvalidArgumentException('Opening stock must be between 0 and 999999.');
    }
    if ($threshold === false || $threshold < 0 || $threshold > 999999) {
        throw new InvalidArgumentException('Low-stock threshold must be between 0 and 999999.');
    }
    if (mb_strlen($name) > 120) {
        throw new InvalidArgumentException('Medicine name must not exceed 120 characters.');
    }
    $lengthErrors = [];
    validateMaxLengths($data, ['dosage' => ['Dosage', 120], 'description' => ['Description', 255]], $lengthErrors);
    if ($lengthErrors) {
        throw new InvalidArgumentException($lengthErrors[0]);
    }

    $existing = findMedicineByName($pdo, $name);
    if ($existing !== null) {
        throw new InvalidArgumentException(
            (int) ($existing['IsArchived'] ?? 0) === 1
                ? $existing['Name'] . ' already exists in Archived medicines. Restore it instead of adding it again.'
                : $existing['Name'] . ' already exists in the inventory. Use Add stock to increase its quantity.'
        );
    }

    $stmt = $pdo->prepare(
        "INSERT INTO Medicines (Name, Stock, Dosage, Description, LowStockThreshold, CreatedByAdminID)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $name,
        $stock,
        trim((string) ($data['dosage'] ?? '')) ?: null,
        trim((string) ($data['description'] ?? '')) ?: null,
        $threshold,
        $adminId,
    ]);
    auditLog($pdo, 'inventory', 'inventory.medicine_added', 'Added medicine ' . $name . ' with opening stock of ' . $stock . '.', ['subject_role' => null]);
}

function medicineNameById(PDO $pdo, int $medicineId): string
{
    $stmt = $pdo->prepare('SELECT Name FROM Medicines WHERE MedicineID = ?');
    $stmt->execute([$medicineId]);
    return (string) ($stmt->fetchColumn() ?: 'medicine #' . $medicineId);
}

function addMedicineStock(PDO $pdo, int $medicineId, int $quantity): void
{
    if (!databaseTableExists($pdo, 'Medicines')) {
        throw new RuntimeException('Medicine inventory storage is not installed.');
    }
    if ($medicineId <= 0 || $quantity <= 0 || $quantity > 999999) {
        throw new InvalidArgumentException('Choose a medicine and enter a quantity from 1 to 999999.');
    }

    $sql = 'UPDATE Medicines SET Stock = Stock + ? WHERE MedicineID = ?';
    if (medicineArchiveInstalled($pdo)) {
        $sql .= ' AND IsArchived = 0';
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$quantity, $medicineId]);
    if ($stmt->rowCount() === 0) {
        throw new InvalidArgumentException('The selected medicine was not found.');
    }
    auditLog($pdo, 'inventory', 'inventory.stock_added', 'Added ' . $quantity . ' unit(s) of stock to ' . medicineNameById($pdo, $medicineId) . '.', ['subject_role' => null]);
}

function archiveMedicine(PDO $pdo, int $medicineId): void
{
    if (!databaseTableExists($pdo, 'Medicines') || !medicineArchiveInstalled($pdo)) {
        throw new RuntimeException('Medicine archiving is not installed. Run the medicine archive migration first.');
    }
    if ($medicineId <= 0) {
        throw new InvalidArgumentException('Choose a valid medicine to archive.');
    }

    $stmt = $pdo->prepare(
        'UPDATE Medicines SET IsArchived = 1, ArchivedAt = NOW() WHERE MedicineID = ? AND IsArchived = 0'
    );
    $stmt->execute([$medicineId]);
    if ($stmt->rowCount() === 0) {
        throw new InvalidArgumentException('The selected medicine was not found or is already archived.');
    }
    auditLog($pdo, 'inventory', 'inventory.medicine_archived', 'Archived medicine ' . medicineNameById($pdo, $medicineId) . '.', ['subject_role' => null]);
}

function restoreMedicine(PDO $pdo, int $medicineId): void
{
    if (!databaseTableExists($pdo, 'Medicines') || !medicineArchiveInstalled($pdo)) {
        throw new RuntimeException('Medicine archiving is not installed. Run the medicine archive migration first.');
    }
    if ($medicineId <= 0) {
        throw new InvalidArgumentException('Choose a valid medicine to restore.');
    }

    $stmt = $pdo->prepare(
        'UPDATE Medicines SET IsArchived = 0, ArchivedAt = NULL WHERE MedicineID = ? AND IsArchived = 1'
    );
    $stmt->execute([$medicineId]);
    if ($stmt->rowCount() === 0) {
        throw new InvalidArgumentException('The selected medicine was not found in the archive.');
    }
    auditLog($pdo, 'inventory', 'inventory.medicine_restored', 'Restored medicine ' . medicineNameById($pdo, $medicineId) . ' from the archive.', ['subject_role' => null]);
}

function ensureChatConversation(PDO $pdo, int $studentId): void
{
    if (!databaseTableExists($pdo, 'ChatConversations')) {
        return;
    }

    $stmt = $pdo->prepare(
        "INSERT IGNORE INTO ChatConversations (StudentID, Status, LastMessageAt) VALUES (?, 'open', NOW())"
    );
    $stmt->execute([$studentId]);
}

function fetchChatConversation(PDO $pdo, int $studentId): array
{
    if (!databaseTableExists($pdo, 'ChatConversations')) {
        return ['Status' => 'open'];
    }

    ensureChatConversation($pdo, $studentId);
    $stmt = $pdo->prepare("SELECT * FROM ChatConversations WHERE StudentID = ?");
    $stmt->execute([$studentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: ['Status' => 'open'];
}

function updateChatConversationStatus(PDO $pdo, int $studentId, string $status, ?int $adminId = null): void
{
    if (!databaseTableExists($pdo, 'ChatConversations')) {
        return;
    }

    ensureChatConversation($pdo, $studentId);
    $stmt = $pdo->prepare(
        "UPDATE ChatConversations
         SET Status = ?, ResolvedAt = IF(? = 'resolved', NOW(), NULL), ResolvedByAdminID = IF(? = 'resolved', ?, NULL)
         WHERE StudentID = ?"
    );
    $stmt->execute([$status, $status, $status, $adminId, $studentId]);
}

function validateEmail(string $email, array &$errors): void
{
    $allowed_domain = 'fcat.com.ph';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } elseif ($email !== '' && !str_ends_with(strtolower($email), '@' . $allowed_domain)) {
        $errors[] = "Email must be a @$allowed_domain address.";
    }
}

function consentChoiceMarks(mixed $value): string
{
    if ($value === null || $value === '') {
        return '☐ YES &nbsp;&nbsp;&nbsp;&nbsp; ☐ NO';
    }

    return (int) $value === 1
        ? '☑ YES &nbsp;&nbsp;&nbsp;&nbsp; ☐ NO'
        : '☐ YES &nbsp;&nbsp;&nbsp;&nbsp; ☑ NO';
}

function normalizeGuardianRelationship(?string $relationship): string
{
    return match ($relationship) {
        'Stepfather' => 'Father',
        'Stepmother' => 'Mother',
        'Legal Guardian' => 'Guardian',
        default => $relationship ?? '',
    };
}

function guardianRelationshipMarks(?string $relationship): string
{
    $normalized = normalizeGuardianRelationship($relationship);

    return ($normalized === 'Mother' ? '☑' : '☐') . ' Mother &nbsp; '
        . ($normalized === 'Father' ? '☑' : '☐') . ' Father &nbsp; '
        . ($normalized === 'Guardian' ? '☑' : '☐') . ' Guardian';
}

// Sender name shown on the automatic reply a student gets when a conversation starts or reopens.
const CHAT_AUTO_REPLY_SENDER = 'FCAT HealthWard (automatic reply)';
const CHAT_AUTO_REPLY_HOURS = 48;

/**
 * SenderRole 'admin' is the clinic side of a conversation: an admin (AdminID), a nurse (NurseID),
 * or the automatic reply (IsAutoReply = 1). Staff viewers see every clinic-side message as their own side.
 */
function formatChatMessage(array $row, string $viewerRole): array
{
    $isAutoReply = !empty($row['IsAutoReply']);
    $senderName = $row['SenderRole'] === 'admin'
        ? ($isAutoReply ? CHAT_AUTO_REPLY_SENDER : ($row['AdminName'] ?? ''))
        : trim(($row['StudentFirstName'] ?? '') . ' ' . ($row['StudentLastName'] ?? ''));

    return [
        'message_id'    => (int) $row['MessageID'],
        'student_id'    => (int) $row['StudentID'],
        'sender_role'   => $row['SenderRole'],
        'sender_name'   => $senderName ?: ($row['SenderRole'] === 'admin' ? 'Clinic staff' : 'Student'),
        'content'       => $row['Content'],
        'created_at'    => $row['CreatedAt'],
        'is_mine'       => $viewerRole === 'student' ? $row['SenderRole'] === 'student' : $row['SenderRole'] !== 'student',
        'is_auto_reply' => $isAutoReply,
    ];
}

/** SELECT ... FROM for chat messages with the student's and the clinic sender's names. */
function chatMessageSelectSql(PDO $pdo): string
{
    $byNurse = databaseColumnExists($pdo, 'Messages', 'NurseID');
    return "SELECT m.*, s.FirstName AS StudentFirstName, s.LastName AS StudentLastName, "
        . ($byNurse ? 'COALESCE(a.FullName, n.FullName)' : 'a.FullName') . " AS AdminName
         FROM Messages m
         JOIN Students s ON s.StudentID = m.StudentID
         LEFT JOIN Admins a ON a.AdminID = m.AdminID
         " . ($byNurse ? 'LEFT JOIN Nurses n ON n.NurseID = m.NurseID' : '');
}

// Adds Messages.IsAutoReply if add_chat_auto_reply.sql was never run.
function ensureChatAutoReplyColumn(PDO $pdo): bool
{
    static $ready = null;
    if ($ready === null) {
        try {
            $ready = (bool) $pdo->query("SHOW COLUMNS FROM Messages LIKE 'IsAutoReply'")->fetch();
            if (!$ready) {
                $pdo->exec("ALTER TABLE Messages ADD COLUMN IsAutoReply TINYINT(1) NOT NULL DEFAULT 0 AFTER Content");
                $ready = true;
            }
        } catch (PDOException $error) {
            error_log('Could not add Messages.IsAutoReply: ' . $error->getMessage());
            $ready = false;
        }
    }
    return $ready;
}

function chatAutoReplyText(string $studentFirstName): string
{
    $name = trim($studentFirstName);
    return 'Hi' . ($name !== '' ? ' ' . $name : '') . '! We received your message and expect to reply within '
        . CHAT_AUTO_REPLY_HOURS . ' hours.';
}

function fetchChatMessages(PDO $pdo, int $studentId): array
{
    $stmt = $pdo->prepare(
        chatMessageSelectSql($pdo) . "
         WHERE m.StudentID = ?
         ORDER BY m.CreatedAt ASC, m.MessageID ASC"
    );
    $stmt->execute([$studentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function markChatMessagesRead(PDO $pdo, int $studentId, string $readerRole): void
{
    if ($readerRole === 'student') {
        $stmt = $pdo->prepare(
            "UPDATE Messages SET IsRead = 1 WHERE StudentID = ? AND SenderRole = 'admin' AND IsRead = 0"
        );
    } else {
        $stmt = $pdo->prepare(
            "UPDATE Messages SET IsRead = 1 WHERE StudentID = ? AND SenderRole = 'student' AND IsRead = 0"
        );
    }
    $stmt->execute([$studentId]);
}

/**
 * Saves a chat message. $senderRole is 'student' or 'admin' (the clinic side); a nurse sends as the
 * clinic side with $nurseId set. When a student opens a brand-new conversation, or writes again after
 * it was resolved, one automatic reply is saved right after their message and returned in $autoReply.
 */
function sendChatMessage(PDO $pdo, int $studentId, string $senderRole, ?int $adminId, string $content, ?int $nurseId = null, ?array &$autoReply = null): array
{
    $autoReply = null;
    $hasConversations = databaseTableExists($pdo, 'ChatConversations');
    $recordNurse = $nurseId !== null && databaseColumnExists($pdo, 'Messages', 'NurseID');
    $autoReplyReady = $senderRole === 'student' && ensureChatAutoReplyColumn($pdo);

    $ownsTransaction = !$pdo->inTransaction();
    if ($ownsTransaction) {
        $pdo->beginTransaction();
    }
    try {
        ensureChatConversation($pdo, $studentId);

        $needsAutoReply = false;
        if ($autoReplyReady) {
            // Lock the conversation so two quick messages can't both trigger the automatic reply.
            $wasResolved = false;
            if ($hasConversations) {
                $status = $pdo->prepare("SELECT Status FROM ChatConversations WHERE StudentID = ? FOR UPDATE");
                $status->execute([$studentId]);
                $wasResolved = $status->fetchColumn() === 'resolved';
            }
            $prior = $pdo->prepare("SELECT COUNT(*) FROM Messages WHERE StudentID = ?");
            $prior->execute([$studentId]);
            $needsAutoReply = $wasResolved || (int) $prior->fetchColumn() === 0;
        }

        $stmt = $pdo->prepare(
            $recordNurse
                ? "INSERT INTO Messages (StudentID, SenderRole, AdminID, NurseID, Content) VALUES (?, ?, ?, ?, ?)"
                : "INSERT INTO Messages (StudentID, SenderRole, AdminID, Content) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute($recordNurse
            ? [$studentId, $senderRole, $adminId, $nurseId, $content]
            : [$studentId, $senderRole, $adminId, $content]);
        $messageId = (int) $pdo->lastInsertId();

        if ($hasConversations) {
            $conversationUpdate = $senderRole === 'student'
                ? "UPDATE ChatConversations
                   SET Status = 'open', LastMessageAt = NOW(), ResolvedAt = NULL, ResolvedByAdminID = NULL
                   WHERE StudentID = ?"
                : "UPDATE ChatConversations SET LastMessageAt = NOW() WHERE StudentID = ?";
            $stmt = $pdo->prepare($conversationUpdate);
            $stmt->execute([$studentId]);
        }

        $autoReplyId = null;
        if ($needsAutoReply) {
            $firstName = $pdo->prepare("SELECT FirstName FROM Students WHERE StudentID = ?");
            $firstName->execute([$studentId]);
            // The student is looking at the chat when it arrives, so it starts out read.
            $stmt = $pdo->prepare(
                "INSERT INTO Messages (StudentID, SenderRole, AdminID, Content, IsAutoReply, IsRead) VALUES (?, 'admin', NULL, ?, 1, 1)"
            );
            $stmt->execute([$studentId, chatAutoReplyText((string) $firstName->fetchColumn())]);
            $autoReplyId = (int) $pdo->lastInsertId();
        }

        if ($ownsTransaction) {
            $pdo->commit();
        }
    } catch (Throwable $error) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }

    if ($senderRole === 'student') {
        createNotification(
            $pdo,
            'admin',
            null,
            'chat',
            'New student message',
            'A student sent a new clinic message.',
            getBasePath() . '/app/admin/messages.php?student_id=' . $studentId
        );
    } else {
        createNotification(
            $pdo,
            'student',
            $studentId,
            'chat',
            'New clinic message',
            $nurseId !== null ? 'A clinic nurse sent you a message.' : 'The clinic admin sent you a message.',
            getBasePath() . '/students/messages.php'
        );
    }

    $load = $pdo->prepare(chatMessageSelectSql($pdo) . " WHERE m.MessageID = ? AND m.StudentID = ?");
    $load->execute([$messageId, $studentId]);
    $message = $load->fetch(PDO::FETCH_ASSOC);
    if (!$message) {
        throw new RuntimeException('Unable to load the newly created chat message.');
    }
    if ($autoReplyId !== null) {
        $load->execute([$autoReplyId, $studentId]);
        $autoReply = $load->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    return $message;
}
function fetchChatThreads(PDO $pdo): array
{
    $hasConversations = databaseTableExists($pdo, 'ChatConversations');
    $statusSelect = $hasConversations ? "COALESCE(c.Status, 'open')" : "'open'";
    $statusJoin = $hasConversations ? "LEFT JOIN ChatConversations c ON c.StudentID = s.StudentID" : "";
    $statusGroup = $hasConversations ? ", c.Status" : "";
    // The inbox preview shows the last real message, not the automatic reply.
    $skipAutoReply = databaseColumnExists($pdo, 'Messages', 'IsAutoReply') ? ' AND m2.IsAutoReply = 0' : '';

    $stmt = $pdo->query(
        "SELECT s.StudentID, s.FirstName, s.LastName,
                SUM(m.SenderRole = 'student' AND m.IsRead = 0) AS UnreadCount,
                MAX(m.CreatedAt) AS LastActivity,
                {$statusSelect} AS ConversationStatus,
                (SELECT Content FROM Messages m2
                 WHERE m2.StudentID = s.StudentID{$skipAutoReply}
                 ORDER BY m2.CreatedAt DESC, m2.MessageID DESC LIMIT 1) AS LastMessage
         FROM Messages m
         JOIN Students s ON s.StudentID = m.StudentID
         {$statusJoin}
         GROUP BY s.StudentID, s.FirstName, s.LastName{$statusGroup}
         ORDER BY LastActivity DESC"
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countUnreadChatForAdmin(PDO $pdo): int
{
    $stmt = $pdo->query(
        "SELECT COUNT(*) FROM Messages WHERE SenderRole = 'student' AND IsRead = 0"
    );
    return (int) $stmt->fetchColumn();
}

function currentChatSessionId(): string
{
    return session_id();
}

function currentChatUser(): array
{
    $user = currentUser();
    return [
        'role' => $user['role'],
        'id'   => (int) $user['id'],
    ];
}

function chatReturnPath(): string
{
    return in_array($_SESSION['role'] ?? '', CLINIC_STAFF_ROLES, true)
        ? getBasePath() . '/app/admin/messages.php'
        : getBasePath() . '/students/messages.php';
}

function cleanupChatPresence(PDO $pdo): void
{
    $timeout = CHAT_SESSION_TIMEOUT_SECONDS;
    $pdo->exec(
        "DELETE FROM ActiveChatSessions
         WHERE LastSeenAt < (NOW() - INTERVAL {$timeout} SECOND)"
    );
    $pdo->exec(
        "DELETE q FROM ChatQueue q
         INNER JOIN ActiveChatSessions a ON a.SessionID = q.SessionID"
    );
    $pdo->exec(
        "DELETE FROM ChatQueue
         WHERE LastSeenAt < (NOW() - INTERVAL {$timeout} SECOND)"
    );
}

/** Students with a chat page open. Only students count toward CHAT_ACTIVE_LIMIT; staff are never queued. */
function activeChatCount(PDO $pdo): int
{
    cleanupChatPresence($pdo);
    return (int) $pdo->query("SELECT COUNT(*) FROM ActiveChatSessions WHERE UserRole = 'student'")->fetchColumn();
}

function isCurrentChatSessionActive(PDO $pdo): bool
{
    cleanupChatPresence($pdo);
    $stmt = $pdo->prepare("SELECT 1 FROM ActiveChatSessions WHERE SessionID = ?");
    $stmt->execute([currentChatSessionId()]);
    return (bool) $stmt->fetchColumn();
}

function touchChatSession(PDO $pdo): void
{
    $chatUser = currentChatUser();
    $stmt = $pdo->prepare(
        "INSERT INTO ActiveChatSessions (SessionID, UserRole, UserID, LastSeenAt)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE UserRole = VALUES(UserRole), UserID = VALUES(UserID), LastSeenAt = NOW()"
    );
    $stmt->execute([currentChatSessionId(), $chatUser['role'], $chatUser['id']]);

    $stmt = $pdo->prepare("DELETE FROM ChatQueue WHERE SessionID = ?");
    $stmt->execute([currentChatSessionId()]);
}

function addCurrentChatSessionToQueue(PDO $pdo): void
{
    $chatUser = currentChatUser();
    $stmt = $pdo->prepare(
        "INSERT INTO ChatQueue (SessionID, UserRole, UserID, LastSeenAt)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE UserRole = VALUES(UserRole), UserID = VALUES(UserID), LastSeenAt = NOW()"
    );
    $stmt->execute([currentChatSessionId(), $chatUser['role'], $chatUser['id']]);
}

function chatQueuePosition(PDO $pdo): ?int
{
    cleanupChatPresence($pdo);
    $stmt = $pdo->prepare("SELECT QueueID FROM ChatQueue WHERE SessionID = ?");
    $stmt->execute([currentChatSessionId()]);
    $queueId = $stmt->fetchColumn();

    if (!$queueId) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ChatQueue WHERE QueueID <= ?");
    $stmt->execute([$queueId]);
    return (int) $stmt->fetchColumn();
}

function isCurrentChatSessionFirstInQueue(PDO $pdo): bool
{
    cleanupChatPresence($pdo);
    $stmt = $pdo->query("SELECT SessionID FROM ChatQueue ORDER BY QueueID ASC LIMIT 1");
    return $stmt->fetchColumn() === currentChatSessionId();
}

function admitCurrentChatSessionIfPossible(PDO $pdo): bool
{
    cleanupChatPresence($pdo);

    if (isCurrentChatSessionActive($pdo)) {
        touchChatSession($pdo);
        return true;
    }

    // Admins and nurses always get in, so a full queue of students can never keep staff from answering.
    if (in_array(currentChatUser()['role'], CLINIC_STAFF_ROLES, true)) {
        touchChatSession($pdo);
        return true;
    }

    if (activeChatCount($pdo) < CHAT_ACTIVE_LIMIT) {
        $position = chatQueuePosition($pdo);
        if ($position === null || isCurrentChatSessionFirstInQueue($pdo)) {
            touchChatSession($pdo);
            return true;
        }
    }

    addCurrentChatSessionToQueue($pdo);
    return false;
}

function requireChatCapacity(PDO $pdo): void
{
    if (admitCurrentChatSessionIfPossible($pdo)) {
        return;
    }

    header('Location: ' . getBasePath() . '/public/chat-queue.php');
    exit;
}

function chatQueueStatus(PDO $pdo): array
{
    $allowed = admitCurrentChatSessionIfPossible($pdo);
    return [
        'allowed'      => $allowed,
        'active_count' => activeChatCount($pdo),
        'limit'        => CHAT_ACTIVE_LIMIT,
        'position'     => $allowed ? null : chatQueuePosition($pdo),
        'return_url'   => chatReturnPath(),
    ];
}

function auditCategories(): array
{
    return [
        'security'     => 'Sign-in & security',
        'account'      => 'Account',
        'clinic_form'  => 'Clinic form',
        'students'     => 'Student records',
        'visits'       => 'Clinic visits',
        'inventory'    => 'Medicine inventory',
        'nurses'       => 'Nurses',
        'announcements'=> 'Announcements',
        'messages'     => 'Messages',
    ];
}

function auditAccountName(PDO $pdo, ?string $role, ?int $id): ?string
{
    static $cache = [];
    if ($id === null || !in_array($role, ['admin', 'student', 'nurse'], true)) {
        return null;
    }
    $key = $role . ':' . $id;
    if (!array_key_exists($key, $cache)) {
        $stmt = match ($role) {
            'admin' => $pdo->prepare("SELECT COALESCE(NULLIF(FullName, ''), Username) FROM Admins WHERE AdminID = ?"),
            'nurse' => $pdo->prepare("SELECT FullName FROM Nurses WHERE NurseID = ?"),
            default => $pdo->prepare("SELECT CONCAT(FirstName, ' ', LastName) FROM Students WHERE StudentID = ?"),
        };
        $stmt->execute([$id]);
        $name = $stmt->fetchColumn();
        $cache[$key] = $name === false ? null : (string) $name;
    }
    return $cache[$key];
}

/**
 * Records an audit event. The subject (the account the event concerns) defaults to the actor;
 * pass 'subject_role' => null for events that don't belong to one account (e.g. inventory).
 * Never throws, so a logging problem can't break the action being audited.
 */
function auditLog(PDO $pdo, string $category, string $action, string $description, array $context = []): void
{
    try {
        if (!databaseTableExists($pdo, 'AuditLogs')) {
            return;
        }

        $actorRole = $context['actor_role'] ?? ($_SESSION['role'] ?? 'guest');
        $actorRole = in_array($actorRole, ['admin', 'student', 'nurse', 'guest'], true) ? $actorRole : 'guest';
        $actorId = array_key_exists('actor_id', $context)
            ? $context['actor_id']
            : (isset($_SESSION['user_id']) && $actorRole !== 'guest' ? (int) $_SESSION['user_id'] : null);
        $actorName = $context['actor_name']
            ?? auditAccountName($pdo, $actorRole, $actorId)
            ?? ($actorRole === 'guest' ? 'Unknown visitor' : ucfirst($actorRole));

        if (array_key_exists('subject_role', $context)) {
            $subjectRole = $context['subject_role'];
            $subjectId = $context['subject_id'] ?? null;
        } else {
            $subjectRole = $actorRole !== 'guest' ? $actorRole : null;
            $subjectId = $subjectRole !== null ? $actorId : null;
        }
        $subjectName = $context['subject_name'] ?? auditAccountName($pdo, $subjectRole, $subjectId);

        $stmt = $pdo->prepare(
            "INSERT INTO AuditLogs
             (ActorRole, ActorID, ActorName, SubjectRole, SubjectID, SubjectName, Category, Action, Description, IPAddress, UserAgent)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $actorRole,
            $actorId,
            mb_substr($actorName, 0, 150),
            $subjectRole,
            $subjectId,
            $subjectName !== null ? mb_substr($subjectName, 0, 150) : null,
            $category,
            $action,
            mb_substr($description, 0, 500),
            mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
            mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255) ?: null,
        ]);
    } catch (Throwable $error) {
        error_log('Audit log write failed: ' . $error->getMessage());
    }
}

/** Returns the labels of fields whose values differ between $before and $after (values are not recorded). */
function auditChangedFields(array $before, array $after, array $labels): array
{
    $changed = [];
    foreach ($labels as $key => $label) {
        if (trim((string) ($before[$key] ?? '')) !== trim((string) ($after[$key] ?? ''))) {
            $changed[] = $label;
        }
    }
    return $changed;
}

function auditLogWhere(array $filters): array
{
    $where = [];
    $params = [];

    if (!empty($filters['subject_role']) && isset($filters['subject_id'])) {
        $where[] = 'SubjectRole = ? AND SubjectID = ?';
        array_push($params, $filters['subject_role'], (int) $filters['subject_id']);
    }
    if (isset($filters['involving_admin_id'])) {
        $where[] = "((ActorRole = 'admin' AND ActorID = ?) OR (SubjectRole = 'admin' AND SubjectID = ?))";
        array_push($params, (int) $filters['involving_admin_id'], (int) $filters['involving_admin_id']);
    }
    if (isset($filters['involving_nurse_id'])) {
        $where[] = "((ActorRole = 'nurse' AND ActorID = ?) OR (SubjectRole = 'nurse' AND SubjectID = ?))";
        array_push($params, (int) $filters['involving_nurse_id'], (int) $filters['involving_nurse_id']);
    }
    if (!empty($filters['category']) && array_key_exists($filters['category'], auditCategories())) {
        $where[] = 'Category = ?';
        $params[] = $filters['category'];
    }
    if (!empty($filters['search'])) {
        $where[] = '(Description LIKE ? OR ActorName LIKE ? OR SubjectName LIKE ? OR IPAddress LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        array_push($params, $term, $term, $term, $term);
    }
    if (!empty($filters['date_from'])) {
        $where[] = 'CreatedAt >= ?';
        $params[] = $filters['date_from'] . ' 00:00:00';
    }
    if (!empty($filters['date_to'])) {
        $where[] = 'CreatedAt <= ?';
        $params[] = $filters['date_to'] . ' 23:59:59';
    }

    return [$where ? ' WHERE ' . implode(' AND ', $where) : '', $params];
}

/** Students the admin can pick in the Audit Log, including deleted students that still have history. */
function auditStudentAccounts(PDO $pdo): array
{
    $accounts = [];
    foreach ($pdo->query("SELECT StudentID, CONCAT(LastName, ', ', FirstName) AS Name FROM Students ORDER BY LastName, FirstName") as $row) {
        $accounts[(int) $row['StudentID']] = $row['Name'];
    }
    if (databaseTableExists($pdo, 'AuditLogs')) {
        $history = $pdo->query(
            "SELECT SubjectID, MAX(SubjectName) AS Name FROM AuditLogs
             WHERE SubjectRole = 'student' AND SubjectID IS NOT NULL GROUP BY SubjectID"
        );
        foreach ($history as $row) {
            if (!isset($accounts[(int) $row['SubjectID']])) {
                $accounts[(int) $row['SubjectID']] = ($row['Name'] ?: 'Student') . ' (deleted)';
            }
        }
    }
    return $accounts;
}

/** Nurses the admin can pick in the Audit Log, including deleted nurses that still have history. */
function auditNurseAccounts(PDO $pdo): array
{
    $accounts = [];
    if (!databaseTableExists($pdo, 'Nurses')) {
        return $accounts;
    }
    foreach ($pdo->query("SELECT NurseID, FullName FROM Nurses ORDER BY FullName") as $row) {
        $accounts[(int) $row['NurseID']] = $row['FullName'];
    }
    if (databaseTableExists($pdo, 'AuditLogs')) {
        $history = $pdo->query(
            "SELECT SubjectID, MAX(SubjectName) AS Name FROM AuditLogs
             WHERE SubjectRole = 'nurse' AND SubjectID IS NOT NULL GROUP BY SubjectID"
        );
        foreach ($history as $row) {
            if (!isset($accounts[(int) $row['SubjectID']])) {
                $accounts[(int) $row['SubjectID']] = ($row['Name'] ?: 'Nurse') . ' (deleted)';
            }
        }
    }
    return $accounts;
}

function countAuditLogs(PDO $pdo, array $filters = []): int
{
    if (!databaseTableExists($pdo, 'AuditLogs')) {
        return 0;
    }
    [$whereSql, $params] = auditLogWhere($filters);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM AuditLogs' . $whereSql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

function fetchAuditLogs(PDO $pdo, array $filters = [], int $limit = 50, int $offset = 0): array
{
    if (!databaseTableExists($pdo, 'AuditLogs')) {
        return [];
    }
    [$whereSql, $params] = auditLogWhere($filters);
    $stmt = $pdo->prepare(
        'SELECT * FROM AuditLogs' . $whereSql
        . ' ORDER BY CreatedAt DESC, AuditLogID DESC LIMIT ' . max(1, $limit) . ' OFFSET ' . max(0, $offset)
    );
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/** Reads and validates the Audit Log filters from the query string. */
function auditLogRequestFilters(): array
{
    $validDate = static function (string $value): string {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value ? $value : '';
    };
    $category = (string) ($_GET['category'] ?? '');
    // Admin-only account selector: '' (all), 'mine', 'student:<id>' or 'nurse:<id>'. Ignored on the student and nurse pages.
    $account = (string) ($_GET['account'] ?? '');
    if ($account !== 'mine' && !preg_match('/^(student|nurse):\d{1,10}$/', $account)) {
        $account = '';
    }

    return [
        'search'    => mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100),
        'category'  => array_key_exists($category, auditCategories()) ? $category : '',
        'account'   => $account,
        'date_from' => $validDate(trim((string) ($_GET['date_from'] ?? ''))),
        'date_to'   => $validDate(trim((string) ($_GET['date_to'] ?? ''))),
    ];
}

// Philippine mobile numbers: exactly 11 digits starting with 09 (e.g. 09171234567).
// Spaces and dashes are tolerated on input and removed before storing.
function validateMobileNumber(string $value, string $label, array &$errors, bool $required = false): string
{
    $value = trim($value);
    if ($value === '') {
        if ($required) {
            $errors[] = $label . ' is required.';
        }
        return '';
    }

    $digits = preg_replace('/[\s\-]+/', '', $value);
    if (!preg_match('/^09\d{9}$/', $digits)) {
        $errors[] = $label . ' must be exactly 11 digits starting with 09 (e.g. 09171234567).';
        return $value;
    }
    return $digits;
}

function mobileInputValue(?string $value): string
{
    return preg_replace('/[\s\-]+/', '', (string) $value);
}

/** Shared password policy for students and admins: 8+ characters with a number and a special character. */
function validateNewPassword(string $password, array &$errors): void
{
    if (strlen($password) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }
    if (strlen($password) > 72) {
        // bcrypt only uses the first 72 bytes.
        $errors[] = 'New password must not exceed 72 characters.';
    }
    if (!preg_match('/\d/', $password)) {
        $errors[] = 'New password must contain at least one number.';
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'New password must contain at least one special character.';
    }
}

/**
 * Adds an error for every value longer than its database column allows.
 * $limits maps a key in $values to [label, max characters].
 */
function validateMaxLengths(array $values, array $limits, array &$errors): void
{
    foreach ($limits as $key => [$label, $max]) {
        if (mb_strlen(trim((string) ($values[$key] ?? ''))) > $max) {
            $errors[] = $label . ' must not exceed ' . $max . ' characters.';
        }
    }
}

/** Courses offered at FCAT and the department each belongs to; the clinic form fills Department from Course. */
function clinicCourseDepartments(): array
{
    return [
        'Bachelor of Science in Criminology'                 => 'College of Criminal Justice',
//        'Bachelor of Science in Education'                   => 'College of Education',
        'Bachelor of Secondary Education (Major in Science)' => 'College of Education',
        'Bachelor of Elementary Education'                   => 'College of Education',
        'Bachelor of Science in Computer Engineering'        => 'College of Computer Studies',
        'Bachelor of Science in Computer Science'            => 'College of Computer Studies',
        'Bachelor of Science in Hospitality and Management'  => 'College of Hospitality Management',
        'Bachelor of Science in Accountancy'                 => 'College of Business Administration and Accountancy',
        'Bachelor of Science in Business Administration'     => 'College of Business Administration and Accountancy',
        'Bachelor of Science in Nursing'                     => 'College of Nursing',
    ];
}

/** Courses offered at FCAT, shown as the Course choices on the clinic form. */
function clinicCourseOptions(): array
{
    return array_keys(clinicCourseDepartments());
}

/** Illnesses listed on FCAT School Clinic Form 01, in form order. */
function clinicIllnessList(): array
{
    return [
        'Asthma', 'Chicken Pox', 'Chronic Ear Infections or Otitis Media',
        'Diabetes', 'Epilepsy', 'Fainting Spells', 'Febrile Convulsions',
        'Heart Disorder', 'Hepatitis A', 'Hepatitis B',
        'Measles', 'Meningitis', 'Mumps', 'Primary Complex',
        'Rubella', 'Scoliosis', 'Skin Problems',
        'Urinary Tract Infections', 'Whooping Cough', 'Others',
    ];
}

/** Absolute path of a stored guardian ID image, or null if the stored path points outside the consent upload folder. */
function consentImageAbsolutePath(?string $relativePath): ?string
{
    $normalizedPath = str_replace('\\', '/', ltrim((string) $relativePath, '/'));
    if (!str_starts_with($normalizedPath, 'public/uploads/consent/') || str_contains($normalizedPath, '..')) {
        return null;
    }
    $absolutePath = dirname(__DIR__, 2) . '/' . $normalizedPath;
    return is_file($absolutePath) ? $absolutePath : null;
}

function deleteStoredConsentImage(?string $relativePath): void
{
    $absolutePath = consentImageAbsolutePath($relativePath);
    if ($absolutePath !== null) {
        unlink($absolutePath);
    }
}

/** Streams a stored guardian ID image with safe headers, or returns false if it can't be served. */
function streamConsentImage(?string $relativePath, ?string $downloadName = null): bool
{
    $absolutePath = consentImageAbsolutePath($relativePath);
    if ($absolutePath === null) {
        return false;
    }
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';
    if (!isset($extensions[$mime])) {
        return false;
    }

    header('Content-Type: ' . $mime);
    if ($downloadName !== null) {
        // Saves the file instead of opening it; the name is built by the caller from trusted values only.
        header('Content-Disposition: attachment; filename="' . $downloadName . '.' . $extensions[$mime] . '"');
    }
    header('Content-Length: ' . filesize($absolutePath));
    header('Cache-Control: private, no-store, max-age=0');
    header('X-Content-Type-Options: nosniff');
    readfile($absolutePath);
    return true;
}

// Failed sign-ins allowed within LOGIN_LOCKOUT_MINUTES before sign-in is refused. The per-IP limit is high
// because a school network often shares one public IP address across many students.
const LOGIN_MAX_FAILURES_PER_ACCOUNT_AND_IP = 5;
const LOGIN_MAX_FAILURES_PER_ACCOUNT = 10;
const LOGIN_MAX_FAILURES_PER_IP = 30;
const LOGIN_LOCKOUT_MINUTES = 15;

/**
 * True when sign-in should be refused for now because of too many recent failed attempts:
 * against this account from this IP, against this account from anywhere, or from this IP overall.
 * Counts come from the audit log, so throttling needs the AuditLogs table.
 */
function loginIsThrottled(PDO $pdo, ?string $targetRole, ?int $targetId): bool
{
    if (!databaseTableExists($pdo, 'AuditLogs')) {
        return false;
    }

    $window = (int) LOGIN_LOCKOUT_MINUTES;
    $recentFailures = static function (string $condition, array $params) use ($pdo, $window): int {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM AuditLogs
             WHERE Action = 'auth.login_failed' AND {$condition}
               AND CreatedAt >= (NOW() - INTERVAL {$window} MINUTE)"
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    };

    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $hasAccount = $targetRole !== null && $targetId !== null;

    if ($hasAccount && $ip !== ''
        && $recentFailures('SubjectRole = ? AND SubjectID = ? AND IPAddress = ?', [$targetRole, $targetId, $ip]) >= LOGIN_MAX_FAILURES_PER_ACCOUNT_AND_IP) {
        return true;
    }
    if ($hasAccount
        && $recentFailures('SubjectRole = ? AND SubjectID = ?', [$targetRole, $targetId]) >= LOGIN_MAX_FAILURES_PER_ACCOUNT) {
        return true;
    }
    return $ip !== '' && $recentFailures('IPAddress = ?', [$ip]) >= LOGIN_MAX_FAILURES_PER_IP;
}
