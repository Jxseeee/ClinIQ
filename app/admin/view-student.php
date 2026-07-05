<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: ../../public/index.php"); exit; }

$data = fetchStudentFullProfile($pdo, (int)$id);
if (!$data) { header("Location: ../../public/index.php?error=notfound"); exit; }

['student' => $student, 'g1' => $g1, 'g2' => $g2, 'medHistory' => $medHistory] = $data;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body>
    <?php include __DIR__ . '/../includes/admin-nav.php'; ?>
    <div class="container container-wide">
        <h1>Student #<?= htmlspecialchars($student['StudentID']) ?> — <?= htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']) ?></h1>

        <?php include __DIR__ . '/../includes/profile-display.php'; ?>

        <div class="form-actions" style="margin-top: 20px; margin-bottom: 40px;">
            <a href="edit-student.php?id=<?= $student['StudentID'] ?>" class="btn btn-warning">Edit</a>
            <a href="../../public/index.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
    <script src="../../public/assets/js/main.js"></script>
</body>
</html>
