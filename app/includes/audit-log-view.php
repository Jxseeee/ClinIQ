<?php
// Shared Audit Log filters, table, and pagination for the admin and student pages.
// Expects: $auditViewerRole, $auditViewerId, $auditFilters, $auditEntries, $auditTotal, $auditPage, $auditPageCount.
$auditCategoryLabels = auditCategories();
$auditIsAdmin = $auditViewerRole === 'admin';
$auditPageUrl = static function (int $page) use ($auditFilters): string {
    return '?' . http_build_query(array_filter($auditFilters + ['page' => $page], static fn($value): bool => $value !== '' && $value !== null));
};
$auditRoleLabel = static fn(?string $role): string => match ($role) {
    'admin' => 'Administrator',
    'student' => 'Student',
    default => 'Not signed in',
};
$auditSelectedStudentId = $auditSelectedStudentId ?? null;
?>
<section class="reports-section audit-log-section" data-live-scope>
    <div class="reports-section-heading">
        <div>
            <h2><?= $auditIsAdmin ? 'Activity history' : 'Your account activity' ?></h2>
            <p>
                <?= $auditIsAdmin
                    ? 'Every important change across the clinic system, newest first.'
                    : 'Sign-ins, password and account changes, clinic form updates, and actions the clinic took on your account.' ?>
            </p>
        </div>
        <span class="audit-total" data-audit-total><strong><?= number_format($auditTotal) ?></strong> <?= $auditTotal === 1 ? 'entry' : 'entries' ?></span>
    </div>

    <div data-audit-banner>
        <?php if ($auditIsAdmin && $auditSelectedStudentId !== null): ?>
            <div class="audit-viewing-student">
                <?= studentDashboardIcon('user') ?>
                <span>Showing the audit log of <strong><?= htmlspecialchars($auditStudentAccounts[$auditSelectedStudentId] ?? 'Student') ?></strong> (#<?= $auditSelectedStudentId ?>)</span>
                <a href="audit-log.php" class="btn btn-secondary" data-live-link>Show all activity</a>
            </div>
        <?php endif; ?>
    </div>

    <form method="GET" class="reports-filter-form audit-filter-form"
          data-live-filter="[data-audit-total],[data-audit-banner],[data-audit-results]"
          data-live-params="search,category,account,date_from,date_to,page">
        <label class="audit-filter-field audit-filter-search">
            <span>Search</span>
            <input type="search" name="search" value="<?= htmlspecialchars($auditFilters['search']) ?>" placeholder="<?= $auditIsAdmin ? 'Name, activity, or IP address' : 'Activity or IP address' ?>">
        </label>
        <label class="audit-filter-field">
            <span>Category</span>
            <select name="category">
                <option value="">All categories</option>
                <?php foreach ($auditCategoryLabels as $categoryKey => $categoryLabel): ?>
                    <?php if (!$auditIsAdmin && in_array($categoryKey, ['inventory', 'nurses', 'announcements'], true)) continue; ?>
                    <option value="<?= htmlspecialchars($categoryKey) ?>" <?= $auditFilters['category'] === $categoryKey ? 'selected' : '' ?>><?= htmlspecialchars($categoryLabel) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php if ($auditIsAdmin): ?>
            <label class="audit-filter-field audit-filter-account">
                <span>Show</span>
                <select name="account">
                    <option value="">All activity</option>
                    <option value="mine" <?= $auditFilters['account'] === 'mine' ? 'selected' : '' ?>>My account only</option>
                    <?php if (!empty($auditStudentAccounts)): ?>
                        <optgroup label="A specific student">
                            <?php foreach ($auditStudentAccounts as $accountId => $accountName): ?>
                                <option value="student:<?= (int) $accountId ?>" <?= $auditSelectedStudentId === (int) $accountId ? 'selected' : '' ?>>#<?= (int) $accountId ?> &mdash; <?= htmlspecialchars($accountName) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </label>
        <?php endif; ?>
        <label class="audit-filter-field">
            <span>From</span>
            <input type="date" name="date_from" value="<?= htmlspecialchars($auditFilters['date_from']) ?>" min="2000-01-01" max="<?= date('Y-m-d') ?>">
        </label>
        <label class="audit-filter-field">
            <span>To</span>
            <input type="date" name="date_to" value="<?= htmlspecialchars($auditFilters['date_to']) ?>" min="2000-01-01" max="<?= date('Y-m-d') ?>">
        </label>
        <div class="audit-filter-actions">
            <a href="audit-log.php" class="btn btn-secondary" data-live-link>Reset</a>
        </div>
    </form>
    <div class="alert alert-danger" data-live-filter-error hidden></div>

    <div data-audit-results>
    <div class="reports-table-wrap">
        <table class="reports-table audit-log-table">
            <thead>
                <tr>
                    <th>Date &amp; time</th>
                    <th>Activity</th>
                    <th>Performed by</th>
                    <?php if ($auditIsAdmin): ?><th>Account</th><?php endif; ?>
                    <th>IP address</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($auditEntries): ?>
                <?php foreach ($auditEntries as $entry): ?>
                    <?php
                        $entryTime = strtotime($entry['CreatedAt']);
                        $isSelf = $entry['ActorRole'] === $auditViewerRole && (int) $entry['ActorID'] === $auditViewerId;
                        $isAlert = $entry['Action'] === 'auth.login_failed';
                        // Students never see staff names or staff IP addresses.
                        $hideStaffDetails = !$auditIsAdmin && $entry['ActorRole'] === 'admin';
                    ?>
                    <tr class="<?= $isAlert ? 'is-alert' : '' ?>">
                        <td data-label="Date &amp; time">
                            <time datetime="<?= date('c', $entryTime) ?>">
                                <strong><?= date('M d, Y', $entryTime) ?></strong>
                                <small><?= date('g:i:s A', $entryTime) ?></small>
                            </time>
                        </td>
                        <td data-label="Activity">
                            <span class="audit-badge audit-badge-<?= htmlspecialchars($entry['Category']) ?>"><?= htmlspecialchars($auditCategoryLabels[$entry['Category']] ?? ucfirst($entry['Category'])) ?></span>
                            <p class="audit-description"><?= htmlspecialchars($entry['Description']) ?></p>
                        </td>
                        <td data-label="Performed by">
                            <?php if ($isSelf): ?>
                                <strong>You</strong>
                                <small><?= htmlspecialchars($auditRoleLabel($entry['ActorRole'])) ?></small>
                            <?php elseif ($hideStaffDetails): ?>
                                <strong>FCAT HealthWard staff</strong>
                                <small>School clinic</small>
                            <?php else: ?>
                                <strong><?= htmlspecialchars($entry['ActorName']) ?></strong>
                                <small><?= htmlspecialchars($auditRoleLabel($entry['ActorRole'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <?php if ($auditIsAdmin): ?>
                            <td data-label="Account">
                                <?php if ($entry['SubjectRole'] !== null): ?>
                                    <strong><?= htmlspecialchars($entry['SubjectName'] ?? 'Deleted account') ?></strong>
                                    <small><?= htmlspecialchars($auditRoleLabel($entry['SubjectRole'])) ?><?= $entry['SubjectRole'] === 'student' && $entry['SubjectID'] !== null ? ' #' . (int) $entry['SubjectID'] : '' ?></small>
                                <?php else: ?>
                                    <span class="audit-muted">Clinic-wide</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                        <td data-label="IP address"><span class="audit-ip"><?= $hideStaffDetails ? '—' : htmlspecialchars($entry['IPAddress'] ?? '—') ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="<?= $auditIsAdmin ? 5 : 4 ?>" class="reports-empty-cell">No activity matches the selected filters.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($auditPageCount > 1): ?>
        <nav class="audit-pagination" aria-label="Audit log pages">
            <?php if ($auditPage > 1): ?>
                <a href="<?= htmlspecialchars($auditPageUrl($auditPage - 1)) ?>" class="btn btn-secondary" data-live-link><?= studentDashboardIcon('chevron-left') ?> Newer</a>
            <?php else: ?>
                <span class="btn btn-secondary is-disabled" aria-disabled="true"><?= studentDashboardIcon('chevron-left') ?> Newer</span>
            <?php endif; ?>
            <span class="audit-page-status">Page <?= $auditPage ?> of <?= $auditPageCount ?></span>
            <?php if ($auditPage < $auditPageCount): ?>
                <a href="<?= htmlspecialchars($auditPageUrl($auditPage + 1)) ?>" class="btn btn-secondary" data-live-link>Older <?= studentDashboardIcon('chevron-right') ?></a>
            <?php else: ?>
                <span class="btn btn-secondary is-disabled" aria-disabled="true">Older <?= studentDashboardIcon('chevron-right') ?></span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
    </div>
</section>
