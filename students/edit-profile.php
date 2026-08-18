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
$wizardStartStep = 1;
$clinicTimezone = new DateTimeZone('Asia/Manila');
$maximumBirthDate = new DateTimeImmutable('today', $clinicTimezone);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
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

    $stepOneErrors = [];
    $stepTwoErrors = [];

    if ($course === '')      $stepOneErrors[] = 'Course is required.';
    if ($yearLevel === '')   $stepOneErrors[] = 'Year Level is required.';
    if ($department === '')  $stepOneErrors[] = 'Department is required.';
    if ($dob === '') {
        $stepOneErrors[] = 'Date of Birth is required.';
    } else {
        $birthDate = DateTimeImmutable::createFromFormat('!Y-m-d', $dob, $clinicTimezone);
        if (!$birthDate || $birthDate->format('Y-m-d') !== $dob) {
            $stepOneErrors[] = 'Please choose a valid Date of Birth.';
        } elseif ($birthDate > $maximumBirthDate) {
            $stepOneErrors[] = 'Date of Birth cannot be in the future.';
        }
    }
    if ($gender === '')      $stepOneErrors[] = 'Gender is required.';
    if ($streetAddress === '') $stepOneErrors[] = 'Street Address is required.';
    if ($municipality === '')  $stepOneErrors[] = 'Municipality is required.';
    if ($city === '')          $stepOneErrors[] = 'City is required.';
    if ($g1LastName === '' || $g1FirstName === '') {
        $stepTwoErrors[] = 'Guardian 1 name is required.';
    }
    if ($g1Mobile === '') $stepTwoErrors[] = 'Guardian 1 mobile number is required.';

    $errors = array_merge($stepOneErrors, $stepTwoErrors);
    $wizardStartStep = !empty($stepOneErrors) ? 1 : (!empty($stepTwoErrors) ? 2 : 1);

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
            $wizardStartStep = max(1, min(4, (int) ($_POST['wizard_step'] ?? 1)));
        }
    }
}

