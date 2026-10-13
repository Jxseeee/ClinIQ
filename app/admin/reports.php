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

        if ($reportAction === 'update_visit_status') {
            updateClinicVisitStatus(
                $pdo,
                (int) ($_POST['visit_id'] ?? 0),
                (string) ($_POST['status'] ?? ''),
                (string) ($_POST['follow_up_date'] ?? '')
            );
            // Return to the same filtered view of the visit reports.
            $keepFilters = [];
            foreach (['medicine_search', 'stock_filter', 'visit_search', 'visit_status', 'visit_date', 'visit_page'] as $filterKey) {
                if (is_string($_GET[$filterKey] ?? null) && trim($_GET[$filterKey]) !== '') {
                    $keepFilters[$filterKey] = trim($_GET[$filterKey]);
                }
            }
            header('Location: reports.php?' . http_build_query($keepFilters + ['success' => 'visit_status']) . '#visit-reports');
            exit;
        }

        $reportErrors[] = 'Invalid report action.';
    } catch (PDOException $error) {
        $reportErrors[] = match (true) {
            $reportAction === 'add_medicine' && $error->getCode() === '23000' => 'A medicine with that name already exists.',
            $reportAction === 'update_visit_status' => 'The visit status could not be updated.',
            default => 'The medicine inventory could not be updated.',
        };
    } catch (InvalidArgumentException | RuntimeException $error) {
        $reportErrors[] = $error->getMessage();
    }
}

$medicineDialogHasError = $reportErrors !== [] && $reportAction === 'add_medicine';
$stockDialogHasError = $reportErrors !== [] && $reportAction === 'add_stock';
$visitStatusDialogHasError = $reportErrors !== [] && $reportAction === 'update_visit_status';

$queryString = static fn(string $key): string => is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : '';
$medicineSearch = $queryString('medicine_search');
$lowStockOnly = $queryString('stock_filter') === 'low';
$visitSearch = mb_substr($queryString('visit_search'), 0, 100);
$visitStatus = in_array($queryString('visit_status'), ['open', 'completed', 'follow-up'], true)
    ? $queryString('visit_status')
    : '';

// Visit reports: an optional visit_date (Y-m-d) shows only that day's visits; without it every visit is listed.
$today = new DateTimeImmutable('today');
$visitDateValue = $queryString('visit_date');
$visitDate = null;
$visitDateError = '';
if ($visitDateValue !== '') {
    $parsedVisitDate = DateTimeImmutable::createFromFormat('!Y-m-d', $visitDateValue);
    if (!$parsedVisitDate || $parsedVisitDate->format('Y-m-d') !== $visitDateValue) {
        $visitDateError = 'Please choose a valid visit date. Showing visits from all dates instead.';
    } elseif ($parsedVisitDate > $today) {
        $visitDateError = 'The visit date cannot be in the future. Showing visits from all dates instead.';
    } else {
        $visitDate = $parsedVisitDate;
    }
}
$visitFiltersActive = $visitSearch !== '' || $visitStatus !== '' || $visitDate !== null;

// Builds a Reports URL that keeps the current filters, applying $changes (null removes a key).
$reportsUrl = static function (array $changes) use ($queryString, $medicineSearch, $lowStockOnly, $visitSearch, $visitStatus, $visitDate): string {
    $params = array_filter([
        'medicine_search' => $medicineSearch,
        'stock_filter' => $lowStockOnly ? 'low' : '',
        'visit_search' => $visitSearch,
        'visit_status' => $visitStatus,
        'visit_date' => $visitDate ? $visitDate->format('Y-m-d') : '',
        'visit_page' => ctype_digit($queryString('visit_page')) ? $queryString('visit_page') : '',
    ], static fn(string $value): bool => $value !== '');
    foreach ($changes as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
    }
    $query = http_build_query($params);
    return 'reports.php' . ($query !== '' ? '?' . $query : '') . '#visit-reports';
};

