<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$nurseErrors = [];
$nurseAction = '';
$isJsonRequest = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $nurseAction = (string) ($_POST['action'] ?? '');
    $isJsonRequest = $nurseAction === 'toggle_nurse_duty'
        && str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');

    try {
        if ($nurseAction === 'add_nurse') {
            createNurse($pdo, $_POST, (int) $_SESSION['user_id']);
            header('Location: nurses.php?success=added');
            exit;
        }

        if ($nurseAction === 'edit_nurse') {
            updateNurse(
                $pdo,
                (int) ($_POST['nurse_id'] ?? 0),
                $_POST,
                (int) $_SESSION['user_id']
            );
            header('Location: nurses.php?success=updated');
            exit;
        }

        if ($nurseAction === 'delete_nurse') {
            deleteNurse(
                $pdo,
                (int) ($_POST['nurse_id'] ?? 0),
                (int) $_SESSION['user_id']
            );
            header('Location: nurses.php?success=deleted');
            exit;
        }

        if ($nurseAction === 'toggle_nurse_duty') {
            $nurseId = (int) ($_POST['nurse_id'] ?? 0);
            $isOnDuty = (int) ($_POST['is_on_duty'] ?? 0) === 1;
            $dutyCampus = $isOnDuty ? (string) ($_POST['duty_campus'] ?? '') : null;
            $onDutyCount = setNurseDutyStatus(
                $pdo,
                $nurseId,
                $isOnDuty,
                (int) $_SESSION['user_id'],
                $dutyCampus
            );

            if ($isJsonRequest) {
                $totalCount = (int) $pdo->query("SELECT COUNT(*) FROM Nurses")->fetchColumn();
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'isOnDuty' => $isOnDuty,
                    'campus' => $isOnDuty ? $dutyCampus : null,
                    'onDutyCount' => $onDutyCount,
                    'offDutyCount' => max(0, $totalCount - $onDutyCount),
                ]);
                exit;
            }

            header('Location: nurses.php?success=duty_updated');
            exit;
        }

        throw new InvalidArgumentException('Invalid nurse action.');
    } catch (PDOException $error) {
        $nurseErrors[] = 'The nurse directory could not be updated.';
    } catch (InvalidArgumentException | RuntimeException $error) {
        $nurseErrors[] = $error->getMessage();
    }

    if ($isJsonRequest) {
        http_response_code(422);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $nurseErrors[0] ?? 'The duty status could not be updated.',
        ]);
        exit;
    }
}

$nurses = fetchNurses($pdo);
$totalNurses = count($nurses);
$onDutyNurses = countNursesOnDuty($pdo);
$offDutyNurses = max(0, $totalNurses - $onDutyNurses);
$addDialogHasError = $nurseErrors !== [] && $nurseAction === 'add_nurse';
$editDialogHasError = $nurseErrors !== [] && $nurseAction === 'edit_nurse';

$successMessages = [
    'added' => 'Nurse added to the directory.',
    'updated' => 'Nurse details updated.',
    'deleted' => 'Nurse removed from the directory.',
    'duty_updated' => 'Nurse duty status updated.',
];
$successMessage = $successMessages[(string) ($_GET['success'] ?? '')] ?? '';

