<?php
require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/includes/salary_helper.php');

require_once dirname(__FILE__) . '/vendor/autoload.php';

if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] != 'admin') {
    header('location:' . BASE_URL);
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['month']) && isset($_POST['year'])) {
    $selected_month = $_POST['month'];
    $selected_year = $_POST['year'];
    $month_year = $selected_month . ', ' . $selected_year;

    if (isset($_POST['export_excel'])) {
        // Export Excel functionality
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $headers = [
            'Employee Name', 'Designation', 'Basic Salary', 'Car Allowance', 'House Rent', 'Conveyance Allowance', 'Medical Allowance',
            'Overtime', 'Traveling Expenses', 'Loans Repayment', 'Performance Bonus', 'Professional Tax', 'Income Tax',
            'Employee Provident Fund', 'Other Deductions', 'Arrear Salary', 'Leave Without Pay', 'Driver Allowance',
            'Net Salary', 'Total Deduction', 'Gross Salary'
        ];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Fetch salary data joined with employee data
        $sql = "SELECT s.*, e.first_name, e.last_name, e.designation
                FROM `" . DB_PREFIX . "salaries` s
                LEFT JOIN `" . DB_PREFIX . "employees` e ON s.emp_code = e.emp_code
                WHERE s.pay_month = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $month_year);
        $stmt->execute();
        $result = $stmt->get_result();

        $rowNum = 2;
        while ($row = $result->fetch_assoc()) {
            $employeeName = $row['first_name'] . ' ' . $row['last_name'];
            $sheet->setCellValue('A' . $rowNum, $employeeName);
            $sheet->setCellValue('B' . $rowNum, $row['designation']);
            $sheet->setCellValue('C' . $rowNum, $row['basic_salary']);
            $sheet->setCellValue('D' . $rowNum, $row['car_allowance']);
            $sheet->setCellValue('E' . $rowNum, $row['house_rent']);
            $sheet->setCellValue('F' . $rowNum, $row['conveyance_allowance']);
            $sheet->setCellValue('G' . $rowNum, $row['medical_allowance']);
            $sheet->setCellValue('H' . $rowNum, $row['overtime']);
            $sheet->setCellValue('I' . $rowNum, $row['traveling_expenses']);
            $sheet->setCellValue('J' . $rowNum, $row['loans_repayment']);
            $sheet->setCellValue('K' . $rowNum, $row['performance_bonus']);
            $sheet->setCellValue('L' . $rowNum, $row['professional_tax']);
            $sheet->setCellValue('M' . $rowNum, $row['income_tax']);
            $sheet->setCellValue('N' . $rowNum, $row['employee_provident_fund']);
            $sheet->setCellValue('O' . $rowNum, $row['other_deductions']);
            $sheet->setCellValue('P' . $rowNum, $row['arrear_salary']);
            $sheet->setCellValue('Q' . $rowNum, $row['leave_without_pay']);
            $sheet->setCellValue('R' . $rowNum, $row['driver_allowance']);
            $sheet->setCellValue('S' . $rowNum, $row['net_salary']);
            $sheet->setCellValue('T' . $rowNum, $row['total_deduction']);
            $sheet->setCellValue('U' . $rowNum, $row['gross_salary']);
            $rowNum++;
        }

        // Add sum formulas for numeric columns
        $lastDataRow = $rowNum - 1;
        $sumColumns = range('C', 'U'); // Columns with numeric data
        $sumRow = $rowNum;

        foreach ($sumColumns as $col) {
            $sheet->setCellValue($col . $sumRow, "=SUM({$col}2:{$col}{$lastDataRow})");
        }

        // Add label for sum row
        $sheet->setCellValue('A' . $sumRow, 'Total');
        $sheet->mergeCells("A{$sumRow}:B{$sumRow}");
        $sheet->getStyle("A{$sumRow}:U{$sumRow}")->getFont()->setBold(true);

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="salary_export_' . $month_year . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    if (isset($_POST['generate_bank_data'])) {
        // Generate Bank Data Excel functionality
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set page margins
        $sheet->getPageMargins()->setTop(0.75);
        $sheet->getPageMargins()->setRight(0.7);
        $sheet->getPageMargins()->setLeft(0.7);
        $sheet->getPageMargins()->setBottom(0.75);

        // Set header text with merges and styling
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'The Manager');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'IFIC Bank Limited');
        $sheet->getStyle('A2')->getFont()->setBold(true);

        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A3', 'Nikunj Branch, Nikunjo - 2');
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->mergeCells('A4:C4');
        $sheet->setCellValue('A4', 'Dhaka');

        // Date and reference on right side
        $sheet->mergeCells('D1:D2');
        $sheet->setCellValue('D1', date('jS F Y'));
        $sheet->getStyle('D1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $sheet->mergeCells('D3:D4');
        $sheet->setCellValue('D3', 'CDBL/Finance/' . str_replace(', ', '/', $month_year));
        $sheet->getStyle('D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Initialize totalNetSalary before loop
        $totalNetSalary = 0;

        // Fetch employee net salary and account no for the selected month
        $sql = "SELECT e.first_name, e.last_name, e.account_no, s.net_salary 
                FROM cdbl_employees e 
                LEFT JOIN cdbl_salaries s ON e.emp_code = s.emp_code AND s.pay_month = ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . $db->error);
        }
        $stmt->bind_param("s", $month_year);
        if (!$stmt->execute()) {
            die('Execute failed: ' . $stmt->error);
        }
        $result = $stmt->get_result();
        if (!$result) {
            die('Get result failed: ' . $stmt->error);
        }

        $rowNum = 10; // Table header row is 9, data starts at 10
        $slNo = 1;
        while ($row = $result->fetch_assoc()) {
            $name = trim($row['first_name'] . ' ' . $row['last_name']);
            if (empty($name)) {
                $name = 'N/A';
            }
            $accountNo = trim($row['account_no']);
            if (empty($accountNo)) {
                $accountNo = 'N/A';
            }
            $amount = floatval($row['net_salary']);
            $totalNetSalary += $amount;

            $sheet->setCellValue('A' . $rowNum, $slNo);
            $sheet->setCellValue('B' . $rowNum, $name);
            $sheet->setCellValue('C' . $rowNum, $accountNo);
            $sheet->setCellValue('D' . $rowNum, $amount);

            // Set borders for data cells
            foreach (['A', 'B', 'C', 'D'] as $colLetter) {
                $sheet->getStyle($colLetter . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }

            $rowNum++;
            $slNo++;
        }

        // Subject line after totalNetSalary is calculated
        $totalAmountFormatted = number_format($totalNetSalary, 2, '.', ',');
        $subjectText = "Sub: Transfer of Fund\nPlease debit the sum of Tk. {$totalAmountFormatted}/- to our SND A/C # 0190171903041 and transfer to credit of the following Accounts at your branch, amounts as listed below :\n\n(Salary for {$month_year})";
        $sheet->mergeCells('A6:D8');
        $sheet->setCellValue('A6', $subjectText);
        $sheet->getStyle('A6')->getAlignment()->setWrapText(true);

        // Table headers start at row 9
        $headerRow = 9;
        $headers = ['Sl. No.', 'Name', 'A/C No.', 'Amount TK'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->getStyle($col . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $col++;
        }

        // Add total row
        $sheet->setCellValue('A' . $rowNum, '');
        $sheet->setCellValue('B' . $rowNum, 'Total Taka');
        $sheet->setCellValue('C' . $rowNum, '');
        $sheet->setCellValue('D' . $rowNum, $totalNetSalary);

        // Style total row
        $sheet->getStyle('B' . $rowNum)->getFont()->setBold(true);
        foreach (['A', 'B', 'C', 'D'] as $colLetter) {
            $sheet->getStyle($colLetter . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        // Add signature lines
        $signatureRow = $rowNum + 3;
        $sheet->setCellValue('A' . $signatureRow, 'Authorised Signature');
        $sheet->setCellValue('D' . $signatureRow, 'Authorised Signature');

        // Set bold for signature labels
        $sheet->getStyle('A' . $signatureRow)->getFont()->setBold(true);
        $sheet->getStyle('D' . $signatureRow)->getFont()->setBold(true);

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="IFIC_Bank_Monthly_Generated_Data_' . str_replace(', ', '_', $month_year) . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    if (isset($_POST['generate_salaries'])) {
        // Existing generate salaries logic
        $employees = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "employees`");

        $message = '';
        while ($employee = mysqli_fetch_assoc($employees)) {
            $emp_id = $employee['emp_code'];

            global $db;
            $empHeads = [];
            $payheadSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "pay_structure` AS `pay`, `" . DB_PREFIX . "payheads` AS `head` WHERE `head`.`payhead_id` = `pay`.`payhead_id` AND `pay`.`emp_code` = '$emp_id'");
            if ($payheadSQL) {
                while ($row = mysqli_fetch_assoc($payheadSQL)) {
                    $empHeads[] = $row;
                }
            }
            if (!is_array($empHeads)) {
                $empHeads = [];
            }

            $checkSalarySQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "salaries` WHERE `emp_code` = '$emp_id' AND `pay_month` = '$month_year'");
            if (mysqli_num_rows($checkSalarySQL) == 0) {

                $earnings_heads = [];
                $earnings_amounts = [];
                $deductions_heads = [];
                $deductions_amounts = [];

                foreach ($empHeads as $head) {
                    if ($head['payhead_type'] == 'earnings') {
                        $earnings_heads[] = $head['payhead_name'];
                        $earnings_amounts[] = $head['default_salary'];
                    } elseif ($head['payhead_type'] == 'deductions') {
                        $deductions_heads[] = $head['payhead_name'];
                        $deductions_amounts[] = $head['default_salary'];
                    }
                }

                // Generate salary slip and insert salary record
                $result = generate_salary_slip($emp_id, $month_year, $earnings_heads, $earnings_amounts, $deductions_heads, $deductions_amounts);
                if ($result['code'] != 0) {
                    $message .= "Failed to generate payslip for employee $emp_id: " . $result['result'] . "<br>";
                }
            }
        }
        if (empty($message)) {
            $message = "Salaries and payslips for $month_year have been successfully generated for all employees.";
        }
        // Redirect to avoid form resubmission on reload
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['generate_bank_data'])) {
        // Generate Bank Data Excel functionality
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set page margins
        $sheet->getPageMargins()->setTop(0.75);
        $sheet->getPageMargins()->setRight(0.7);
        $sheet->getPageMargins()->setLeft(0.7);
        $sheet->getPageMargins()->setBottom(0.75);

        // Set header text with merges and styling
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'The Manager');
        $sheet->getStyle('A1')->getFont()->setBold(true);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'IFIC Bank Limited');
        $sheet->getStyle('A2')->getFont()->setBold(true);

        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A3', 'Nikunj Branch, Nikunjo - 2');
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->mergeCells('A4:C4');
        $sheet->setCellValue('A4', 'Dhaka');

        // Date and reference on right side
        $sheet->mergeCells('D1:D2');
        $sheet->setCellValue('D1', date('jS F Y'));
        $sheet->getStyle('D1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        $sheet->mergeCells('D3:D4');
        $sheet->setCellValue('D3', 'CDBL/Finance/' . str_replace(', ', '/', $month_year));
        $sheet->getStyle('D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Subject line
        $totalAmountFormatted = number_format($totalNetSalary, 2, '.', ',');
        $subjectText = "Sub: Transfer of Fund\nPlease debit the sum of Tk. {$totalAmountFormatted}/- to our SND A/C # 0190171903041 and transfer to credit of the following Accounts at your branch, amounts as listed below :\n\n(Salary for {$month_year})";
        $sheet->mergeCells('A6:D8');
        $sheet->setCellValue('A6', $subjectText);
        $sheet->getStyle('A6')->getAlignment()->setWrapText(true);

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(15);

        // Table headers start at row 9
        $headerRow = 9;
        $headers = ['Sl. No.', 'Name', 'A/C No.', 'Amount TK'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $headerRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $sheet->getStyle($col . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $col++;
        }

        // Fetch employee net salary and account no for the selected month
        $sql = "SELECT e.first_name, e.last_name, e.account_no, s.net_salary 
                FROM cdbl_employees e 
                LEFT JOIN cdbl_salaries s ON e.emp_code = s.emp_code AND s.pay_month = ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . $db->error);
        }
        $stmt->bind_param("s", $month_year);
        if (!$stmt->execute()) {
            die('Execute failed: ' . $stmt->error);
        }
        $result = $stmt->get_result();
        if (!$result) {
            die('Get result failed: ' . $stmt->error);
        }

        $rowNum = $headerRow + 1;
        $totalNetSalary = 0;
        $slNo = 1;
        while ($row = $result->fetch_assoc()) {
            $name = trim($row['first_name'] . ' ' . $row['last_name']);
            if (empty($name)) {
                $name = 'N/A';
            }
            $accountNo = trim($row['account_no']);
            if (empty($accountNo)) {
                $accountNo = 'N/A';
            }
            $amount = floatval($row['net_salary']);
            $totalNetSalary += $amount;

            $sheet->setCellValue('A' . $rowNum, $slNo);
            $sheet->setCellValue('B' . $rowNum, $name);
            $sheet->setCellValue('C' . $rowNum, $accountNo);
            $sheet->setCellValue('D' . $rowNum, $amount);

            // Set borders for data cells
            foreach (['A', 'B', 'C', 'D'] as $colLetter) {
                $sheet->getStyle($colLetter . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }

            $rowNum++;
            $slNo++;
        }

        // Add total row
        $sheet->setCellValue('A' . $rowNum, '');
        $sheet->setCellValue('B' . $rowNum, 'Total Taka');
        $sheet->setCellValue('C' . $rowNum, '');
        $sheet->setCellValue('D' . $rowNum, $totalNetSalary);

        // Style total row
        $sheet->getStyle('B' . $rowNum)->getFont()->setBold(true);
        foreach (['A', 'B', 'C', 'D'] as $colLetter) {
            $sheet->getStyle($colLetter . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        // Add signature lines
        $signatureRow = $rowNum + 3;
        $sheet->setCellValue('A' . $signatureRow, 'Authorised Signature');
        $sheet->setCellValue('D' . $signatureRow, 'Authorised Signature');

        // Set bold for signature labels
        $sheet->getStyle('A' . $signatureRow)->getFont()->setBold(true);
        $sheet->getStyle('D' . $signatureRow)->getFont()->setBold(true);

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="IFIC_Bank_Monthly_Generated_Data_' . str_replace(', ', '_', $month_year) . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // Existing generate salaries logic
    $employees = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "employees`");

    $message = '';
    while ($employee = mysqli_fetch_assoc($employees)) {
        $emp_id = $employee['emp_code'];

        global $db;
        $empHeads = [];
        $payheadSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "pay_structure` AS `pay`, `" . DB_PREFIX . "payheads` AS `head` WHERE `head`.`payhead_id` = `pay`.`payhead_id` AND `pay`.`emp_code` = '$emp_id'");
        if ($payheadSQL) {
            while ($row = mysqli_fetch_assoc($payheadSQL)) {
                $empHeads[] = $row;
            }
        }
        if (!is_array($empHeads)) {
            $empHeads = [];
        }

        $checkSalarySQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "salaries` WHERE `emp_code` = '$emp_id' AND `pay_month` = '$month_year'");
        if (mysqli_num_rows($checkSalarySQL) == 0) {

            $earnings_heads = [];
            $earnings_amounts = [];
            $deductions_heads = [];
            $deductions_amounts = [];

            foreach ($empHeads as $head) {
                if ($head['payhead_type'] == 'earnings') {
                    $earnings_heads[] = $head['payhead_name'];
                    $earnings_amounts[] = $head['default_salary'];
                } elseif ($head['payhead_type'] == 'deductions') {
                    $deductions_heads[] = $head['payhead_name'];
                    $deductions_amounts[] = $head['default_salary'];
                }
            }

            // Generate salary slip and insert salary record
            $result = generate_salary_slip($emp_id, $month_year, $earnings_heads, $earnings_amounts, $deductions_heads, $deductions_amounts);
            if ($result['code'] != 0) {
                $message .= "Failed to generate payslip for employee $emp_id: " . $result['result'] . "<br>";
            }
        }
    }
    if (empty($message)) {
        $message = "Salaries and payslips for $month_year have been successfully generated for all employees.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <title>Salary Assign - Payroll</title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/datatables/dataTables.bootstrap.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/datatables/jquery.dataTables_themeroller.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/skins/_all-skins.min.css">

    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <?php require_once(dirname(__FILE__) . '/partials/topnav.php'); ?>

        <?php require_once(dirname(__FILE__) . '/partials/sidenav.php'); ?>

        <div class="content-wrapper">
            <section class="content-header">
                <h1>Salary Assign</h1>
                <ol class="breadcrumb">
                    <li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Salary Assign</li>
                </ol>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-xs-12">
                        <?php if (!empty($message)) { ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                        <?php } ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="month">Select Month</label>
                                <select class="form-control" id="month" name="month" required>
                                    <option value="January">January</option>
                                    <option value="February">February</option>
                                    <option value="March">March</option>
                                    <option value="April">April</option>
                                    <option value="May">May</option>
                                    <option value="June">June</option>
                                    <option value="July">July</option>
                                    <option value="August">August</option>
                                    <option value="September">September</option>
                                    <option value="October">October</option>
                                    <option value="November">November</option>
                                    <option value="December">December</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="year">Select Year</label>
                                <input type="number" class="form-control" id="year" name="year" required placeholder="Enter Year (e.g., 2025)" />
                            </div>

                            <button type="submit" class="btn btn-primary" name="generate_salaries">Generate Salaries & Payslips</button>
                            <button type="submit" class="btn btn-success" name="export_excel" style="margin-left: 10px;">Export Excel</button>
                            <button type="submit" class="btn btn-info" name="generate_bank_data" style="margin-left: 10px;">Generate Bank Data</button>
                        </form>
                    </div>
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <strong> &copy; CDBL Payroll Management System | </strong> Developed By CDBL VAS Team 2025
        </footer>
    </div>

    <script src="<?php echo BASE_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script src="<?php echo BASE_URL; ?>bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL; ?>plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="<?php echo BASE_URL; ?>plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script src="<?php echo BASE_URL; ?>plugins/jquery-validator/validator.min.js"></script>
    <script src="<?php echo BASE_URL; ?>plugins/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="<?php echo BASE_URL; ?>dist/js/app.min.js"></script>
    <script type="text/javascript">
        var baseurl = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>dist/js/script.js?rand=<?php echo rand(); ?>"></script>
</body>

</html>
