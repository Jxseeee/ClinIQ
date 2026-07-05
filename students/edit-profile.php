<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();

$sid = $_SESSION['user_id'];
$data = fetchStudentFullProfile($pdo, (int)$sid);
if (!$data) { header("Location: ../public/logout.php"); exit; }

['student' => $student, 'g1' => $g1, 'g2' => $g2, 'medMap' => $medMap] = $data;

$illnesses = [
    'Asthma', 'Chicken Pox', 'Chronic Ear Infections or Otitis Media',
    'Diabetes', 'Epilepsy', 'Fainting Spells', 'Febrile Convulsions',
    'Heart Disorder', 'Hepatitis A', 'Hepatitis B',
    'Measles', 'Meningitis', 'Mumps', 'Primary Complex',
    'Rubella', 'Scoliosis', 'Skin Problems',
    'Urinary Tract Infections', 'Whooping Cough', 'Others',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = fn($key) => trim($_POST[$key] ?? '');

    $course      = $f('course');
    $yearLevel   = $f('year_level');
    $department   = $f('department');
    $dob         = $f('date_of_birth');
    $gender      = $f('gender');
    $streetAddress = $f('street_address');
    $municipality  = $f('municipality');
    $city          = $f('city');
    $g1LastName  = $f('g1_last_name');
    $g1FirstName = $f('g1_first_name');
    $g1Mobile    = $f('g1_mobile');

    if ($course === '')      $errors[] = 'Course is required.';
    if ($yearLevel === '')   $errors[] = 'Year Level is required.';
    if ($department === '')  $errors[] = 'Department is required.';
    if ($dob === '')         $errors[] = 'Date of Birth is required.';
    if ($gender === '')      $errors[] = 'Gender is required.';
    if ($streetAddress === '') $errors[] = 'Street Address is required.';
    if ($municipality === '')  $errors[] = 'Municipality is required.';
    if ($city === '')          $errors[] = 'City is required.';
    if ($g1LastName === '' || $g1FirstName === '') {
        $errors[] = 'Guardian 1 name is required.';
    }
    if ($g1Mobile === '') $errors[] = 'Guardian 1 mobile number is required.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE Students SET
                MiddleName=?, Course=?, YearLevel=?, Department=?, PreferredName=?,
                DateOfBirth=?, Citizenship=?, Gender=?, Height=?, Weight=?,
                HomeTelephone=?, StreetAddress=?, Municipality=?, City=?, MedicationsRegular=?,
                AllergyFood=?, AllergyMedicine=?, AllergyOthers=?,
                CustodialParent=?, CorrespondenceTo=?
                WHERE StudentID=?");
            $stmt->execute([
                $f('middle_name'), $course, $yearLevel, $department, $f('preferred_name'),
                $dob ?: null, $f('citizenship'), $gender, $f('height'), $f('weight'),
                $f('home_telephone'), $streetAddress, $municipality, $city, $f('medications_regular'),
                $f('allergy_food'), $f('allergy_medicine'), $f('allergy_others'),
                $f('custodial_parent'), $f('correspondence_to'),
                $sid
            ]);

            foreach (['guardian1' => 'g1_', 'guardian2' => 'g2_'] as $type => $prefix) {
                $pdo->prepare("DELETE FROM Guardians WHERE StudentID = ? AND GuardianType = ?")
                    ->execute([$sid, $type]);

                $gLast = $f($prefix . 'last_name');
                $gFirst = $f($prefix . 'first_name');
                if ($gLast !== '' || $gFirst !== '') {
                    $stmt = $pdo->prepare("INSERT INTO Guardians
                        (StudentID, GuardianType, Relationship, LastName, FirstName, MiddleName,
                         OfficePhone, MobileNumber, EmailAddress, EmergencyContactName, EmergencyContactMobile)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                    $stmt->execute([
                        $sid, $type, $f($prefix . 'relationship'),
                        $gLast, $gFirst, $f($prefix . 'middle_name'),
                        $f($prefix . 'office_phone'), $f($prefix . 'mobile'),
                        $f($prefix . 'email'),
                        $f($prefix . 'emergency_name'), $f($prefix . 'emergency_mobile'),
                    ]);
                }
            }

            $pdo->prepare("DELETE FROM MedicalHistory WHERE StudentID = ?")->execute([$sid]);
            $checkedIllnesses = $_POST['illness'] ?? [];
            foreach ($checkedIllnesses as $illness) {
                $safeIllness = htmlspecialchars_decode($illness);
                $stmt = $pdo->prepare("INSERT INTO MedicalHistory (StudentID, Illness, DiagnosisDate, DiagnosisAge) VALUES (?,?,?,?)");
                $stmt->execute([
                    $sid,
                    $safeIllness,
                    $f('illness_date_' . md5($illness)) ?: null,
                    $f('illness_age_' . md5($illness)) ?: null,
                ]);
            }

            $pdo->commit();
            header("Location: profile.php?success=updated");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

$v = fn($key) => htmlspecialchars($student[$key] ?? '');
$gv = fn($arr, $key) => htmlspecialchars($arr[$key] ?? '');
$studentPageTitle = 'Clinic Form';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="icon" type="image/png" href="../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page">
    <?php include __DIR__ . '/../app/includes/student-dashboard-start.php'; ?>
    <div class="dashboard-content-card">
        <h1>School Clinic Form</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Profile updated successfully.</div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin:0;padding-left:18px;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

        <!-- ── SECTION 1: Student Information ── -->
        <div class="card form-section">
            <h2>Student Information</h2>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" value="<?= $v('LastName') ?>" disabled>
                </div>
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" value="<?= $v('FirstName') ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" name="middle_name" value="<?= $v('MiddleName') ?>">
                </div>
            </div>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label>Course *</label>
                    <input type="text" name="course" value="<?= $v('Course') ?>" required>
                </div>
                <div class="form-group">
                    <label>Year Level *</label>
                    <select name="year_level" required>
                        <option value="">Select</option>
                        <?php foreach (['1st Year','2nd Year','3rd Year','4th Year','5th Year'] as $yr): ?>
                            <option value="<?= $yr ?>" <?= ($student['YearLevel'] ?? '') === $yr ? 'selected' : '' ?>><?= $yr ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Department *</label>
                    <input type="text" name="department" value="<?= $v('Department') ?>" required>
                </div>
            </div>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label>Preferred Name</label>
                    <input type="text" name="preferred_name" value="<?= $v('PreferredName') ?>">
                </div>
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="date_of_birth" value="<?= $v('DateOfBirth') ?>" required>
                </div>
                <div class="form-group">
                    <label>Citizenship</label>
                    <input type="text" name="citizenship" value="<?= $v('Citizenship') ?>">
                </div>
            </div>
            <div class="form-row form-row-4">
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" required>
                        <option value="">Select</option>
                        <?php foreach (['Male','Female','Other'] as $g): ?>
                            <option value="<?= $g ?>" <?= ($student['Gender'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Height (m)</label>
                    <input type="text" name="height" value="<?= $v('Height') ?>" placeholder="e.g. 1.65" class="numeric-input">
                </div>
                <div class="form-group">
                    <label>Weight (Kg)</label>
                    <input type="text" name="weight" value="<?= $v('Weight') ?>" placeholder="e.g. 60" class="numeric-input">
                </div>
                <div class="form-group">
                    <label>Home Telephone</label>
                    <input type="text" name="home_telephone" value="<?= $v('HomeTelephone') ?>" class="phone-input">
                </div>
            </div>
            <div class="form-group">
                <label>Street Address *</label>
                <input type="text" name="street_address" value="<?= $v('StreetAddress') ?>" placeholder="House No., Street, Barangay" required>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label>Municipality *</label>
                    <input type="text" name="municipality" value="<?= $v('Municipality') ?>" class="text-only" required>
                </div>
                <div class="form-group">
                    <label>City *</label>
                    <input type="text" name="city" value="<?= $v('City') ?>" class="text-only" required>
                </div>
            </div>
        </div>

        <!-- ── SECTION 2: Parent / Guardian Information ── -->
        <div class="card form-section">
            <h2>Parent / Guardian Information</h2>
            <div class="guardian-columns">
                <!-- Guardian 1 -->
                <div class="guardian-col">
                    <h3>Guardian 1 *</h3>
                    <div class="form-group">
                        <label>Relationship</label>
                        <select name="g1_relationship">
                            <option value="">Select</option>
                            <?php foreach (['Father','Stepfather','Legal Guardian'] as $r): ?>
                                <option value="<?= $r ?>" <?= ($g1['Relationship'] ?? '') === $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="g1_last_name" value="<?= $gv($g1, 'LastName') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="g1_first_name" value="<?= $gv($g1, 'FirstName') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="g1_middle_name" value="<?= $gv($g1, 'MiddleName') ?>">
                    </div>
                    <div class="form-group">
                        <label>Office Phone</label>
                        <input type="text" name="g1_office_phone" value="<?= $gv($g1, 'OfficePhone') ?>" class="phone-input">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number *</label>
                        <input type="text" name="g1_mobile" value="<?= $gv($g1, 'MobileNumber') ?>" class="phone-input" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="g1_email" value="<?= $gv($g1, 'EmailAddress') ?>">
                    </div>
                    <p class="text-muted" style="margin-top:12px;">Emergency Contact (aside from above)</p>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="g1_emergency_name" value="<?= $gv($g1, 'EmergencyContactName') ?>">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="g1_emergency_mobile" value="<?= $gv($g1, 'EmergencyContactMobile') ?>" class="phone-input">
                    </div>
                </div>

                <!-- Guardian 2 -->
                <div class="guardian-col">
                    <h3>Guardian 2</h3>
                    <div class="form-group">
                        <label>Relationship</label>
                        <select name="g2_relationship">
                            <option value="">Select</option>
                            <?php foreach (['Mother','Stepmother','Legal Guardian'] as $r): ?>
                                <option value="<?= $r ?>" <?= ($g2['Relationship'] ?? '') === $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="g2_last_name" value="<?= $gv($g2, 'LastName') ?>">
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="g2_first_name" value="<?= $gv($g2, 'FirstName') ?>">
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="g2_middle_name" value="<?= $gv($g2, 'MiddleName') ?>">
                    </div>
                    <div class="form-group">
                        <label>Office Phone</label>
                        <input type="text" name="g2_office_phone" value="<?= $gv($g2, 'OfficePhone') ?>" class="phone-input">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="g2_mobile" value="<?= $gv($g2, 'MobileNumber') ?>" class="phone-input">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="g2_email" value="<?= $gv($g2, 'EmailAddress') ?>">
                    </div>
                    <p class="text-muted" style="margin-top:12px;">Emergency Contact (aside from above)</p>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="g2_emergency_name" value="<?= $gv($g2, 'EmergencyContactName') ?>">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="g2_emergency_mobile" value="<?= $gv($g2, 'EmergencyContactMobile') ?>" class="phone-input">
                    </div>
                </div>
            </div>

            <div class="form-row form-row-2" style="margin-top:20px;">
                <div class="form-group">
                    <label>Who is the custodial parent/guardian?</label>
                    <select name="custodial_parent">
                        <option value="">Select</option>
                        <?php foreach (['Guardian 1' => 'guardian1', 'Guardian 2' => 'guardian2', 'Both' => 'both'] as $label => $val): ?>
                            <option value="<?= $val ?>" <?= ($student['CustodialParent'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Correspondence should be sent to?</label>
                    <select name="correspondence_to">
                        <option value="">Select</option>
                        <?php foreach (['Guardian 1' => 'guardian1', 'Guardian 2' => 'guardian2', 'Both' => 'both'] as $label => $val): ?>
                            <option value="<?= $val ?>" <?= ($student['CorrespondenceTo'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- ── SECTION 3: Past Medical History ── -->
        <div class="card form-section">
            <h2>Past Medical History</h2>
            <p class="text-muted">Check any that apply and optionally provide the date and age of diagnosis.</p>
            <div class="illness-grid">
                <?php foreach ($illnesses as $ill):
                    $key = md5($ill);
                    $checked = isset($medMap[$ill]);
                    $savedDate = $medMap[$ill]['DiagnosisDate'] ?? '';
                    $savedAge  = $medMap[$ill]['DiagnosisAge'] ?? '';
                ?>
                <div class="illness-row">
                    <label class="illness-check">
                        <input type="checkbox" name="illness[]" value="<?= htmlspecialchars($ill) ?>"
                               <?= $checked ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($ill) ?></span>
                    </label>
                    <input type="text" name="illness_date_<?= $key ?>" value="<?= htmlspecialchars($savedDate) ?>"
                           placeholder="Date" class="illness-field">
                    <input type="number" name="illness_age_<?= $key ?>" value="<?= htmlspecialchars($savedAge) ?>"
                           placeholder="Age" class="illness-field illness-age" min="0">
                </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group" style="margin-top:20px;">
                <label>Medication/s taken on a regular basis</label>
                <textarea name="medications_regular" rows="2"><?= $v('MedicationsRegular') ?></textarea>
            </div>
        </div>

        <!-- ── SECTION 4: Allergies ── -->
        <div class="card form-section">
            <h2>Allergies</h2>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label>Food</label>
                    <input type="text" name="allergy_food" value="<?= $v('AllergyFood') ?>">
                </div>
                <div class="form-group">
                    <label>Medicine</label>
                    <input type="text" name="allergy_medicine" value="<?= $v('AllergyMedicine') ?>">
                </div>
                <div class="form-group">
                    <label>Others</label>
                    <input type="text" name="allergy_others" value="<?= $v('AllergyOthers') ?>">
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top:10px; margin-bottom:40px;">
            <button type="submit" class="btn btn-success">Save Profile</button>
            <a href="profile.php" class="btn btn-secondary">Cancel</a>
        </div>

        </form>
    </div>
    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>
    <script src="../public/assets/js/main.js"></script>
</body>
</html>
