<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireClinicStaff();

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: students.php"); exit; }

$data = fetchStudentFullProfile($pdo, (int)$id);
if (!$data) { header("Location: students.php?error=notfound"); exit; }

['student' => $student, 'g1' => $g1, 'g2' => $g2, 'medHistory' => $medHistory] = $data;

$medMap = [];
foreach ($medHistory as $row) {
    $medMap[$row['Illness']] = $row;
}

$illnesses = clinicIllnessList();

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
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>
    <div class="patient-records-page">
        <div class="patient-records-actions">
            <div>
                <h1><?= htmlspecialchars(trim(($student['FirstName'] ?? '') . ' ' . ($student['LastName'] ?? ''))) ?></h1>
                <p class="privacy-consent-status">
                    <?php if (!empty($student['PrivacyConsentAt'])): ?>
                        <?= studentDashboardIcon('shield') ?> Data privacy consent given <?= date('M d, Y g:i A', strtotime($student['PrivacyConsentAt'])) ?>
                    <?php else: ?>
                        <?= studentDashboardIcon('warning') ?> Clinic form not yet completed with data privacy consent
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <?php if (isAdmin()): ?>
                    <a href="edit-student.php?id=<?= (int) $student['StudentID'] ?>" class="btn btn-success paper-download-btn">Edit Student</a>
                    <a href="audit-log.php?account=student:<?= (int) $student['StudentID'] ?>" class="btn btn-secondary paper-download-btn">View audit log</a>
                <?php endif; ?>
                <a href="students.php" class="btn btn-secondary paper-download-btn">Back to List</a>
            </div>
        </div>

        <section class="clinic-paper-form">
            <p class="paper-form-code">FCAT School Clinic Form 01</p>
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
        <a href="download-student-form.php?id=<?= (int) $student['StudentID'] ?>&amp;form=clinic" class="btn btn-success paper-download-btn">Download PDF</a>

        <section class="consent-paper-form">
            <div class="consent-paper-inner">
                <p class="paper-form-code">FCAT School Clinic Form 02</p>
                <h2>CONSENT FORM</h2>
                <p>I give consent for my child to receive the following:</p>
                <div class="consent-line"><span>First aid treatment at the school clinic</span><span><?= consentChoiceMarks(null) ?></span></div>
                <div class="consent-line"><span>Non-prescription medicine in case of emergency</span><span><?= consentChoiceMarks(null) ?></span></div>
                <div class="consent-line"><span>Should a student require hospitalization, we encourage them to visit one of the nearby hospitals:</span><span><?= consentChoiceMarks(null) ?></span></div>
                <p>I acknowledge that it is my responsibility to inform FCAT school clinic of any update in my child's medical records.</p>
                <p>Safety and well-being of the learner is the top priority. If in case of emergency, we ask the parents to:</p>
                <p>☐ give the school consent for the decision to attend the learner's need.</p>
                <p>☐ call the parents/guardians first.</p>
                <p>☐ Others: <?= htmlspecialchars($student['EmergencyOther'] ?? '') ?></p>
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
                        <a href="view-consent-image.php?id=<?= (int) $student['StudentID'] ?>" target="_blank" class="btn btn-secondary paper-download-btn">View Full Image</a>
                        <a href="view-consent-image.php?id=<?= (int) $student['StudentID'] ?>&amp;download=1" class="btn btn-success paper-download-btn">Download ID</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="dashboard-empty admin-consent-empty">No parent or guardian ID has been uploaded.</div>
            <?php endif; ?>
        </section>

        <section class="student-announcements-panel admin-full-panel clinic-visit-panel">
            <div class="dashboard-section-title">
                <span><?= studentDashboardIcon('medical') ?></span>
                <h2>Clinic Visit History</h2>
            </div>
            <?php if (!empty($clinicVisits)): ?>
                <div class="clinic-visit-card-list">
                    <?php foreach ($clinicVisits as $visit): ?>
                        <article class="clinic-visit-card">
                            <div>
                                <h3><?= date('M d, Y', strtotime($visit['TimeIn'] ?? $visit['CreatedAt'])) ?></h3>
                                <?= visitTimesHtml($visit) ?>
                                <p><?= htmlspecialchars($visit['Complaint']) ?></p>
                                <?php if (!empty($visit['Treatment'])): ?>
                                    <small>Treatment: <?= htmlspecialchars($visit['Treatment']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($visit['MedicineGiven'])): ?>
                                    <small>Medicine Given: <?= htmlspecialchars($visit['MedicineGiven']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($visit['MedicineDosage'])): ?>
                                    <small>Dosage: <?= htmlspecialchars($visit['MedicineDosage']) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($visit['MedicineQuantity'])): ?>
                                    <small>Quantity Given: <?= (int) $visit['MedicineQuantity'] ?></small>
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
    <script src="../../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