$medicineSummary = medicineInventorySummary($pdo);
$allMedicines = fetchMedicines($pdo);
$medicines = fetchMedicines($pdo, $medicineSearch, $lowStockOnly);
$archivedMedicines = fetchMedicines($pdo, '', false, true);
$medicineArchiveInstalled = medicineArchiveInstalled($pdo);
$existingMedicineNames = array_merge(
    array_map(static fn(array $medicine): array => ['name' => $medicine['Name'], 'archived' => false], $allMedicines),
    array_map(static fn(array $medicine): array => ['name' => $medicine['Name'], 'archived' => true], $archivedMedicines)
);
// Filtered in SQL and paged so the page stays fast as visit history grows.
$visitFilters = ['search' => $visitSearch, 'status' => $visitStatus, 'date' => $visitDate ? $visitDate->format('Y-m-d') : ''];
$visitPerPage = 50;
$visitTotal = countClinicVisits($pdo, null, $visitFilters);
$visitPageCount = max(1, (int) ceil($visitTotal / $visitPerPage));
$visitPage = min($visitPageCount, max(1, (int) $queryString('visit_page')));
$visitReports = fetchClinicVisits($pdo, null, $visitPerPage, $visitFilters, ($visitPage - 1) * $visitPerPage);
$visitStatusCounts = countClinicVisitsByStatus($pdo, $visitFilters);
$visitWord = static fn(int $count): string => $count === 1 ? 'visit' : 'visits';

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

        <?php if ($reportErrors && !$medicineDialogHasError && !$stockDialogHasError && !$visitStatusDialogHasError): ?>
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

        <section class="reports-section medicine-inventory-section" data-live-scope>
            <div class="reports-section-heading">
                <div>
                    <h2>Medicine inventory</h2>
                    <p>Manage available clinic supplies and low-stock thresholds.</p>
                </div>
                <form method="GET" class="reports-filter-form" data-live-filter="[data-medicine-results]" data-live-params="medicine_search,stock_filter">
                    <?php foreach (['visit_search' => $visitSearch, 'visit_status' => $visitStatus, 'visit_date' => $visitDate ? $visitDate->format('Y-m-d') : ''] as $keepKey => $keepValue): ?>
                        <?php if ($keepValue !== ''): ?><input type="hidden" name="<?= $keepKey ?>" value="<?= htmlspecialchars($keepValue) ?>"><?php endif; ?>
                    <?php endforeach; ?>
                    <input type="search" name="medicine_search" value="<?= htmlspecialchars($medicineSearch) ?>" placeholder="Search medicine" aria-label="Search medicine">
                    <select name="stock_filter" aria-label="Stock filter">
                        <option value="">All stock</option>
                        <option value="low" <?= $lowStockOnly ? 'selected' : '' ?>>Low stock</option>
                    </select>
                </form>
            </div>
            <div class="alert alert-danger" data-live-filter-error hidden></div>

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

            <div class="reports-table-wrap" data-medicine-results>
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

        <section class="reports-section visit-reports-section" id="visit-reports" data-live-scope>
            <div class="reports-section-heading">
                <div>
                    <h2>Visit reports</h2>
                    <p data-visit-subtitle>
                        <?= $visitDate ? 'Visits on ' . htmlspecialchars($visitDate->format('l, F j, Y')) : 'All clinic visits, newest first' ?>
                        &middot; <?= number_format($visitTotal) ?> <?= $visitWord($visitTotal) ?><?= $visitSearch !== '' || $visitStatus !== '' ? ' matching your filters' : '' ?>
                    </p>
                </div>
                <form method="GET" action="reports.php#visit-reports" class="reports-filter-form visit-filter-form"
                      data-live-filter="[data-visit-subtitle],[data-visit-actions],[data-visit-results]"
                      data-live-params="visit_search,visit_status,visit_date,visit_page">
                    <?php foreach (['medicine_search' => $medicineSearch, 'stock_filter' => $lowStockOnly ? 'low' : ''] as $keepKey => $keepValue): ?>
                        <?php if ($keepValue !== ''): ?><input type="hidden" name="<?= $keepKey ?>" value="<?= htmlspecialchars($keepValue) ?>"><?php endif; ?>
                    <?php endforeach; ?>
                    <input type="search" name="visit_search" value="<?= htmlspecialchars($visitSearch) ?>" placeholder="Search patient or concern" aria-label="Search patient or concern" maxlength="100">
                    <select name="visit_status" aria-label="Visit status filter">
                        <option value="">All statuses</option>
                        <option value="open" <?= $visitStatus === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="completed" <?= $visitStatus === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="follow-up" <?= $visitStatus === 'follow-up' ? 'selected' : '' ?>>Follow-up</option>
                    </select>
                    <label class="sr-only" for="visit-date">Visit date (leave empty for all dates)</label>
                    <input type="date" id="visit-date" name="visit_date"
                           value="<?= $visitDate ? htmlspecialchars($visitDate->format('Y-m-d')) : '' ?>"
                           min="2000-01-01" max="<?= $today->format('Y-m-d') ?>"
                           title="Choose a date to show only that day's visits">
                    <span class="visit-filter-actions" data-visit-actions>
                        <?php if (!$visitDate || $visitDate != $today): ?>
                            <a href="<?= htmlspecialchars($reportsUrl(['visit_date' => $today->format('Y-m-d'), 'visit_page' => null])) ?>" class="btn btn-secondary" data-live-link>Today</a>
                        <?php endif; ?>
                        <?php if ($visitFiltersActive): ?>
                            <a href="<?= htmlspecialchars($reportsUrl(['visit_search' => null, 'visit_status' => null, 'visit_date' => null, 'visit_page' => null])) ?>" class="btn btn-secondary" data-live-link>Show all</a>
                        <?php endif; ?>
                    </span>
                </form>
            </div>
            <div class="alert alert-danger visit-date-alert" data-live-filter-error hidden></div>
            <?php if ($queryString('success') === 'visit_status'): ?>
                <div class="alert alert-success visit-date-alert">Visit status updated.</div>
            <?php endif; ?>

            <?php
                // When saving failed, reopen the dialog with what the admin had chosen.
                $statusDialogStatus = $visitStatusDialogHasError ? (string) ($_POST['status'] ?? 'completed') : 'completed';
                $statusDialogCurrent = $visitStatusDialogHasError && in_array($_POST['visit_current_status'] ?? '', ['open', 'follow-up'], true)
                    ? (string) $_POST['visit_current_status']
                    : 'open';
            ?>
            <dialog class="reports-dialog" id="visit-status-dialog" aria-labelledby="visit-status-dialog-title" aria-describedby="visit-status-dialog-description" <?= $visitStatusDialogHasError ? 'data-report-dialog-auto-open' : '' ?>>
                <div class="reports-dialog-shell">
                    <div class="reports-dialog-heading">
                        <span class="reports-dialog-icon is-stock" aria-hidden="true"><?= studentDashboardIcon('medical') ?></span>
                        <div>
                            <h2 id="visit-status-dialog-title">Change visit status</h2>
                            <p id="visit-status-dialog-description">Open and follow-up visits can be updated. Once a visit is completed it can no longer be changed.</p>
                        </div>
                        <button type="button" class="reports-dialog-close" data-report-dialog-close aria-label="Close change status dialog" title="Close">
                            <?= studentDashboardIcon('close') ?>
                        </button>
                    </div>

                    <form method="POST" class="reports-tool-form reports-dialog-form reports-stock-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <input type="hidden" name="action" value="update_visit_status">
                        <input type="hidden" name="visit_id" value="<?= $visitStatusDialogHasError ? (int) ($_POST['visit_id'] ?? 0) : 0 ?>" data-visit-status-id>
                        <input type="hidden" name="visit_patient" value="<?= $visitStatusDialogHasError ? htmlspecialchars((string) ($_POST['visit_patient'] ?? '')) : '' ?>" data-visit-status-patient-input>
                        <input type="hidden" name="visit_current_status" value="<?= htmlspecialchars($statusDialogCurrent) ?>" data-visit-status-current-input>
                        <div class="alert alert-danger reports-dialog-alert" data-visit-status-error <?= $visitStatusDialogHasError ? '' : 'hidden' ?>><?= $visitStatusDialogHasError ? htmlspecialchars($reportErrors[0]) : '' ?></div>

                        <p class="visit-status-dialog-summary reports-field-wide">
                            <span>Patient: <strong data-visit-status-patient><?= $visitStatusDialogHasError ? htmlspecialchars((string) ($_POST['visit_patient'] ?? '')) : '' ?></strong></span>
                            <span>Current status: <span class="visit-status visit-status-<?= htmlspecialchars($statusDialogCurrent) ?>" data-visit-status-current><?= $statusDialogCurrent === 'follow-up' ? 'Follow Up' : 'Open' ?></span></span>
                        </p>

                        <label class="reports-form-field reports-field-wide" for="visit-status-select">
                            <span>New status</span>
                            <select id="visit-status-select" name="status" required autofocus data-visit-status-select>
                                <?php foreach (['completed' => 'Completed', 'follow-up' => 'Follow-up', 'open' => 'Open'] as $statusValue => $statusLabel): ?>
                                    <option value="<?= $statusValue ?>" <?= $statusDialogStatus === $statusValue ? 'selected' : '' ?>><?= $statusLabel ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="reports-form-field reports-field-wide" for="visit-status-follow-up" data-visit-status-date-field <?= $statusDialogStatus === 'follow-up' ? '' : 'hidden' ?>>
                            <span>Follow-up date</span>
                            <input type="date" id="visit-status-follow-up" name="follow_up_date"
                                   value="<?= $visitStatusDialogHasError ? htmlspecialchars((string) ($_POST['follow_up_date'] ?? '')) : '' ?>"
                                   min="<?= $today->format('Y-m-d') ?>" max="<?= $today->modify('+30 days')->format('Y-m-d') ?>"
                                   <?= $statusDialogStatus === 'follow-up' ? 'required' : 'disabled' ?>
                                   data-visit-status-date>
                        </label>
                        <p class="visit-status-dialog-note reports-field-wide">Choosing <strong>Completed</strong> is final: the visit's status can't be changed after that.</p>

                        <div class="reports-dialog-actions">
                            <button type="button" class="btn btn-secondary" data-report-dialog-close>Cancel</button>
                            <button type="submit" class="btn btn-success">Save status</button>
                        </div>
                    </form>
                </div>
            </dialog>

            <div class="visit-report-results" data-visit-results>
            <?php if ($visitDateError !== ''): ?>
                <div class="alert alert-danger visit-date-alert"><?= htmlspecialchars($visitDateError) ?></div>
            <?php endif; ?>

            <div class="visit-report-summary" aria-label="Visit totals for the current view">
                <span class="visit-summary-total"><strong><?= number_format($visitTotal) ?></strong> <?= $visitWord($visitTotal) ?></span>
                <span class="visit-status visit-status-completed"><?= number_format($visitStatusCounts['completed']) ?> Completed</span>
                <span class="visit-status visit-status-follow-up"><?= number_format($visitStatusCounts['follow-up']) ?> Follow Up</span>
                <span class="visit-status visit-status-open"><?= number_format($visitStatusCounts['open']) ?> Open</span>
            </div>

            <?php if ($visitDate): ?>
                <nav class="visit-day-nav" aria-label="Change visit date">
                    <a href="<?= htmlspecialchars($reportsUrl(['visit_date' => $visitDate->modify('-1 day')->format('Y-m-d'), 'visit_page' => null])) ?>" class="btn btn-secondary" data-live-link>
                        <?= studentDashboardIcon('chevron-left') ?> Previous day
                    </a>
                    <span class="visit-day-label"><?= studentDashboardIcon('calendar') ?><?= htmlspecialchars($visitDate->format('F j, Y')) ?></span>
                    <?php if ($visitDate < $today): ?>
                        <a href="<?= htmlspecialchars($reportsUrl(['visit_date' => $visitDate->modify('+1 day')->format('Y-m-d'), 'visit_page' => null])) ?>" class="btn btn-secondary" data-live-link>
                            Next day <?= studentDashboardIcon('chevron-right') ?>
                        </a>
                    <?php else: ?>
                        <span class="btn btn-secondary is-disabled" aria-disabled="true">Next day <?= studentDashboardIcon('chevron-right') ?></span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

            <div class="reports-table-wrap">
                <table class="reports-table visit-report-table">
                    <thead>
                        <tr><th>Patient name</th><th>Concern &amp; care</th><th>Medicine given</th><th>Time</th><th>Consulted by</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php if ($visitReports): ?>
                        <?php $currentVisitDay = null; ?>
                        <?php foreach ($visitReports as $visit): ?>
                            <?php
                                $visitTime = strtotime($visit['TimeIn'] ?? $visit['CreatedAt']);
                                $visitDay = date('Y-m-d', $visitTime);
                                $isStudentVisit = ($visit['PersonType'] ?? 'student') === 'student';
                                $visitDetails = array_filter([
                                    'Vitals' => trim((string) ($visit['Vitals'] ?? '')),
                                    'Assessment' => trim((string) ($visit['Assessment'] ?? '')),
                                    'Treatment' => trim((string) ($visit['Treatment'] ?? '')),
                                    'Disposition' => trim((string) ($visit['Disposition'] ?? '')),
                                    'Follow-up' => !empty($visit['FollowUpDate']) ? date('M j, Y', strtotime($visit['FollowUpDate'])) : '',
                                ], static fn(string $value): bool => $value !== '');
                            ?>
                            <?php if (!$visitDate && $visitDay !== $currentVisitDay): ?>
                                <?php $currentVisitDay = $visitDay; ?>
                                <tr class="visit-day-row">
                                    <th colspan="6" scope="colgroup">
                                        <?= studentDashboardIcon('calendar') ?>
                                        <a href="<?= htmlspecialchars($reportsUrl(['visit_date' => $visitDay, 'visit_page' => null])) ?>" title="Show only this day's visits" data-live-link><?= htmlspecialchars(date('l, F j, Y', $visitTime)) ?></a>
                                    </th>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <td data-label="Patient">
                                    <div>
                                        <?php if ($isStudentVisit): ?>
                                            <strong><a class="visit-patient-link" href="view-student.php?id=<?= (int) $visit['PersonID'] ?>"><?= htmlspecialchars(trim(($visit['FirstName'] ?? '') . ' ' . ($visit['LastName'] ?? ''))) ?></a></strong>
                                            <small>Student #<?= (int) $visit['PersonID'] ?></small>
                                        <?php else: ?>
                                            <strong><?= htmlspecialchars(trim((string) ($visit['FirstName'] ?? ''))) ?></strong>
                                            <small>Staff</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="Concern">
                                    <div>
                                        <span class="visit-complaint" title="<?= htmlspecialchars($visit['Complaint']) ?>"><?= htmlspecialchars(mb_strimwidth($visit['Complaint'], 0, 200, '…')) ?></span>
                                        <?php foreach ($visitDetails as $detailLabel => $detailValue): ?>
                                            <small title="<?= htmlspecialchars($detailValue) ?>"><b><?= htmlspecialchars($detailLabel) ?>:</b> <?= htmlspecialchars(mb_strimwidth($detailValue, 0, 140, '…')) ?></small>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td data-label="Medicine given">
                                    <div>
                                        <?= htmlspecialchars($visit['MedicineGiven'] ?: '—') ?>
                                        <?php if (!empty($visit['MedicineDosage'])): ?><small>Dosage: <?= htmlspecialchars($visit['MedicineDosage']) ?></small><?php endif; ?>
                                        <?php if (!empty($visit['MedicineQuantity'])): ?><small>Quantity: <?= (int) $visit['MedicineQuantity'] ?></small><?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="Time">
                                    <div>
                                        <?php if ($visitDate): ?><small><?= htmlspecialchars(date('M j, Y', $visitTime)) ?></small><?php endif; ?>
                                        <?= visitTimesHtml($visit) ?>
                                    </div>
                                </td>
                                <td data-label="Consulted by"><?= htmlspecialchars($visit['AdminName'] ?: '—') ?></td>
                                <td data-label="Status">
                                    <div class="visit-status-cell">
                                        <?php if ($visit['Status'] === 'completed'): ?>
                                            <span class="visit-status visit-status-completed" title="Completed visits are final">Completed</span>
                                        <?php elseif ((int) ($visit['IsInClinic'] ?? 0) === 1): ?>
                                            <span class="visit-status visit-status-<?= htmlspecialchars($visit['Status']) ?>" title="The status can be edited after the patient is checked out"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $visit['Status']))) ?></span>
                                        <?php else: ?>
                                            <span class="visit-status visit-status-<?= htmlspecialchars($visit['Status']) ?>"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $visit['Status']))) ?></span>
                                            <button type="button" class="visit-status-edit"
                                                    data-report-dialog-open="visit-status-dialog"
                                                    data-visit-id="<?= (int) $visit['VisitID'] ?>"
                                                    data-visit-patient="<?= htmlspecialchars(trim(($visit['FirstName'] ?? '') . ' ' . ($visit['LastName'] ?? '')), ENT_QUOTES) ?>"
                                                    data-visit-status="<?= htmlspecialchars($visit['Status']) ?>"
                                                    data-visit-follow-up="<?= htmlspecialchars((string) ($visit['FollowUpDate'] ?? '')) ?>"
                                                    aria-haspopup="dialog" aria-controls="visit-status-dialog"
                                                    aria-label="Edit status for <?= htmlspecialchars(trim(($visit['FirstName'] ?? '') . ' ' . ($visit['LastName'] ?? '')), ENT_QUOTES) ?>">
                                                Edit
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="reports-empty-cell">
                                <?php if ($visitDate && $visitSearch === '' && $visitStatus === ''): ?>
                                    No clinic visits were recorded on <?= htmlspecialchars($visitDate->format('F j, Y')) ?>.
                                <?php elseif ($visitFiltersActive): ?>
                                    No clinic visits match the selected filters.
                                <?php else: ?>
                                    No clinic visits have been recorded yet.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($visitPageCount > 1): ?>
                <nav class="audit-pagination" aria-label="Visit report pages">
                    <?php if ($visitPage > 1): ?>
                        <a href="<?= htmlspecialchars($reportsUrl(['visit_page' => $visitPage > 2 ? $visitPage - 1 : null])) ?>" class="btn btn-secondary" data-live-link><?= studentDashboardIcon('chevron-left') ?> Newer</a>
                    <?php else: ?>
                        <span class="btn btn-secondary is-disabled" aria-disabled="true"><?= studentDashboardIcon('chevron-left') ?> Newer</span>
                    <?php endif; ?>
                    <span class="audit-page-status">Page <?= $visitPage ?> of <?= $visitPageCount ?></span>
                    <?php if ($visitPage < $visitPageCount): ?>
                        <a href="<?= htmlspecialchars($reportsUrl(['visit_page' => $visitPage + 1])) ?>" class="btn btn-secondary" data-live-link>Older <?= studentDashboardIcon('chevron-right') ?></a>
                    <?php else: ?>
                        <span class="btn btn-secondary is-disabled" aria-disabled="true">Older <?= studentDashboardIcon('chevron-right') ?></span>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
            </div>
        </section>
    </div>

    <?php include __DIR__ . '/../includes/admin-dashboard-end.php'; ?>
    <script src="../../public/assets/js/main.js?v=<?= assetVersion('js/main.js') ?>"></script>
</body>
</html>