$adminPageTitle = 'Nurses';
$adminContentClass = 'nurses-content';
$adminBreadcrumbTrail = ['Nurses'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurses</title>
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>

    <div class="nurses-page" data-nurses-page>
        <div class="nurses-page-heading">
            <div>
                <h1>Nurses</h1>
                <p>Manage the clinic team and control who is currently on duty.</p>
            </div>
            <button type="button" class="btn btn-success admin-panel-action nurses-add-button" data-nurse-dialog-open="add-nurse-dialog">
                + Add Nurse
            </button>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        <?php if ($nurseErrors && !$addDialogHasError && !$editDialogHasError): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($nurseErrors[0]) ?></div>
        <?php endif; ?>
        <div class="alert alert-danger nurses-ajax-alert" data-nurses-ajax-alert hidden></div>

        <section class="nurses-summary-grid" aria-label="Nurse directory summary">
            <article class="nurses-summary-card">
                <span class="nurses-summary-icon"><?= studentDashboardIcon('nurse') ?></span>
                <div>
                    <small>Total nurses</small>
                    <strong data-nurses-total><?= $totalNurses ?></strong>
                </div>
            </article>
            <article class="nurses-summary-card is-on-duty">
                <span class="nurses-summary-icon"><?= studentDashboardIcon('check') ?></span>
                <div>
                    <small>On duty</small>
                    <strong data-nurses-on-duty><?= $onDutyNurses ?></strong>
                </div>
            </article>
            <article class="nurses-summary-card is-off-duty">
                <span class="nurses-summary-icon"><?= studentDashboardIcon('moon') ?></span>
                <div>
                    <small>Off duty</small>
                    <strong data-nurses-off-duty><?= $offDutyNurses ?></strong>
                </div>
            </article>
        </section>

        <section class="nurses-directory-panel">
            <div class="nurses-directory-heading">
                <div>
                    <h2>Clinic nurse directory</h2>
                    <p>Duty changes update the dashboard count immediately.</p>
                </div>
            </div>

            <?php if ($nurses): ?>
                <div class="nurses-table-wrap">
                    <table class="nurses-table">
                        <thead>
                            <tr>
                                <th>Nurse</th>
                                <th>Position</th>
                                <th>License number</th>
                                <th>Status</th>
                                <th>Duty control</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nurses as $nurse): ?>
                                <?php $isOnDuty = (int) $nurse['IsOnDuty'] === 1; ?>
                                <tr data-nurse-row="<?= (int) $nurse['NurseID'] ?>">
                                    <td data-label="Nurse">
                                        <span class="nurse-name-cell">
                                            <span><?= studentDashboardIcon('nurse') ?></span>
                                            <strong><?= htmlspecialchars($nurse['FullName']) ?></strong>
                                        </span>
                                    </td>
                                    <td data-label="Position"><?= htmlspecialchars($nurse['Position']) ?></td>
                                    <td data-label="License number"><?= htmlspecialchars($nurse['LicenseNumber'] ?: '—') ?></td>
                                    <td data-label="Status">
                                        <span class="nurse-duty-status <?= $isOnDuty ? 'is-on-duty' : 'is-off-duty' ?>" data-nurse-duty-status>
                                            <?= $isOnDuty ? 'On duty' : 'Off duty' ?>
                                        </span>
                                        <small class="nurse-duty-campus" data-nurse-duty-campus <?= $isOnDuty ? '' : 'hidden' ?>>
                                            <?= studentDashboardIcon('pin') ?>
                                            <span data-nurse-duty-campus-name><?= htmlspecialchars($isOnDuty ? ($nurse['DutyCampus'] ?: 'Campus not set') : '') ?></span>
                                        </small>
                                    </td>
                                    <td data-label="Duty control">
                                        <form method="POST" class="nurse-duty-form" data-nurse-duty-form>
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                            <input type="hidden" name="action" value="toggle_nurse_duty">
                                            <input type="hidden" name="nurse_id" value="<?= (int) $nurse['NurseID'] ?>">
                                            <input type="hidden" name="is_on_duty" value="<?= $isOnDuty ? '0' : '1' ?>" data-nurse-duty-value>
                                            <input type="hidden" name="duty_campus" value="" data-nurse-duty-campus-input>
                                            <button type="submit" class="nurse-duty-toggle" role="switch"
                                                    aria-checked="<?= $isOnDuty ? 'true' : 'false' ?>"
                                                    aria-label="Toggle duty status for <?= htmlspecialchars($nurse['FullName'], ENT_QUOTES) ?>"
                                                    data-nurse-name="<?= htmlspecialchars($nurse['FullName'], ENT_QUOTES) ?>"
                                                    title="<?= $isOnDuty ? 'Set off duty' : 'Set on duty' ?>"
                                                    data-nurse-duty-toggle>
                                                <span class="nurse-duty-toggle-track" aria-hidden="true"><span></span></span>
                                            </button>
                                        </form>
                                    </td>
                                    <td data-label="Actions" class="nurse-row-actions">
                                        <button type="button" class="btn nurse-edit-button"
                                                data-nurse-edit
                                                data-nurse-id="<?= (int) $nurse['NurseID'] ?>"
                                                data-nurse-name="<?= htmlspecialchars($nurse['FullName'], ENT_QUOTES) ?>"
                                                data-nurse-position="<?= htmlspecialchars($nurse['Position'], ENT_QUOTES) ?>"
                                                data-nurse-license="<?= htmlspecialchars($nurse['LicenseNumber'] ?? '', ENT_QUOTES) ?>">
                                            Edit
                                        </button>
                                        <form method="POST" class="delete-form nurse-delete-form"
                                              data-delete-title="Delete nurse"
                                              data-delete-message="Delete <?= htmlspecialchars($nurse['FullName'], ENT_QUOTES) ?> from the nurse directory? This action cannot be undone.">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                            <input type="hidden" name="action" value="delete_nurse">
                                            <input type="hidden" name="nurse_id" value="<?= (int) $nurse['NurseID'] ?>">
                                            <button type="submit" class="btn nurse-delete-button">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="nurses-empty-state">
                    <span><?= studentDashboardIcon('nurse') ?></span>
                    <h3>No nurses added yet</h3>
                    <p>Add the clinic team, then toggle who is currently on duty.</p>
                    <button type="button" class="btn btn-success" data-nurse-dialog-open="add-nurse-dialog">Add first nurse</button>
                </div>
            <?php endif; ?>
        </section>

        <dialog class="nurse-dialog" id="add-nurse-dialog" aria-labelledby="add-nurse-title" aria-describedby="add-nurse-description" <?= $addDialogHasError ? 'data-nurse-dialog-auto-open' : '' ?>>
            <div class="nurse-dialog-shell">
                <div class="nurse-dialog-heading">
                    <span class="nurse-dialog-icon"><?= studentDashboardIcon('nurse') ?></span>
                    <div>
                        <h2 id="add-nurse-title">Add nurse</h2>
                        <p id="add-nurse-description">Create a nurse profile for clinic duty management.</p>
                    </div>
                    <button type="button" class="nurse-dialog-close" data-nurse-dialog-close aria-label="Close add nurse dialog" title="Close">
                        <?= studentDashboardIcon('close') ?>
                    </button>
                </div>
                <form method="POST" class="nurse-dialog-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                    <input type="hidden" name="action" value="add_nurse">
                    <?php if ($addDialogHasError): ?>
                        <div class="alert alert-danger nurse-dialog-alert"><?= htmlspecialchars($nurseErrors[0]) ?></div>
                    <?php endif; ?>
                    <label for="add-nurse-name">
                        <span>Full name</span>
                        <input id="add-nurse-name" type="text" name="full_name" value="<?= $addDialogHasError ? htmlspecialchars((string) ($_POST['full_name'] ?? '')) : '' ?>" maxlength="120" autocomplete="off" required autofocus>
                    </label>
                    <label for="add-nurse-position">
                        <span>Position</span>
                        <input id="add-nurse-position" type="text" name="position" value="<?= $addDialogHasError ? htmlspecialchars((string) ($_POST['position'] ?? 'Clinic Nurse')) : 'Clinic Nurse' ?>" maxlength="100" autocomplete="off" required>
                    </label>
                    <label for="add-nurse-license">
                        <span>License number</span>
                        <input id="add-nurse-license" type="text" name="license_number" value="<?= $addDialogHasError ? htmlspecialchars((string) ($_POST['license_number'] ?? '')) : '' ?>" maxlength="80" autocomplete="off">
                    </label>
                    <label class="nurse-initial-duty" for="add-nurse-duty">
                        <input id="add-nurse-duty" type="checkbox" name="is_on_duty" value="1" data-add-nurse-duty <?= $addDialogHasError && (int) ($_POST['is_on_duty'] ?? 0) === 1 ? 'checked' : '' ?>>
                        <span>Mark this nurse as on duty</span>
                    </label>
                    <?php $addCampusSelected = $addDialogHasError ? (string) ($_POST['duty_campus'] ?? '') : ''; ?>
                    <fieldset class="nurse-campus-options" data-add-nurse-campus <?= $addDialogHasError && (int) ($_POST['is_on_duty'] ?? 0) === 1 ? '' : 'hidden' ?>>
                        <legend>Campus on duty</legend>
                        <?php foreach (clinicCampuses() as $campusOption): ?>
                            <label class="nurse-campus-option">
                                <input type="radio" name="duty_campus" value="<?= htmlspecialchars($campusOption) ?>" <?= $addCampusSelected === $campusOption ? 'checked' : '' ?>>
                                <span><?= studentDashboardIcon('pin') ?><?= htmlspecialchars($campusOption) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                    <div class="nurse-dialog-actions">
                        <button type="button" class="btn btn-secondary" data-nurse-dialog-close>Cancel</button>
                        <button type="submit" class="btn btn-success">Save nurse</button>
                    </div>
                </form>
            </div>
        </dialog>

        <dialog class="nurse-dialog" id="nurse-campus-dialog" aria-labelledby="nurse-campus-title" aria-describedby="nurse-campus-description" data-nurse-campus-dialog>
            <div class="nurse-dialog-shell">
                <div class="nurse-dialog-heading">
                    <span class="nurse-dialog-icon"><?= studentDashboardIcon('pin') ?></span>
                    <div>
                        <h2 id="nurse-campus-title">Set on duty</h2>
                        <p id="nurse-campus-description">Choose the campus where <strong data-nurse-campus-name>this nurse</strong> is on duty.</p>
                    </div>
                    <button type="button" class="nurse-dialog-close" data-nurse-dialog-close aria-label="Close campus dialog" title="Close">
                        <?= studentDashboardIcon('close') ?>
                    </button>
                </div>
                <form class="nurse-dialog-form" data-nurse-campus-form>
                    <div class="alert alert-danger nurse-dialog-alert" data-nurse-campus-error hidden>Choose a campus to continue.</div>
                    <fieldset class="nurse-campus-options">
                        <legend class="sr-only">Campus</legend>
                        <?php foreach (clinicCampuses() as $index => $campusOption): ?>
                            <label class="nurse-campus-option">
                                <input type="radio" name="campus_choice" value="<?= htmlspecialchars($campusOption) ?>" <?= $index === 0 ? 'autofocus' : '' ?>>
                                <span><?= studentDashboardIcon('pin') ?><?= htmlspecialchars($campusOption) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                    <div class="nurse-dialog-actions">
                        <button type="button" class="btn btn-secondary" data-nurse-dialog-close>Cancel</button>
                        <button type="submit" class="btn btn-success">Set on duty</button>
                    </div>
                </form>
            </div>
        </dialog>

        <dialog class="nurse-dialog" id="edit-nurse-dialog" aria-labelledby="edit-nurse-title" aria-describedby="edit-nurse-description" <?= $editDialogHasError ? 'data-nurse-dialog-auto-open' : '' ?>>
            <div class="nurse-dialog-shell">
                <div class="nurse-dialog-heading">
                    <span class="nurse-dialog-icon"><?= studentDashboardIcon('settings') ?></span>
                    <div>
                        <h2 id="edit-nurse-title">Edit nurse</h2>
                        <p id="edit-nurse-description">Update this nurse's directory information.</p>
                    </div>
                    <button type="button" class="nurse-dialog-close" data-nurse-dialog-close aria-label="Close edit nurse dialog" title="Close">
                        <?= studentDashboardIcon('close') ?>
                    </button>
                </div>
                <form method="POST" class="nurse-dialog-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                    <input type="hidden" name="action" value="edit_nurse">
                    <input type="hidden" name="nurse_id" value="<?= $editDialogHasError ? (int) ($_POST['nurse_id'] ?? 0) : 0 ?>" data-edit-nurse-id>
                    <?php if ($editDialogHasError): ?>
                        <div class="alert alert-danger nurse-dialog-alert"><?= htmlspecialchars($nurseErrors[0]) ?></div>
                    <?php endif; ?>
                    <label for="edit-nurse-name">
                        <span>Full name</span>
                        <input id="edit-nurse-name" type="text" name="full_name" value="<?= $editDialogHasError ? htmlspecialchars((string) ($_POST['full_name'] ?? '')) : '' ?>" maxlength="120" autocomplete="off" required autofocus data-edit-nurse-name>
                    </label>
                    <label for="edit-nurse-position">
                        <span>Position</span>
                        <input id="edit-nurse-position" type="text" name="position" value="<?= $editDialogHasError ? htmlspecialchars((string) ($_POST['position'] ?? '')) : '' ?>" maxlength="100" autocomplete="off" required data-edit-nurse-position>
                    </label>
                    <label for="edit-nurse-license">
                        <span>License number</span>
                        <input id="edit-nurse-license" type="text" name="license_number" value="<?= $editDialogHasError ? htmlspecialchars((string) ($_POST['license_number'] ?? '')) : '' ?>" maxlength="80" autocomplete="off" data-edit-nurse-license>
                    </label>
                    <div class="nurse-dialog-actions">
                        <button type="button" class="btn btn-secondary" data-nurse-dialog-close>Cancel</button>
                        <button type="submit" class="btn btn-success">Save changes</button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>

    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>