$postFieldMap = [
    'MiddleName' => 'middle_name', 'Course' => 'course', 'YearLevel' => 'year_level',
    'Department' => 'department', 'PreferredName' => 'preferred_name', 'DateOfBirth' => 'date_of_birth',
    'Citizenship' => 'citizenship', 'Gender' => 'gender', 'Height' => 'height', 'Weight' => 'weight',
    'HomeTelephone' => 'home_telephone', 'StreetAddress' => 'street_address',
    'Municipality' => 'municipality', 'City' => 'city', 'MedicationsRegular' => 'medications_regular',
    'AllergyFood' => 'allergy_food', 'AllergyMedicine' => 'allergy_medicine', 'AllergyOthers' => 'allergy_others',
    'CustodialParent' => 'custodial_parent', 'CorrespondenceTo' => 'correspondence_to',
];
$rawStudentValue = static function (string $key) use ($student, $postFieldMap): string {
    $postKey = $postFieldMap[$key] ?? '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $postKey !== '' && array_key_exists($postKey, $_POST)) {
        return trim((string) $_POST[$postKey]);
    }
    return (string) ($student[$key] ?? '');
};
$v = fn($key) => htmlspecialchars($rawStudentValue($key));
$gv = fn($arr, $key) => htmlspecialchars($arr[$key] ?? '');
$wizardStepMeta = [
    1 => ['Student Information', 'Provide accurate personal and academic information.'],
    2 => ['Parent / Guardian Information', 'Add guardian and emergency contact details.'],
    3 => ['Past History', 'Record medical history, medicines, and allergies.'],
    4 => ['Consent Form', 'Review the consent document and download a copy.'],
];
$studentPageTitle = 'Clinic Form';
$studentContentClass = 'clinic-wizard-content';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="icon" type="image/png" href="../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../app/includes/student-dashboard-start.php'; ?>
    <div class="clinic-wizard-page">
        <a href="profile.php" class="clinic-wizard-back">&larr; My Profile</a>

        <ol class="clinic-wizard-progress" aria-label="Clinic form progress">
            <?php foreach ($wizardStepMeta as $stepNumber => [$stepTitle]): ?>
                <li class="<?= $stepNumber < $wizardStartStep ? 'is-complete' : ($stepNumber === $wizardStartStep ? 'is-active' : '') ?>">
                    <span class="clinic-wizard-node" data-wizard-indicator="<?= $stepNumber ?>"
                          aria-label="Step <?= $stepNumber ?>: <?= htmlspecialchars($stepTitle) ?>">
                        <?= $stepNumber ?>
                    </span>
                </li>
            <?php endforeach; ?>
        </ol>

        <header class="clinic-wizard-masthead">
            <img src="../public/assets/images/favicon.png" alt="FCAT Clinic logo" class="clinic-wizard-logo">
            <div>
                <h1 class="clinic-wizard-title">FCAT School Clinic Form 01</h1>
                <p id="clinic-wizard-step-title"><?= htmlspecialchars($wizardStepMeta[$wizardStartStep][0]) ?></p>
                <small id="clinic-wizard-step-description"><?= htmlspecialchars($wizardStepMeta[$wizardStartStep][1]) ?></small>
            </div>
            <span class="clinic-wizard-visual" aria-hidden="true">
                <img src="../public/assets/images/Vector%201.png" alt="" class="clinic-wizard-vector">
                <img src="../public/assets/images/asset1.png" alt="" class="clinic-wizard-visual-image">
            </span>
        </header>

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

                <form method="POST" action="" class="clinic-wizard-form"
                            data-clinic-wizard data-start-step="<?= $wizardStartStep ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <input type="hidden" name="wizard_step" value="<?= $wizardStartStep ?>" data-wizard-step-input>

        <!-- ── SECTION 1: Student Information ── -->
                <section class="card form-section clinic-wizard-step" data-wizard-step="1">
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
                    <input type="text" name="middle_name" value="<?= $v('MiddleName') ?>" placeholder="e.g. Santos">
                </div>
            </div>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label>Course *</label>
                    <input type="text" name="course" value="<?= $v('Course') ?>" placeholder="e.g. Bachelor of Science in Information Technology" required>
                </div>
                <div class="form-group">
                    <label>Year Level *</label>
                    <select name="year_level" required>
                        <option value="">Select</option>
                        <?php foreach (['1st Year','2nd Year','3rd Year','4th Year'] as $yr): ?>
                            <option value="<?= $yr ?>" <?= $rawStudentValue('YearLevel') === $yr ? 'selected' : '' ?>><?= $yr ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Department *</label>
                    <input type="text" name="department" value="<?= $v('Department') ?>" placeholder="e.g. College of Computing" required>
                </div>
            </div>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label>Preferred Name</label>
                    <input type="text" name="preferred_name" value="<?= $v('PreferredName') ?>" placeholder="e.g. Juan">
                </div>
                <div class="form-group">
                    <label for="date_of_birth" id="birth-date-label">Date of Birth *</label>
                    <div class="birth-date-picker" data-birth-date-picker
                         data-min-date="1900-01-01"
                         data-max-date="<?= $maximumBirthDate->format('Y-m-d') ?>">
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?= $v('DateOfBirth') ?>"
                               min="1900-01-01" max="<?= $maximumBirthDate->format('Y-m-d') ?>" required>
                        <button type="button" class="birth-date-trigger" id="birth_date_trigger"
                                aria-haspopup="dialog" aria-controls="birth-date-calendar"
                                aria-expanded="false" aria-labelledby="birth-date-label birth-date-display" hidden>
                            <span class="birth-date-icon" aria-hidden="true"><?= studentDashboardIcon('calendar') ?></span>
                            <span id="birth-date-display">Select birth date</span>
                            <span class="birth-date-chevron" aria-hidden="true">&#9662;</span>
                        </button>
                        <div class="birth-date-calendar" id="birth-date-calendar"
                             role="dialog" aria-label="Choose Date of Birth" hidden>
                            <div class="birth-date-calendar-header">
                                <label>
                                    <span>Month</span>
                                    <select data-birth-month aria-label="Birth month"></select>
                                </label>
                                <label>
                                    <span>Year</span>
                                    <select data-birth-year aria-label="Birth year"></select>
                                </label>
                            </div>
                            <div class="birth-date-calendar-weekdays" aria-hidden="true">
                                <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                            </div>
                            <div class="birth-date-calendar-grid" role="grid" aria-label="Birth date calendar days"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Citizenship</label>
                    <input type="text" name="citizenship" value="<?= $v('Citizenship') ?>" placeholder="e.g. Filipino">
                </div>
            </div>
            <div class="form-row form-row-4">
                <div class="form-group">
                    <label>Gender *</label>
                    <select name="gender" required>
                        <option value="">Select</option>
                        <?php foreach (['Male','Female','Other'] as $g): ?>
                            <option value="<?= $g ?>" <?= $rawStudentValue('Gender') === $g ? 'selected' : '' ?>><?= $g ?></option>
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
                    <input type="text" name="home_telephone" value="<?= $v('HomeTelephone') ?>" placeholder="0000-000" class="phone-input">
                </div>
            </div>
            <div class="form-group">
                <label>Street Address *</label>
                <input type="text" name="street_address" value="<?= $v('StreetAddress') ?>" placeholder="House No., Street, Barangay" required>
            </div>
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label>Municipality *</label>
                    <input type="text" name="municipality" value="<?= $v('Municipality') ?>" placeholder="e.g. Bacoor" class="text-only" required>
                </div>
                <div class="form-group">
                    <label>City *</label>
                    <input type="text" name="city" value="<?= $v('City') ?>" placeholder="e.g. Cavite" class="text-only" required>
                </div>
            </div>
            <div class="clinic-wizard-actions">
                <span></span>
                <button type="button" class="btn btn-success" data-wizard-next>Next</button>
            </div>
        </section>

        <!-- ── SECTION 2: Parent / Guardian Information ── -->
        <section class="card form-section clinic-wizard-step" data-wizard-step="2" hidden>
            <h2>Parent / Guardian Information</h2>
            <div class="guardian-columns">
                <!-- Guardian 1 -->
                <div class="guardian-col">
                    <h3>Guardian 1 *</h3>
                    <div class="form-group">
                        <label>Relationship</label>
                        <select name="g1_relationship">
                            <option value="">Select role</option>
                            <?php foreach (['Mother','Father','Guardian'] as $r): ?>
                                <?php $g1RelationshipSelected = normalizeGuardianRelationship($g1['Relationship'] ?? '') === $r; ?>
                                <option value="<?= $r ?>" <?= $g1RelationshipSelected ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="g1_last_name" value="<?= $gv($g1, 'LastName') ?>" placeholder="e.g. Dela Cruz" required>
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="g1_first_name" value="<?= $gv($g1, 'FirstName') ?>" placeholder="e.g. Maria" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="g1_middle_name" value="<?= $gv($g1, 'MiddleName') ?>" placeholder="e.g. S.">
                    </div>
                    <div class="form-group">
                        <label>Office Phone</label>
                        <input type="text" name="g1_office_phone" value="<?= $gv($g1, 'OfficePhone') ?>" placeholder="09XXXXXXXXX" class="phone-input">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number *</label>
                        <input type="text" name="g1_mobile" value="<?= $gv($g1, 'MobileNumber') ?>" placeholder="09XXXXXXXXX" class="phone-input" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="g1_email" value="<?= $gv($g1, 'EmailAddress') ?>" placeholder="someone@gmail.com">
                    </div>
                    <p class="text-muted" style="margin-top:12px;">Emergency Contact (aside from above)</p>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="g1_emergency_name" value="<?= $gv($g1, 'EmergencyContactName') ?>" placeholder="e.g. Sahur, TungTung S.">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="g1_emergency_mobile" value="<?= $gv($g1, 'EmergencyContactMobile') ?>" placeholder="09XXXXXXXXX" class="phone-input">
                    </div>
                </div>

                <!-- Guardian 2 -->
                <div class="guardian-col">
                    <h3>Guardian 2</h3>
                    <div class="form-group">
                        <label>Relationship</label>
                        <select name="g2_relationship">
                            <option value="">Select role</option>
                            <?php foreach (['Mother','Father','Guardian'] as $r): ?>
                                <?php $g2RelationshipSelected = normalizeGuardianRelationship($g2['Relationship'] ?? '') === $r; ?>
                                <option value="<?= $r ?>" <?= $g2RelationshipSelected ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="g2_last_name" value="<?= $gv($g2, 'LastName') ?>" placeholder="e.g. Dela Cruz">
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="g2_first_name" value="<?= $gv($g2, 'FirstName') ?>" placeholder="e.g. Maria">
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="g2_middle_name" value="<?= $gv($g2, 'MiddleName') ?>" placeholder="e.g. S.">
                    </div>
                    <div class="form-group">
                        <label>Office Phone</label>
                        <input type="text" name="g2_office_phone" value="<?= $gv($g2, 'OfficePhone') ?>" placeholder="09XXXXXXXXX" class="phone-input">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="g2_mobile" value="<?= $gv($g2, 'MobileNumber') ?>" placeholder="09XXXXXXXXX" class="phone-input">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="g2_email" value="<?= $gv($g2, 'EmailAddress') ?>" placeholder="someone@gmail.com">
                    </div>
                    <p class="text-muted" style="margin-top:12px;">Emergency Contact (aside from above)</p>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="g2_emergency_name" value="<?= $gv($g2, 'EmergencyContactName') ?>" placeholder="e.g. Sahur, TungTung S.">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="g2_emergency_mobile" value="<?= $gv($g2, 'EmergencyContactMobile') ?>" placeholder="09XXXXXXXXX" class="phone-input">
                    </div>
                </div>
            </div>

            <div class="form-row form-row-2" style="margin-top:20px;">
                <div class="form-group">
                    <label>Who is the custodial parent/guardian?</label>
                    <select name="custodial_parent">
                        <option value="">Select</option>
                        <?php foreach (['Guardian 1' => 'guardian1', 'Guardian 2' => 'guardian2', 'Both' => 'both'] as $label => $val): ?>
                            <option value="<?= $val ?>" <?= $rawStudentValue('CustodialParent') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Correspondence should be sent to?</label>
                    <select name="correspondence_to">
                        <option value="">Select</option>
                        <?php foreach (['Guardian 1' => 'guardian1', 'Guardian 2' => 'guardian2', 'Both' => 'both'] as $label => $val): ?>
                            <option value="<?= $val ?>" <?= $rawStudentValue('CorrespondenceTo') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="clinic-wizard-actions">
                <button type="button" class="btn btn-secondary" data-wizard-back>Back</button>
                <button type="button" class="btn btn-success" data-wizard-next>Next</button>
            </div>
        </section>

        <!-- ── SECTION 3: Past Medical History ── -->
        <section class="card form-section clinic-wizard-step" data-wizard-step="3" hidden>
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
                              placeholder="mm/dd/yyyy" class="illness-field">
                          <input type="text" name="illness_age_<?= $key ?>" value="<?= htmlspecialchars($savedAge) ?>"
                              placeholder="Age" class="illness-field illness-age integer-input"
                              inputmode="numeric" pattern="[0-9]{1,3}" maxlength="3">
                </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group" style="margin-top:20px;">
                <label>Medication/s taken on a regular basis</label>
                <textarea name="medications_regular" rows="2"><?= $v('MedicationsRegular') ?></textarea>
            </div>
        </section>

        <!-- ── SECTION 4: Allergies ── -->
        <section class="card form-section clinic-wizard-step clinic-wizard-allergies" data-wizard-step="3" hidden>
            <h2>Allergies</h2>
            <div class="form-row form-row-3">
                <div class="form-group">
                    <label>Food</label>
                    <input type="text" name="allergy_food" value="<?= $v('AllergyFood') ?>" placeholder="Please specify">
                </div>
                <div class="form-group">
                    <label>Medicine</label>
                    <input type="text" name="allergy_medicine" value="<?= $v('AllergyMedicine') ?>" placeholder="Please specify">
                </div>
                <div class="form-group">
                    <label>Others</label>
                    <input type="text" name="allergy_others" value="<?= $v('AllergyOthers') ?>" placeholder="Please specify">
                </div>
            </div>
            <div class="clinic-wizard-actions">
                <button type="button" class="btn btn-secondary" data-wizard-back>Back</button>
                <button type="button" class="btn btn-success" data-wizard-next>Next</button>
            </div>
        </section>

        <section class="card form-section clinic-wizard-step clinic-wizard-consent" data-wizard-step="4" hidden>
            <h2>Consent Form</h2>
            <div class="clinic-consent-grid clinic-consent-preview" aria-label="Consent form preview">
                <div class="clinic-consent-column">
                    <p>I give consent for my child to receive the following:</p>
                    <div class="clinic-consent-display-row"><span>First aid treatment at the school clinic</span><strong>☐ YES &nbsp;&nbsp;&nbsp;&nbsp; ☐ NO</strong></div>
                    <div class="clinic-consent-display-row"><span>Non-prescription medicine in case of emergency</span><strong>☐ YES &nbsp;&nbsp;&nbsp;&nbsp; ☐ NO</strong></div>
                    <div class="clinic-consent-display-row"><span>Hospitalization when emergency care is required</span><strong>☐ YES &nbsp;&nbsp;&nbsp;&nbsp; ☐ NO</strong></div>
                </div>

                <div class="clinic-consent-column">
                    <p>I acknowledge that it is my responsibility to inform the FCAT clinic of updates to my medical records.</p>
                    <p>In an emergency, I ask the clinic to:</p>
                    <p class="clinic-consent-display-check">☐ Make the immediate care decision needed for the learner.</p>
                    <p class="clinic-consent-display-check">☐ Call the parent or guardian first.</p>
                    <p class="clinic-consent-display-check">☐ Other: ______________________________</p>
                </div>
            </div>
            <div class="clinic-wizard-actions">
                <button type="button" class="btn btn-secondary" data-wizard-back>Back</button>
                <div class="clinic-wizard-final-actions">
                    <a href="download-consent-form.php" class="btn btn-secondary clinic-consent-download"><?= studentDashboardIcon('file') ?> Download consent form</a>
                    <button type="submit" class="btn btn-success">Save clinic form</button>
                </div>
            </div>
        </section>

        </form>
    </div>
    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>
    <script src="../public/assets/js/main.js"></script>
</body>
</html>
