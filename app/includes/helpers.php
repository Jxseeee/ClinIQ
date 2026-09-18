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
        'trash'     => '<path d="M4.5 7h15"></path><path d="M9 3.5h6L16 7H8l1-3.5Z"></path><path d="m7 7 .75 13.5h8.5L17 7"></path><path d="M10 11v5.5"></path><path d="M14 11v5.5"></path>',
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

    if ($targetRole === 'admin') {
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

    if ($targetRole === 'admin') {
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

    if ($targetRole === 'admin') {
        $stmt = $pdo->prepare("UPDATE Notifications SET IsRead = 1 WHERE TargetRole = 'admin' AND IsRead = 0");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("UPDATE Notifications SET IsRead = 1 WHERE TargetRole = 'student' AND TargetUserID = ? AND IsRead = 0");
        $stmt->execute([$targetUserId]);
    }
}

function fetchClinicVisits(PDO $pdo, ?int $studentId = null, int $limit = 0): array
{
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return [];
    }

        $sql = "SELECT v.*,
               CASE WHEN v.StudentID IS NOT NULL THEN s.FirstName ELSE staff.FullName END AS FirstName,
               CASE WHEN v.StudentID IS NOT NULL THEN s.LastName ELSE '' END AS LastName,
               CASE WHEN v.StudentID IS NOT NULL THEN 'student' ELSE 'staff' END AS PersonType,
               COALESCE(v.StudentID, v.StaffID) AS PersonID,
               a.FullName AS AdminName
            FROM ClinicVisits v
            LEFT JOIN Students s ON s.StudentID = v.StudentID
            LEFT JOIN Admins staff ON staff.AdminID = v.StaffID
            LEFT JOIN Admins a ON a.AdminID = v.AdminID";
    $params = [];

    if ($studentId !== null) {
        $sql .= " WHERE v.StudentID = ?";
        $params[] = $studentId;
    }

    $sql .= " ORDER BY v.CreatedAt DESC";
    if ($limit > 0) {
        $sql .= " LIMIT " . (int) $limit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createClinicVisit(PDO $pdo, int $studentId, int $adminId, array $data): void
{
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO ClinicVisits
         (StudentID, AdminID, Complaint, Vitals, Assessment, Treatment, MedicineGiven, MedicineDosage, Status, Disposition, FollowUpDate)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $studentId,
        $adminId,
        trim($data['complaint'] ?? ''),
        trim($data['vitals'] ?? '') ?: null,
        trim($data['assessment'] ?? '') ?: null,
        trim($data['treatment'] ?? '') ?: null,
        trim($data['medicine_given'] ?? '') ?: null,
        trim($data['medicine_dosage'] ?? '') ?: null,
        $data['status'] ?? 'completed',
        trim($data['disposition'] ?? '') ?: null,
        trim($data['follow_up_date'] ?? '') ?: null,
    ]);

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

function countClinicVisits(PDO $pdo): int
{
    if (!databaseTableExists($pdo, 'ClinicVisits')) {
        return 0;
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
    $stmt = $pdo->prepare(
        "SELECT v.*, a.FullName AS AdminName
         FROM ClinicVisits v
         LEFT JOIN Admins a ON a.AdminID = v.AdminID
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
            $medicine = $pdo->prepare("SELECT MedicineID, Name, Stock FROM Medicines WHERE MedicineID = ? FOR UPDATE");
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
            trim((string) ($data['follow_up_date'] ?? '')) ?: null,
        ]);
        $visitId = (int) $pdo->lastInsertId();

        $checkIn = $pdo->prepare(
            "INSERT INTO ClinicCheckIns
             (PersonType, StudentID, StaffID, VisitID, CheckedInByAdminID)
             VALUES (?, ?, ?, ?, ?)"
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
        $pdo->commit();
        return $checkInId;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

function checkOutClinicCheckIn(PDO $pdo, int $checkInId): void
{
    if (!databaseTableExists($pdo, 'ClinicCheckIns') || $checkInId <= 0) {
        throw new InvalidArgumentException('Choose a valid active check-in.');
    }
    $pdo->beginTransaction();
    try {
        $visit = $pdo->prepare(
            "SELECT VisitID FROM ClinicCheckIns
             WHERE CheckInID = ? AND CheckedOutAt IS NULL FOR UPDATE"
        );
        $visit->execute([$checkInId]);
        $visitId = (int) $visit->fetchColumn();
        if ($visitId <= 0) {
            throw new InvalidArgumentException('This person is no longer checked in.');
        }
        $stmt = $pdo->prepare("UPDATE ClinicCheckIns SET CheckedOutAt = NOW() WHERE CheckInID = ?");
        $stmt->execute([$checkInId]);
        $closeVisit = $pdo->prepare("UPDATE ClinicVisits SET Status = 'completed' WHERE VisitID = ? AND Status = 'open'");
        $closeVisit->execute([$visitId]);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

function fetchClinicOccupancy(PDO $pdo): array
{
    $result = [
        'NursesOnDuty' => 0,
        'StudentsInClinic' => 0,
        'UpdatedAt' => null,
    ];

    if (databaseTableExists($pdo, 'ClinicOccupancy')) {
        $row = $pdo->query(
            "SELECT NursesOnDuty, StudentsInClinic, UpdatedAt
             FROM ClinicOccupancy WHERE OccupancyID = 1"
        )->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $result = $row;
        }
    }

    if (databaseTableExists($pdo, 'Nurses')) {
        $result['NursesOnDuty'] = countNursesOnDuty($pdo);
    }
    if (databaseTableExists($pdo, 'ClinicCheckIns')) {
        $result['StudentsInClinic'] = countActiveClinicCheckIns($pdo);
    }

    return $result;
}

function fetchNurses(PDO $pdo): array
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        return [];
    }

    return $pdo->query(
        "SELECT NurseID, FullName, Position, LicenseNumber, IsOnDuty, CreatedAt, UpdatedAt
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

function syncNursesOnDuty(PDO $pdo, int $adminId): int
{
    $count = countNursesOnDuty($pdo);
    if (!databaseTableExists($pdo, 'ClinicOccupancy')) {
        return $count;
    }

    $pdo->exec(
        "INSERT IGNORE INTO ClinicOccupancy (OccupancyID, NursesOnDuty, StudentsInClinic)
         VALUES (1, 0, 0)"
    );
    $stmt = $pdo->prepare(
        "UPDATE ClinicOccupancy SET NursesOnDuty = ?, UpdatedByAdminID = ? WHERE OccupancyID = 1"
    );
    $stmt->execute([$count, $adminId]);
    return $count;
}

function createNurse(PDO $pdo, array $data, int $adminId): int
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        throw new RuntimeException('Nurse directory storage is not installed.');
    }

    [$fullName, $position, $licenseNumber] = validateNurseData($data);
    $isOnDuty = (int) ($data['is_on_duty'] ?? 0) === 1 ? 1 : 0;
    $stmt = $pdo->prepare(
        "INSERT INTO Nurses (FullName, Position, LicenseNumber, IsOnDuty, CreatedByAdminID)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$fullName, $position, $licenseNumber, $isOnDuty, $adminId]);
    $nurseId = (int) $pdo->lastInsertId();
    syncNursesOnDuty($pdo, $adminId);
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
    }
    syncNursesOnDuty($pdo, $adminId);
}

function setNurseDutyStatus(PDO $pdo, int $nurseId, bool $isOnDuty, int $adminId): int
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        throw new RuntimeException('Nurse directory storage is not installed.');
    }
    if ($nurseId <= 0) {
        throw new InvalidArgumentException('Choose a valid nurse to update.');
    }

    $stmt = $pdo->prepare("UPDATE Nurses SET IsOnDuty = ? WHERE NurseID = ?");
    $stmt->execute([$isOnDuty ? 1 : 0, $nurseId]);
    if ($stmt->rowCount() === 0) {
        $exists = $pdo->prepare("SELECT COUNT(*) FROM Nurses WHERE NurseID = ?");
        $exists->execute([$nurseId]);
        if (!(bool) $exists->fetchColumn()) {
            throw new InvalidArgumentException('The selected nurse was not found.');
        }
    }
    return syncNursesOnDuty($pdo, $adminId);
}

