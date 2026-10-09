<?php

use Dompdf\Dompdf;
use Dompdf\Options;

function pdfValue($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function checkedBox(bool $checked): string
{
    return $checked ? '&#9745;' : '&#9744;';
}

function pdfDate(?string $value): string
{
    $timestamp = $value ? strtotime($value) : false;
    return $timestamp ? date('F j, Y', $timestamp) : '';
}

function pdfField(string $label, $value, int $colspan = 1): string
{
    return '<td' . ($colspan > 1 ? ' colspan="' . $colspan . '"' : '') . '>'
        . '<span class="lbl">' . pdfValue($label) . '</span>'
        . '<span class="val">' . (trim((string) $value) !== '' ? pdfValue($value) : '&nbsp;') . '</span>'
        . '</td>';
}

// The logo is a JPEG so Dompdf can embed it without the PHP GD extension.
function pdfLogoDataUri(): string
{
    static $uri = null;
    if ($uri === null) {
        $path = dirname(__DIR__, 2) . '/public/assets/images/fcat-clinic-logo-print.jpg';
        $uri = is_file($path) ? 'data:image/jpeg;base64,' . base64_encode((string) file_get_contents($path)) : '';
    }
    return $uri;
}

function pdfLetterhead(string $formCode, string $title, string $subtitle, array $student): string
{
    $logo = pdfLogoDataUri();

    return '<p class="form-code">' . pdfValue($formCode) . '</p>'
        . '<table class="letterhead"><tr>'
        . '<td class="lh-logo">' . ($logo !== '' ? '<img src="' . $logo . '" alt="">' : '') . '</td>'
        . '<td class="lh-text">'
        . '<div class="lh-school">FERNANDEZ COLLEGE OF ARTS AND TECHNOLOGY</div>'
        . '<div class="lh-office">School Clinic &middot; Health Services</div>'
        . '<div class="lh-est">Est. 1993</div>'
        . '</td>'
        . '<td class="lh-meta"><span class="lbl">Student No.</span><span class="lh-id">' . pdfValue($student['StudentID'] ?? '') . '</span></td>'
        . '</tr></table>'
        . '<div class="doc-title">' . pdfValue($title) . '</div>'
        . '<div class="doc-subtitle">' . pdfValue($subtitle) . '</div>';
}

function studentFullName(array $person, bool $lastFirst = false): string
{
    $last = trim((string) ($person['LastName'] ?? ''));
    $first = trim((string) ($person['FirstName'] ?? ''));
    $middle = trim((string) ($person['MiddleName'] ?? ''));

    if ($lastFirst) {
        return trim($last . ($last !== '' && ($first !== '' || $middle !== '') ? ', ' : '') . trim($first . ' ' . $middle));
    }
    return trim(preg_replace('/\s+/', ' ', $first . ' ' . $middle . ' ' . $last));
}

// Several short values side by side in one cell, to keep guardian columns compact.
function pdfSplitField(array $fields): string
{
    $cells = '';
    foreach ($fields as $label => $value) {
        $cells .= '<td><span class="lbl">' . pdfValue($label) . '</span><span class="val">'
            . (trim((string) $value) !== '' ? pdfValue($value) : '&nbsp;') . '</span></td>';
    }
    return '<td><table class="split"><tr>' . $cells . '</tr></table></td>';
}

function pdfGuardianLabel(?string $value): string
{
    return match ($value) {
        'guardian1' => 'Guardian 1',
        'guardian2' => 'Guardian 2',
        'both' => 'Both guardians',
        default => '',
    };
}

function studentClinicFormHtml(array $student, array $g1, array $g2, array $medHistory): string
{
    $illnesses = clinicIllnessList();

    $medMap = [];
    foreach ($medHistory as $row) {
        $medMap[$row['Illness']] = $row;
    }

    $illnessCell = static function (?string $illness) use ($medMap): string {
        if ($illness === null) {
            return '<td></td><td></td><td></td>';
        }
        $row = $medMap[$illness] ?? null;
        return '<td class="ill-name">' . checkedBox($row !== null) . ' ' . pdfValue($illness) . '</td>'
            . '<td class="ill-date">' . pdfValue($row ? pdfDate($row['DiagnosisDate'] ?? null) : '') . '</td>'
            . '<td class="ill-age">' . pdfValue($row['DiagnosisAge'] ?? '') . '</td>';
    };
    $illnessRows = '';
    $half = (int) ceil(count($illnesses) / 2);
    for ($i = 0; $i < $half; $i++) {
        $illnessRows .= '<tr>' . $illnessCell($illnesses[$i]) . $illnessCell($illnesses[$i + $half] ?? null) . '</tr>';
    }

    $relationshipMarks = static function (array $guardian): string {
        $relationship = normalizeGuardianRelationship($guardian['Relationship'] ?? '');
        return checkedBox($relationship === 'Mother') . ' Mother &nbsp;&nbsp; '
            . checkedBox($relationship === 'Father') . ' Father &nbsp;&nbsp; '
            . checkedBox($relationship === 'Guardian') . ' Guardian';
    };
    $emergency = static fn(array $guardian): string => trim(
        ($guardian['EmergencyContactName'] ?? '')
        . (!empty($guardian['EmergencyContactMobile']) ? ' (' . $guardian['EmergencyContactMobile'] . ')' : '')
    );
    $address = implode(', ', array_filter([
        trim((string) ($student['StreetAddress'] ?? '')),
        trim((string) ($student['Municipality'] ?? '')),
        trim((string) ($student['City'] ?? '')),
    ]));
    $consentLine = !empty($student['PrivacyConsentAt'])
        ? 'Data Privacy Act (RA 10173) consent given through the FCAT Clinic portal on ' . date('F j, Y \a\t g:i A', strtotime($student['PrivacyConsentAt'])) . '.'
        : 'Data Privacy Act (RA 10173) consent has not yet been recorded in the FCAT Clinic portal.';

    return '<!doctype html><html><head><meta charset="utf-8"><style>' . formPdfCss() . '</style></head><body>'
        . pdfLetterhead(
            'FCAT School Clinic Form 01',
            'Student Health Information Form',
            'This record is confidential and is used only by authorized FCAT School Clinic personnel.',
            $student
        )

        . '<div class="section-title">I. Student Information</div>'
        . '<table class="fields">'
        . '<tr>' . pdfField('Last Name', $student['LastName'] ?? '') . pdfField('First Name', $student['FirstName'] ?? '') . pdfField('Middle Name', $student['MiddleName'] ?? '') . pdfField('Preferred Name', $student['PreferredName'] ?? '') . '</tr>'
        . '<tr>' . pdfField('Course', $student['Course'] ?? '', 2) . pdfField('Year Level', $student['YearLevel'] ?? '') . pdfField('Department', $student['Department'] ?? '') . '</tr>'
        . '<tr>' . pdfField('Date of Birth', pdfDate($student['DateOfBirth'] ?? null)) . pdfField('Gender', $student['Gender'] ?? '') . pdfField('Citizenship', $student['Citizenship'] ?? '') . pdfField('Home Telephone', $student['HomeTelephone'] ?? '') . '</tr>'
        . '<tr>' . pdfField('Height (m)', $student['Height'] ?? '') . pdfField('Weight (kg)', $student['Weight'] ?? '') . pdfField('Home Address', $address, 2) . '</tr>'
        . '</table>'

        . '<div class="section-title">II. Parent / Guardian Information</div>'
        . '<table class="fields guardians">'
        . '<tr><th>Guardian 1 &nbsp;&nbsp;<span class="marks">' . $relationshipMarks($g1) . '</span></th><th>Guardian 2 &nbsp;&nbsp;<span class="marks">' . $relationshipMarks($g2) . '</span></th></tr>'
        . '<tr>' . pdfField('Name (Last, First Middle)', studentFullName($g1, true)) . pdfField('Name (Last, First Middle)', studentFullName($g2, true)) . '</tr>'
        . '<tr>' . pdfSplitField(['Mobile Number' => $g1['MobileNumber'] ?? '', 'Office Phone' => $g1['OfficePhone'] ?? '', 'Email Address' => $g1['EmailAddress'] ?? ''])
            . pdfSplitField(['Mobile Number' => $g2['MobileNumber'] ?? '', 'Office Phone' => $g2['OfficePhone'] ?? '', 'Email Address' => $g2['EmailAddress'] ?? '']) . '</tr>'
        . '<tr>' . pdfField('Emergency Contact (aside from above)', $emergency($g1)) . pdfField('Emergency Contact (aside from above)', $emergency($g2)) . '</tr>'
        . '<tr>' . pdfField('Custodial Parent / Guardian', pdfGuardianLabel($student['CustodialParent'] ?? null)) . pdfField('Correspondence Should Be Sent To', pdfGuardianLabel($student['CorrespondenceTo'] ?? null)) . '</tr>'
        . '</table>'

        . '<div class="section-title">III. Past Medical History, Medications &amp; Allergies</div>'
        . '<p class="note">Checked items are illnesses the student has had, with the date and age of diagnosis when known.</p>'
        . '<table class="history"><thead><tr>'
        . '<th class="ill-name">Illness</th><th class="ill-date">Date</th><th class="ill-age">Age</th>'
        . '<th class="ill-name">Illness</th><th class="ill-date">Date</th><th class="ill-age">Age</th>'
        . '</tr></thead><tbody>' . $illnessRows . '</tbody></table>'
        . '<table class="fields">'
        . '<tr>' . pdfField('Medication/s Taken on a Regular Basis', $student['MedicationsRegular'] ?? '', 3) . '</tr>'
        . '<tr>' . pdfField('Allergies: Food', $student['AllergyFood'] ?? '') . pdfField('Allergies: Medicine', $student['AllergyMedicine'] ?? '') . pdfField('Allergies: Others', $student['AllergyOthers'] ?? '') . '</tr>'
        . '</table>'

        . '<div class="certification">'
        . '<p>I hereby certify that the information given above is true and correct to the best of my knowledge, and I will inform the FCAT School Clinic of any changes.</p>'
        . '<p class="privacy">' . pdfValue($consentLine) . '</p>'
        . '</div>'
        . '<table class="signatures"><tr>'
        . '<td><div class="sig-name">' . pdfValue(studentFullName($student)) . '</div><div class="sig-line">Signature of Student over Printed Name / Date</div></td>'
        . '<td class="sig-right"><div class="sig-name">' . (studentFullName($g1) !== '' ? pdfValue(studentFullName($g1)) : '&nbsp;') . '</div><div class="sig-line">Signature of Parent / Guardian over Printed Name / Date</div></td>'
        . '</tr></table>'
        . '</body></html>';
}

function studentConsentFormHtml(array $student, array $g1): string
{
    $courseAndYear = trim(implode(' - ', array_filter([
        trim((string) ($student['Course'] ?? '')),
        trim((string) ($student['YearLevel'] ?? '')),
    ])));
    $guardianName = studentFullName($g1);
    $choiceRow = static fn(string $label): string => '<tr><td class="choice-label">' . pdfValue($label) . '</td>'
        . '<td class="choice-box">' . checkedBox(false) . ' YES</td><td class="choice-box">' . checkedBox(false) . ' NO</td></tr>';

    return '<!doctype html><html><head><meta charset="utf-8"><style>' . formPdfCss() . '</style></head><body>'
        . pdfLetterhead(
            'FCAT School Clinic Form 02',
            'Parent / Guardian Consent Form',
            'To be accomplished and signed by the parent or guardian, then submitted to the FCAT School Clinic.',
            $student
        )

        . '<table class="fields">'
        . '<tr>' . pdfField('Name of Student', studentFullName($student)) . pdfField('Student No.', $student['StudentID'] ?? '') . pdfField('Course & Year Level', $courseAndYear) . '</tr>'
        . '</table>'

        . '<div class="section-title">I. Consent to Clinic Care</div>'
        . '<p class="body-text">I, the undersigned parent/guardian of the student named above, give consent for my child to receive the following:</p>'
        . '<table class="choices">'
        . $choiceRow('First aid treatment at the school clinic')
        . $choiceRow('Non-prescription medicine in case of emergency')
        . $choiceRow('Should the student require hospitalization, we encourage them to visit one of the nearby hospitals')
        . '</table>'
        . '<p class="body-text">I acknowledge that it is my responsibility to inform the FCAT School Clinic of any update in my child\'s medical records.</p>'

        . '<div class="section-title">II. In Case of Emergency</div>'
        . '<p class="body-text">The safety and well-being of the learner is our top priority. In case of emergency, I ask the school to:</p>'
        . '<table class="choices emergency">'
        . '<tr><td>' . checkedBox(false) . ' Make the decision needed to attend to the learner\'s needs (I give the school my consent).</td></tr>'
        . '<tr><td>' . checkedBox(false) . ' Call the parents/guardians first.</td></tr>'
        . '<tr><td>' . checkedBox(false) . ' Others, please specify: ' . (trim((string) ($student['EmergencyOther'] ?? '')) !== '' ? '<u>' . pdfValue($student['EmergencyOther']) . '</u>' : '______________________________________________') . '</td></tr>'
        . '</table>'

        . '<div class="section-title">III. Parent / Guardian Acknowledgement</div>'
        . '<table class="fields">'
        . '<tr>' . pdfField("Parent's / Guardian's Name", $guardianName) . pdfField('Relationship to Student', normalizeGuardianRelationship($g1['Relationship'] ?? '')) . pdfField('Mobile Number', $g1['MobileNumber'] ?? '') . '</tr>'
        . '</table>'
        . '<table class="signatures"><tr>'
        . '<td><div class="sig-name">' . ($guardianName !== '' ? pdfValue($guardianName) : '&nbsp;') . '</div><div class="sig-line">Signature of Parent / Guardian over Printed Name</div></td>'
        . '<td class="sig-date"><div class="sig-name">&nbsp;</div><div class="sig-line">Date</div></td>'
        . '</tr></table>'
        . '</body></html>';
}

function formPdfCss(): string
{
    return '@page{margin:22pt 34pt 38pt;}'
        . '*{box-sizing:border-box;}'
        . 'body{margin:0;font-family:"DejaVu Sans",sans-serif;font-size:8pt;color:#1d2621;background:#fff;line-height:1.22;}'
        . '.form-code{margin:0 0 4pt;font-size:8pt;font-weight:bold;color:#123f2d;letter-spacing:.3pt;}'
        . '.letterhead{width:100%;border-collapse:collapse;border-bottom:2pt solid #123f2d;margin-bottom:7pt;}'
        . '.letterhead td{vertical-align:middle;padding:0 0 5pt;}'
        . '.lh-logo{width:54pt;}.lh-logo img{width:46pt;height:46pt;}'
        . '.lh-school{font-size:12pt;font-weight:bold;color:#123f2d;letter-spacing:.4pt;}'
        . '.lh-office{font-size:9.5pt;font-weight:bold;color:#8d1d35;margin-top:2pt;}'
        . '.lh-est{font-size:7pt;color:#6d7a71;margin-top:2pt;}'
        . '.lh-meta{width:92pt;text-align:right;}.lh-id{display:block;font-size:12pt;font-weight:bold;color:#123f2d;}'
        . '.doc-title{text-align:center;font-size:12.5pt;font-weight:bold;text-transform:uppercase;letter-spacing:1pt;color:#123f2d;}'
        . '.doc-subtitle{text-align:center;font-size:7pt;color:#6d7a71;margin:1pt 0 6pt;}'
        . '.section-title{margin:7pt 0 3pt;padding:2.5pt 6pt;background:#e8f1eb;border-left:3pt solid #123f2d;font-size:8.5pt;font-weight:bold;text-transform:uppercase;letter-spacing:.5pt;color:#123f2d;}'
        . 'table{width:100%;border-collapse:collapse;}'
        . '.fields{table-layout:fixed;margin-bottom:4pt;}'
        . '.fields td,.fields th{border:.75pt solid #9aa79e;padding:2pt 5pt;vertical-align:top;}'
        . '.fields th{background:#f3f6f3;font-size:8pt;text-align:left;color:#123f2d;padding:3pt 5pt;}.fields th .marks{font-weight:normal;color:#1d2621;font-size:7.6pt;}'
        . '.lbl{display:block;font-size:5.8pt;text-transform:uppercase;letter-spacing:.3pt;color:#6d7a71;}'
        . '.val{display:block;min-height:9pt;font-size:8.2pt;color:#111;}'
        . '.marks{font-size:8pt;}'
        . '.split{table-layout:fixed;}.split td{border:0;padding:0 4pt 0 0;vertical-align:top;}'
        . '.note{margin:0 0 3pt;font-size:6.8pt;color:#6d7a71;}'
        . '.history{table-layout:fixed;margin-bottom:4pt;page-break-inside:avoid;}'
        . '.history th,.history td{border:.75pt solid #9aa79e;padding:1.6pt 4pt;font-size:7.3pt;}'
        . '.history th{background:#f3f6f3;text-align:left;color:#123f2d;}'
        . '.ill-name{width:32%;}.ill-date{width:12.5%;}.ill-age{width:5.5%;text-align:center;}'
        . '.certification{margin-top:7pt;font-size:7.6pt;}'
        . '.certification p{margin:0 0 3pt;}'
        . '.privacy{font-size:7.3pt;color:#1f6b4b;}'
        . '.signatures{margin-top:6pt;table-layout:fixed;page-break-inside:avoid;}'
        . '.signatures td{width:50%;padding:16pt 14pt 0 0;vertical-align:bottom;}.signatures .sig-right{padding:16pt 0 0 14pt;}'
        . '.signatures .sig-date{width:30%;padding-right:0;}'
        . '.sig-name{text-align:center;font-size:8.8pt;min-height:11pt;}'
        . '.sig-line{border-top:.9pt solid #1d2621;text-align:center;font-size:6.8pt;color:#4a564e;padding-top:2pt;}'
        . '.clinic-use{margin-top:10pt;table-layout:fixed;border:1pt dashed #9aa79e;page-break-inside:avoid;}'
        . '.clinic-use td{padding:3pt 6pt 9pt;vertical-align:top;}'
        . '.cu-title{width:22%;font-size:7.5pt;font-weight:bold;text-transform:uppercase;color:#8d1d35;}'
        . '.body-text{margin:4pt 0 6pt;}'
        . '.choices{margin:2pt 0 6pt;}'
        . '.choices td{padding:4pt 2pt;border-bottom:.5pt solid #d8e1d8;}'
        . '.choice-label{width:72%;}.choice-box{width:14%;white-space:nowrap;}'
        . '.emergency td{border-bottom:0;padding:3pt 2pt;}';
}

function downloadStudentPdf(string $html, string $filename): void
{
    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Footer on every page: confidentiality note, generation time, and page numbers.
    $canvas = $dompdf->getCanvas();
    $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
    $footerY = $canvas->get_height() - 28;
    $formCode = str_contains($filename, 'consent') ? 'FCAT School Clinic Form 02' : 'FCAT School Clinic Form 01';
    $canvas->page_text(36, $footerY, $formCode . '  ·  Confidential health record  ·  Generated ' . date('M j, Y g:i A'), $font, 6.5, [0.43, 0.48, 0.44]);
    $canvas->page_text($canvas->get_width() - 96, $footerY, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 6.5, [0.43, 0.48, 0.44]);

    $dompdf->stream($filename, ['Attachment' => true]);
    exit;
}
