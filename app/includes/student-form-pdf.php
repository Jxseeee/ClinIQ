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

function studentClinicFormHtml(array $student, array $g1, array $g2, array $medHistory): string
{
    $leftIllnesses = [
        'Asthma',
        'Chronic Ear Infections or Otitis Media',
        'Epilepsy',
        'Febrile Convulsions',
        'Hepatitis A',
        'Measles',
        'Mumps',
        'Rubella',
        'Whooping Cough',
    ];
    $rightIllnesses = [
        'Chicken Pox',
        'Diabetes',
        'Fainting Spells',
        'Heart Disorder',
        'Hepatitis B',
        'Meningitis',
        'Primary Complex',
        'Scoliosis',
        'Others',
    ];

    $medMap = [];
    foreach ($medHistory as $row) {
        $medMap[$row['Illness']] = $row;
    }

    $illnessRows = '';
    $rowCount = max(count($leftIllnesses), count($rightIllnesses));
    for ($i = 0; $i < $rowCount; $i++) {
        $left = $leftIllnesses[$i] ?? '';
        $right = $rightIllnesses[$i] ?? '';
        $leftRow = $medMap[$left] ?? [];
        $rightRow = $right ? ($medMap[$right] ?? []) : [];

        $illnessRows .= '<tr>'
            . '<td>' . ($left ? checkedBox(isset($medMap[$left])) . ' ' . pdfValue($left) : '') . '</td>'
            . '<td>' . pdfValue($leftRow['DiagnosisDate'] ?? '') . '</td>'
            . '<td>' . pdfValue($leftRow['DiagnosisAge'] ?? '') . '</td>'
            . '<td>' . ($right ? checkedBox(isset($medMap[$right])) . ' ' . pdfValue($right) : '') . '</td>'
            . '<td>' . pdfValue($rightRow['DiagnosisDate'] ?? '') . '</td>'
            . '<td>' . pdfValue($rightRow['DiagnosisAge'] ?? '') . '</td>'
            . '</tr>';
    }

    return '<!doctype html><html><head><meta charset="utf-8"><style>'
        . formPdfCss()
        . '</style></head><body>'
        . '<main class="pdf-page">'
        . '<h1>FCAT School Clinic Form 01</h1>'
        . '<h2>Student Information</h2>'
        . '<table><tr><td>Last: ' . pdfValue($student['LastName'] ?? '') . '</td><td>First: ' . pdfValue($student['FirstName'] ?? '') . '</td><td>Middle: ' . pdfValue($student['MiddleName'] ?? '') . '</td></tr>'
        . '<tr><td>Course: ' . pdfValue($student['Course'] ?? '') . '</td><td>Year Level: ' . pdfValue($student['YearLevel'] ?? '') . '</td><td>Department: ' . pdfValue($student['Department'] ?? '') . '</td></tr>'
        . '<tr><td>Preferred Name: ' . pdfValue($student['PreferredName'] ?? '') . '</td><td>Date of Birth: ' . pdfValue($student['DateOfBirth'] ?? '') . '</td><td>Citizenship: ' . pdfValue($student['Citizenship'] ?? '') . '</td></tr>'
        . '<tr><td>Height (m): ' . pdfValue($student['Height'] ?? '') . '</td><td>Weight (kg): ' . pdfValue($student['Weight'] ?? '') . '</td><td>Gender: ' . pdfValue($student['Gender'] ?? '') . '</td></tr>'
        . '<tr><td colspan="2">Home Address: ' . pdfValue($student['StreetAddress'] ?? '') . ', ' . pdfValue($student['Municipality'] ?? '') . ', ' . pdfValue($student['City'] ?? '') . '</td><td>Home Telephone: ' . pdfValue($student['HomeTelephone'] ?? '') . '</td></tr></table>'
        . '<h2>Parent/Guardian Information</h2>'
        . '<table><tr><td>' . checkedBox(($g1['Relationship'] ?? '') === 'Father') . ' Father &nbsp; ' . checkedBox(($g1['Relationship'] ?? '') === 'Stepfather') . ' Stepfather &nbsp; ' . checkedBox(($g1['Relationship'] ?? '') === 'Legal Guardian') . ' Legal Guardian</td><td>' . checkedBox(($g2['Relationship'] ?? '') === 'Mother') . ' Mother &nbsp; ' . checkedBox(($g2['Relationship'] ?? '') === 'Stepmother') . ' Stepmother &nbsp; ' . checkedBox(($g2['Relationship'] ?? '') === 'Legal Guardian') . ' Legal Guardian</td></tr>'
        . '<tr><td>Last Name: ' . pdfValue($g1['LastName'] ?? '') . '</td><td>Last Name: ' . pdfValue($g2['LastName'] ?? '') . '</td></tr>'
        . '<tr><td>First Name: ' . pdfValue($g1['FirstName'] ?? '') . '</td><td>First Name: ' . pdfValue($g2['FirstName'] ?? '') . '</td></tr>'
        . '<tr><td>Mobile Number: ' . pdfValue($g1['MobileNumber'] ?? '') . '</td><td>Mobile Number: ' . pdfValue($g2['MobileNumber'] ?? '') . '</td></tr>'
        . '<tr><td>Email Address: ' . pdfValue($g1['EmailAddress'] ?? '') . '</td><td>Email Address: ' . pdfValue($g2['EmailAddress'] ?? '') . '</td></tr></table>'
        . '<h2>Past History</h2>'
        . '<table class="history-table"><thead><tr><th>Illness</th><th>Date</th><th>Age</th><th>Illness</th><th>Date</th><th>Age</th></tr></thead><tbody>' . $illnessRows
        . '</tbody></table>'
        . '<p class="medication-line"><strong>Medication/s taken on regular basis:</strong> ' . pdfValue($student['MedicationsRegular'] ?? '') . '</p>'
        . '<p class="allergy-instruction">Please indicate if the child has any allergies.</p>'
        . '<table class="paper-allergy-table"><tr><th>Foods</th><th>Medicine</th><th>Others</th></tr><tr><td>' . pdfValue($student['AllergyFood'] ?? '') . '</td><td>' . pdfValue($student['AllergyMedicine'] ?? '') . '</td><td>' . pdfValue($student['AllergyOthers'] ?? '') . '</td></tr></table>'
        . '</main></body></html>';
}

