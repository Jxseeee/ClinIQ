<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$checkInErrors = [];
$clinicTimezone = new DateTimeZone('Asia/Manila');
$minimumFollowUpDate = new DateTimeImmutable('today', $clinicTimezone);
$maximumFollowUpDate = $minimumFollowUpDate->modify('+30 days');
$selectedType = in_array(($_GET['person_type'] ?? $_POST['person_type'] ?? ''), ['student', 'staff'], true)
    ? (string) ($_GET['person_type'] ?? $_POST['person_type'])
    : '';
$selectedId = (int) ($_GET['person_id'] ?? $_POST['person_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'create_check_in') {
            $followUpValue = trim((string) ($_POST['follow_up_date'] ?? ''));
            if ($followUpValue !== '') {
                $followUpDate = DateTimeImmutable::createFromFormat('!Y-m-d', $followUpValue, $clinicTimezone);
                if (!$followUpDate || $followUpDate->format('Y-m-d') !== $followUpValue) {
                    throw new InvalidArgumentException('Please choose a valid follow-up date.');
                }
                if ($followUpDate < $minimumFollowUpDate || $followUpDate > $maximumFollowUpDate) {
                    throw new InvalidArgumentException('Follow-up date must be between today and 30 days ahead.');
                }
            }
            createClinicCheckIn($pdo, $selectedType, $selectedId, (int) $_SESSION['user_id'], $_POST);
            header('Location: check-in.php?success=checked_in');
            exit;
        }
        if ($action === 'check_out') {
            checkOutClinicCheckIn($pdo, (int) ($_POST['check_in_id'] ?? 0));
            header('Location: check-in.php?success=checked_out');
            exit;
        }
        throw new InvalidArgumentException('Invalid check-in action.');
    } catch (InvalidArgumentException | RuntimeException | PDOException $error) {
        $checkInErrors[] = $error instanceof PDOException
            ? 'The clinic check-in could not be saved.'
            : $error->getMessage();
    }
}

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
$successMessages = [
    'checked_in' => 'Check-in added. The person is now listed as currently in clinic.',
    'checked_out' => 'Check-out completed. Clinic occupancy has been updated.',
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
                <p>Search an existing student or staff account, record the visit, and manage who is currently in clinic.</p>
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
                        <div><h2>Find a person</h2><p>Only students and staff already registered in the system can be checked in.</p></div>
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
                                <span class="checkin-person-icon"><?= studentDashboardIcon($person['PersonType'] === 'student' ? 'user' : 'settings') ?></span>
                                <span><strong><?= htmlspecialchars($person['DisplayName']) ?></strong><?php if ($person['Details'] !== ''): ?><small><?= htmlspecialchars($person['Details']) ?></small><?php endif; ?></span>
                                <span><?= ucfirst($person['PersonType']) ?></span>
                            </a>
                        <?php endforeach; ?>
                        <div class="checkin-empty" data-checkin-search-empty hidden>No matching student or staff account was found.</div>
                        <?php if (!$searchResults): ?>
                            <div class="checkin-empty" data-checkin-roster-empty>No registered students or staff were found.</div>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if ($selectedPerson): ?>
                    <section class="checkin-panel checkin-form-panel">
                        <div class="checkin-selected-person">
                            <span><?= studentDashboardIcon($selectedType === 'student' ? 'user' : 'settings') ?></span>
                            <div><small>Checking in <?= htmlspecialchars(ucfirst($selectedType)) ?></small><strong><?= htmlspecialchars($selectedPerson['DisplayName']) ?></strong><?php if ($selectedPerson['Details'] !== ''): ?><p><?= htmlspecialchars($selectedPerson['Details']) ?></p><?php endif; ?></div>
                            <a href="check-in.php" class="btn btn-secondary">Change</a>
                        </div>
                        <form method="POST" class="checkin-visit-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                            <input type="hidden" name="action" value="create_check_in">
                            <input type="hidden" name="person_type" value="<?= htmlspecialchars($selectedType) ?>">
                            <input type="hidden" name="person_id" value="<?= $selectedId ?>">
                            <div class="form-row form-row-2">
                                <div class="form-group"><label for="complaint">Complaint *</label><textarea id="complaint" name="complaint" rows="3" required><?= htmlspecialchars($_POST['complaint'] ?? '') ?></textarea></div>
                                <div class="form-group"><label for="vitals">Vitals</label><textarea id="vitals" name="vitals" rows="3" placeholder="BP, temperature, pulse, etc."><?= htmlspecialchars($_POST['vitals'] ?? '') ?></textarea></div>
                            </div>
                            <div class="form-row form-row-2">
                                <div class="form-group"><label for="assessment">Assessment</label><textarea id="assessment" name="assessment" rows="3"><?= htmlspecialchars($_POST['assessment'] ?? '') ?></textarea></div>
                                <div class="form-group"><label for="treatment">Treatment</label><textarea id="treatment" name="treatment" rows="3"><?= htmlspecialchars($_POST['treatment'] ?? '') ?></textarea></div>
                            </div>
                            <div class="form-row form-row-3">
                                <div class="form-group"><label for="medicine_id">Medicine given</label><select id="medicine_id" name="medicine_id" data-checkin-medicine><option value="" data-stock="0">No medicine</option><?php foreach ($medicines as $medicine): ?><?php $medicineStock = (int) $medicine['Stock']; ?><option value="<?= (int) $medicine['MedicineID'] ?>" data-stock="<?= $medicineStock ?>" <?= (int) ($_POST['medicine_id'] ?? 0) === (int) $medicine['MedicineID'] ? 'selected' : '' ?> <?= $medicineStock > 0 ? '' : 'disabled' ?>><?= htmlspecialchars($medicine['Name']) ?> — <?= $medicineStock > 0 ? $medicineStock . ' in stock' : 'Out of stock' ?></option><?php endforeach; ?></select></div>
                                <div class="form-group"><label for="medicine_dosage">Dosage given</label><input type="text" id="medicine_dosage" name="medicine_dosage" value="<?= htmlspecialchars($_POST['medicine_dosage'] ?? '') ?>" placeholder="e.g. 500 mg, one tablet" maxlength="120" data-checkin-medicine-dosage></div>
                                <div class="form-group"><label for="medicine_quantity">Quantity given</label><input type="number" id="medicine_quantity" name="medicine_quantity" value="<?= htmlspecialchars($_POST['medicine_quantity'] ?? '') ?>" min="1" max="999999" step="1" inputmode="numeric" placeholder="Units dispensed" data-checkin-medicine-quantity></div>
                            </div>
                            <div class="form-row form-row-3">
                                <div class="form-group"><label for="status">Status</label><select id="status" name="status"><option value="open">Open</option><option value="completed">Completed</option><option value="follow-up">Follow-up</option></select></div>
                                <div class="form-group"><label for="disposition">Disposition</label><input type="text" id="disposition" name="disposition" value="<?= htmlspecialchars($_POST['disposition'] ?? '') ?>" placeholder="Returned to class, sent home, etc."></div>
                                <div class="form-group"><label for="follow_up_date">Follow-up date</label><input type="date" id="follow_up_date" name="follow_up_date" min="<?= $minimumFollowUpDate->format('Y-m-d') ?>" max="<?= $maximumFollowUpDate->format('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['follow_up_date'] ?? '') ?>"></div>
                            </div>
                            <button type="submit" class="btn btn-success checkin-submit">Check In</button>
                        </form>
                    </section>

                    <section class="checkin-panel">
                        <div class="checkin-section-heading"><span><?= studentDashboardIcon('records') ?></span><div><h2>Visit records</h2><p>Previous clinic visits for <?= htmlspecialchars($selectedPerson['DisplayName']) ?>.</p></div></div>
                        <?php if ($personVisits): ?><div class="checkin-history-list"><?php foreach ($personVisits as $visit): ?><article><div><strong><?= date('M d, Y g:i A', strtotime($visit['CreatedAt'])) ?></strong><p><?= htmlspecialchars($visit['Complaint']) ?></p><?php if ($visit['MedicineGiven']): ?><small><?= htmlspecialchars($visit['MedicineGiven']) ?><?= $visit['MedicineDosage'] ? ' · ' . htmlspecialchars($visit['MedicineDosage']) : '' ?><?= !empty($visit['MedicineQuantity']) ? ' · Qty ' . (int) $visit['MedicineQuantity'] : '' ?></small><?php endif; ?></div><span class="visit-status visit-status-<?= htmlspecialchars($visit['Status']) ?>"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $visit['Status']))) ?></span></article><?php endforeach; ?></div><?php else: ?><div class="checkin-empty">No previous clinic visits.</div><?php endif; ?>
                    </section>
                <?php endif; ?>
            </main>

            <aside class="checkin-current-panel checkin-panel">
                <div class="checkin-section-heading"><span><?= studentDashboardIcon('medical') ?></span><div><h2>Current in clinic</h2><p><?= count($activeCheckIns) ?> active check-in<?= count($activeCheckIns) === 1 ? '' : 's' ?></p></div></div>
                <?php if ($activeCheckIns): ?>
                    <div class="checkin-current-list">
                        <?php foreach ($activeCheckIns as $checkIn): ?>
                            <article class="checkin-current-person">
                                <span><?= studentDashboardIcon($checkIn['PersonType'] === 'student' ? 'user' : 'settings') ?></span>
                                <div><strong><?= htmlspecialchars($checkIn['DisplayName']) ?></strong><small><?= htmlspecialchars($checkIn['Details']) ?></small><time><?= date('g:i A', strtotime($checkIn['CheckedInAt'])) ?></time></div>
                                <form method="POST"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>"><input type="hidden" name="action" value="check_out"><input type="hidden" name="check_in_id" value="<?= (int) $checkIn['CheckInID'] ?>"><button type="submit" class="btn btn-secondary">Check Out</button></form>
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
