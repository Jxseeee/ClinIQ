<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: students.php"); exit; }

$data = fetchStudentFullProfile($pdo, (int)$id);
if (!$data) { header("Location: students.php?error=notfound"); exit; }

$visitErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_visit') {
    verifyCsrfToken();

    if (trim($_POST['complaint'] ?? '') === '') {
        $visitErrors[] = 'Complaint is required.';
    }

    if (empty($visitErrors)) {
        createClinicVisit($pdo, (int) $id, (int) $_SESSION['user_id'], $_POST);
        header('Location: view-student.php?id=' . urlencode((string) $id) . '&visit=added');
        exit;
    }
}

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
$clinicVisits = fetchClinicVisits($pdo, (int) $student['StudentID']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student</title>
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.min.css">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>
    <div class="patient-records-page">
        <div class="patient-records-actions">
            <h1>FCAT School Clinic Form 01</h1>
            <div>
                <a href="edit-student.php?id=<?= (int) $student['StudentID'] ?>" class="btn btn-success paper-download-btn">Edit Student</a>
                <a href="students.php" class="btn btn-secondary paper-download-btn">Back to List</a>
            </div>
        </div>

        <?php if (($_GET['visit'] ?? '') === 'added'): ?>
            <div class="alert alert-success">Clinic visit recorded successfully.</div>
        <?php endif; ?>

        <?php if (!empty($visitErrors)): ?>
            <div class="alert alert-danger">
                <ul style="margin:0; padding-left:18px;">
                    <?php foreach ($visitErrors as $error): ?>
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
                <h2>Past History <span class="paper-section-note">(Please check if the child has a past history of the following diseases, please provide the date and age)</span></h2>
                <table class="paper-table paper-history-table paper-history-admin-table">
                    <tr>
                        <th>Illness</th>
                        <th>Date</th>
                        <th>Age</th>
                        <th>Illness</th>
                        <th>Date</th>
                        <th>Age</th>
                    </tr>
                    <?php foreach (array_chunk($illnesses, 2) as $pair): ?>
                        <tr>
                            <?php foreach ($pair as $illness): ?>
                                <td><?= isset($medMap[$illness]) ? '☑' : '☐' ?> <?= htmlspecialchars($illness) ?></td>
                                <td><?= htmlspecialchars($medMap[$illness]['DiagnosisDate'] ?? '') ?></td>
                                <td><?= htmlspecialchars($medMap[$illness]['DiagnosisAge'] ?? '') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3">Medication/s taken on regular basis:</td>
                        <td colspan="3"><?= htmlspecialchars($student['MedicationsRegular'] ?? '') ?></td>
                    </tr>
                </table>
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

        <section class="student-announcements-panel admin-full-panel clinic-visit-panel">
            <div class="dashboard-section-title">
                <span><?= studentDashboardIcon('records') ?></span>
                <h2>Clinic Visits</h2>
            </div>

            <form method="POST" class="clinic-visit-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <input type="hidden" name="action" value="add_visit">
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label for="complaint">Complaint *</label>
                        <textarea id="complaint" name="complaint" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="vitals">Vitals</label>
                        <textarea id="vitals" name="vitals" rows="3" placeholder="BP, temperature, pulse, etc."></textarea>
                    </div>
                </div>
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label for="assessment">Assessment</label>
                        <textarea id="assessment" name="assessment" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="treatment">Treatment</label>
                        <textarea id="treatment" name="treatment" rows="3"></textarea>
                    </div>
                </div>
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="completed">Completed</option>
                            <option value="open">Open</option>
                            <option value="follow-up">Follow-up</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="disposition">Disposition</label>
                        <input type="text" id="disposition" name="disposition" placeholder="Returned to class, sent home, etc.">
                    </div>
                    <div class="form-group">
                        <label for="follow_up_date">Follow-up Date</label>
                        <input type="date" id="follow_up_date" name="follow_up_date">
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Add Visit</button>
            </form>

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
                <div class="dashboard-empty">No clinic visits recorded for this student.</div>
            <?php endif; ?>
        </section>
    </div>
    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js"></script>
</body>
</html>
