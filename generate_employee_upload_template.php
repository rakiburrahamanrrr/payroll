<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set header row with all required fields
$headers = [
    'emp_code', 'first_name', 'last_name', 'dob', 'email', 'mobile',
    'emp_grade', 'empsal_grade', 'joining_date', 'blood_group', 'employment_type',
    'gender', 'marital_status', 'address', 'paraddress', 'national_id', 'verification',
    'employee_id', 'employment_status', 'department', 'designation', 'photo',
    'emp_password', 'insurance_id', 'account_no', 'etin_no'
];
$sheet->fromArray($headers, NULL, 'A1');

// Sample data rows
$data = [
    ['E001', 'John', 'Doe', '1980-01-01', 'john.doe@example.com', '1234567890', 'A', '1', '2020-01-15', 'O+', 'Permanent',
     'male', 'Single', '123 Main St', '123 Main St', 'NID123456', 'Not Verified', 1, 'Active', 'IT', 'Developer', 'default.jpg',
     sha1('password123'), 'INS123', '123456789', 'ETIN123'],
    ['E002', 'Jane', 'Smith', '1985-05-20', 'jane.smith@example.com', '0987654321', 'B', '2', '2021-06-01', 'A-', 'Contractual',
     'female', 'Married', '456 Elm St', '456 Elm St', 'NID654321', 'Not Verified', 2, 'Active', 'HR', 'Manager', 'default.jpg',
     sha1('password123'), 'INS456', '987654321', 'ETIN456'],
];

// Write data starting from row 2
$sheet->fromArray($data, NULL, 'A2');

// Set date columns format (dob and joining_date)
$dateColumns = ['D', 'I'];
foreach ($dateColumns as $col) {
    $sheet->getStyle($col . '2:' . $col . (count($data) + 1))
        ->getNumberFormat()
        ->setFormatCode('yyyy-mm-dd');
}

// Save to file
$filename = 'employee_upload_template_full.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

echo "Template generated: " . $filename . PHP_EOL;
?>
