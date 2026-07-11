<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$students = $pdo->query(
    "SELECT StudentID, FirstName, LastName, Email, Phone, CreatedAt FROM Students ORDER BY CreatedAt DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$adminPageTitle = 'Students';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students</title>
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.min.css">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>
        <h1>Students</h1>

        <section id="all-students" class="admin-table-section student-announcements-panel">
            <div class="dashboard-section-title">
                <span><?= studentDashboardIcon('records') ?></span>
                <h2>All Students</h2>
                <a href="add-student.php" class="btn btn-success admin-panel-action">+ Add Student</a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                        $msg = match ($_GET['success']) {
                            'added'          => 'Student added successfully.',
                            'updated'        => 'Student updated successfully.',
                            'deleted'        => 'Student deleted successfully.',
                            'password_reset' => 'Student password has been reset.',
                            default          => 'Operation completed.',
                        };
                        echo htmlspecialchars($msg);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (($_GET['success'] ?? '') === 'added' && !empty($_SESSION['flash_temp_password'])): ?>
                <div class="alert alert-warning">
                    Default password: <strong><?= htmlspecialchars($_SESSION['flash_temp_password']) ?></strong>
                    — the student must change it on first login.
                </div>
                <?php unset($_SESSION['flash_temp_password']); ?>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'notfound'): ?>
                <div class="alert alert-danger">Student not found.</div>
            <?php endif; ?>

            <?php if (!empty($students)): ?>
                <div class="search-box admin-search-box">
                    <input type="text" id="student-search" placeholder="Search students...">
                </div>
                <table class="admin-dashboard-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                        <tr>
                            <td data-label="#"><?= htmlspecialchars($s['StudentID']) ?></td>
                            <td data-label="First Name"><?= htmlspecialchars($s['FirstName']) ?></td>
                            <td data-label="Last Name"><?= htmlspecialchars($s['LastName']) ?></td>
                            <td data-label="Email"><?= htmlspecialchars($s['Email'] ?? '—') ?></td>
                            <td data-label="Phone"><?= htmlspecialchars($s['Phone'] ?? '—') ?></td>
                            <td data-label="Actions" class="actions">
                                <a href="view-student.php?id=<?= $s['StudentID'] ?>" class="btn btn-info">View</a>
                                <a href="edit-student.php?id=<?= $s['StudentID'] ?>" class="btn btn-warning">Edit</a>
                                <form method="POST" action="delete-student.php" class="delete-form">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $s['StudentID'] ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="dashboard-empty">No students yet. Click <strong>+ Add Student</strong> to get started.</div>
            <?php endif; ?>
        </section>
    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js"></script>
</body>
</html>
