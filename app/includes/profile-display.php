<?php $d = fn($val) => displayValue($val); ?>

<div class="card form-section">
    <h2>Student Information</h2>
    <dl class="detail-grid detail-grid-wide">
        <dt>Student ID</dt>      <dd><?= $d($student['StudentID']) ?></dd>
        <dt>Name</dt>             <dd><?= $d($student['LastName']) ?>, <?= $d($student['FirstName']) ?> <?= htmlspecialchars($student['MiddleName'] ?? '') ?></dd>
        <dt>Preferred Name</dt>   <dd><?= $d($student['PreferredName']) ?></dd>
        <dt>Course</dt>           <dd><?= $d($student['Course']) ?></dd>
        <dt>Year Level</dt>       <dd><?= $d($student['YearLevel']) ?></dd>
        <dt>Department</dt>       <dd><?= $d($student['Department']) ?></dd>
        <dt>Date of Birth</dt>    <dd><?= $student['DateOfBirth'] ? date('M d, Y', strtotime($student['DateOfBirth'])) : '—' ?></dd>
        <dt>Gender</dt>           <dd><?= $d($student['Gender']) ?></dd>
        <dt>Citizenship</dt>      <dd><?= $d($student['Citizenship']) ?></dd>
        <dt>Height</dt>           <dd><?= $d($student['Height']) ?><?= $student['Height'] ? ' m' : '' ?></dd>
        <dt>Weight</dt>           <dd><?= $d($student['Weight']) ?><?= $student['Weight'] ? ' kg' : '' ?></dd>
        <dt>Email</dt>            <dd><?= $d($student['Email']) ?></dd>
        <dt>Phone</dt>            <dd><?= $d($student['Phone']) ?></dd>
        <dt>Home Telephone</dt>   <dd><?= $d($student['HomeTelephone']) ?></dd>
        <dt>Street Address</dt>   <dd><?= $d($student['StreetAddress']) ?></dd>
        <dt>Municipality</dt>     <dd><?= $d($student['Municipality']) ?></dd>
        <dt>City</dt>             <dd><?= $d($student['City']) ?></dd>
    </dl>
</div>

<?php if (!empty($g1)): ?>
<div class="card form-section">
    <h2>Parent / Guardian Information</h2>
    <div class="guardian-columns">
        <div class="guardian-col">
            <h3>Guardian 1<?= ($g1['Relationship'] ?? '') ? ' (' . htmlspecialchars($g1['Relationship']) . ')' : '' ?></h3>
            <dl class="detail-grid">
                <dt>Name</dt>          <dd><?= $d($g1['LastName']) ?>, <?= $d($g1['FirstName']) ?> <?= htmlspecialchars($g1['MiddleName'] ?? '') ?></dd>
                <dt>Office Phone</dt>  <dd><?= $d($g1['OfficePhone']) ?></dd>
                <dt>Mobile</dt>        <dd><?= $d($g1['MobileNumber']) ?></dd>
                <dt>Email</dt>         <dd><?= $d($g1['EmailAddress']) ?></dd>
                <?php if (!empty($g1['EmergencyContactName'])): ?>
                    <dt>Emergency Contact</dt> <dd><?= $d($g1['EmergencyContactName']) ?> (<?= $d($g1['EmergencyContactMobile']) ?>)</dd>
                <?php endif; ?>
            </dl>
        </div>
        <?php if (!empty($g2)): ?>
        <div class="guardian-col">
            <h3>Guardian 2<?= ($g2['Relationship'] ?? '') ? ' (' . htmlspecialchars($g2['Relationship']) . ')' : '' ?></h3>
            <dl class="detail-grid">
                <dt>Name</dt>          <dd><?= $d($g2['LastName']) ?>, <?= $d($g2['FirstName']) ?> <?= htmlspecialchars($g2['MiddleName'] ?? '') ?></dd>
                <dt>Office Phone</dt>  <dd><?= $d($g2['OfficePhone']) ?></dd>
                <dt>Mobile</dt>        <dd><?= $d($g2['MobileNumber']) ?></dd>
                <dt>Email</dt>         <dd><?= $d($g2['EmailAddress']) ?></dd>
                <?php if (!empty($g2['EmergencyContactName'])): ?>
                    <dt>Emergency Contact</dt> <dd><?= $d($g2['EmergencyContactName']) ?> (<?= $d($g2['EmergencyContactMobile']) ?>)</dd>
                <?php endif; ?>
            </dl>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($medHistory)): ?>
<div class="card form-section">
    <h2>Past Medical History</h2>
    <table class="compact-table">
        <thead><tr><th>Illness</th><th>Date</th><th>Age</th></tr></thead>
        <tbody>
            <?php foreach ($medHistory as $mh): ?>
            <tr>
                <td><?= htmlspecialchars($mh['Illness']) ?></td>
                <td><?= htmlspecialchars($mh['DiagnosisDate'] ?? '—') ?></td>
                <td><?= htmlspecialchars($mh['DiagnosisAge'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (!empty($student['MedicationsRegular'])): ?>
        <p style="margin-top:14px;"><strong>Regular Medications:</strong> <?= htmlspecialchars($student['MedicationsRegular']) ?></p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($student['AllergyFood'] || $student['AllergyMedicine'] || $student['AllergyOthers']): ?>
<div class="card form-section">
    <h2>Allergies</h2>
    <dl class="detail-grid">
        <dt>Food</dt>     <dd><?= $d($student['AllergyFood']) ?></dd>
        <dt>Medicine</dt> <dd><?= $d($student['AllergyMedicine']) ?></dd>
        <dt>Others</dt>   <dd><?= $d($student['AllergyOthers']) ?></dd>
    </dl>
</div>
<?php endif; ?>