function deleteNurse(PDO $pdo, int $nurseId, int $adminId): void
{
    if (!databaseTableExists($pdo, 'Nurses')) {
        throw new RuntimeException('Nurse directory storage is not installed.');
    }
    if ($nurseId <= 0) {
        throw new InvalidArgumentException('Choose a valid nurse to delete.');
    }

    $stmt = $pdo->prepare("DELETE FROM Nurses WHERE NurseID = ?");
    $stmt->execute([$nurseId]);
    if ($stmt->rowCount() === 0) {
        throw new InvalidArgumentException('The selected nurse was not found.');
    }
    syncNursesOnDuty($pdo, $adminId);
}

function medicineInventorySummary(PDO $pdo): array
{
    if (!databaseTableExists($pdo, 'Medicines')) {
        return ['TotalMedicines' => 0, 'LowStockMedicines' => 0];
    }

    $row = $pdo->query(
        "SELECT COUNT(*) AS TotalMedicines,
                SUM(Stock <= LowStockThreshold) AS LowStockMedicines
         FROM Medicines"
    )->fetch(PDO::FETCH_ASSOC);

    return [
        'TotalMedicines' => (int) ($row['TotalMedicines'] ?? 0),
        'LowStockMedicines' => (int) ($row['LowStockMedicines'] ?? 0),
    ];
}

