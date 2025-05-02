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
    $html = '<style>
    @page{margin:20px 20px;font-family:Arial;font-size:14px;}
    .div_half{float:left;margin:0 0 30px 0;width:50%;}
    .logo{width:250px;padding:0;}
    .com_title{text-align:center;font-size:16px;margin:0;}
    .subject{text-align:center;font-size:20px;font-weight:bold;}
    .emp_info{width:100%;margin:0 0 30px 0;}
    .table{border:1px solid #ccc;margin:0 0 30px 0;}
    .salary_info{width:100%;margin:0;}
    .salary_info th,.salary_info td{border:1px solid #ccc;margin:0;padding:5px;vertical-align:middle;}
    </style>';

    $html .= '<div class="div_half">';
    $html .= '<img class="logo" src="path_to_logo.png" alt="Company Logo" />';
    $html .= '</div>';
    $html .= '<div class="div_half">';
    $html .= '<h2 class="com_title">Your Company Name</h2>';
    $html .= '</div>';

    $html .= '<p class="subject">Salary Slip for ' . $pay_month . '</p>';

    $html .= '<table class="emp_info">';
    $html .= '<tr><td>Employee Code</td><td>: ' . strtoupper($employee_id) . '</td></tr>';
    $html .= '</table>';

    $html .= '<table class="table" cellspacing="0" cellpadding="0" width="100%">';
    $html .= '<thead><tr><th>Earnings</th><th>Amount (Bdt.)</th></tr></thead>';
    $html .= '<tr><td>Basic Salary</td><td>' . $pay_head_values['basic_salary'] . '</td></tr>';
    $html .= '<tr><td>House Rent</td><td>' . $pay_head_values['house_rent'] . '</td></tr>';
    $html .= '<tr><td>Medical Allowance</td><td>' . $pay_head_values['medical_allowance'] . '</td></tr>';
    $html .= '</table>';

    $payslip_path = dirname(dirname(__FILE__)) . '/payslips/' . $employee_id . '/' . $pay_month . '/';
    if (!file_exists($payslip_path)) {
        mkdir($payslip_path, 0777, true);
    }

    $pdf_file_path = $payslip_path . $pay_month . '.pdf';

    $pdf->writeHTML($html);
    $pdf->Output($pdf_file_path, 'F');

    return ['code' => 0, 'result' => 'Payslip generated successfully.'];
}
?>
