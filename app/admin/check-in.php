<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireClinicStaff();

$checkInErrors = [];
// The clinic time zone set from APP_TIMEZONE in app/config/database.php.
$clinicTimezone = new DateTimeZone(date_default_timezone_get());
$minimumFollowUpDate = new DateTimeImmutable('today', $clinicTimezone);
$maximumFollowUpDate = $minimumFollowUpDate->modify('+30 days');
// fetchCheckInPerson() further checks that the chosen person exists (and that nurse/other patients are supported).
$selectedType = in_array(($_GET['person_type'] ?? $_POST['person_type'] ?? ''), ['student', 'staff', 'nurse', 'other'], true)
    ? (string) ($_GET['person_type'] ?? $_POST['person_type'])
    : '';
$selectedId = (int) ($_GET['person_id'] ?? $_POST['person_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'create_check_in' || $action === 'add_other_patient') {
            // A follow-up date only applies to follow-up visits: required for them, discarded for any other status.
            $followUpValue = trim((string) ($_POST['follow_up_date'] ?? ''));
            if (($_POST['status'] ?? '') !== 'follow-up') {
                $followUpValue = '';
                $_POST['follow_up_date'] = '';
            } elseif ($followUpValue === '') {
                throw new InvalidArgumentException('Choose a follow-up date for a follow-up visit.');
            }
            if ($followUpValue !== '') {
                $followUpDate = DateTimeImmutable::createFromFormat('!Y-m-d', $followUpValue, $clinicTimezone);
                if (!$followUpDate || $followUpDate->format('Y-m-d') !== $followUpValue) {
                    throw new InvalidArgumentException('Please choose a valid follow-up date.');
                }
                if ($followUpDate < $minimumFollowUpDate || $followUpDate > $maximumFollowUpDate) {
                    throw new InvalidArgumentException('Follow-up date must be between today and 30 days ahead.');
                }
            }
            // The visit is credited to whoever is signed in: an admin (AdminID) or a nurse (NurseID).
            $recordedByAdmin = isAdmin() ? (int) $_SESSION['user_id'] : null;
            $recordedByNurse = isNurse() ? (int) $_SESSION['user_id'] : null;
            if ($action === 'add_other_patient') {
                // Faculty, non-teaching staff or a visitor who isn't in the list yet: save them and their visit
                // together, so a check-in that fails (e.g. no complaint) leaves no half-added patient behind.
                if (!otherPatientsSupported($pdo)) { // also runs any one-time database setup before the transaction
                    throw new RuntimeException('Adding other patients is not installed yet. Run database/migrations/add_other_patients.sql.');
                }
                $pdo->beginTransaction();
                try {
                    $otherPatientId = createOtherPatient($pdo, $_POST);
                    $checkInId = createClinicCheckIn($pdo, 'other', $otherPatientId, $recordedByAdmin, $_POST, $recordedByNurse);
                    $pdo->commit();
                } catch (Throwable $error) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    throw $error;
                }
            } else {
                $checkInId = createClinicCheckIn($pdo, $selectedType, $selectedId, $recordedByAdmin, $_POST, $recordedByNurse);
            }
            $checkedInAt = $pdo->prepare('SELECT CheckedInAt FROM ClinicCheckIns WHERE CheckInID = ?');
            $checkedInAt->execute([$checkInId]);
            $_SESSION['checkin_flash_time'] = (string) $checkedInAt->fetchColumn();
            header('Location: check-in.php?success=checked_in');
            exit;
        }
        if ($action === 'check_out') {
            $_SESSION['checkin_flash_time'] = checkOutClinicCheckIn($pdo, (int) ($_POST['check_in_id'] ?? 0));
            header('Location: check-in.php?success=checked_out');
            exit;
        }
        throw new InvalidArgumentException('Invalid check-in action.');
    } catch (InvalidArgumentException | RuntimeException | PDOException $error) {
        $checkInErrors[] = $error instanceof PDOException
            ? ($action === 'add_other_patient' ? 'The patient could not be saved.' : 'The clinic check-in could not be saved.')
            : $error->getMessage();
    }
}
// Reopen the "add a patient" form (with what was typed) when saving it failed.
$newPatientFormOpen = $checkInErrors !== [] && ($action ?? '') === 'add_other_patient';
$canAddOtherPatients = otherPatientsSupported($pdo);

