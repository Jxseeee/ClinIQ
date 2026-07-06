<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();

$data = fetchStudentFullProfile($pdo, (int)$_SESSION['user_id']);
if (!$data) { header("Location: ../public/logout.php"); exit; }

['student' => $student, 'g1' => $g1, 'g2' => $g2, 'medHistory' => $medHistory] = $data;
$profileComplete = !empty($student['Course']) && !empty($student['Gender']) && !empty($student['DateOfBirth']) && !empty($student['StreetAddress']);
$studentPageTitle = 'Patient Records';
$uploadErrors = [];
$clinicVisits = fetchClinicVisits($pdo, (int) $_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_consent_image') {
    verifyCsrfToken();

    if (empty($_FILES['consent_image']['name']) || !is_uploaded_file($_FILES['consent_image']['tmp_name'])) {
        $uploadErrors[] = 'Please choose an image to upload.';
    } elseif ($_FILES['consent_image']['size'] > 5 * 1024 * 1024) {
        $uploadErrors[] = 'Image must be 5MB or smaller.';
    } else {
        $mime = mime_content_type($_FILES['consent_image']['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($extensions[$mime])) {
            $uploadErrors[] = 'Only JPG, PNG, or WEBP images are allowed.';
        } else {
            $uploadDir = dirname(__DIR__) . '/public/uploads/consent';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            $filename = 'consent_' . (int) $_SESSION['user_id'] . '_' . time() . '.' . $extensions[$mime];
            $target = $uploadDir . '/' . $filename;

            if (move_uploaded_file($_FILES['consent_image']['tmp_name'], $target)) {
                if (!empty($student['ConsentImagePath'])) {
                    $oldPath = dirname(__DIR__) . '/' . ltrim($student['ConsentImagePath'], '/');
                    if (is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $relativePath = 'public/uploads/consent/' . $filename;
                $stmt = $pdo->prepare("UPDATE Students SET ConsentImagePath = ? WHERE StudentID = ?");
                $stmt->execute([$relativePath, $_SESSION['user_id']]);
                header('Location: profile.php?upload=success');
                exit;
            }

            $uploadErrors[] = 'Upload failed. Please try again.';
        }
    }
}

$medMap = [];
foreach ($medHistory as $row) {
    $medMap[$row['Illness']] = $row;
}
$illnesses = [
    'Asthma', 'Chicken Pox', 'Chronic Ear Infections or Otitis Media',
    'Diabetes', 'Epilepsy', 'Fainting Spells', 'Febrile Convulsions',
    'Heart Disorder', 'Hepatitis A', 'Hepatitis B',
    'Measles', 'Meningitis', 'Mumps', 'Primary Complex',
    'Rubella', 'Scoliosis', 'Skin Problems',
    'Urinary Tract Infections', 'Whooping Cough', 'Others',
];
$studentContentClass = 'patient-records-content';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../public/assets/css/style.min.css">
    <link rel="icon" type="image/png" href="../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../app/includes/student-dashboard-start.php'; ?>
    <div class="patient-records-page">
        <div class="patient-records-actions">
            <h1>FCAT School Clinic Form 01</h1>
            <div>
                <a href="edit-profile.php" class="btn btn-success paper-download-btn">Edit</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Profile updated successfully.</div>
        <?php endif; ?>

        <?php if (isset($_GET['password_changed'])): ?>
            <div class="alert alert-success">Password changed successfully.</div>
        <?php endif; ?>

        <?php if (($_GET['upload'] ?? '') === 'success'): ?>
            <div class="alert alert-success">Consent image uploaded successfully.</div>
        <?php endif; ?>

        <?php if (!$profileComplete): ?>
            <div class="alert alert-warning">
                Your clinic form is incomplete. Please <a href="edit-profile.php"><strong>fill it out</strong></a>.
            </div>
        <?php endif; ?>

        <?php if (!empty($uploadErrors)): ?>
            <div class="alert alert-danger">
                <ul style="margin:0; padding-left:18px;">
                    <?php foreach ($uploadErrors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="clinic-paper-form">
            <div class="paper-section">
                <h2>Student Information</h2>
                <table class="paper-table">
                    <tr><td>Last: <?= htmlspecialchars($student['LastName'] ?? '') ?></td><td>First: <?= htmlspecialchars($student['FirstName'] ?? '') ?></td><td>Middle: <?= htmlspecialchars($student['MiddleName'] ?? '') ?></td></tr>
                    <tr><td>Course: <?= htmlspecialchars($student['Course'] ?? '') ?></td><td>Year Level: <?= htmlspecialchars($student['YearLevel'] ?? '') ?></td><td>Department: <?= htmlspecialchars($student['Department'] ?? '') ?></td></tr>
                    <tr><td>Preferred Name: <?= htmlspecialchars($student['PreferredName'] ?? '') ?></td><td>Date of Birth: <?= htmlspecialchars($student['DateOfBirth'] ?? '') ?></td><td>Citizenship: <?= htmlspecialchars($student['Citizenship'] ?? '') ?></td></tr>
                    <tr><td>Height (m): <?= htmlspecialchars($student['Height'] ?? '') ?></td><td>Weight (kg): <?= htmlspecialchars($student['Weight'] ?? '') ?></td><td>Gender: <?= htmlspecialchars($student['Gender'] ?? '') ?></td></tr>
                    <tr><td colspan="2">Home Address: <?= htmlspecialchars(trim(($student['StreetAddress'] ?? '') . ' ' . ($student['Municipality'] ?? '') . ' ' . ($student['City'] ?? ''))) ?></td><td>Home Telephone: <?= htmlspecialchars($student['HomeTelephone'] ?? '') ?></td></tr>
                </table>
            </div>

            <div class="paper-section">
                <h2>Parent/Guardian Information</h2>
                <table class="paper-table">
                    <tr><td><?= ($g1['Relationship'] ?? '') === 'Father' ? '☑' : '☐' ?> Father &nbsp; <?= ($g1['Relationship'] ?? '') === 'Stepfather' ? '☑' : '☐' ?> Stepfather &nbsp; <?= ($g1['Relationship'] ?? '') === 'Legal Guardian' ? '☑' : '☐' ?> Legal Guardian</td><td><?= ($g2['Relationship'] ?? '') === 'Mother' ? '☑' : '☐' ?> Mother &nbsp; <?= ($g2['Relationship'] ?? '') === 'Stepmother' ? '☑' : '☐' ?> Stepmother &nbsp; <?= ($g2['Relationship'] ?? '') === 'Legal Guardian' ? '☑' : '☐' ?> Legal Guardian</td></tr>
                    <tr><td>Last Name: <?= htmlspecialchars($g1['LastName'] ?? '') ?></td><td>Last Name: <?= htmlspecialchars($g2['LastName'] ?? '') ?></td></tr>
                    <tr><td>First Name: <?= htmlspecialchars($g1['FirstName'] ?? '') ?></td><td>First Name: <?= htmlspecialchars($g2['FirstName'] ?? '') ?></td></tr>
                    <tr><td>Mobile Number: <?= htmlspecialchars($g1['MobileNumber'] ?? '') ?></td><td>Mobile Number: <?= htmlspecialchars($g2['MobileNumber'] ?? '') ?></td></tr>
                    <tr><td>Email Address: <?= htmlspecialchars($g1['EmailAddress'] ?? '') ?></td><td>Email Address: <?= htmlspecialchars($g2['EmailAddress'] ?? '') ?></td></tr>
                </table>
            </div>

            <div class="paper-section">
                <h2>Past History</h2>
                <table class="paper-table paper-history-table">
                    <?php foreach (array_chunk($illnesses, 2) as $pair): ?>
                        <tr>
                            <?php foreach ($pair as $illness): ?>
                                <td><?= isset($medMap[$illness]) ? '☑' : '☐' ?> <?= htmlspecialchars($illness) ?></td>
                                <td><?= htmlspecialchars($medMap[$illness]['DiagnosisDate'] ?? '') ?></td>
                                <td><?= htmlspecialchars($medMap[$illness]['DiagnosisAge'] ?? '') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <p><strong>Medication/s taken on regular basis:</strong> <?= htmlspecialchars($student['MedicationsRegular'] ?? '') ?></p>
            </div>

            <div class="paper-section">
                <p>Please indicate if the child has any allergies.</p>
                <table class="paper-table paper-allergy-table">
                    <tr><th>Foods</th><th>Medicine</th><th>Others</th></tr>
                    <tr><td><?= htmlspecialchars($student['AllergyFood'] ?? '') ?></td><td><?= htmlspecialchars($student['AllergyMedicine'] ?? '') ?></td><td><?= htmlspecialchars($student['AllergyOthers'] ?? '') ?></td></tr>
                </table>
            </div>
        </section>
        <a href="download-clinic-form.php" class="btn btn-success paper-download-btn">Download a copy</a>

        <section class="student-announcements-panel admin-full-panel clinic-visit-panel">
            <div class="dashboard-section-title">
                <span><?= studentDashboardIcon('records') ?></span>
                <h2>Clinic Visit History</h2>
            </div>

            <?php if (!empty($clinicVisits)): ?>
                <div class="appointment-card-list">
                    <?php foreach ($clinicVisits as $visit): ?>
                        <article class="appointment-card">
                            <div>
                                <h3><?= date('M d, Y g:i A', strtotime($visit['CreatedAt'])) ?></h3>
                                <p><?= htmlspecialchars($visit['Complaint']) ?></p>
                                <?php if (!empty($visit['Treatment'])): ?>
                                    <small>Treatment: <?= htmlspecialchars($visit['Treatment']) ?></small>
                                <?php endif; ?>
                            </div>
                            <strong><?= htmlspecialchars(ucfirst($visit['Status'])) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="dashboard-empty">No clinic visits recorded yet.</div>
            <?php endif; ?>
        </section>

        <section class="consent-paper-form">
            <div class="consent-paper-inner">
                <h2>CONSENT FORM</h2>
                <p>I give consent for my child to receive the following:</p>
                <div class="consent-line"><span>First aid treatment at the school clinic</span><span>☐ YES &nbsp;&nbsp;&nbsp;&nbsp; ☐ NO</span></div>
                <div class="consent-line"><span>Non-prescription medicine in case of emergency</span><span>☐ YES &nbsp;&nbsp;&nbsp;&nbsp; ☐ NO</span></div>
                <div class="consent-line"><span>Should a student require hospitalization, we encourage them to visit one of the nearby hospitals:</span><span>☐ YES &nbsp;&nbsp;&nbsp;&nbsp; ☐ NO</span></div>
                <p>I acknowledge that it is my responsibility to inform FCAT school clinic of any update in my child's medical records.</p>
                <p>Safety and well-being of the learner is the top priority. If in case of emergency, we ask the parents to:</p>
                <p>☐ give the school consent for the decision to attend the learner's need.</p>
                <p>☐ call the parents/guardians first.</p>
                <p>☐ Others: Please Specify ______________________________</p>
                <div class="consent-signature">
                    <span>Parent's/Guardian's Name: ______________________________</span>
                    <span>Signature: ______________________________</span>
                    <span>Date: ______________________________</span>
                </div>
            </div>
        </section>
        <a href="download-consent-form.php" class="btn btn-success paper-download-btn">Download a copy</a>

        <section class="consent-upload-section">
            <h2>Parent/Guardian's ID</h2>
            <form method="POST" enctype="multipart/form-data" class="consent-upload-form">
                <input type="hidden" name="action" value="upload_consent_image">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <label class="consent-upload-box">
                    <input type="file" name="consent_image" accept="image/png,image/jpeg,image/webp" required>
                    <?php if (!empty($student['ConsentImagePath'])): ?>
                        <img src="view-consent-image.php" alt="Uploaded parent or guardian ID" class="consent-upload-preview">
                        <span class="consent-upload-prompt">Change image</span>
                    <?php else: ?>
                        <span class="consent-upload-prompt">Upload image</span>
                    <?php endif; ?>
                </label>
                <div class="consent-upload-actions">
                    <p class="consent-file-name" aria-live="polite"></p>
                    <button type="submit" class="btn btn-success paper-download-btn consent-upload-submit" hidden>Upload</button>
                    <?php if (!empty($student['ConsentImagePath'])): ?>
                        <a href="view-consent-image.php" target="_blank" class="btn btn-success paper-download-btn">View</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>
    </div>
    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>
    <script src="../public/assets/js/main.js"></script>
</body>
</html>
