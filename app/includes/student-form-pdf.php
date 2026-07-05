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
    $illnesses = [
        'Asthma', 'Chicken Pox', 'Chronic Ear Infections or Otitis Media',
        'Diabetes', 'Epilepsy', 'Fainting Spells', 'Febrile Convulsions',
        'Heart Disorder', 'Hepatitis A', 'Hepatitis B',
        'Measles', 'Meningitis', 'Mumps', 'Primary Complex',
        'Rubella', 'Scoliosis', 'Skin Problems',
        'Urinary Tract Infections', 'Whooping Cough', 'Others',
    ];

    $medMap = [];
    foreach ($medHistory as $row) {
        $medMap[$row['Illness']] = $row;
    }

    $illnessRows = '';
    foreach (array_chunk($illnesses, 2) as $pair) {
        $left = $pair[0] ?? '';
        $right = $pair[1] ?? '';
        $leftRow = $medMap[$left] ?? [];
        $rightRow = $right ? ($medMap[$right] ?? []) : [];

        $illnessRows .= '<tr>'
            . '<td>' . checkedBox(isset($medMap[$left])) . ' ' . pdfValue($left) . '</td>'
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
        . '<h1>FCAT School Clinic Form 01</h1>'
        . '<div class="form-box">'
        . '<h2>Student Information</h2>'
        . '<table><tr><td>Last: ' . pdfValue($student['LastName'] ?? '') . '</td><td>First: ' . pdfValue($student['FirstName'] ?? '') . '</td><td>Middle: ' . pdfValue($student['MiddleName'] ?? '') . '</td></tr>'
        . '<tr><td>Course: ' . pdfValue($student['Course'] ?? '') . '</td><td>Year Level: ' . pdfValue($student['YearLevel'] ?? '') . '</td><td>Department: ' . pdfValue($student['Department'] ?? '') . '</td></tr>'
        . '<tr><td>Preferred Name: ' . pdfValue($student['PreferredName'] ?? '') . '</td><td>Date of Birth: ' . pdfValue($student['DateOfBirth'] ?? '') . '</td><td>Citizenship: ' . pdfValue($student['Citizenship'] ?? '') . '</td></tr>'
        . '<tr><td>Height (m): ' . pdfValue($student['Height'] ?? '') . '</td><td>Weight (kg): ' . pdfValue($student['Weight'] ?? '') . '</td><td>Gender: ' . pdfValue($student['Gender'] ?? '') . '</td></tr>'
        . '<tr><td colspan="2">Home Address: ' . pdfValue($student['StreetAddress'] ?? '') . ', ' . pdfValue($student['Municipality'] ?? '') . ', ' . pdfValue($student['City'] ?? '') . '</td><td>Home Telephone: ' . pdfValue($student['HomeTelephone'] ?? '') . '</td></tr></table>'
        . '<h2>Parent/Guardian Information</h2>'
        . '<table><tr><td>Guardian 1: ' . pdfValue(($g1['FirstName'] ?? '') . ' ' . ($g1['LastName'] ?? '')) . '</td><td>Relationship: ' . pdfValue($g1['Relationship'] ?? '') . '</td><td>Mobile: ' . pdfValue($g1['MobileNumber'] ?? '') . '</td></tr>'
        . '<tr><td>Guardian 2: ' . pdfValue(($g2['FirstName'] ?? '') . ' ' . ($g2['LastName'] ?? '')) . '</td><td>Relationship: ' . pdfValue($g2['Relationship'] ?? '') . '</td><td>Mobile: ' . pdfValue($g2['MobileNumber'] ?? '') . '</td></tr></table>'
        . '<h2>Past History</h2>'
        . '<table><thead><tr><th>Illness</th><th>Date</th><th>Age</th><th>Illness</th><th>Date</th><th>Age</th></tr></thead><tbody>' . $illnessRows . '</tbody></table>'
        . '<p><strong>Medication/s taken on regular basis:</strong> ' . pdfValue($student['MedicationsRegular'] ?? '') . '</p>'
        . '<h2>Allergies</h2>'
        . '<table><tr><th>Foods</th><th>Medicine</th><th>Others</th></tr><tr><td>' . pdfValue($student['AllergyFood'] ?? '') . '</td><td>' . pdfValue($student['AllergyMedicine'] ?? '') . '</td><td>' . pdfValue($student['AllergyOthers'] ?? '') . '</td></tr></table>'
        . '</div></body></html>';
}

