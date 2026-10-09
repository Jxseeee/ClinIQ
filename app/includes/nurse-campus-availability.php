<?php
// "Nurses on duty by campus" panel for the student dashboard (admins manage duty on the Nurses page).
$nurseAvailability = $nurseAvailability ?? fetchNurseAvailabilityByCampus($pdo);
$campusesWithNurse = count(array_filter(
    $nurseAvailability,
    static fn(array $nurses, string $campus): bool => $nurses !== [] && $campus !== 'Campus not set',
    ARRAY_FILTER_USE_BOTH
));
$totalOnDutyNurses = array_sum(array_map('count', $nurseAvailability));
$campusCount = count(clinicCampuses());
?>
<section class="student-announcements-panel nurse-availability-panel" aria-labelledby="nurse-availability-title">
    <div class="dashboard-section-title">
        <span><?= studentDashboardIcon('pin') ?></span>
        <h2 id="nurse-availability-title">Nurses on duty by campus</h2>
    </div>
    <p class="nurse-availability-summary <?= $totalOnDutyNurses > 0 ? 'is-available' : 'is-unavailable' ?>">
        <?php if ($totalOnDutyNurses === 0): ?>
            No nurses are on duty right now.
        <?php else: ?>
            <?= $campusesWithNurse ?> of <?= $campusCount ?> campus<?= $campusCount === 1 ? '' : 'es' ?> <?= $campusesWithNurse === 1 ? 'has' : 'have' ?> a nurse on duty
            &middot; <?= $totalOnDutyNurses ?> nurse<?= $totalOnDutyNurses === 1 ? '' : 's' ?> available
        <?php endif; ?>
    </p>

    <div class="nurse-campus-grid">
        <?php foreach ($nurseAvailability as $campus => $campusNurses): ?>
            <?php if ($campus === 'Campus not set' && $campusNurses === []) continue; ?>
            <?php $hasNurse = $campusNurses !== []; ?>
            <article class="nurse-campus-tile <?= $hasNurse ? 'is-available' : 'is-unavailable' ?>">
                <header>
                    <span class="nurse-campus-tile-icon"><?= studentDashboardIcon('pin') ?></span>
                    <h3><?= htmlspecialchars($campus) ?></h3>
                </header>
                <span class="nurse-campus-state">
                    <span class="nurse-campus-dot" aria-hidden="true"></span>
                    <?= $hasNurse ? 'Nurse available' : 'No nurse on duty' ?>
                </span>
                <?php if ($hasNurse): ?>
                    <ul class="nurse-campus-list">
                        <?php foreach ($campusNurses as $campusNurse): ?>
                            <li>
                                <?= studentDashboardIcon('nurse') ?>
                                <span><strong><?= htmlspecialchars($campusNurse['FullName']) ?></strong><small><?= htmlspecialchars($campusNurse['Position']) ?></small></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="nurse-campus-empty">Visit another campus clinic or message the clinic for help.</p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