function studentConsentFormHtml(array $student, array $g1): string
{
    return '<!doctype html><html><head><meta charset="utf-8"><style>'
        . consentPdfCss()
        . '</style></head><body>'
        . '<div class="consent-page">'
        . '<h1>CONSENT FORM</h1>'
        . '<div class="consent-main">'
        . '<p>I give consent for my child to receive the following:</p>'
        . '<table class="choice-table"><tr><td>First aid treatment at the school clinic</td><td>' . checkedBox(false) . ' YES</td><td>' . checkedBox(false) . ' NO</td></tr>'
        . '<tr><td>Non-prescription medicine in case of emergency</td><td>' . checkedBox(false) . ' YES</td><td>' . checkedBox(false) . ' NO</td></tr>'
        . '<tr><td>Should a student require hospitalization, we encourage them to visit one of the nearby hospitals:</td><td>' . checkedBox(false) . ' YES</td><td>' . checkedBox(false) . ' NO</td></tr></table>'
        . '<p>I acknowledge that it is my responsibility to inform FCAT school clinic of any update in my child\'s medical records.</p>'
        . '<p>Safety and well-being of the learner is the top priority. In case of emergency, we ask the parents to:</p>'
        . '<p class="indent">' . checkedBox(false) . ' give the school consent for the decision to attend the learner\'s need.</p>'
        . '<p class="indent">' . checkedBox(false) . ' call the parents/guardians first.</p>'
        . '<p class="indent">' . checkedBox(false) . ' Others: Please Specify&nbsp;&nbsp;______________________________</p>'
        . '</div>'
        . '<div class="signature">Parent\'s/Guardian\'s Name: ______________________________<br>Signature: ______________________________<br>Date: ______________________________</div>'
        . '</div></body></html>';
}

