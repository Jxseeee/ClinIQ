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

        if ($reportAction === 'delete_medicine') {
            deleteMedicine($pdo, (int) ($_POST['medicine_id'] ?? 0));
            header('Location: reports.php?success=medicine_deleted');
            exit;
        }

        $reportErrors[] = 'Invalid report action.';
    } catch (PDOException $error) {
        $reportErrors[] = $reportAction === 'add_medicine' && $error->getCode() === '23000'
            ? 'A medicine with that name already exists.'
            : ($reportAction === 'delete_medicine'
                ? 'The medicine could not be deleted.'
                : 'The medicine inventory could not be updated.');
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
$featuredMedicines = array_slice($allMedicines, 0, 4);
$medicines = fetchMedicines($pdo, $medicineSearch, $lowStockOnly);
$visitReports = fetchClinicVisits($pdo);

if ($visitSearch !== '') {
    $needle = mb_strtolower($visitSearch);
    $visitReports = array_values(array_filter($visitReports, static function (array $visit) use ($needle): bool {
        $haystack = mb_strtolower(implode(' ', [
            $visit['FirstName'] ?? '',
            $visit['LastName'] ?? '',
            $visit['Complaint'] ?? '',
            $visit['Treatment'] ?? '',
            $visit['MedicineGiven'] ?? '',
            $visit['AdminName'] ?? '',
        ]));
        return str_contains($haystack, $needle);
    }));
}

if ($visitStatus !== '') {
    $visitReports = array_values(array_filter(
        $visitReports,
        static fn(array $visit): bool => ($visit['Status'] ?? '') === $visitStatus
    ));
}

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
    <link rel="stylesheet" href="../../public/assets/css/style.css">
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
            <button type="button" class="btn btn-success reports-print-button" data-print-report>
                <?= studentDashboardIcon('report') ?> Print report
            </button>
        </div>

        <?php if (($_GET['success'] ?? '') === 'medicine_added'): ?>
            <div class="alert alert-success">Medicine added to inventory.</div>
        <?php elseif (($_GET['success'] ?? '') === 'stock_added'): ?>
            <div class="alert alert-success">Medicine stock updated.</div>
        <?php elseif (($_GET['success'] ?? '') === 'medicine_deleted'): ?>
            <div class="alert alert-success">Medicine deleted from inventory.</div>
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

        <?php if ($featuredMedicines): ?>
            <section class="medicine-showcase" aria-label="Medicine stock overview">
                <?php foreach ($featuredMedicines as $index => $medicine): ?>
                    <article class="medicine-showcase-card medicine-tone-<?= ($index % 4) + 1 ?>">
                        <span><img class="reports-medicine-card-icon" src="../../public/assets/images/Group%2083.svg" alt=""></span>
                        <div>
                            <h3><?= htmlspecialchars($medicine['Name']) ?></h3>
                            <strong><?= (int) $medicine['Stock'] ?></strong>
                            <small>in stock</small>
                        </div>
                    </article>
                <?php endforeach; ?>
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
                            <input id="medicine-name" type="text" name="name" value="<?= $medicineDialogHasError ? htmlspecialchars((string) ($_POST['name'] ?? '')) : '' ?>" maxlength="120" autocomplete="off" required autofocus>
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
                                    <form method="POST" class="delete-form reports-delete-form" data-delete-title="Delete medicine" data-delete-message="Delete <?= htmlspecialchars($medicine['Name']) ?> from the medicine inventory? This action cannot be undone.">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                        <input type="hidden" name="action" value="delete_medicine">
                                        <input type="hidden" name="medicine_id" value="<?= (int) $medicine['MedicineID'] ?>">
                                        <button type="submit" class="btn reports-delete-button" title="Delete <?= htmlspecialchars($medicine['Name']) ?>">
                                            <?= studentDashboardIcon('trash') ?> <span>Delete</span>
                                        </button>
                                    </form>
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
                        <tr><th>Patient name</th><th>Concern</th><th>Medicine given</th><th>Date</th><th>Consulted by</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($visitReports): ?>
                        <?php foreach ($visitReports as $visit): ?>
                            <tr>
                                <td data-label="Patient"><strong><?= htmlspecialchars(trim(($visit['FirstName'] ?? '') . ' ' . ($visit['LastName'] ?? ''))) ?></strong><small>#<?= (int) $visit['StudentID'] ?></small></td>
                                <td data-label="Concern"><?= htmlspecialchars($visit['Complaint']) ?></td>
                                <td data-label="Medicine given"><?= htmlspecialchars($visit['MedicineGiven'] ?: '—') ?></td>
                                <td data-label="Date"><?= date('M d, Y', strtotime($visit['CreatedAt'])) ?><small><?= date('g:i A', strtotime($visit['CreatedAt'])) ?></small></td>
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
        </section>
    </div>

    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js"></script>
</body>
</html>