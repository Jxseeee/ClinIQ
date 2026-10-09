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
        'audit'     =>'<rect x="4.5" y="4" width="13" height="16.5" rx="2"></rect><path d="M8.5 4V2.75h5V4"></path><path d="M8 9h6"></path><path d="M8 12.5h3.5"></path><circle cx="17" cy="16.5" r="3.75"></circle><path d="M17 14.75v1.75l1.25 1"></path>',
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
    $stmt = $pdo->query(
        "SELECT a.*, ad.FullName AS AuthorName
         FROM Announcements a
         LEFT JOIN Admins ad ON a.AdminID = ad.AdminID
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

function countUnreadNotifications(PDO $pdo, string $targetRole, ?int $targetUserId = null): int
{
    if (!databaseTableExists($pdo, 'Notifications')) {
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
    if (!databaseTableExists($pdo, 'Notifications')) {
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
    if (!databaseTableExists($pdo, 'Notifications')) {
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

/**
 * $filters (optional): 'search' matches patient, complaint, treatment, medicine, or staff name;
 * 'status' is open / completed / follow-up.
 */
function fetchClinicVisits(PDO $pdo, ?int $studentId = null, int $limit = 0, array $filters = []): array
{
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return [];
    }

    [$checkInSelect, $checkInJoin] = clinicVisitCheckInSql($pdo);
    $sql = "SELECT v.*,
               CASE WHEN v.StudentID IS NOT NULL THEN s.FirstName ELSE staff.FullName END AS FirstName,
               CASE WHEN v.StudentID IS NOT NULL THEN s.LastName ELSE '' END AS LastName,
               CASE WHEN v.StudentID IS NOT NULL THEN 'student' ELSE 'staff' END AS PersonType,
               COALESCE(v.StudentID, v.StaffID) AS PersonID,
               a.FullName AS AdminName,
               {$checkInSelect}
            FROM ClinicVisits v
            LEFT JOIN Students s ON s.StudentID = v.StudentID
            LEFT JOIN Admins staff ON staff.AdminID = v.StaffID
            LEFT JOIN Admins a ON a.AdminID = v.AdminID
            {$checkInJoin}";
    $where = [];
    $params = [];

    if ($studentId !== null) {
        $where[] = 'v.StudentID = ?';
        $params[] = $studentId;
    }
    $search = trim((string) ($filters['search'] ?? ''));
    if ($search !== '') {
        $where[] = "(CONCAT_WS(' ', s.FirstName, s.LastName, staff.FullName) LIKE ?
                     OR v.Complaint LIKE ? OR v.Treatment LIKE ? OR v.MedicineGiven LIKE ? OR a.FullName LIKE ?)";
        $term = '%' . $search . '%';
        array_push($params, $term, $term, $term, $term, $term);
    }
    if (in_array($filters['status'] ?? '', ['open', 'completed', 'follow-up'], true)) {
        $where[] = 'v.Status = ?';
        $params[] = $filters['status'];
    }

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= " ORDER BY v.CreatedAt DESC";
    if ($limit > 0) {
        $sql .= " LIMIT " . (int) $limit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countClinicVisits(PDO $pdo, ?int $studentId = null): int
{
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return 0;
    }

    if ($studentId !== null) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ClinicVisits WHERE StudentID = ?");
        $stmt->execute([$studentId]);
        return (int) $stmt->fetchColumn();
    }

    return (int) $pdo->query("SELECT COUNT(*) FROM ClinicVisits")->fetchColumn();
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
    $params = [];

    if ($query !== '') {
        $term = '%' . $query . '%';
        $studentWhere .= " AND (CONCAT(s.FirstName, ' ', s.LastName) LIKE ? OR CAST(s.StudentID AS CHAR) LIKE ?)";
        $staffWhere .= ' AND staff.FullName LIKE ?';
        $params = [$term, $term, $term];
    }

    $sql = "SELECT 'student' AS PersonType,
                   s.StudentID AS PersonID,
                   CONCAT(s.FirstName, ' ', s.LastName) AS DisplayName,
                   CONCAT('#', s.StudentID) AS Details,
                   CONCAT_WS(' ', s.FirstName, s.LastName, s.StudentID) AS SearchText
            FROM Students s
            {$studentWhere}
            UNION ALL
            SELECT 'staff' AS PersonType,
                   staff.AdminID AS PersonID,
                   staff.FullName AS DisplayName,
                   '' AS Details,
                   staff.FullName AS SearchText
            FROM Admins staff
            {$staffWhere}
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
    if ($personId <= 0 || !in_array($personType, ['student', 'staff'], true)) {
        return null;
    }

    if ($personType === 'student') {
        $stmt = $pdo->prepare(
            "SELECT 'student' AS PersonType, StudentID AS PersonID,
                    CONCAT(FirstName, ' ', LastName) AS DisplayName,
                    CONCAT('#', StudentID) AS Details
             FROM Students WHERE StudentID = ?"
        );
    } else {
        $stmt = $pdo->prepare(
            "SELECT 'staff' AS PersonType, AdminID AS PersonID, FullName AS DisplayName,
                    '' AS Details
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
    $column = $personType === 'staff' ? 'StaffID' : 'StudentID';
    [$checkInSelect, $checkInJoin] = clinicVisitCheckInSql($pdo);
    $stmt = $pdo->prepare(
        "SELECT v.*, a.FullName AS AdminName, {$checkInSelect}
         FROM ClinicVisits v
         LEFT JOIN Admins a ON a.AdminID = v.AdminID
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
    return $pdo->query(
        "SELECT c.*, v.Complaint, v.Status,
                COALESCE(CONCAT(s.FirstName, ' ', s.LastName), staff.FullName) AS DisplayName,
                CASE WHEN c.PersonType = 'student'
                     THEN CONCAT('#', s.StudentID, ' · Student')
                     ELSE CONCAT('@', staff.Username, ' · Staff') END AS Details
         FROM ClinicCheckIns c
         JOIN ClinicVisits v ON v.VisitID = c.VisitID
         LEFT JOIN Students s ON s.StudentID = c.StudentID
         LEFT JOIN Admins staff ON staff.AdminID = c.StaffID
         WHERE c.CheckedOutAt IS NULL
         ORDER BY c.CheckedInAt ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

function createClinicCheckIn(PDO $pdo, string $personType, int $personId, int $adminId, array $data): int
{
    if (!databaseTableExists($pdo, 'ClinicCheckIns')) {
        throw new RuntimeException('Clinic check-in storage is not installed.');
    }
    $person = fetchCheckInPerson($pdo, $personType, $personId);
    if (!$person) {
        throw new InvalidArgumentException('Choose an existing student or staff member.');
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
    $status = in_array(($data['status'] ?? ''), ['open', 'completed', 'follow-up'], true)
        ? (string) $data['status']
        : 'open';
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

    $pdo->beginTransaction();
    try {
        $duplicateSql = $personType === 'student'
            ? "SELECT COUNT(*) FROM ClinicCheckIns WHERE StudentID = ? AND CheckedOutAt IS NULL FOR UPDATE"
            : "SELECT COUNT(*) FROM ClinicCheckIns WHERE StaffID = ? AND CheckedOutAt IS NULL FOR UPDATE";
        $duplicate = $pdo->prepare($duplicateSql);
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
        $visit = $pdo->prepare(
            "INSERT INTO ClinicVisits
             (StudentID, StaffID, AdminID, Complaint, Vitals, Assessment, Treatment,
              MedicineGiven, MedicineID, MedicineDosage, MedicineQuantity, Status, Disposition, FollowUpDate)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $visit->execute([
            $studentId,
            $staffId,
            $adminId,
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
        ]);
        $visitId = (int) $pdo->lastInsertId();

        $checkIn = $pdo->prepare(
            "INSERT INTO ClinicCheckIns
             (PersonType, StudentID, StaffID, VisitID, CheckedInByAdminID, CheckedInAt)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );
        $checkIn->execute([$personType, $studentId, $staffId, $visitId, $adminId]);
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
            'Checked in ' . $person['DisplayName'] . ' (' . $personType . ') to the clinic. Complaint: '
                . mb_strimwidth($complaint, 0, 160, '...')
                . ($medicineName !== null ? '. Medicine given: ' . $medicineName . ' x' . $medicineQuantity : '') . '.',
            ['actor_role' => 'admin', 'actor_id' => $adminId, 'subject_role' => $personType === 'staff' ? 'admin' : 'student', 'subject_id' => $personId]
        );
        $pdo->commit();
        return $checkInId;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
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
            "SELECT VisitID, PersonType, StudentID, StaffID FROM ClinicCheckIns
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
        $isStaff = ($checkIn['PersonType'] ?? '') === 'staff';
        $subjectRole = $isStaff ? 'admin' : 'student';
        $subjectId = (int) ($isStaff ? $checkIn['StaffID'] : $checkIn['StudentID']);
        auditLog(
            $pdo,
            'visits',
            'visit.checked_out',
            'Checked out ' . (auditAccountName($pdo, $subjectRole, $subjectId) ?? 'a ' . ($isStaff ? 'staff member' : 'student'))
                . ' (' . ($isStaff ? 'staff' : 'student') . ') from the clinic.',
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
    return $pdo->query(
        "SELECT NurseID, FullName, Position, LicenseNumber, IsOnDuty, {$campusColumn}, CreatedAt, UpdatedAt
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

function createNurse(PDO $pdo, array $data, int $adminId): int
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        throw new RuntimeException('Nurse directory storage is not installed.');
    }

    [$fullName, $position, $licenseNumber] = validateNurseData($data);
    $isOnDuty = (int) ($data['is_on_duty'] ?? 0) === 1 ? 1 : 0;
    // A nurse added as on duty must be assigned to a campus.
    $campus = $isOnDuty ? validateDutyCampus($data['duty_campus'] ?? null) : null;

    if (ensureNurseDutyCampusColumn($pdo)) {
        $stmt = $pdo->prepare(
            "INSERT INTO Nurses (FullName, Position, LicenseNumber, IsOnDuty, DutyCampus, CreatedByAdminID)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$fullName, $position, $licenseNumber, $isOnDuty, $campus, $adminId]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO Nurses (FullName, Position, LicenseNumber, IsOnDuty, CreatedByAdminID)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$fullName, $position, $licenseNumber, $isOnDuty, $adminId]);
    }
    $nurseId = (int) $pdo->lastInsertId();
    auditLog(
        $pdo,
        'nurses',
        'nurse.added',
        'Added nurse ' . $fullName . ' (' . $position . ')' . ($isOnDuty ? ', on duty at ' . $campus : '') . '.',
        ['subject_role' => null]
    );
    return $nurseId;
}

function updateNurse(PDO $pdo, int $nurseId, array $data, int $adminId): void
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        throw new RuntimeException('Nurse directory storage is not installed.');
    }
    if ($nurseId <= 0) {
        throw new InvalidArgumentException('Choose a valid nurse to update.');
    }

    [$fullName, $position, $licenseNumber] = validateNurseData($data);
    $stmt = $pdo->prepare(
        "UPDATE Nurses SET FullName = ?, Position = ?, LicenseNumber = ? WHERE NurseID = ?"
    );
    $stmt->execute([$fullName, $position, $licenseNumber, $nurseId]);
    if ($stmt->rowCount() === 0) {
        $exists = $pdo->prepare("SELECT COUNT(*) FROM Nurses WHERE NurseID = ?");
        $exists->execute([$nurseId]);
        if (!(bool) $exists->fetchColumn()) {
            throw new InvalidArgumentException('The selected nurse was not found.');
        }
    } else {
        auditLog($pdo, 'nurses', 'nurse.updated', 'Updated nurse details for ' . $fullName . '.', ['subject_role' => null]);
    }
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
        auditLog(
            $pdo,
            'nurses',
            'nurse.duty_changed',
            'Set nurse ' . $nurseName->fetchColumn() . ($isOnDuty ? ' on duty at ' . $campus . '.' : ' off duty.'),
            ['subject_role' => null]
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
    $stmt = $pdo->prepare("DELETE FROM Nurses WHERE NurseID = ?");
    $stmt->execute([$nurseId]);
    if ($stmt->rowCount() === 0) {
        throw new InvalidArgumentException('The selected nurse was not found.');
    }
    auditLog($pdo, 'nurses', 'nurse.deleted', 'Removed nurse ' . $deletedNurseName . ' from the directory.', ['subject_role' => null]);
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

function formatChatMessage(array $row, string $viewerRole): array
{
    $senderName = $row['SenderRole'] === 'admin'
        ? ($row['AdminName'] ?? 'Admin')
        : trim(($row['StudentFirstName'] ?? '') . ' ' . ($row['StudentLastName'] ?? ''));

    return [
        'message_id'  => (int) $row['MessageID'],
        'student_id'  => (int) $row['StudentID'],
        'sender_role' => $row['SenderRole'],
        'sender_name' => $senderName ?: ($row['SenderRole'] === 'admin' ? 'Admin' : 'Student'),
        'content'     => $row['Content'],
        'created_at'  => $row['CreatedAt'],
        'is_mine'     => $row['SenderRole'] === $viewerRole,
    ];
}

function fetchChatMessages(PDO $pdo, int $studentId): array
{
    $stmt = $pdo->prepare(
        "SELECT m.*, s.FirstName AS StudentFirstName, s.LastName AS StudentLastName, a.FullName AS AdminName
         FROM Messages m
         JOIN Students s ON s.StudentID = m.StudentID
         LEFT JOIN Admins a ON a.AdminID = m.AdminID
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

function sendChatMessage(PDO $pdo, int $studentId, string $senderRole, ?int $adminId, string $content): array
{
    ensureChatConversation($pdo, $studentId);

    $stmt = $pdo->prepare(
        "INSERT INTO Messages (StudentID, SenderRole, AdminID, Content) VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$studentId, $senderRole, $adminId, $content]);
    $messageId = (int) $pdo->lastInsertId();

    if (databaseTableExists($pdo, 'ChatConversations')) {
        $conversationUpdate = $senderRole === 'student'
            ? "UPDATE ChatConversations
               SET Status = 'open', LastMessageAt = NOW(), ResolvedAt = NULL, ResolvedByAdminID = NULL
               WHERE StudentID = ?"
            : "UPDATE ChatConversations SET LastMessageAt = NOW() WHERE StudentID = ?";
        $stmt = $pdo->prepare($conversationUpdate);
        $stmt->execute([$studentId]);
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
            'The clinic admin sent you a message.',
            getBasePath() . '/students/messages.php'
        );
    }

    $stmt = $pdo->prepare(
        "SELECT m.*, s.FirstName AS StudentFirstName, s.LastName AS StudentLastName, a.FullName AS AdminName
         FROM Messages m
         JOIN Students s ON s.StudentID = m.StudentID
         LEFT JOIN Admins a ON a.AdminID = m.AdminID
         WHERE m.MessageID = ? AND m.StudentID = ?"
    );
    $stmt->execute([$messageId, $studentId]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
        throw new RuntimeException('Unable to load the newly created chat message.');
    }

    return $message;
}

function fetchChatThreads(PDO $pdo): array
{
    $hasConversations = databaseTableExists($pdo, 'ChatConversations');
    $statusSelect = $hasConversations ? "COALESCE(c.Status, 'open')" : "'open'";
    $statusJoin = $hasConversations ? "LEFT JOIN ChatConversations c ON c.StudentID = s.StudentID" : "";
    $statusGroup = $hasConversations ? ", c.Status" : "";

    $stmt = $pdo->query(
        "SELECT s.StudentID, s.FirstName, s.LastName,
                SUM(m.SenderRole = 'student' AND m.IsRead = 0) AS UnreadCount,
                MAX(m.CreatedAt) AS LastActivity,
                {$statusSelect} AS ConversationStatus,
                (SELECT Content FROM Messages m2
                 WHERE m2.StudentID = s.StudentID
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
    return ($_SESSION['role'] ?? '') === 'admin'
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

function activeChatCount(PDO $pdo): int
{
    cleanupChatPresence($pdo);
    return (int) $pdo->query("SELECT COUNT(*) FROM ActiveChatSessions")->fetchColumn();
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
    if ($id === null || !in_array($role, ['admin', 'student'], true)) {
        return null;
    }
    $key = $role . ':' . $id;
    if (!array_key_exists($key, $cache)) {
        $stmt = $role === 'admin'
            ? $pdo->prepare("SELECT COALESCE(NULLIF(FullName, ''), Username) FROM Admins WHERE AdminID = ?")
            : $pdo->prepare("SELECT CONCAT(FirstName, ' ', LastName) FROM Students WHERE StudentID = ?");
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
        $actorRole = in_array($actorRole, ['admin', 'student', 'guest'], true) ? $actorRole : 'guest';
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
    // Admin-only account selector: '' (all), 'mine', or 'student:<id>'. Ignored on the student page.
    $account = (string) ($_GET['account'] ?? '');
    if ($account !== 'mine' && !preg_match('/^student:\d{1,10}$/', $account)) {
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
function streamConsentImage(?string $relativePath): bool
{
    $absolutePath = consentImageAbsolutePath($relativePath);
    if ($absolutePath === null) {
        return false;
    }
    $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        return false;
    }

    header('Content-Type: ' . $mime);
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
