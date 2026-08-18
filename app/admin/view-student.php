<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: students.php"); exit; }

$data = fetchStudentFullProfile($pdo, (int)$id);
if (!$data) { header("Location: students.php?error=notfound"); exit; }

$clinicTimezone = new DateTimeZone('Asia/Manila');
$minimumFollowUpDate = new DateTimeImmutable('today', $clinicTimezone);
$maximumFollowUpDate = $minimumFollowUpDate->modify('+30 days');
$visitErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_visit') {
    verifyCsrfToken();

    if (trim($_POST['complaint'] ?? '') === '') {
        $visitErrors[] = 'Complaint is required.';
    }

    $followUpDateValue = trim($_POST['follow_up_date'] ?? '');
    if ($followUpDateValue !== '') {
        $followUpDate = DateTimeImmutable::createFromFormat('!Y-m-d', $followUpDateValue, $clinicTimezone);

        if (!$followUpDate || $followUpDate->format('Y-m-d') !== $followUpDateValue) {
            $visitErrors[] = 'Please choose a valid follow-up date.';
        } elseif ($followUpDate < $minimumFollowUpDate) {
            $visitErrors[] = 'Follow-up date cannot be earlier than today.';
        } elseif ($followUpDate > $maximumFollowUpDate) {
            $visitErrors[] = 'Follow-up date must be within the next 30 days.';
        }
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
    <link rel="stylesheet" href="../../public/assets/css/style.css">
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
                    <tr><td><?= guardianRelationshipMarks($g1['Relationship'] ?? '') ?></td><td><?= guardianRelationshipMarks($g2['Relationship'] ?? '') ?></td></tr>
                    <tr><td>Last Name: <?= htmlspecialchars($g1['LastName'] ?? '') ?></td><td>Last Name: <?= htmlspecialchars($g2['LastName'] ?? '') ?></td></tr>
                    <tr><td>First Name: <?= htmlspecialchars($g1['FirstName'] ?? '') ?></td><td>First Name: <?= htmlspecialchars($g2['FirstName'] ?? '') ?></td></tr>
                    <tr><td>Office Phone: <?= htmlspecialchars($g1['OfficePhone'] ?? '') ?></td><td>Office Phone: <?= htmlspecialchars($g2['OfficePhone'] ?? '') ?></td></tr>
                    <tr><td>Mobile Number: <?= htmlspecialchars($g1['MobileNumber'] ?? '') ?></td><td>Mobile Number: <?= htmlspecialchars($g2['MobileNumber'] ?? '') ?></td></tr>
                    <tr><td>Email Address: <?= htmlspecialchars($g1['EmailAddress'] ?? '') ?></td><td>Email Address: <?= htmlspecialchars($g2['EmailAddress'] ?? '') ?></td></tr>
                    <tr><td>Emergency Contact: <?= htmlspecialchars(trim(($g1['EmergencyContactName'] ?? '') . ' ' . ($g1['EmergencyContactMobile'] ?? ''))) ?></td><td>Emergency Contact: <?= htmlspecialchars(trim(($g2['EmergencyContactName'] ?? '') . ' ' . ($g2['EmergencyContactMobile'] ?? ''))) ?></td></tr>
                    <tr><td>Custodial Parent/Guardian: <?= htmlspecialchars(ucwords(str_replace('guardian', 'Guardian ', $student['CustodialParent'] ?? ''))) ?></td><td>Correspondence To: <?= htmlspecialchars(ucwords(str_replace('guardian', 'Guardian ', $student['CorrespondenceTo'] ?? ''))) ?></td></tr>
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
                <div class="consent-line"><span>First aid treatment at the school clinic</span><span><?= consentChoiceMarks($student['ConsentFirstAid'] ?? null) ?></span></div>
                <div class="consent-line"><span>Non-prescription medicine in case of emergency</span><span><?= consentChoiceMarks($student['ConsentNonPrescription'] ?? null) ?></span></div>
                <div class="consent-line"><span>Should a student require hospitalization, we encourage them to visit one of the nearby hospitals:</span><span><?= consentChoiceMarks($student['ConsentHospitalization'] ?? null) ?></span></div>
                <p>I acknowledge that it is my responsibility to inform FCAT school clinic of any update in my child's medical records.</p>
                <p>Safety and well-being of the learner is the top priority. If in case of emergency, we ask the parents to:</p>
                <p><?= (int) ($student['EmergencyDecisionConsent'] ?? 0) === 1 ? '☑' : '☐' ?> give the school consent for the decision to attend the learner's need.</p>
                <p><?= (int) ($student['EmergencyCallGuardian'] ?? 0) === 1 ? '☑' : '☐' ?> call the parents/guardians first.</p>
                <p><?= trim((string) ($student['EmergencyOther'] ?? '')) !== '' ? '☑' : '☐' ?> Others: <?= htmlspecialchars($student['EmergencyOther'] ?? '') ?></p>
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
                <span><?= studentDashboardIcon('medical') ?></span>
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
                <div class="form-group">
                    <label for="medicine_given">Medicine Given</label>
                    <input type="text" id="medicine_given" name="medicine_given"
                           value="<?= htmlspecialchars($_POST['medicine_given'] ?? '') ?>"
                           placeholder="e.g. Paracetamol 500 mg" maxlength="255">
                </div>
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="completed">Completed</option>
                            <option value="follow-up">Follow-up</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="disposition">Disposition</label>
                        <input type="text" id="disposition" name="disposition" placeholder="Returned to class, sent home, etc.">
                    </div>
                    <div class="form-group">
                        <label for="follow_up_date" id="follow-up-date-label">Follow-up Date</label>
                        <div class="follow-up-date-picker"
                             data-follow-up-date-picker
                             data-min-date="<?= $minimumFollowUpDate->format('Y-m-d') ?>"
                             data-max-date="<?= $maximumFollowUpDate->format('Y-m-d') ?>">
                            <input type="date" id="follow_up_date" name="follow_up_date"
                                   min="<?= $minimumFollowUpDate->format('Y-m-d') ?>"
                                   max="<?= $maximumFollowUpDate->format('Y-m-d') ?>"
                                   value="<?= htmlspecialchars($_POST['follow_up_date'] ?? '') ?>">
                            <button type="button" class="follow-up-date-trigger"
                                    id="follow_up_date_trigger"
                                    aria-haspopup="dialog"
                                    aria-controls="follow-up-calendar"
                                    aria-expanded="false"
                                    aria-labelledby="follow-up-date-label follow-up-date-display"
                                    hidden>
                                <span class="follow-up-date-icon" aria-hidden="true"><?= studentDashboardIcon('calendar') ?></span>
                                <span id="follow-up-date-display">Select a date</span>
                                <span class="follow-up-date-chevron" aria-hidden="true">&#9662;</span>
                            </button>
                            <div class="follow-up-calendar" id="follow-up-calendar"
                                 role="dialog" aria-labelledby="follow-up-calendar-month" hidden>
                                <div class="follow-up-calendar-header">
                                    <button type="button" class="follow-up-calendar-nav" data-follow-up-previous aria-label="Previous month">&lsaquo;</button>
                                    <strong id="follow-up-calendar-month"></strong>
                                    <button type="button" class="follow-up-calendar-nav" data-follow-up-next aria-label="Next month">&rsaquo;</button>
                                </div>
                                <div class="follow-up-calendar-weekdays" aria-hidden="true">
                                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                                </div>
                                <div class="follow-up-calendar-grid" role="grid" aria-labelledby="follow-up-calendar-month"></div>
                                <button type="button" class="follow-up-date-clear" data-follow-up-clear>Clear date</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Add Visit</button>
            </form>

            <?php if (!empty($clinicVisits)): ?>
                <div class="clinic-visit-card-list">
                    <?php foreach ($clinicVisits as $visit): ?>
                        <article class="clinic-visit-card">
                            <div>
                                <h3><?= date('M d, Y g:i A', strtotime($visit['CreatedAt'])) ?></h3>
                                <p><?= htmlspecialchars($visit['Complaint']) ?></p>
                                <?php if (!empty($visit['Treatment'])): ?>
                                    <small>Treatment: <?= htmlspecialchars($visit['Treatment']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($visit['MedicineGiven'])): ?>
                                    <small>Medicine Given: <?= htmlspecialchars($visit['MedicineGiven']) ?></small>
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