$searchQuery = trim((string) ($_GET['q'] ?? ''));
$searchResults = searchCheckInPeople($pdo, '', 0);
$selectedPerson = $selectedType !== '' && $selectedId > 0
    ? fetchCheckInPerson($pdo, $selectedType, $selectedId)
    : null;
$activeCheckIns = fetchActiveClinicCheckIns($pdo);
$medicines = fetchMedicines($pdo);
$personVisits = $selectedPerson
    ? fetchClinicVisitsForPerson($pdo, $selectedType, $selectedId)
    : [];
$flashTime = !empty($_SESSION['checkin_flash_time'])
    ? ' at ' . date('g:i A', strtotime($_SESSION['checkin_flash_time']))
    : '';
unset($_SESSION['checkin_flash_time']);
$successMessages = [
    'checked_in' => 'Checked in' . $flashTime . '. The person is now listed as currently in clinic.',
    'checked_out' => 'Checked out' . $flashTime . '. Clinic occupancy has been updated.',
];
$successMessage = $successMessages[(string) ($_GET['success'] ?? '')] ?? '';

$adminPageTitle = 'Check In';
$adminContentClass = 'checkin-content';
$adminBreadcrumbTrail = ['Check In'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check In</title>
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?= (int) filemtime(__DIR__ . '/../../public/assets/css/style.css') ?>">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>

    <div class="checkin-page">
        <header class="checkin-page-heading">
            <div>
                <h1>Check In</h1>
                <p>Search an existing student, nurse or staff account, record the visit, and manage who is currently in clinic.</p>
            </div>
            <span class="checkin-live-count"><strong><?= count($activeCheckIns) ?></strong> currently in clinic</span>
        </header>

        <?php if ($successMessage !== ''): ?><div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div><?php endif; ?>
        <?php if ($checkInErrors): ?><div class="alert alert-danger"><?= htmlspecialchars($checkInErrors[0]) ?></div><?php endif; ?>

        <div class="checkin-layout">
            <main class="checkin-main">
                <section class="checkin-panel checkin-search-panel">
                    <div class="checkin-section-heading">
                        <span><?= studentDashboardIcon('user') ?></span>
                        <div><h2>Find a person</h2><p>Search students, nurses, admins and patients added before. Anyone else can be added below.</p></div>
                    </div>
                    <form method="GET" class="checkin-search-form" data-checkin-search-form>
                           <label for="checkin-search" class="sr-only">Search by name or student number</label>
                        <input type="search" id="checkin-search" name="q" value="<?= htmlspecialchars($searchQuery) ?>"
                               placeholder="Search name or student number" autocomplete="off"
                               role="combobox" aria-autocomplete="list" aria-controls="checkin-search-results" aria-expanded="false">
                    </form>
                    <div class="checkin-search-results" id="checkin-search-results" data-checkin-search-results role="listbox" aria-live="polite" hidden>
                        <?php foreach ($searchResults as $person): ?>
                            <a href="check-in.php?person_type=<?= urlencode($person['PersonType']) ?>&person_id=<?= (int) $person['PersonID'] ?>"
                               class="checkin-person-result" role="option" data-checkin-person
                               data-search-text="<?= htmlspecialchars($person['SearchText']) ?>">
                                <?= personAvatarHtml($person['PhotoPath'] ?? null, $person['DisplayName'], 'checkin-person-icon') ?>
                                <span><strong><?= htmlspecialchars($person['DisplayName']) ?></strong><?php if ($person['Details'] !== ''): ?><small><?= htmlspecialchars($person['Details']) ?></small><?php endif; ?></span>
                                <span><?= htmlspecialchars($person['TypeLabel'] ?? ucfirst($person['PersonType'])) ?></span>
                            </a>
                        <?php endforeach; ?>
                        <div class="checkin-empty" data-checkin-search-empty hidden>No match found. Use "Add a patient" below for someone who isn't in the list.</div>
                        <?php if (!$searchResults): ?>
                            <div class="checkin-empty" data-checkin-roster-empty>No registered students, nurses or staff were found.</div>
                        <?php endif; ?>
                    </div>

                    <?php if ($canAddOtherPatients): ?>
                        <details class="checkin-new-patient" <?= $newPatientFormOpen ? 'open' : '' ?>>
                            <summary>Patient not in the list? Add faculty, staff or a visitor</summary>
                            <?php
                                // What was typed into this form, shown again only when saving it failed.
                                $newValue = static fn(string $key): string => $newPatientFormOpen ? (string) ($_POST[$key] ?? '') : '';
                                $newStatus = $newValue('status') === 'follow-up' ? 'follow-up' : 'open';
                            ?>
                            <form method="POST" action="check-in.php" class="checkin-new-patient-form checkin-visit-form" data-confirm
                                  data-confirm-title="Add patient and check in"
                                  data-confirm-message="Add this patient and check them in to the clinic now? The time in is recorded when you confirm."
                                  data-confirm-label="Check in" data-confirm-class="btn-success">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="action" value="add_other_patient">
                                <div class="form-row form-row-2">
                                    <div class="form-group">
                                        <label for="other_full_name">Full name *</label>
                                        <input type="text" id="other_full_name" name="full_name" maxlength="120" required
                                               value="<?= $newPatientFormOpen ? htmlspecialchars((string) ($_POST['full_name'] ?? '')) : '' ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="other_patient_type">Type *</label>
                                        <?php $typedPatientType = $newPatientFormOpen ? (string) ($_POST['patient_type'] ?? '') : ''; ?>
                                        <select id="other_patient_type" name="patient_type" required>
                                            <option value="">Choose…</option>
                                            <?php foreach (otherPatientTypes() as $otherType): ?>
                                                <option value="<?= htmlspecialchars($otherType) ?>" <?= $typedPatientType === $otherType ? 'selected' : '' ?>><?= htmlspecialchars($otherType) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row form-row-2">
                                    <div class="form-group">
                                        <label for="other_department">Department or office</label>
                                        <input type="text" id="other_department" name="department" maxlength="100" placeholder="e.g. Registrar, Security"
                                               value="<?= $newPatientFormOpen ? htmlspecialchars((string) ($_POST['department'] ?? '')) : '' ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="other_contact_number">Contact number</label>
                                        <input type="text" id="other_contact_number" name="contact_number" class="mobile-input" inputmode="numeric"
                                               maxlength="11" pattern="09[0-9]{9}" title="11 digits starting with 09, e.g. 09171234567" placeholder="09XXXXXXXXX"
                                               value="<?= $newPatientFormOpen ? htmlspecialchars(mobileInputValue($_POST['contact_number'] ?? '')) : '' ?>">
                                    </div>
                                </div>

                                <?php // The visit, the same fields as checking in a person from the list. ?>
                                <div class="form-row form-row-2">
                                    <div class="form-group"><label for="new_complaint">Complaint *</label><textarea id="new_complaint" name="complaint" rows="3" maxlength="2000" required><?= htmlspecialchars($newValue('complaint')) ?></textarea></div>
                                    <div class="form-group"><label for="new_vitals">Vitals</label><textarea id="new_vitals" name="vitals" rows="3" maxlength="255" placeholder="BP, temperature, pulse, etc."><?= htmlspecialchars($newValue('vitals')) ?></textarea></div>
                                </div>
                                <div class="form-row form-row-2">
                                    <div class="form-group"><label for="new_assessment">Assessment</label><textarea id="new_assessment" name="assessment" rows="3" maxlength="5000"><?= htmlspecialchars($newValue('assessment')) ?></textarea></div>
                                    <div class="form-group"><label for="new_treatment">Treatment</label><textarea id="new_treatment" name="treatment" rows="3" maxlength="5000"><?= htmlspecialchars($newValue('treatment')) ?></textarea></div>
                                </div>
                                <div class="form-row form-row-3">
                                    <div class="form-group">
                                        <label for="new_medicine_id">Medicine given</label>
                                        <select id="new_medicine_id" name="medicine_id" data-checkin-medicine>
                                            <option value="" data-stock="0">No medicine</option>
                                            <?php foreach ($medicines as $medicine): ?>
                                                <?php $medicineStock = (int) $medicine['Stock']; ?>
                                                <option value="<?= (int) $medicine['MedicineID'] ?>" data-stock="<?= $medicineStock ?>" data-name="<?= htmlspecialchars($medicine['Name'], ENT_QUOTES) ?>"
                                                    <?= (int) $newValue('medicine_id') === (int) $medicine['MedicineID'] ? 'selected' : '' ?> <?= $medicineStock > 0 ? '' : 'disabled' ?>>
                                                    <?= htmlspecialchars($medicine['Name']) ?> — <?= $medicineStock > 0 ? $medicineStock . ' in stock' : 'Out of stock' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group"><label for="new_medicine_dosage">Dosage given</label><input type="text" id="new_medicine_dosage" name="medicine_dosage" value="<?= htmlspecialchars($newValue('medicine_dosage')) ?>" placeholder="e.g. 500 mg, one tablet" maxlength="120" data-checkin-medicine-dosage></div>
                                    <div class="form-group"><label for="new_medicine_quantity">Quantity given</label><input type="text" id="new_medicine_quantity" name="medicine_quantity" value="<?= htmlspecialchars(preg_replace('/\D/', '', $newValue('medicine_quantity'))) ?>" class="integer-input" inputmode="numeric" pattern="[1-9][0-9]{0,5}" maxlength="6" title="Whole number of units, e.g. 2" placeholder="Units dispensed" autocomplete="off" data-checkin-medicine-quantity></div>
                                </div>
                                <div class="form-row form-row-3">
                                    <div class="form-group">
                                        <label for="new_status">Status</label>
                                        <select id="new_status" name="status" data-checkin-status>
                                            <?php foreach (['open' => 'Open', 'follow-up' => 'Follow-up'] as $statusValue => $statusLabel): ?>
                                                <option value="<?= $statusValue ?>" <?= $newStatus === $statusValue ? 'selected' : '' ?>><?= $statusLabel ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted checkin-status-hint">Open visits are marked Completed automatically when the patient checks out.</small>
                                    </div>
                                    <div class="form-group"><label for="new_disposition">Disposition</label><input type="text" id="new_disposition" name="disposition" maxlength="100" value="<?= htmlspecialchars($newValue('disposition')) ?>" placeholder="Returned to work, sent home, etc."></div>
                                    <div class="form-group"><label for="new_follow_up_date">Follow-up date</label><input type="date" id="new_follow_up_date" name="follow_up_date" min="<?= $minimumFollowUpDate->format('Y-m-d') ?>" max="<?= $maximumFollowUpDate->format('Y-m-d') ?>" value="<?= $newStatus === 'follow-up' ? htmlspecialchars($newValue('follow_up_date')) : '' ?>" title="Set the status to Follow-up to choose a date" data-checkin-follow-up <?= $newStatus === 'follow-up' ? 'required' : 'disabled' ?>></div>
                                </div>
                                <button type="submit" class="btn btn-success checkin-submit">Add and check in</button>
                            </form>
                        </details>
                    <?php endif; ?>
                </section>

                <?php if ($selectedPerson): ?>
                    <section class="checkin-panel checkin-form-panel">
                        <div class="checkin-selected-person">
                            <?= personAvatarHtml($selectedPerson['PhotoPath'] ?? null, $selectedPerson['DisplayName']) ?>
                            <div><small>Checking in <?= htmlspecialchars($selectedPerson['TypeLabel'] ?? ucfirst($selectedType)) ?></small><strong><?= htmlspecialchars($selectedPerson['DisplayName']) ?></strong><?php if ($selectedPerson['Details'] !== ''): ?><p><?= htmlspecialchars($selectedPerson['Details']) ?></p><?php endif; ?></div>
                            <a href="check-in.php" class="btn btn-secondary">Change</a>
                        </div>
                        <form method="POST" class="checkin-visit-form" data-confirm
                              data-confirm-title="Confirm check-in"
                              data-confirm-message="Check in <?= htmlspecialchars($selectedPerson['DisplayName'], ENT_QUOTES) ?> to the clinic now? The time in is recorded when you confirm."
                              data-confirm-label="Check in" data-confirm-class="btn-success">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                            <input type="hidden" name="action" value="create_check_in">
                            <input type="hidden" name="person_type" value="<?= htmlspecialchars($selectedType) ?>">
                            <input type="hidden" name="person_id" value="<?= $selectedId ?>">
                            <div class="form-row form-row-2">
                                <div class="form-group"><label for="complaint">Complaint *</label><textarea id="complaint" name="complaint" rows="3" maxlength="2000" required><?= htmlspecialchars($_POST['complaint'] ?? '') ?></textarea></div>
                                <div class="form-group"><label for="vitals">Vitals</label><textarea id="vitals" name="vitals" rows="3" maxlength="255" placeholder="BP, temperature, pulse, etc."><?= htmlspecialchars($_POST['vitals'] ?? '') ?></textarea></div>
                            </div>
                            <div class="form-row form-row-2">
                                <div class="form-group"><label for="assessment">Assessment</label><textarea id="assessment" name="assessment" rows="3" maxlength="5000"><?= htmlspecialchars($_POST['assessment'] ?? '') ?></textarea></div>
                                <div class="form-group"><label for="treatment">Treatment</label><textarea id="treatment" name="treatment" rows="3" maxlength="5000"><?= htmlspecialchars($_POST['treatment'] ?? '') ?></textarea></div>
                            </div>
                            <div class="form-row form-row-3">
                                <div class="form-group"><label for="medicine_id">Medicine given</label><select id="medicine_id" name="medicine_id" data-checkin-medicine><option value="" data-stock="0">No medicine</option><?php foreach ($medicines as $medicine): ?><?php $medicineStock = (int) $medicine['Stock']; ?><option value="<?= (int) $medicine['MedicineID'] ?>" data-stock="<?= $medicineStock ?>" data-name="<?= htmlspecialchars($medicine['Name'], ENT_QUOTES) ?>"<?= (int) ($_POST['medicine_id'] ?? 0) === (int) $medicine['MedicineID'] ? 'selected' : '' ?> <?= $medicineStock > 0 ? '' : 'disabled' ?>><?= htmlspecialchars($medicine['Name']) ?> — <?= $medicineStock > 0 ? $medicineStock . ' in stock' : 'Out of stock' ?></option><?php endforeach; ?></select></div>
                                <div class="form-group"><label for="medicine_dosage">Dosage given</label><input type="text" id="medicine_dosage" name="medicine_dosage" value="<?= htmlspecialchars($_POST['medicine_dosage'] ?? '') ?>" placeholder="e.g. 500 mg, one tablet" maxlength="120" data-checkin-medicine-dosage></div>
                                <div class="form-group"><label for="medicine_quantity">Quantity given</label><input type="text" id="medicine_quantity" name="medicine_quantity" value="<?= htmlspecialchars(preg_replace('/\D/', '', (string) ($_POST['medicine_quantity'] ?? ''))) ?>" class="integer-input" inputmode="numeric" pattern="[1-9][0-9]{0,5}" maxlength="6" title="Whole number of units, e.g. 2" placeholder="Units dispensed" autocomplete="off" data-checkin-medicine-quantity></div>
                            </div>
                            <div class="form-row form-row-3">
                                <div class="form-group"><label for="status">Status</label><select id="status" name="status" data-checkin-status><?php foreach (['open' => 'Open', 'follow-up' => 'Follow-up'] as $statusValue => $statusLabel): ?><option value="<?= $statusValue ?>" <?= ($_POST['status'] ?? 'open') === $statusValue ? 'selected' : '' ?>><?= $statusLabel ?></option><?php endforeach; ?></select><small class="text-muted checkin-status-hint">Open visits are marked Completed automatically when the patient checks out.</small></div>
                                <div class="form-group"><label for="disposition">Disposition</label><input type="text" id="disposition" name="disposition" maxlength="100" value="<?= htmlspecialchars($_POST['disposition'] ?? '') ?>" placeholder="Returned to class, sent home, etc."></div>
                                <?php $followUpEnabled = ($_POST['status'] ?? 'open') === 'follow-up'; ?><div class="form-group"><label for="follow_up_date">Follow-up date</label><input type="date" id="follow_up_date" name="follow_up_date" min="<?= $minimumFollowUpDate->format('Y-m-d') ?>" max="<?= $maximumFollowUpDate->format('Y-m-d') ?>" value="<?= $followUpEnabled ? htmlspecialchars($_POST['follow_up_date'] ?? '') : '' ?>" title="Set the status to Follow-up to choose a date" data-checkin-follow-up <?= $followUpEnabled ? 'required' : 'disabled' ?>></div>
                            </div>
                            <button type="submit" class="btn btn-success checkin-submit">Check In</button>
                        </form>
                    </section>

                    <section class="checkin-panel">
                        <div class="checkin-section-heading"><span><?= studentDashboardIcon('records') ?></span><div><h2>Visit records</h2><p>Previous clinic visits for <?= htmlspecialchars($selectedPerson['DisplayName']) ?>.</p></div></div>
                        <?php if ($personVisits): ?><div class="checkin-history-list"><?php foreach ($personVisits as $visit): ?><article><div><strong><?= date('M d, Y', strtotime($visit['TimeIn'] ?? $visit['CreatedAt'])) ?></strong><?= visitTimesHtml($visit) ?><p><?= htmlspecialchars($visit['Complaint']) ?></p><?php if ($visit['MedicineGiven']): ?><small><?= htmlspecialchars($visit['MedicineGiven']) ?><?= $visit['MedicineDosage'] ? ' · ' . htmlspecialchars($visit['MedicineDosage']) : '' ?><?= !empty($visit['MedicineQuantity']) ? ' · Qty ' . (int) $visit['MedicineQuantity'] : '' ?></small><?php endif; ?></div><span class="visit-status visit-status-<?= htmlspecialchars($visit['Status']) ?>"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $visit['Status']))) ?></span></article><?php endforeach; ?></div><?php else: ?><div class="checkin-empty">No previous clinic visits.</div><?php endif; ?>
                    </section>
                <?php endif; ?>
            </main>

            <aside class="checkin-current-panel checkin-panel">
                <div class="checkin-section-heading"><span><?= studentDashboardIcon('medical') ?></span><div><h2>Current in clinic</h2><p><?= count($activeCheckIns) ?> active check-in<?= count($activeCheckIns) === 1 ? '' : 's' ?></p></div></div>
                <?php if ($activeCheckIns): ?>
                    <div class="checkin-current-list">
                        <?php foreach ($activeCheckIns as $checkIn): ?>
                            <article class="checkin-current-person">
                                <?= personAvatarHtml($checkIn['PhotoPath'] ?? null, (string) $checkIn['DisplayName']) ?>
                                <div><strong><?= htmlspecialchars($checkIn['DisplayName']) ?></strong><small><?= htmlspecialchars($checkIn['Details']) ?></small><time datetime="<?= date('c', strtotime($checkIn['CheckedInAt'])) ?>">Time in <?= date('g:i A', strtotime($checkIn['CheckedInAt'])) ?></time></div>
                                <form method="POST" data-confirm
                                      data-confirm-title="Confirm check-out"
                                      data-confirm-message="Check out <?= htmlspecialchars($checkIn['DisplayName'], ENT_QUOTES) ?> from the clinic now? The time out is recorded when you confirm.<?= ($checkIn['Status'] ?? '') === 'open' ? ' Their open visit will be marked Completed, which is final.' : '' ?>"
                                      data-confirm-label="Check out" data-confirm-class="btn-success"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>"><input type="hidden" name="action" value="check_out"><input type="hidden" name="check_in_id" value="<?= (int) $checkIn['CheckInID'] ?>"><button type="submit" class="btn btn-secondary">Check Out</button></form>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="checkin-empty">No one is currently checked in.</div>
                <?php endif; ?>
            </aside>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js?v=<?= (int) filemtime(__DIR__ . '/../../public/assets/js/main.js') ?>"></script>
</body>
</html>
