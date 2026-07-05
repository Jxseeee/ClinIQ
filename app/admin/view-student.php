<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: ../../public/index.php"); exit; }

$data = fetchStudentFullProfile($pdo, (int)$id);
if (!$data) { header("Location: ../../public/index.php?error=notfound"); exit; }

['student' => $student, 'g1' => $g1, 'g2' => $g2, 'medHistory' => $medHistory] = $data;

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

$adminPageTitle = 'Student Record';
$adminContentClass = 'patient-records-content';
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
<body class="student-dashboard-page admin-dashboard-page">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>
    <div class="patient-records-page">
        <div class="patient-records-actions">
            <h1>FCAT School Clinic Form 01</h1>
            <div>
                <a href="edit-student.php?id=<?= (int) $student['StudentID'] ?>" class="btn btn-success paper-download-btn">Edit Student</a>
                <a href="../../public/index.php" class="btn btn-secondary paper-download-btn">Back to List</a>
            </div>
        </div>

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

        <section class="consent-upload-section">
            <h2>Parent/Guardian's ID</h2>
            <?php if (!empty($student['ConsentImagePath'])): ?>
                <div class="consent-upload-form admin-consent-viewer">
                    <a href="view-consent-image.php?id=<?= (int) $student['StudentID'] ?>" target="_blank" class="consent-upload-box admin-consent-image-link">
                        <img src="view-consent-image.php?id=<?= (int) $student['StudentID'] ?>" alt="Uploaded parent or guardian ID" class="consent-upload-preview">
                        <span class="consent-upload-prompt">View uploaded ID</span>
                    </a>
                    <div class="consent-upload-actions">
                        <a href="view-consent-image.php?id=<?= (int) $student['StudentID'] ?>" target="_blank" class="btn btn-success paper-download-btn">View Full Image</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="dashboard-empty admin-consent-empty">No parent or guardian ID has been uploaded.</div>
            <?php endif; ?>
        </section>
    </div>
    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js"></script>
</body>
</html>
