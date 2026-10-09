<?php
require __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$reportErrors = [];
$reportAction = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $reportAction = (string) ($_POST['action'] ?? '');

    try {
        if ($reportAction === 'add_medicine') {
            createMedicine($pdo, $_POST, (int) $_SESSION['user_id']);
            header('Location: reports.php?success=medicine_added');
            exit;
        }

        if ($reportAction === 'add_stock') {
            addMedicineStock(
                $pdo,
                (int) ($_POST['medicine_id'] ?? 0),
                (int) ($_POST['quantity'] ?? 0)
            );
            header('Location: reports.php?success=stock_added');
            exit;
        }

        if ($reportAction === 'archive_medicine') {
            archiveMedicine($pdo, (int) ($_POST['medicine_id'] ?? 0));
            header('Location: reports.php?success=medicine_archived');
            exit;
        }

        if ($reportAction === 'restore_medicine') {
            restoreMedicine($pdo, (int) ($_POST['medicine_id'] ?? 0));
            header('Location: reports.php?success=medicine_restored');
            exit;
        }

        $reportErrors[] = 'Invalid report action.';
    } catch (PDOException $error) {
        $reportErrors[] = $reportAction === 'add_medicine' && $error->getCode() === '23000'
            ? 'A medicine with that name already exists.'
            : 'The medicine inventory could not be updated.';
    } catch (InvalidArgumentException | RuntimeException $error) {
        $reportErrors[] = $error->getMessage();
    }
}

$medicineDialogHasError = $reportErrors !== [] && $reportAction === 'add_medicine';
$stockDialogHasError = $reportErrors !== [] && $reportAction === 'add_stock';

$medicineSearch = trim((string) ($_GET['medicine_search'] ?? ''));
$lowStockOnly = ($_GET['stock_filter'] ?? '') === 'low';
$visitSearch = trim((string) ($_GET['visit_search'] ?? ''));
$visitStatus = in_array(($_GET['visit_status'] ?? ''), ['open', 'completed', 'follow-up'], true)
    ? (string) $_GET['visit_status']
    : '';

$medicineSummary = medicineInventorySummary($pdo);
$allMedicines = fetchMedicines($pdo);
$medicines = fetchMedicines($pdo, $medicineSearch, $lowStockOnly);
$archivedMedicines = fetchMedicines($pdo, '', false, true);
$medicineArchiveInstalled = medicineArchiveInstalled($pdo);
$existingMedicineNames = array_merge(
    array_map(static fn(array $medicine): array => ['name' => $medicine['Name'], 'archived' => false], $allMedicines),
    array_map(static fn(array $medicine): array => ['name' => $medicine['Name'], 'archived' => true], $archivedMedicines)
);
// Filtered in SQL and capped so the page stays fast as visit history grows; filters reach older visits.
$visitReportLimit = 200;
$visitReports = fetchClinicVisits(
    $pdo,
    null,
    $visitReportLimit + 1,
    ['search' => mb_substr($visitSearch, 0, 100), 'status' => $visitStatus]
);
$visitReportsTruncated = count($visitReports) > $visitReportLimit;
$visitReports = array_slice($visitReports, 0, $visitReportLimit);