function formPdfCss(): string
{
    return '@page{margin:18pt;}html,body{margin:0;padding:0;}*{box-sizing:border-box;}body{font-family:DejaVu Sans,sans-serif;font-size:5.9px;color:#111;background:#f5eeee;}.pdf-page{width:100%;background:#f5eeee;border:1px solid #aaa;padding:12pt 10pt 14pt;page-break-before:auto;page-break-after:auto;page-break-inside:avoid;overflow:hidden;}h1{width:92%;margin:0 auto 12px;font-size:11px;font-weight:bold;line-height:1.1;text-align:left;}h2{width:92%;margin:0 auto 10px;font-size:6.8px;text-transform:uppercase;font-weight:bold;letter-spacing:.1px;}h2:not(:first-child){margin-top:11px;}table{width:92%;max-width:92%;table-layout:fixed;border-collapse:collapse;margin:0 auto 11px;background:transparent;page-break-inside:avoid;}td,th{border:1px solid #888;padding:3px 3.5px;vertical-align:top;line-height:1.08;word-wrap:break-word;overflow-wrap:anywhere;}th{font-weight:normal;text-align:center;background:transparent;}.history-table{margin-bottom:8px;}.history-table th:nth-child(1),.history-table td:nth-child(1),.history-table th:nth-child(4),.history-table td:nth-child(4){width:31%;}.history-table th:nth-child(2),.history-table td:nth-child(2),.history-table th:nth-child(5),.history-table td:nth-child(5){width:12%;}.history-table th:nth-child(3),.history-table td:nth-child(3),.history-table th:nth-child(6),.history-table td:nth-child(6){width:5%;}.history-table td{height:16pt;}.medication-line,.allergy-instruction{width:92%;margin-left:auto;margin-right:auto;}.medication-line{margin-top:0;margin-bottom:11px;font-size:7.6px;font-weight:bold;}.allergy-instruction{margin-top:0;margin-bottom:9px;font-size:7.2px;}.paper-allergy-table{margin-bottom:0;}.paper-allergy-table td{height:42pt;}';
}

function consentPdfCss(): string
{
    return '@page{margin:0;}html,body{margin:0;padding:0;width:842pt;height:595pt;overflow:hidden;}body{font-family:DejaVu Sans,sans-serif;font-size:8.6px;color:#222;background:#fff;}.consent-page{position:relative;box-sizing:border-box;width:842pt;height:330pt;max-height:330pt;margin:0;background:#f5eeee;border:1px solid #aaa;padding:36pt 54pt;page-break-before:avoid;page-break-after:avoid;page-break-inside:avoid;overflow:hidden;}h1{text-align:center;font-size:11px;margin:0 0 20pt;font-weight:bold;}.consent-main{width:72%;}p{margin:0 0 8pt;line-height:1.2;}.choice-table{width:100%;border-collapse:collapse;margin:0 0 9pt;}.choice-table td{border:0;padding:0 0 6pt;vertical-align:top;line-height:1.15;}.choice-table td:first-child{width:74%;}.choice-table td:nth-child(2),.choice-table td:nth-child(3){width:42pt;text-align:left;white-space:nowrap;}.indent{margin-left:0;}.signature{position:absolute;right:58pt;bottom:38pt;width:270pt;line-height:1.75;white-space:nowrap;}';
}

function downloadStudentPdf(string $html, string $filename): void
{
    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $orientation = str_contains($filename, 'consent-form') ? 'landscape' : 'portrait';
    $dompdf->setPaper('A4', $orientation);
    $dompdf->render();
    $dompdf->stream($filename, ['Attachment' => true]);
    exit;
}
