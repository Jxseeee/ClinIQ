<?php
require __DIR__ . '/../app/config/auth.php';
requireAdmin();

$stmt = $pdo->query("SELECT StudentID, FirstName, LastName, Email, Phone, CreatedAt FROM Students ORDER BY CreatedAt DESC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body>
    <?php include __DIR__ . '/../app/includes/admin-nav.php'; ?>
    <div class="container">
        <div class="header-row">
            <h1>Student Management</h1>
            <a href="../app/admin/add-student.php" class="btn btn-primary">+ Add Student</a>
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

        <?php if (count($students) > 0): ?>
            <div class="search-box">
                <input type="text" id="student-search" placeholder="Search students...">
            </div>
            <table>
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
                        <td><?= htmlspecialchars($s['StudentID']) ?></td>
                        <td><?= htmlspecialchars($s['FirstName']) ?></td>
                        <td><?= htmlspecialchars($s['LastName']) ?></td>
                        <td><?= htmlspecialchars($s['Email'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['Phone'] ?? '—') ?></td>
                        <td class="actions">
                            <a href="../app/admin/view-student.php?id=<?= $s['StudentID'] ?>" class="btn btn-info">View</a>
                            <a href="../app/admin/edit-student.php?id=<?= $s['StudentID'] ?>" class="btn btn-warning">Edit</a>
                            <form method="POST" action="../app/admin/delete-student.php" class="delete-form">
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
            <div class="card empty-state">
                <p>No students found. Click <strong>+ Add Student</strong> to get started.</p>
            </div>
        <?php endif; ?>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