$adminPageTitle = 'Reports';
$adminContentClass = 'reports-content';
$adminBreadcrumbTrail = ['Reports'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="../../public/assets/css/tailwind.css">
    <link rel="stylesheet" href="../../public/assets/css/style.css?v=<?= assetVersion('css/style.css') ?>">
    <link rel="icon" type="image/png" href="../../public/assets/images/favicon.png">
</head>
<body class="student-dashboard-page admin-dashboard-page antialiased selection:bg-green-200 selection:text-green-950">
    <?php include __DIR__ . '/../includes/admin-dashboard-start.php'; ?>

    <div class="reports-page">
        <div class="reports-page-heading">
            <div>
                <h1>Reports</h1>
                <p>Monitor medicine stock and review clinic visit activity.</p>
            </div>
        </div>

        <?php if (($_GET['success'] ?? '') === 'medicine_added'): ?>
            <div class="alert alert-success">Medicine added to inventory.</div>
        <?php elseif (($_GET['success'] ?? '') === 'stock_added'): ?>
            <div class="alert alert-success">Medicine stock updated.</div>
        <?php elseif (($_GET['success'] ?? '') === 'medicine_archived'): ?>
            <div class="alert alert-success">Medicine moved to the archive. You can restore it when stock arrives again.</div>
        <?php elseif (($_GET['success'] ?? '') === 'medicine_restored'): ?>
            <div class="alert alert-success">Medicine restored to the active inventory.</div>
        <?php endif; ?>

        <?php if ($reportErrors && !$medicineDialogHasError && !$stockDialogHasError): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($reportErrors[0]) ?></div>
        <?php endif; ?>

        <section class="reports-summary-grid" aria-label="Medicine inventory summary">
            <article class="reports-summary-card">
                <span class="reports-summary-icon"><img src="../../public/assets/images/medicine.png" alt=""></span>
                <div>
                    <h2>Total medicines</h2>
                    <strong><?= (int) $medicineSummary['TotalMedicines'] ?></strong>
                    <p>different medicine types in inventory</p>
                </div>
            </article>
            <article class="reports-summary-card is-warning">
                <span class="reports-summary-icon"><img src="../../public/assets/images/arrow.png" alt=""></span>
                <div>
                    <h2>Low stock medicines</h2>
                    <strong><?= (int) $medicineSummary['LowStockMedicines'] ?></strong>
                    <p>at or below their stock threshold</p>
                </div>
            </article>
        </section>

        <?php if ($allMedicines): ?>
            <section class="medicine-showcase medicine-carousel" aria-label="Medicine stock overview" data-medicine-carousel>
                <button type="button" class="medicine-carousel-button is-previous is-disabled" data-medicine-carousel-previous aria-label="Show previous medicine" aria-hidden="true" disabled>
                    <?= studentDashboardIcon('chevron-left') ?>
                </button>
                <div class="medicine-showcase-viewport">
                    <div class="medicine-showcase-track" data-medicine-carousel-track>
                        <?php foreach ($allMedicines as $index => $medicine): ?>
                            <article class="medicine-showcase-card medicine-tone-<?= ($index % 4) + 1 ?>" data-medicine-carousel-item>
                                <span><img class="reports-medicine-card-icon" src="../../public/assets/images/cards.png" alt=""></span>
                                <div class="medicine-showcase-copy">
                                    <h3><?= htmlspecialchars($medicine['Name']) ?></h3>
                                    <div class="medicine-showcase-stock">
                                        <strong><?= (int) $medicine['Stock'] ?></strong>
                                        <small>in stock</small>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="button" class="medicine-carousel-button is-next" data-medicine-carousel-next aria-label="Show next medicine">
                    <?= studentDashboardIcon('chevron-right') ?>
                </button>
                <p class="medicine-carousel-status" data-medicine-carousel-status aria-live="polite"></p>
            </section>
        <?php endif; ?>

        <section class="reports-section medicine-inventory-section">
            <div class="reports-section-heading">
                <div>
                    <h2>Medicine inventory</h2>
                    <p>Manage available clinic supplies and low-stock thresholds.</p>
                </div>
                <form method="GET" class="reports-filter-form">
                    <input type="search" name="medicine_search" value="<?= htmlspecialchars($medicineSearch) ?>" placeholder="Search medicine">
                    <select name="stock_filter" aria-label="Stock filter">
                        <option value="">All stock</option>
                        <option value="low" <?= $lowStockOnly ? 'selected' : '' ?>>Low stock</option>
                    </select>
                    <button type="submit" class="btn btn-secondary">Filter</button>
                </form>
            </div>

            <div class="reports-management-tools" aria-label="Medicine inventory actions">
                <button type="button" class="btn reports-action-button" data-report-dialog-open="add-medicine-dialog" aria-haspopup="dialog" aria-controls="add-medicine-dialog">
                    <?= studentDashboardIcon('plus') ?> Add new medicine
                </button>
                <button type="button" class="btn reports-action-button is-secondary" data-report-dialog-open="add-stock-dialog" aria-haspopup="dialog" aria-controls="add-stock-dialog" <?= $allMedicines ? '' : 'disabled' ?>>
                    <?= studentDashboardIcon('plus') ?> Add stock
                </button>
            </div>

            <dialog class="reports-dialog" id="add-medicine-dialog" aria-labelledby="add-medicine-dialog-title" aria-describedby="add-medicine-dialog-description" <?= $medicineDialogHasError ? 'data-report-dialog-auto-open' : '' ?>>
                <div class="reports-dialog-shell">
                    <div class="reports-dialog-heading">
                        <span class="reports-dialog-icon" aria-hidden="true"><img src="../../public/assets/images/bottle.png" alt=""></span>
                        <div>
                            <h2 id="add-medicine-dialog-title">Add new medicine</h2>
                            <p id="add-medicine-dialog-description">Create an inventory item and set its opening stock level.</p>
                        </div>
                        <button type="button" class="reports-dialog-close" data-report-dialog-close aria-label="Close add medicine dialog" title="Close">
                            <?= studentDashboardIcon('close') ?>
                        </button>
                    </div>

                    <form method="POST" class="reports-tool-form reports-dialog-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <input type="hidden" name="action" value="add_medicine">
                        <?php if ($medicineDialogHasError): ?>
                            <div class="alert alert-danger reports-dialog-alert"><?= htmlspecialchars($reportErrors[0]) ?></div>
                        <?php endif; ?>
                        <label class="reports-form-field reports-field-wide" for="medicine-name">
                            <span>Medicine name</span>
                            <input id="medicine-name" type="text" name="name" value="<?= $medicineDialogHasError ? htmlspecialchars((string) ($_POST['name'] ?? '')) : '' ?>" maxlength="120" autocomplete="off" required autofocus
                                   aria-describedby="medicine-name-duplicate" data-medicine-name-input
                                   data-existing-medicines="<?= htmlspecialchars(json_encode($existingMedicineNames)) ?>">
                            <small class="reports-field-error" id="medicine-name-duplicate" data-medicine-name-duplicate aria-live="polite" hidden></small>
                        </label>
                        <label class="reports-form-field" for="medicine-dosage">
                            <span>Dosage or item type</span>
                            <input id="medicine-dosage" type="text" name="dosage" value="<?= $medicineDialogHasError ? htmlspecialchars((string) ($_POST['dosage'] ?? '')) : '' ?>" maxlength="120" autocomplete="off">
                        </label>
                        <label class="reports-form-field" for="medicine-opening-stock">
                            <span>Opening stock</span>
                            <input id="medicine-opening-stock" type="text" name="stock" value="<?= $medicineDialogHasError ? htmlspecialchars((string) ($_POST['stock'] ?? '')) : '' ?>" class="integer-input" inputmode="numeric" maxlength="6" required>
                        </label>
                        <label class="reports-form-field" for="medicine-threshold">
                            <span>Low-stock threshold</span>
                            <input id="medicine-threshold" type="text" name="low_stock_threshold" value="<?= $medicineDialogHasError ? htmlspecialchars((string) ($_POST['low_stock_threshold'] ?? '20')) : '20' ?>" class="integer-input" inputmode="numeric" maxlength="6" required>
                        </label>
                        <label class="reports-form-field reports-field-wide" for="medicine-description">
                            <span>Description</span>
                            <input id="medicine-description" type="text" name="description" value="<?= $medicineDialogHasError ? htmlspecialchars((string) ($_POST['description'] ?? '')) : '' ?>" maxlength="255" autocomplete="off">
                        </label>
                        <div class="reports-dialog-actions">
                            <button type="button" class="btn btn-secondary" data-report-dialog-close>Cancel</button>
                            <button type="submit" class="btn btn-success">Save medicine</button>
                        </div>
                    </form>
                </div>
            </dialog>

            <dialog class="reports-dialog" id="add-stock-dialog" aria-labelledby="add-stock-dialog-title" aria-describedby="add-stock-dialog-description" <?= $stockDialogHasError ? 'data-report-dialog-auto-open' : '' ?>>
                <div class="reports-dialog-shell">
                    <div class="reports-dialog-heading">
                        <span class="reports-dialog-icon is-stock" aria-hidden="true"><?= studentDashboardIcon('plus') ?></span>
                        <div>
                            <h2 id="add-stock-dialog-title">Add medicine stock</h2>
                            <p id="add-stock-dialog-description">Choose an inventory item and enter the quantity received.</p>
                        </div>
                        <button type="button" class="reports-dialog-close" data-report-dialog-close aria-label="Close add stock dialog" title="Close">
                            <?= studentDashboardIcon('close') ?>
                        </button>
                    </div>

                    <form method="POST" class="reports-tool-form reports-dialog-form reports-stock-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <input type="hidden" name="action" value="add_stock">
                        <?php if ($stockDialogHasError): ?>
                            <div class="alert alert-danger reports-dialog-alert"><?= htmlspecialchars($reportErrors[0]) ?></div>
                        <?php endif; ?>
                        <label class="reports-form-field reports-field-wide" for="stock-medicine">
                            <span>Medicine</span>
                            <select id="stock-medicine" name="medicine_id" required autofocus>
                                <option value="">Select medicine</option>
                                <?php foreach ($allMedicines as $medicine): ?>
                                    <option value="<?= (int) $medicine['MedicineID'] ?>" <?= $stockDialogHasError && (int) ($_POST['medicine_id'] ?? 0) === (int) $medicine['MedicineID'] ? 'selected' : '' ?>><?= htmlspecialchars($medicine['Name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="reports-form-field reports-field-wide" for="stock-quantity">
                            <span>Quantity received</span>
                            <input id="stock-quantity" type="text" name="quantity" value="<?= $stockDialogHasError ? htmlspecialchars((string) ($_POST['quantity'] ?? '')) : '' ?>" class="integer-input" inputmode="numeric" maxlength="6" required>
                        </label>
                        <div class="reports-dialog-actions">
                            <button type="button" class="btn btn-secondary" data-report-dialog-close>Cancel</button>
                            <button type="submit" class="btn btn-success">Update stock</button>
                        </div>
                    </form>
                </div>
            </dialog>

            <div class="reports-table-wrap">
                <table class="reports-table medicine-report-table">
                    <thead>
                        <tr><th>Medicine</th><th>Stock</th><th>Dosage</th><th>Description</th><th>Level</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($medicines): ?>
                        <?php foreach ($medicines as $medicine): ?>
                            <?php $isLowStock = (int) $medicine['Stock'] <= (int) $medicine['LowStockThreshold']; ?>
                            <tr>
                                <td data-label="Medicine"><span class="reports-medicine-name"><span class="reports-inline-medicine-icon" aria-hidden="true"><img src="../../public/assets/images/bottle.png" alt=""></span> <?= htmlspecialchars($medicine['Name']) ?></span></td>
                                <td data-label="Stock"><strong><?= (int) $medicine['Stock'] ?></strong></td>
                                <td data-label="Dosage"><?= htmlspecialchars($medicine['Dosage'] ?: '—') ?></td>
                                <td data-label="Description"><?= htmlspecialchars($medicine['Description'] ?: '—') ?></td>
                                <td data-label="Level"><span class="stock-level <?= $isLowStock ? 'is-low' : 'is-ok' ?>"><?= $isLowStock ? 'Low stock' : 'In stock' ?></span></td>
                                <td data-label="Actions" class="reports-table-actions">
                                    <?php if ($medicineArchiveInstalled): ?>
                                        <form method="POST" class="delete-form reports-row-action-form" data-delete-title="Archive medicine" data-delete-message="Archive <?= htmlspecialchars($medicine['Name']) ?>? It will be hidden from the inventory and Check In until you restore it." data-confirm-label="Archive" data-confirm-class="btn-warning">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                            <input type="hidden" name="action" value="archive_medicine">
                                            <input type="hidden" name="medicine_id" value="<?= (int) $medicine['MedicineID'] ?>">
                                            <button type="submit" class="btn reports-row-action is-archive" title="Archive <?= htmlspecialchars($medicine['Name']) ?>">
                                                <?= studentDashboardIcon('archive') ?>
                                                <span>Archive</span>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="reports-empty-cell">No medicines match the selected filter.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if ($medicineArchiveInstalled): ?>
            <section class="reports-section archived-medicine-section" id="archived-medicines">
                <div class="reports-section-heading">
                    <div>
                        <h2>Archived medicines</h2>
                        <p>Archived items are hidden from the inventory and Check In. Restore one when it is back in stock, then use Add stock to record the quantity.</p>
                    </div>
                </div>

                <div class="reports-table-wrap">
                    <table class="reports-table archived-medicine-table">
                        <thead>
                            <tr><th>Medicine</th><th>Stock</th><th>Dosage</th><th>Archived</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($archivedMedicines): ?>
                            <?php foreach ($archivedMedicines as $medicine): ?>
                                <tr>
                                    <td data-label="Medicine"><span class="reports-medicine-name"><span class="reports-inline-medicine-icon" aria-hidden="true"><img src="../../public/assets/images/bottle.png" alt=""></span> <?= htmlspecialchars($medicine['Name']) ?></span></td>
                                    <td data-label="Stock"><strong><?= (int) $medicine['Stock'] ?></strong></td>
                                    <td data-label="Dosage"><?= htmlspecialchars($medicine['Dosage'] ?: '—') ?></td>
                                    <td data-label="Archived"><?= $medicine['ArchivedAt'] ? date('M d, Y', strtotime($medicine['ArchivedAt'])) : '—' ?></td>
                                    <td data-label="Actions" class="reports-table-actions">
                                        <form method="POST" class="reports-row-action-form">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                            <input type="hidden" name="action" value="restore_medicine">
                                            <input type="hidden" name="medicine_id" value="<?= (int) $medicine['MedicineID'] ?>">
                                            <button type="submit" class="btn reports-row-action is-restore" title="Restore <?= htmlspecialchars($medicine['Name']) ?>">
                                                <?= studentDashboardIcon('restore') ?>
                                                <span>Restore</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="reports-empty-cell">No archived medicines.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <section class="reports-section visit-reports-section">
            <div class="reports-section-heading">
                <div>
                    <h2>Visit reports</h2>
                    <p><?= date('F d, Y') ?> clinic activity</p>
                </div>
                <form method="GET" class="reports-filter-form">
                    <input type="search" name="visit_search" value="<?= htmlspecialchars($visitSearch) ?>" placeholder="Search patient or concern">
                    <select name="visit_status" aria-label="Visit status filter">
                        <option value="">All statuses</option>
                        <option value="open" <?= $visitStatus === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="completed" <?= $visitStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="follow-up" <?= $visitStatus === 'follow-up' ? 'selected' : '' ?>>Follow-up</option>
                    </select>
                    <button type="submit" class="btn btn-secondary">Filter</button>
                </form>
            </div>

            <div class="reports-table-wrap">
                <table class="reports-table visit-report-table">
                    <thead>
                        <tr><th>Patient name</th><th>Concern</th><th>Medicine given</th><th>Date &amp; time</th><th>Consulted by</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($visitReports): ?>
                        <?php foreach ($visitReports as $visit): ?>
                            <tr>
                                <td data-label="Patient"><strong><?= htmlspecialchars(trim(($visit['FirstName'] ?? '') . ' ' . ($visit['LastName'] ?? ''))) ?></strong><small><?= ($visit['PersonType'] ?? 'student') === 'staff' ? 'Staff #' . (int) $visit['PersonID'] : '#' . (int) $visit['StudentID'] ?></small></td>
                                <td data-label="Concern"><?= htmlspecialchars($visit['Complaint']) ?></td>
                                <td data-label="Medicine given"><?= htmlspecialchars($visit['MedicineGiven'] ?: '—') ?><?php if (!empty($visit['MedicineDosage'])): ?><small>Dosage: <?= htmlspecialchars($visit['MedicineDosage']) ?></small><?php endif; ?><?php if (!empty($visit['MedicineQuantity'])): ?><small>Quantity: <?= (int) $visit['MedicineQuantity'] ?></small><?php endif; ?></td>
                                <td data-label="Date"><?= date('M d, Y', strtotime($visit['TimeIn'] ?? $visit['CreatedAt'])) ?><?= visitTimesHtml($visit) ?></td>
                                <td data-label="Consulted by"><?= htmlspecialchars($visit['AdminName'] ?: 'Admin') ?></td>
                                <td data-label="Status"><span class="visit-status visit-status-<?= htmlspecialchars($visit['Status']) ?>"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $visit['Status']))) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="reports-empty-cell">No clinic visits match the selected filter.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($visitReportsTruncated): ?>
                <p class="audit-muted reports-limit-note">Showing the <?= (int) $visitReportLimit ?> most recent visits. Use the search or status filter to find older visits.</p>
            <?php endif; ?>
        </section>
    </div>

    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>