function fetchMedicines(PDO $pdo, string $search = '', bool $lowStockOnly = false): array
{
    if (!databaseTableExists($pdo, 'Medicines')) {
        return [];
    }

    $where = [];
    $params = [];
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

function createMedicine(PDO $pdo, array $data, int $adminId): void
{
    if (!databaseTableExists($pdo, 'Medicines')) {
        throw new RuntimeException('Medicine inventory storage is not installed.');
    }

    $name = trim((string) ($data['name'] ?? ''));
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
}

function addMedicineStock(PDO $pdo, int $medicineId, int $quantity): void
{
    if (!databaseTableExists($pdo, 'Medicines')) {
        throw new RuntimeException('Medicine inventory storage is not installed.');
    }
    if ($medicineId <= 0 || $quantity <= 0 || $quantity > 999999) {
        throw new InvalidArgumentException('Choose a medicine and enter a quantity from 1 to 999999.');
    }

    $stmt = $pdo->prepare("UPDATE Medicines SET Stock = Stock + ? WHERE MedicineID = ?");
    $stmt->execute([$quantity, $medicineId]);
    if ($stmt->rowCount() === 0) {
        throw new InvalidArgumentException('The selected medicine was not found.');
    }
}

function deleteMedicine(PDO $pdo, int $medicineId): void
{
    if (!databaseTableExists($pdo, 'Medicines')) {
        throw new RuntimeException('Medicine inventory storage is not installed.');
    }
    if ($medicineId <= 0) {
        throw new InvalidArgumentException('Choose a valid medicine to delete.');
    }

    $stmt = $pdo->prepare('DELETE FROM Medicines WHERE MedicineID = ?');
    $stmt->execute([$medicineId]);
    if ($stmt->rowCount() === 0) {
        throw new InvalidArgumentException('The selected medicine was not found.');
    }
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

function generateTempPassword(int $length = 10): string
{
    // Unambiguous characters only (no 0/O, 1/l/I) so the password is easy to relay.
    $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $out;
}

function displayValue(?string $val): string
{
    return htmlspecialchars($val ?? '') ?: '—';
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

function countUnreadChatForStudent(PDO $pdo, int $studentId): int
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM Messages WHERE StudentID = ? AND SenderRole = 'admin' AND IsRead = 0"
    );
    $stmt->execute([$studentId]);
    return (int) $stmt->fetchColumn();
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
