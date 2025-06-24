<?php
require_once(dirname(__FILE__) . '/../config.php');
require_once(dirname(__FILE__) . '/../functions.php');
require_once(dirname(__FILE__) . '/../TCPDF/tcpdf.php');

function numberToWords($number)
{
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'forty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'numberToWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . numberToWords(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = (int) ($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . numberToWords($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= numberToWords($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}

function generate_salary_slip($employee_id, $pay_month, $earnings_heads, $earnings_amounts, $deductions_heads, $deductions_amounts)
{
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

    $gross_salary = array_sum(array_filter($earnings_amounts, function ($value) {
        return $value !== '';
    }));
    $total_deduction = array_sum(array_filter($deductions_amounts, function ($value) {
        return $value !== '';
    }));

    $pay_head_values['gross_salary'] = $gross_salary;
    $pay_head_values['total_deduction'] = $total_deduction;
    $pay_head_values['net_salary'] = $gross_salary - $total_deduction;

    $columns = implode(", ", array_map(function ($col) {
        return "`" . $col . "`";
    }, array_keys($pay_head_values)));

    $escaped_values = array_map(function ($value) use ($db) {
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
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    // Set up the page style
    $html = '
    <style>
        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .logo {
            width: 80px;
            height: 80px;
            align-items: center;
            margin: 0 auto;
        }
        .header-text {
            text-align: center;
            width: 100%;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .payslip-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .employee-info-table {
            width: 100%;
            margin-top: 15px;
            font-size: 13px;
            border-collapse: collapse;
        }
        .employee-info-table td {
            padding: 6px 8px;
            vertical-align: middle;
            border: 1px solid #bbb;
        }
        .employee-info-table td.label {
            width: 30%;
            font-weight: bold;
            text-align: left;
        }
        .employee-info-table td.separator {
            width: 5%;
            text-align: center;
        }
        .employee-info-table td.value {
            width: 65%;
            text-align: left;
        }
        .salary-table {
            width: 100%;
            margin-top: 20px;
            font-size: 13px;
            border-collapse: collapse;
        }
        .salary-table th, .salary-table td {
            padding: 8px 10px;
            border: 1px solid #bbb;
        }
        .salary-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .footer1-table{
            width: 100%;
            margin-top: 80px;
            font-size: 13px;
            border-collapse: collapse;
        }    
        .footer-table {
            width: 100%;
            padding-top: 50px;
            margin-top: 80px;
            font-size: 13px;
            border-collapse: collapse;
        }
        .footer-table td {
            padding: 8px;
        }
        .footer-left {
            text-align: left;
            width: 50%;
        }
        .footer-right {
            padding-right: 10px;
            padding-top: 50px;    
            text-align: right;
            width: 50%;
        }
    </style>
    ';

    // Header Section with logos and key icon
    // 
    $html .= '
<table style="width:100%; margin-top: 10px; margin-bottom: 10px;" align="center">
  <tr>
    <td align="center" style="width:50%;">
      <img src="' . dirname(dirname(__FILE__)) . '/dist/img/cdbllogo.png" width="80" height="80" />
    </td>
    <td align="center" style="width:50%;">
      <img src="' . dirname(dirname(__FILE__)) . '/dist/img/logo-key.jpg" width="80" height="80" />
    </td>
  </tr>
</table>';

    // Company name and payslip title centered below logos
    $html .= '<div class="header-text">';
    $html .= '<div class="company-name">Central Depository Bangladesh Limited</div>';
    $html .= '<div class="payslip-title">Payslip for the Month of ' . $pay_month . '</div>';
    $html .= '</div>';

    // Employee Info Section
    $html .= '<table class="employee-info-table">';
    // Fetch employee details from database
    $employee_query = "SELECT first_name, last_name, designation, department, joining_date FROM `" . DB_PREFIX . "employees` WHERE emp_code = '$employee_id' LIMIT 1";
    $employee_result = mysqli_query($db, $employee_query);
    if (!$employee_result) {
        $emp_name = 'N/A';
        $designation = 'N/A';
        $department = 'N/A';
        $joining_date = 'N/A';
    } else {
        $employee_data = mysqli_fetch_assoc($employee_result);
        $emp_name = trim(($employee_data['first_name'] ?? '') . ' ' . ($employee_data['last_name'] ?? ''));
        if ($emp_name === '') {
            $emp_name = 'N/A';
        }
        $designation = $employee_data['designation'] ?? 'N/A';
        $department = $employee_data['department'] ?? 'N/A';
        $joining_date = !empty($employee_data['joining_date']) ? date('d-M-Y', strtotime($employee_data['joining_date'])) : 'N/A';
    }
    $html .= '<p style="padding-top: 10px;"><strong>Employee Details:</strong></p>';
    $html .= '<table class="employee-info-table">';
    $html .= '<tr><td class="label">Employee Code</td><td class="separator">:</td><td class="value">' . strtoupper($employee_id) . '</td></tr>';
    $html .= '<tr><td class="label">Employee Name</td><td class="separator">:</td><td class="value">' . htmlspecialchars($emp_name) . '</td></tr>';
    $html .= '<tr><td class="label">Designation</td><td class="separator">:</td><td class="value">' . htmlspecialchars($designation) . '</td></tr>';
    $html .= '<tr><td class="label">Department</td><td class="separator">:</td><td class="value">' . htmlspecialchars($department) . '</td></tr>';
    $html .= '<tr><td class="label">Joining Date</td><td class="separator">:</td><td class="value">' . $joining_date . '</td></tr>';
    $html .= '</table>';
    $html .= '<br />';

    // Earnings and Deductions Table

    $html .= '<p><strong>Earnings and Deductions:</strong></p>';
    $html .= '<table class="salary-table">';
    $html .= '<thead><tr><th><strong>Particulars</strong></th><th><strong>Amount (BDT)</strong></th><th><strong>Particulars</strong></th><th><strong>Amount (BDT)</strong></th></tr></thead>';
    $html .= '<tr><td>Basic Salary</td><td>' . number_format($pay_head_values['basic_salary'], 2) . '</td><td>PF</td><td>' . number_format($pay_head_values['employee_provident_fund'], 2) . '</td></tr>';
    $html .= '<tr><td>House Rent</td><td>' . number_format($pay_head_values['house_rent'], 2) . '</td><td>Income Tax</td><td>' . number_format($pay_head_values['income_tax'], 2) . '</td></tr>';
    $html .= '<tr><td>Car Allowance</td><td>' . number_format($pay_head_values['car_allowance'], 2) . '</td><td>Loan Repayment</td><td>' . number_format($pay_head_values['loans_repayment'], 2) . '</td></tr>';
    $html .= '<tr><td>Medical Allowance</td><td>' . number_format($pay_head_values['medical_allowance'], 2) . '</td><td>Other Deductions</td><td>' . number_format($pay_head_values['other_deductions'], 2) . '</td></tr>';
    $html .= '</table>';
    $html .= '</br>';
    // Net Salary and In Words
    $html .= '<table class="footer1-table">';
    $html .= '<tr><td><strong>Net Salary</strong></td><td>: ' . number_format($pay_head_values['net_salary'], 2) . '</td></tr>';
    $html .= '<tr><td><strong>In Words</strong></td><td>: ' . ucfirst(numberToWords($pay_head_values['net_salary'])) . ' Taka</td></tr>';
    $html .= '</table>';

    // Footer Section with prepared by and approved by aligned left and right
    $html .= '<table class="footer-table">';
    $html .= '<tr><td class="footer-left">Prepared By: CDBL Payroll Management System</td><td class="footer-right">Approved By: CDBL Accounts & Finance</td></tr>';
    $html .= '</table>';

    $pdf->WriteHTML($html);
    $payslip_path = dirname(dirname(__FILE__)) . '/payslips/' . $employee_id . '/' . $pay_month . '/';
    if (!file_exists($payslip_path)) {
        mkdir($payslip_path, 0777, true);
    }

    $pdf_file_path = $payslip_path . $pay_month . '.pdf';

    $pdf->Output($pdf_file_path, 'F');

    return ['code' => 0, 'result' => 'Payslip generated successfully.'];
}

function generate_loan_slip_pdf($employee_id, $deduction_month, $loans, $total_repayment, $total_outstanding)
{
    global $db;

    require_once(dirname(__FILE__) . '/../TCPDF/tcpdf.php');

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('CDBL Payroll System');
    $pdf->SetAuthor('CDBL VAS Team');
    $pdf->SetTitle('Loan Slip Statement');
    $pdf->SetMargins(15, 20, 15);
    $pdf->SetAutoPageBreak(TRUE, 20);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    $date_today = date('jS F Y');

    // Fetch employee details
    $employee_query = "SELECT first_name, last_name, designation FROM `" . DB_PREFIX . "employees` WHERE emp_code = '$employee_id' LIMIT 1";
    $employee_result = mysqli_query($db, $employee_query);
    if (!$employee_result) {
        $employee_name = 'N/A';
        $designation = 'N/A';
    } else {
        $employee_data = mysqli_fetch_assoc($employee_result);
        $employee_name = trim(($employee_data['first_name'] ?? '') . ' ' . ($employee_data['last_name'] ?? ''));
        if ($employee_name === '') {
            $employee_name = 'N/A';
        }
        $designation = $employee_data['designation'] ?? 'N/A';
    }

    // Formal letter header
    $html = '<p style="text-align:right;">' . $date_today . '</p>';
    $html .= '<h3 style="text-align:center;">TO WHOM IT MAY CONCERN</h3>';
    $html .= '<p>This is to certify that <b>' . htmlspecialchars($employee_name) . '</b> - ' . htmlspecialchars($designation) . ' has outstanding loans as of <b>' . htmlspecialchars(date('jS F Y', strtotime($deduction_month))) . '</b>.</p>';

    $html .= '<p>The total outstanding loan amount is Tk. <b>' . number_format($total_outstanding, 2) . '</b> and the total repayment for the month of <b>' . htmlspecialchars(date('F, Y', strtotime($deduction_month))) . '</b> is Tk. <b>' . number_format($total_repayment, 2) . '</b>.</p>';

    $html .= '<p>Details of the loans are as follows:</p>';

    // Table header
    $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
    $html .= '<thead><tr style="background-color:#f2f2f2;">
    <th>Loan Category</th>
    <th>Loan Name</th>
    <th>Loan Amount (Tk.)</th>
    <th>Monthly Installment (Tk.)</th>
    <th>Total Deduction (Tk.)</th>
    <th>Remaining Outstanding (Tk.)</th>
    </tr></thead><tbody>';

    foreach ($loans as $loan) {
        $loan_amount_fmt = number_format($loan['loan_amount'], 2);
        $installment_amount_fmt = number_format($loan['loan_installment_amount'], 2);
        $total_deduction_fmt = number_format($loan['total_deduction'], 2);
        $remaining_balance_fmt = number_format($loan['remaining_balance'], 2);

        $html .= '<tr>
        <td>' . htmlspecialchars($loan['loan_category']) . '</td>
        <td>' . htmlspecialchars($loan['loan_name']) . '</td>
        <td style="text-align:right;">' . $loan_amount_fmt . '</td>
        <td style="text-align:right;">' . $installment_amount_fmt . '</td>
        <td style="text-align:right;">' . $total_deduction_fmt . '</td>
        <td style="text-align:right;">' . $remaining_balance_fmt . '</td>
        </tr>';
    }

    $html .= '</tbody></table>';

    $html .= '<br><br><p>Date: ' . $date_today . '</p>';
    $html .= '<p>Jayanta Biswun Mondal<br>Senior Assistant General Manager<br>Finance & Accounts</p>';

    $pdf->writeHTML($html, true, false, true, false, '');

    $loan_slip_path = dirname(dirname(__FILE__)) . '/loanslips/' . $employee_id . '/' . date('Y-m', strtotime($deduction_month)) . '/';
    if (!file_exists($loan_slip_path)) {
        mkdir($loan_slip_path, 0777, true);
    }

    $pdf_file_path = $loan_slip_path . 'loan_slip_' . date('Y_m', strtotime($deduction_month)) . '.pdf';

    $pdf->Output($pdf_file_path, 'F');

    return ['code' => 0, 'result' => 'Loan slip generated successfully.'];
}
