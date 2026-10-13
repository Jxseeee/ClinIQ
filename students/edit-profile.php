<?php
require __DIR__ . '/../app/config/auth.php';
require_once __DIR__ . '/../app/includes/helpers.php';
requireStudent();

$sid = $_SESSION['user_id'];
$data = fetchStudentFullProfile($pdo, (int)$sid);
if (!$data) { header("Location: ../public/logout.php"); exit; }

['student' => $student, 'g1' => $g1, 'g2' => $g2, 'medMap' => $medMap] = $data;

$illnesses = clinicIllnessList();

$errors = [];
$wizardStartStep = 1;
// The clinic time zone set from APP_TIMEZONE in app/config/database.php.
$clinicTimezone = new DateTimeZone(date_default_timezone_get());
// Date of Birth: from 150 years ago up to today, never in the future.
$maximumBirthDate = new DateTimeImmutable('today', $clinicTimezone);
$minimumBirthDate = $maximumBirthDate->modify('-150 years');
$minimumDiagnosisDate = new DateTimeImmutable('1900-01-01', $clinicTimezone);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    // Run any schema fix before the save transaction; ALTER TABLE would implicitly commit it.
    ensurePrivacyConsentColumn();
    $f = static fn(string $key): string => is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';

    $course      = $f('course');
    $yearLevel   = $f('year_level');
    // Department is never taken from the form: it is assigned from the chosen course.
    $department   = clinicCourseDepartments()[$course] ?? '';
    $_POST['department'] = $department;
    $dob         = $f('date_of_birth');
    $gender      = $f('gender');
    $streetAddress = $f('street_address');
    $municipality  = $f('municipality');
    $city          = $f('city');
    $g1LastName  = $f('g1_last_name');
    $g1FirstName = $f('g1_first_name');
    $mobileErrors = [];
    foreach (['g1_mobile' => 'Guardian 1 mobile number', 'g1_emergency_mobile' => 'Guardian 1 emergency contact mobile',
              'g2_mobile' => 'Guardian 2 mobile number', 'g2_emergency_mobile' => 'Guardian 2 emergency contact mobile'] as $mobileKey => $mobileLabel) {
        $_POST[$mobileKey] = validateMobileNumber((string) ($_POST[$mobileKey] ?? ''), $mobileLabel, $mobileErrors, $mobileKey === 'g1_mobile');
    }
    $g1Mobile    = $f('g1_mobile');
    $checkedIllnesses = array_values(array_filter(
        (array) ($_POST['illness'] ?? []),
        static fn($illness): bool => is_string($illness)
    ));

    $stepOneErrors = [];
    $stepTwoErrors = [];
    $stepThreeErrors = [];

    if ($course === '')      $stepOneErrors[] = 'Course is required.';
    elseif (!in_array($course, clinicCourseOptions(), true)) $stepOneErrors[] = 'Please choose a course from the list.';
    if ($yearLevel === '')   $stepOneErrors[] = 'Year Level is required.';
    if ($dob === '') {
        $stepOneErrors[] = 'Date of Birth is required.';
    } else {
        $birthDate = DateTimeImmutable::createFromFormat('!Y-m-d', $dob, $clinicTimezone);
        if (!$birthDate || $birthDate->format('Y-m-d') !== $dob) {
            $stepOneErrors[] = 'Please choose a valid Date of Birth.';
        } elseif ($birthDate > $maximumBirthDate) {
            $stepOneErrors[] = 'Date of Birth cannot be in the future.';
        } elseif ($birthDate < $minimumBirthDate) {
            $stepOneErrors[] = 'Date of Birth cannot be more than 150 years ago.';
        }
    }
    if ($gender === '')      $stepOneErrors[] = 'Gender is required.';
    if ($streetAddress === '') $stepOneErrors[] = 'Street Address is required.';
    if ($municipality === '')  $stepOneErrors[] = 'Municipality is required.';
    if ($city === '')          $stepOneErrors[] = 'City is required.';
    if ($g1LastName === '' || $g1FirstName === '') {
        $stepTwoErrors[] = 'Guardian 1 name is required.';
    }
    $stepTwoErrors = array_merge($stepTwoErrors, $mobileErrors);

    // Server-side limits that match the database columns (browsers can skip maxlength).
    validateMaxLengths($_POST, [
        'middle_name' => ['Middle name', 50], 'course' => ['Course', 100], 'year_level' => ['Year level', 20],
        'department' => ['Department', 100], 'preferred_name' => ['Preferred name', 50], 'citizenship' => ['Citizenship', 50],
        'gender' => ['Gender', 20], 'height' => ['Height', 10], 'weight' => ['Weight', 10], 'home_telephone' => ['Home telephone', 20],
        'street_address' => ['Street address', 255], 'municipality' => ['Municipality', 100], 'city' => ['City', 100],
    ], $stepOneErrors);
    foreach (['g1_' => 'Guardian 1', 'g2_' => 'Guardian 2'] as $prefix => $guardianLabel) {
        validateMaxLengths($_POST, [
            $prefix . 'relationship' => [$guardianLabel . ' relationship', 50],
            $prefix . 'last_name' => [$guardianLabel . ' last name', 50],
            $prefix . 'first_name' => [$guardianLabel . ' first name', 50],
            $prefix . 'middle_name' => [$guardianLabel . ' middle name', 50],
            $prefix . 'office_phone' => [$guardianLabel . ' office phone', 20],
            $prefix . 'email' => [$guardianLabel . ' email address', 100],
            $prefix . 'emergency_name' => [$guardianLabel . ' emergency contact name', 100],
        ], $stepTwoErrors);
    }
    validateMaxLengths($_POST, [
        'custodial_parent' => ['Custodial parent', 20], 'correspondence_to' => ['Correspondence', 20],
    ], $stepTwoErrors);
    validateMaxLengths($_POST, [
        'medications_regular' => ['Regular medications', 2000], 'allergy_food' => ['Food allergies', 255],
        'allergy_medicine' => ['Medicine allergies', 255], 'allergy_others' => ['Other allergies', 255],
    ], $stepThreeErrors);

    foreach ($checkedIllnesses as $illness) {
        if (!in_array($illness, $illnesses, true)) {
            $stepThreeErrors[] = 'Choose a valid medical-history item.';
            continue;
        }

        $diagnosisAgeValue = $f('illness_age_' . md5($illness));
        if ($diagnosisAgeValue !== '' && (!ctype_digit($diagnosisAgeValue) || (int) $diagnosisAgeValue > 150)) {
            $stepThreeErrors[] = 'Age of diagnosis for ' . $illness . ' must be a whole number from 0 to 150.';
        }

        $diagnosisDateValue = $f('illness_date_' . md5($illness));
        if ($diagnosisDateValue === '') {
            continue;
        }

        $diagnosisDate = DateTimeImmutable::createFromFormat('!Y-m-d', $diagnosisDateValue, $clinicTimezone);
        if (!$diagnosisDate || $diagnosisDate->format('Y-m-d') !== $diagnosisDateValue) {
            $stepThreeErrors[] = 'Choose a valid diagnosis date for ' . $illness . '.';
        } elseif ($diagnosisDate < $minimumDiagnosisDate || $diagnosisDate > $maximumBirthDate) {
            $stepThreeErrors[] = 'Diagnosis date for ' . $illness . ' must be between January 1, 1900 and today.';
        }
    }

    $stepFourErrors = [];
    if (($_POST['privacy_consent'] ?? '') !== '1') {
        $stepFourErrors[] = 'Please agree to the Data Privacy Act consent before saving your clinic form.';
    }

    $errors = array_merge($stepOneErrors, $stepTwoErrors, $stepThreeErrors, $stepFourErrors);
    $wizardStartStep = !empty($stepOneErrors)
        ? 1
        : (!empty($stepTwoErrors) ? 2 : (!empty($stepThreeErrors) ? 3 : (!empty($stepFourErrors) ? 4 : 1)));

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

            if (ensurePrivacyConsentColumn()) {
                $pdo->prepare("UPDATE Students SET PrivacyConsentAt = NOW() WHERE StudentID = ?")->execute([$sid]);
            }

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
            foreach ($checkedIllnesses as $illness) {
                $stmt = $pdo->prepare("INSERT INTO MedicalHistory (StudentID, Illness, DiagnosisDate, DiagnosisAge) VALUES (?,?,?,?)");
                $stmt->execute([
                    $sid,
                    $illness,
                    $f('illness_date_' . md5($illness)) ?: null,
                    $f('illness_age_' . md5($illness)) ?: null,
                ]);
            }

            $pdo->commit();

            // Section-level summary of what changed, for the audit log (values are not recorded).
            $differs = static fn(?array $saved, string $column, string $postKey): bool
                => trim((string) ($saved[$column] ?? '')) !== $f($postKey);
            $studentInfoFields = [
                'MiddleName' => 'middle_name', 'Course' => 'course', 'YearLevel' => 'year_level', 'Department' => 'department',
                'PreferredName' => 'preferred_name', 'DateOfBirth' => 'date_of_birth', 'Citizenship' => 'citizenship', 'Gender' => 'gender',
                'Height' => 'height', 'Weight' => 'weight', 'HomeTelephone' => 'home_telephone', 'StreetAddress' => 'street_address',
                'Municipality' => 'municipality', 'City' => 'city',
            ];
            $guardianFields = [
                'Relationship' => 'relationship', 'LastName' => 'last_name', 'FirstName' => 'first_name', 'MiddleName' => 'middle_name',
                'OfficePhone' => 'office_phone', 'MobileNumber' => 'mobile', 'EmailAddress' => 'email',
                'EmergencyContactName' => 'emergency_name', 'EmergencyContactMobile' => 'emergency_mobile',
            ];
            $medicalFields = [
                'MedicationsRegular' => 'medications_regular', 'AllergyFood' => 'allergy_food',
                'AllergyMedicine' => 'allergy_medicine', 'AllergyOthers' => 'allergy_others',
            ];
            $anyDiffers = static function (?array $saved, array $fields, string $prefix = '') use ($differs): bool {
                foreach ($fields as $column => $postKey) {
                    if ($differs($saved, $column, $prefix . $postKey)) {
                        return true;
                    }
                }
                return false;
            };
            $savedIllnesses = array_keys($medMap);
            $newIllnesses = $checkedIllnesses;
            sort($savedIllnesses);
            sort($newIllnesses);

            $changedSections = [];
            if ($anyDiffers($student, $studentInfoFields)) {
                $changedSections[] = 'student information';
            }
            if ($anyDiffers($g1, $guardianFields, 'g1_') || $anyDiffers($g2, $guardianFields, 'g2_')
                || $anyDiffers($student, ['CustodialParent' => 'custodial_parent', 'CorrespondenceTo' => 'correspondence_to'])) {
                $changedSections[] = 'parent/guardian information';
            }
            if ($savedIllnesses !== $newIllnesses || $anyDiffers($student, $medicalFields)) {
                $changedSections[] = 'medical history and allergies';
            }
            $firstCompletion = empty($student['PrivacyConsentAt']);
            auditLog(
                $pdo,
                'clinic_form',
                $firstCompletion ? 'form.clinic_completed' : 'form.clinic_updated',
                ($firstCompletion
                    ? 'Completed the required clinic form (Form 01) and agreed to the Data Privacy Notice (RA 10173).'
                    : 'Saved the clinic form (Form 01) and re-confirmed Data Privacy Act consent.')
                . ($changedSections ? ' Changed: ' . implode(', ', $changedSections) . '.' : ' No details changed.')
            );
            $_SESSION['clinic_form_saved'] = true;
            header("Location: profile.php?success=updated");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Clinic form update failed: ' . $e->getMessage());
            $errors[] = 'Your clinic form could not be saved. Please try again.';
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
// Guardian fields: show what the student just submitted when the form is redisplayed with errors.
$guardianPostKeys = [
    'Relationship' => 'relationship', 'LastName' => 'last_name', 'FirstName' => 'first_name', 'MiddleName' => 'middle_name',
    'OfficePhone' => 'office_phone', 'MobileNumber' => 'mobile', 'EmailAddress' => 'email',
    'EmergencyContactName' => 'emergency_name', 'EmergencyContactMobile' => 'emergency_mobile',
];
$guardianRaw = static function (string $prefix, array $guardian, string $column) use ($guardianPostKeys): string {
    $postKey = $prefix . ($guardianPostKeys[$column] ?? '');
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($guardianPostKeys[$column]) && is_string($_POST[$postKey] ?? null)) {
        return trim($_POST[$postKey]);
    }
    return (string) ($guardian[$column] ?? '');
};
$gv = static fn(string $prefix, array $guardian, string $column): string => htmlspecialchars($guardianRaw($prefix, $guardian, $column));
$wizardStepMeta = [
    1 => ['Student Information', 'Provide accurate personal and academic information.'],
    2 => ['Parent / Guardian Information', 'Add guardian and emergency contact details.'],
    3 => ['Past History', 'Record medical history, medicines, and allergies.'],
    4 => ['Consent Form', 'Review the consent document, agree to the data privacy notice, and save.'],
];
$clinicFormRequired = !studentClinicFormComplete();
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
    <link rel="stylesheet" href="../public/assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../app/includes/student-dashboard-start.php'; ?>
    <div class="clinic-wizard-page">
        <?php if (!$clinicFormRequired): ?>
            <a href="profile.php" class="clinic-wizard-back">&larr; My Profile</a>
        <?php endif; ?>

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
            <img src="../public/assets/images/fcat-clinic-logo.2.png" alt="FCAT HealthWard logo" class="clinic-wizard-logo">
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

        <?php if ($clinicFormRequired): ?>
            <div class="clinic-form-required-notice" role="status">
                <span aria-hidden="true"><?= studentDashboardIcon('shield') ?></span>
                <div>
                    <strong>Complete your clinic form to continue</strong>
                    <p>The FCAT School Clinic needs your health information before you can use the rest of the portal. Fill out all four steps and save the form to unlock your dashboard, patient records, and messages.</p>
                </div>
            </div>
        <?php endif; ?>
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
                        <div class="alert alert-danger clinic-wizard-error" data-wizard-error role="alert" hidden></div>

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
                    <label for="course">Course *</label>
                    <select id="course" name="course" required data-course-select
                            data-course-departments="<?= htmlspecialchars(json_encode(clinicCourseDepartments())) ?>">
                        <option value="">Select course</option>
                        <?php foreach (clinicCourseOptions() as $courseOption): ?>
                            <option value="<?= htmlspecialchars($courseOption) ?>" <?= $rawStudentValue('Course') === $courseOption ? 'selected' : '' ?>><?= htmlspecialchars($courseOption) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php $savedCourse = trim((string) ($student['Course'] ?? '')); ?>
                    <?php if ($savedCourse !== '' && !in_array($savedCourse, clinicCourseOptions(), true)): ?>
                        <small class="clinic-course-notice">Your saved course (<?= htmlspecialchars($savedCourse) ?>) is not in the current course list. Please select your course again.</small>
                    <?php endif; ?>
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
                    <label for="department">Department</label>
                    <?php $selectedCourseDepartment = clinicCourseDepartments()[$rawStudentValue('Course')] ?? ''; ?>
                    <input type="text" id="department" value="<?= htmlspecialchars($selectedCourseDepartment) ?>"
                           placeholder="Assigned from your course" disabled data-course-department
                           title="Department is assigned automatically from your course">
                    <small class="text-muted">Assigned automatically from your course.</small>
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
                         data-min-date="<?= $minimumBirthDate->format('Y-m-d') ?>"
                         data-max-date="<?= $maximumBirthDate->format('Y-m-d') ?>">
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?= $v('DateOfBirth') ?>"
                               min="<?= $minimumBirthDate->format('Y-m-d') ?>" max="<?= $maximumBirthDate->format('Y-m-d') ?>" required>
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
                <button type="button" class="btn clinic-wizard-action clinic-wizard-action-next" data-wizard-next>
                    <span>Next</span><?= studentDashboardIcon('chevron-right') ?>
                </button>
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
                                <?php $g1RelationshipSelected = normalizeGuardianRelationship($guardianRaw('g1_', $g1, 'Relationship')) === $r; ?>
                                <option value="<?= $r ?>" <?= $g1RelationshipSelected ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="g1_last_name" value="<?= $gv('g1_', $g1, 'LastName') ?>" placeholder="e.g. Dela Cruz" required>
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="g1_first_name" value="<?= $gv('g1_', $g1, 'FirstName') ?>" placeholder="e.g. Maria" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="g1_middle_name" value="<?= $gv('g1_', $g1, 'MiddleName') ?>" placeholder="e.g. S.">
                    </div>
                    <div class="form-group">
                        <label>Office Phone</label>
                        <input type="text" name="g1_office_phone" value="<?= $gv('g1_', $g1, 'OfficePhone') ?>" placeholder="09XXXXXXXXX" class="phone-input">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number *</label>
                        <input type="text" name="g1_mobile" value="<?= htmlspecialchars(mobileInputValue($guardianRaw('g1_', $g1, 'MobileNumber'))) ?>" class="mobile-input" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" title="11 digits starting with 09, e.g. 09171234567" placeholder="09XXXXXXXXX" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="g1_email" value="<?= $gv('g1_', $g1, 'EmailAddress') ?>" placeholder="someone@gmail.com">
                    </div>
                    <p class="text-muted" style="margin-top:12px;">Emergency Contact (aside from above)</p>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="g1_emergency_name" value="<?= $gv('g1_', $g1, 'EmergencyContactName') ?>" placeholder="e.g. Reyes, Ana M.">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="g1_emergency_mobile" value="<?= htmlspecialchars(mobileInputValue($guardianRaw('g1_', $g1, 'EmergencyContactMobile'))) ?>" class="mobile-input" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" title="11 digits starting with 09, e.g. 09171234567" placeholder="09XXXXXXXXX">
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
                                <?php $g2RelationshipSelected = normalizeGuardianRelationship($guardianRaw('g2_', $g2, 'Relationship')) === $r; ?>
                                <option value="<?= $r ?>" <?= $g2RelationshipSelected ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="g2_last_name" value="<?= $gv('g2_', $g2, 'LastName') ?>" placeholder="e.g. Dela Cruz">
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="g2_first_name" value="<?= $gv('g2_', $g2, 'FirstName') ?>" placeholder="e.g. Maria">
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="g2_middle_name" value="<?= $gv('g2_', $g2, 'MiddleName') ?>" placeholder="e.g. S.">
                    </div>
                    <div class="form-group">
                        <label>Office Phone</label>
                        <input type="text" name="g2_office_phone" value="<?= $gv('g2_', $g2, 'OfficePhone') ?>" placeholder="09XXXXXXXXX" class="phone-input">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="g2_mobile" value="<?= htmlspecialchars(mobileInputValue($guardianRaw('g2_', $g2, 'MobileNumber'))) ?>" class="mobile-input" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" title="11 digits starting with 09, e.g. 09171234567" placeholder="09XXXXXXXXX">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="g2_email" value="<?= $gv('g2_', $g2, 'EmailAddress') ?>" placeholder="someone@gmail.com">
                    </div>
                    <p class="text-muted" style="margin-top:12px;">Emergency Contact (aside from above)</p>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="g2_emergency_name" value="<?= $gv('g2_', $g2, 'EmergencyContactName') ?>" placeholder="e.g. Reyes, Ana M.">
                    </div>
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="text" name="g2_emergency_mobile" value="<?= htmlspecialchars(mobileInputValue($guardianRaw('g2_', $g2, 'EmergencyContactMobile'))) ?>" class="mobile-input" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" title="11 digits starting with 09, e.g. 09171234567" placeholder="09XXXXXXXXX">
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
                <button type="button" class="btn clinic-wizard-action clinic-wizard-action-back" data-wizard-back>
                    <?= studentDashboardIcon('chevron-left') ?><span>Back</span>
                </button>
                <button type="button" class="btn clinic-wizard-action clinic-wizard-action-next" data-wizard-next>
                    <span>Next</span><?= studentDashboardIcon('chevron-right') ?>
                </button>
            </div>
        </section>

        <!-- ── SECTION 3: Past Medical History ── -->
        <section class="card form-section clinic-wizard-step" data-wizard-step="3" hidden>
            <h2>Past Medical History</h2>
            <p class="text-muted">Check any that apply and optionally provide the date and age of diagnosis.</p>
            <div class="illness-grid">
                <?php foreach ($illnesses as $ill):
                    $key = md5($ill);
                    $dateFieldName = 'illness_date_' . $key;
                    $ageFieldName = 'illness_age_' . $key;
                    $checked = $_SERVER['REQUEST_METHOD'] === 'POST'
                        ? in_array($ill, (array) ($_POST['illness'] ?? []), true)
                        : isset($medMap[$ill]);
                    $savedDate = $_SERVER['REQUEST_METHOD'] === 'POST' && array_key_exists($dateFieldName, $_POST)
                        ? trim((string) $_POST[$dateFieldName])
                        : (string) ($medMap[$ill]['DiagnosisDate'] ?? '');
                    $savedAge = $_SERVER['REQUEST_METHOD'] === 'POST' && array_key_exists($ageFieldName, $_POST)
                        ? trim((string) $_POST[$ageFieldName])
                        : (string) ($medMap[$ill]['DiagnosisAge'] ?? '');
                ?>
                <div class="illness-row">
                    <label class="illness-check">
                        <input type="checkbox" name="illness[]" value="<?= htmlspecialchars($ill) ?>"
                               <?= $checked ? 'checked' : '' ?>>
                        <span><?= htmlspecialchars($ill) ?></span>
                    </label>
                    <input type="date" name="<?= $dateFieldName ?>" value="<?= htmlspecialchars($savedDate) ?>"
                              min="<?= $minimumDiagnosisDate->format('Y-m-d') ?>"
                              max="<?= $maximumBirthDate->format('Y-m-d') ?>"
                              class="illness-field illness-date-field" data-past-date-picker
                              aria-label="Diagnosis date for <?= htmlspecialchars($ill) ?>">
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
                <button type="button" class="btn clinic-wizard-action clinic-wizard-action-back" data-wizard-back>
                    <?= studentDashboardIcon('chevron-left') ?><span>Back</span>
                </button>
                <button type="button" class="btn clinic-wizard-action clinic-wizard-action-next" data-wizard-next>
                    <span>Next</span><?= studentDashboardIcon('chevron-right') ?>
                </button>
            </div>
        </section>

        <section class="card form-section clinic-wizard-step clinic-wizard-consent" data-wizard-step="4" hidden>
            <p class="paper-form-code">FCAT School Clinic Form 02</p>
            <h2>Consent Form</h2>
            <div class="clinic-consent-grid clinic-consent-preview" aria-label="Consent form preview">
                <div class="clinic-consent-column">
                    <p>I give consent for my child to receive the following:</p>
                    <div class="clinic-consent-display-row"><span>First aid treatment at the school clinic</span><strong>☐ YES &nbsp;&nbsp;&nbsp;&nbsp; ☐ NO</strong></div>
                    <div class="clinic-consent-display-row"><span>Non-prescription medicine in case of emergency</span><strong>☐ YES &nbsp;&nbsp;&nbsp;&nbsp; ☐ NO</strong></div>
                    <div class="clinic-consent-display-row"><span>Hospitalization when emergency care is required</span><strong>☐ YES &nbsp;&nbsp;&nbsp;&nbsp; ☐ NO</strong></div>
                </div>

                <div class="clinic-consent-column">
                    <p>I acknowledge that it is my responsibility to inform FCAT HealthWard of updates to my medical records.</p>
                    <p>In an emergency, I ask the clinic to:</p>
                    <p class="clinic-consent-display-check">☐ Make the immediate care decision needed for the learner.</p>
                    <p class="clinic-consent-display-check">☐ Call the parent or guardian first.</p>
                    <p class="clinic-consent-display-check">☐ Other: ______________________________</p>
                </div>
            </div>

            <div class="privacy-consent-box">
                <div class="privacy-consent-heading">
                    <span aria-hidden="true"><?= studentDashboardIcon('shield') ?></span>
                    <h3>Data Privacy Notice</h3>
                </div>
                <p>
                    In compliance with the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong>, its Implementing Rules and Regulations,
                    and issuances of the National Privacy Commission, the FCAT School Clinic collects the personal and health information in this form
                    (student details, parent/guardian and emergency contacts, medical history, medications, and allergies) solely to provide clinic care,
                    respond to emergencies, and keep your school health record. Your information is accessible only to authorized clinic personnel,
                    is kept secure, and will not be shared with third parties without your consent unless required by law. You may access, correct,
                    or request the removal of your information through the clinic.
                </p>
                <label class="privacy-consent-check">
                    <input type="checkbox" name="privacy_consent" value="1" required data-privacy-consent>
                    <span>I have read and understood the Data Privacy Notice, and I <strong>agree to the collection and processing of my personal and health information</strong> by the FCAT School Clinic in accordance with the Data Privacy Act of 2012 (RA 10173).</span>
                </label>
            </div>

            <div class="clinic-wizard-actions clinic-wizard-actions-final">
                <button type="button" class="btn clinic-wizard-action clinic-wizard-action-back" data-wizard-back>
                    <?= studentDashboardIcon('chevron-left') ?><span>Back</span>
                </button>
                <div class="clinic-wizard-final-actions">
                    <a href="download-consent-form.php" class="btn clinic-wizard-action clinic-wizard-action-download clinic-consent-download">Download form</a>
                    <button type="submit" class="btn clinic-wizard-action clinic-wizard-action-save" data-privacy-consent-submit>Save clinic form</button>
                </div>
            </div>
        </section>

        </form>
    </div>
    <?php include __DIR__ . '/../app/includes/student-dashboard-end.php'; ?>
    <script src="../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
