<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../functions.php');
require_once(dirname(__FILE__) . '/../TCPDF/tcpdf.php');

function generate_salary_slip($employee_id, $pay_month, $earnings_heads, $earnings_amounts, $deductions_heads, $deductions_amounts) {
    global $db;

    // Fetch the predefined pay heads from the cdbl_pay_structure table
    $pay_structure_query = "SELECT * FROM `" . DB_PREFIX . "pay_structure` WHERE `emp_code` = '$employee_id'";
    $pay_structure_result = mysqli_query($db, $pay_structure_query);
    $pay_structure = mysqli_fetch_assoc($pay_structure_result);

    if (!$pay_structure) {
        return ['code' => 1, 'result' => 'No pay structure found for this employee.'];
    }

    $pay_head_values = array(
        'basic_salary' => !empty($pay_structure['basic_salary']) ? $pay_structure['basic_salary'] : 0,
        'car_allowance' => !empty($pay_structure['car_allowance']) ? $pay_structure['car_allowance'] : 0,
        'house_rent' => !empty($pay_structure['house_rent']) ? $pay_structure['house_rent'] : 0,
        'conveyance_allowance' => !empty($pay_structure['conveyance_allowance']) ? $pay_structure['conveyance_allowance'] : 0,
        'medical_allowance' => !empty($pay_structure['medical_allowance']) ? $pay_structure['medical_allowance'] : 0,
        'overtime' => !empty($pay_structure['overtime']) ? $pay_structure['overtime'] : 0,
        'traveling_expenses' => !empty($pay_structure['traveling_expenses']) ? $pay_structure['traveling_expenses'] : 0,
        'loans_repayment' => !empty($pay_structure['loans_repayment']) ? $pay_structure['loans_repayment'] : 0,
        'performance_bonus' => !empty($pay_structure['performance_bonus']) ? $pay_structure['performance_bonus'] : 0,
        'professional_tax' => !empty($pay_structure['professional_tax']) ? $pay_structure['professional_tax'] : 0,
        'income_tax' => 0,
        'employee_provident_fund' => 0,
        'other_deductions' => 0,
        'arrear_salary' => 0,
        'leave_without_pay' => 0,
        'driver_allowance' => 0,
        'net_salary' => null,
        'total_deduction' => null,
        'gross_salary' => null
    );

    foreach ($earnings_heads as $index => $head) {
        $head_key = strtolower(str_replace(' ', '_', $head));
        if (array_key_exists($head_key, $pay_head_values)) {
            $pay_head_values[$head_key] = isset($earnings_amounts[$index]) && $earnings_amounts[$index] !== '' ? $earnings_amounts[$index] : 0;
        }
    }

    foreach ($deductions_heads as $index => $head) {
        $head_key = strtolower(str_replace(' ', '_', $head));
        if (array_key_exists($head_key, $pay_head_values)) {
            $pay_head_values[$head_key] = isset($deductions_amounts[$index]) && $deductions_amounts[$index] !== '' ? $deductions_amounts[$index] : 0;
        }
    }

    $gross_salary = array_sum(array_filter($earnings_amounts, function($value) { return $value !== ''; }));
    $total_deduction = array_sum(array_filter($deductions_amounts, function($value) { return $value !== ''; }));

    $pay_head_values['gross_salary'] = $gross_salary;
    $pay_head_values['total_deduction'] = $total_deduction;
    $pay_head_values['net_salary'] = $gross_salary - $total_deduction;

    $columns = implode(", ", array_map(function($col) {
        return "`" . $col . "`";
    }, array_keys($pay_head_values)));

    $escaped_values = array_map(function($value) use ($db) {
        if (is_numeric($value)) {
            return $value;
        } elseif (is_null($value)) {
            return 'NULL';
        } else {
            return "'" . mysqli_real_escape_string($db, $value) . "'";
        }
    }, array_values($pay_head_values));

    $values = implode(", ", $escaped_values);

    $query = "INSERT INTO `cdbl_salaries` 
    (`emp_code`, `pay_month`, `generate_date`, $columns) 
    VALUES 
    ('$employee_id', '$pay_month', NOW(), $values)
    ON DUPLICATE KEY UPDATE ";

    $update_parts = [];
    foreach ($pay_head_values as $col => $val) {
        $escaped_val = is_numeric($val) ? $val : (is_null($val) ? 'NULL' : "'" . mysqli_real_escape_string($db, $val) . "'");
        $update_parts[] = "`$col` = $escaped_val";
    }
    $query .= implode(", ", $update_parts);

    if (!mysqli_query($db, $query)) {
        return ['code' => 1, 'result' => 'Error in generating salary record: ' . mysqli_error($db)];
    }

    // Generate PDF using TCPDF
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Company');
    $pdf->SetTitle('Salary Slip');
    $pdf->SetSubject('Salary Details for ' . $pay_month);

    $pdf->AddPage();
    
    // Set up the page style
    $html = '
    <style>
        .header { text-align: center; font-size: 18px; font-weight: bold; margin-top: 20px; }
        .company-name { font-size: 16px; font-weight: bold; }
        .payslip-title { font-size: 18px; font-weight: bold; margin-top: 5px; }
        .logo { width: 150px; padding: 0; margin-top: 15px; }
        .employee-info-table { width: 100%; margin-top: 20px; font-size: 12px; }
        .employee-info-table td { padding: 5px; vertical-align: middle; border: 1px solid #ddd; }
        .salary-table { width: 100%; margin-top: 20px; font-size: 12px; border-collapse: collapse; }
        .salary-table th, .salary-table td { padding: 8px; border: 1px solid #ddd; }
        .footer { margin-top: 40px; font-size: 12px; }
        .footer td { padding: 5px; }
    </style>
    ';
    
    // Header Section
    $html .= '<div class="header">';
$html .= '<img class="logo" src="' . dirname(dirname(__FILE__)) . '/dist/img/cdbllogo.png" alt="Logo" />';  // Use absolute file path for TCPDF
    $html .= '<div class="company-name">Central Depository Bangladesh Limited</div>';
    $html .= '<div class="payslip-title">Payslip for the Month of ' . $pay_month . '</div>';
    $html .= '</div>';
    
    // Employee Info Section
    $html .= '<table class="employee-info-table">';
    $html .= '<tr><td>Employee Code</td><td>: ' . strtoupper($employee_id) . '</td></tr>';
    $html .= '<tr><td>Employee Name</td><td>: ' . 'John Doe' . '</td></tr>';  // Replace with dynamic data
    $html .= '<tr><td>Designation</td><td>: ' . 'Officer' . '</td></tr>';
    $html .= '<tr><td>Department</td><td>: ' . 'Value Added Services' . '</td></tr>';
    $html .= '<tr><td>Joining Date</td><td>: 01-SEP-2022</td></tr>';
    $html .= '</table>';
    
    // Earnings and Deductions Table
    $html .= '<table class="salary-table">';
    $html .= '<thead><tr><th>Particulars</th><th>Amount (BDT)</th><th>Particulars</th><th>Amount (BDT)</th></tr></thead>';
    $html .= '<tr><td>Basic Salary</td><td>' . $pay_head_values['basic_salary'] . '</td><td>PF</td><td>' . $pay_head_values['employee_provident_fund'] . '</td></tr>';
    $html .= '<tr><td>House Rent</td><td>' . $pay_head_values['house_rent'] . '</td><td>Income Tax</td><td>' . $pay_head_values['income_tax'] . '</td></tr>';
    $html .= '<tr><td>Car Allowance</td><td>' . $pay_head_values['car_allowance'] . '</td><td>Loan Repayment</td><td>' . $pay_head_values['loans_repayment'] . '</td></tr>';
    $html .= '<tr><td>Medical Allowance</td><td>' . $pay_head_values['medical_allowance'] . '</td><td>Other Deductions</td><td>' . $pay_head_values['other_deductions'] . '</td></tr>';
    $html .= '</table>';
    
    // Net Salary
    $html .= '<table class="footer">';
    $html .= '<tr><td><strong>Net Salary</strong></td><td>: ' . $pay_head_values['net_salary'] . '</td></tr>';
    $html .= '<tr><td><strong>In Words</strong></td><td>: ' . ($pay_head_values['net_salary']) . ' Taka</td></tr>';
    $html .= '</table>';
    
    // Footer Section
    $html .= '<div class="footer">';
    $html .= '<p>Prepared By: CDBL Payroll Management System</p>';
    $html .= '<p>Approved By: Raquibul Islam Chowdhury</p>';
    $html .= '</div>';
    
    $pdf->WriteHTML($html);
    $payslip_path = dirname(dirname(__FILE__)) . '/payslips/' . $employee_id . '/' . $pay_month . '/';
    if (!file_exists($payslip_path)) {
        mkdir($payslip_path, 0777, true);
    }

    $pdf_file_path = $payslip_path . $pay_month . '.pdf';

    $pdf->Output($pdf_file_path, 'F');

    return ['code' => 0, 'result' => 'Payslip generated successfully.'];
}
?>