function studentConsentFormHtml(array $student, array $g1): string
{
    $guardian = trim(($g1['FirstName'] ?? '') . ' ' . ($g1['LastName'] ?? ''));

    return '<!doctype html><html><head><meta charset="utf-8"><style>'
        . consentPdfCss()
        . '</style></head><body>'
        . '<div class="consent-page">'
        . '<h1>CONSENT FORM</h1>'
        . '<p>I give consent for my child to receive the following:</p>'
        . '<table class="choice-table"><tr><td>First aid treatment at the school clinic</td><td>' . checkedBox(false) . ' YES</td><td>' . checkedBox(false) . ' NO</td></tr>'
        . '<tr><td>Non-prescription medicine in case of emergency</td><td>' . checkedBox(false) . ' YES</td><td>' . checkedBox(false) . ' NO</td></tr>'
        . '<tr><td>Should a student require hospitalization, we encourage them to visit one of the nearby hospitals:</td><td>' . checkedBox(false) . ' YES</td><td>' . checkedBox(false) . ' NO</td></tr></table>'
        . '<p>I acknowledge that it is my responsibility to inform FCAT school clinic of any update in my child\'s medical records.</p>'
        . '<p>Safety and well-being of the learner is the top priority. In case of emergency, we ask the parents to:</p>'
        . '<p class="indent">' . checkedBox(false) . ' give the school consent for the decision to attend the learner\'s need.</p>'
        . '<p class="indent">' . checkedBox(false) . ' call the parents/guardians first.</p>'
        . '<p class="indent">' . checkedBox(false) . ' Others: Please Specify&nbsp;&nbsp;______________________________</p>'
        . '<div class="signature">Parent\'s/Guardian\'s Name: ______________________________<br>Signature: ______________________________<br>Date: ______________________________</div>'
        . '</div></body></html>';
}

function formPdfCss(): string
{
    return 'body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#333;background:#f5eeee;}h1{text-align:center;font-size:16px;margin:0 0 14px;}h2{font-size:11px;text-transform:uppercase;margin:14px 0 5px;}table{width:100%;border-collapse:collapse;margin-bottom:8px;}td,th{border:1px solid #888;padding:5px;vertical-align:top;}th{font-weight:bold;background:#eee;}.form-box,.consent-box{border:1px solid #aaa;padding:18px;background:#f5eeee;}.no-border td{border:0;padding:6px}.signature{margin-top:32px;text-align:right;line-height:2}';
}

function consentPdfCss(): string
{
    return '@page{margin:0;}body{margin:0;font-family:DejaVu Sans,sans-serif;font-size:12px;color:#333;background:#fff;}.consent-page{box-sizing:border-box;width:100%;height:100%;min-height:595pt;background:#f5eeee;border:1px solid #aaa;padding:54pt 58pt;}h1{text-align:center;font-size:13px;margin:0 0 32pt;font-weight:bold;}p{margin:0 0 14pt;line-height:1.35;}.choice-table{width:100%;border-collapse:collapse;margin:0 0 18pt;}.choice-table td{border:0;padding:0 0 13pt;vertical-align:top;}.choice-table td:first-child{width:74%;}.choice-table td:nth-child(2),.choice-table td:nth-child(3){width:70pt;text-align:left;white-space:nowrap;}.indent{margin-left:34pt;}.signature{width:270pt;margin:34pt 0 0 auto;line-height:2.1;}';
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
