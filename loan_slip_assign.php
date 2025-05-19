<?php
require_once(dirname(__FILE__) . '/config.php');

if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] != 'admin') {
    header('location:' . BASE_URL);
    exit;
}

$message = '';
$show_readjust_form = false;
$readjust_message = '';
$month = '';
$year = '';
$loan_id_input = '';
$deduction_amount_input = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Normalize existing loan_balance deduction_month values to first day of month
    $sql_normalize = "UPDATE loan_balance SET deduction_month = DATE_FORMAT(deduction_month, '%Y-%m-01') WHERE deduction_month != DATE_FORMAT(deduction_month, '%Y-%m-01')";
    if (!$conn->query($sql_normalize)) {
        error_log("Error normalizing deduction_month in loan_balance: " . $conn->error);
    }

    // Function to process loan slips for a given deduction_month
        function process_loan_slips($conn, $deduction_month, &$message, &$show_readjust_form) {
            $sql_loans = "SELECT loan_id, loan_amount, loan_installment_amount, effective_date, employee_id FROM loan_requests WHERE loan_status = 'approved'";
            $result_loans = $conn->query($sql_loans);

            if ($result_loans && $result_loans->num_rows > 0) {
                $already_processed_loans = [];
                $newly_processed_loans = [];

                // Group loans by employee_id to sum installment amounts
                $loans_by_employee = [];

                while ($loan = $result_loans->fetch_assoc()) {
                    $loan_id = intval($loan['loan_id']);
                    $loan_amount = floatval($loan['loan_amount']);
                    $installment_amount = floatval($loan['loan_installment_amount']);
                    $effective_date = $loan['effective_date'];
                    $employee_id = $loan['employee_id'];

                    // Check if input deduction_month is greater than effective_date
                    if (strtotime($deduction_month) < strtotime($effective_date)) {
                        // Skip processing this loan as the input month/year is before effective_date
                        continue;
                    }

                    // Check if loan slip for this loan_id and deduction_month already exists in loan_balance
                    $sql_check = "SELECT * FROM loan_balance WHERE loan_id = $loan_id AND deduction_month = '$deduction_month'";
                    $result_check = $conn->query($sql_check);

                    if ($result_check && $result_check->num_rows > 0) {
                        // Already processed
                        $already_processed_loans[] = $loan_id;
                    } else {
                        // Insert new loan slip entry
                        // Calculate total deduction so far
                        $sql_total_deduction = "SELECT SUM(deduction_amount) as total_deduction FROM loan_balance WHERE loan_id = $loan_id";
                        $result_total = $conn->query($sql_total_deduction);
                        $total_deduction = 0;
                        if ($result_total && $result_total->num_rows > 0) {
                            $row_total = $result_total->fetch_assoc();
                            $total_deduction = floatval($row_total['total_deduction']);
                        }

                        $remaining_balance = $loan_amount - $total_deduction - $installment_amount;
                        if ($remaining_balance < 0) {
                            $remaining_balance = 0;
                        }

                        $sql_insert = "INSERT INTO loan_balance (loan_id, deduction_month, deduction_amount, remaining_balance) VALUES ($loan_id, '$deduction_month', $installment_amount, $remaining_balance)";
                        if ($conn->query($sql_insert) === TRUE) {
                            $newly_processed_loans[] = $loan_id;

                            // Group installment amounts by employee_id
                            if (!isset($loans_by_employee[$employee_id])) {
                                $loans_by_employee[$employee_id] = 0;
                            }
                            $loans_by_employee[$employee_id] += $installment_amount;
                        } else {
                            $message .= "Error inserting loan slip for Loan ID $loan_id: " . $conn->error . "<br>";
                        }
                    }
                }

                // After processing all loans, update cdbl_pay_structure for each employee with total installment amount
                foreach ($loans_by_employee as $employee_id => $total_installment) {
                    // Fetch emp_code from cdbl_employees
                    $sql_emp_code = "SELECT emp_code FROM cdbl_employees WHERE employee_id = '$employee_id'";
                    $result_emp_code = $conn->query($sql_emp_code);
                    if ($result_emp_code && $result_emp_code->num_rows > 0) {
                        $row_emp_code = $result_emp_code->fetch_assoc();
                        $emp_code = $row_emp_code['emp_code'];

                        // Insert or update cdbl_pay_structure
                        // Check if entry exists
                        $sql_check_pay = "SELECT * FROM cdbl_pay_structure WHERE emp_code = '$emp_code' AND payhead_id = 13";
                        $result_check_pay = $conn->query($sql_check_pay);
                        if ($result_check_pay && $result_check_pay->num_rows > 0) {
                            // Update default_salary with total installment amount
                            $sql_update_pay = "UPDATE cdbl_pay_structure SET default_salary = $total_installment WHERE emp_code = '$emp_code' AND payhead_id = 13";
                            $conn->query($sql_update_pay);
                        } else {
                            // Insert new entry
                            $sql_insert_pay = "INSERT INTO cdbl_pay_structure (emp_code, payhead_id, default_salary) VALUES ('$emp_code', 13, $total_installment)";
                            $conn->query($sql_insert_pay);
                        }
                    }
                }

            if (count($already_processed_loans) > 0) {
                $message .= "Loan slip already processed for Loan IDs: " . implode(', ', $already_processed_loans) . " for " . date('F Y', strtotime($deduction_month)) . ". ";
                $message .= "If you want to readjust the loan deduction amount, please provide the details below.";
                $show_readjust_form = true;
            }

            if (count($newly_processed_loans) > 0) {
                $message .= "Loan slip processed successfully for Loan IDs: " . implode(', ', $newly_processed_loans) . " for " . date('F Y', strtotime($deduction_month)) . ".";
            }
        } else {
            $message = "No active loans found to process.";
        }
    }

    // Check if this is a readjustment form submission
    if (isset($_POST['readjust_submit'])) {
        // Process readjustment update
        $loan_id_input = intval($_POST['loan_id']);
        $deduction_amount_input = floatval($_POST['deduction_amount']);
        $month = $_POST['month'];
        $year = $_POST['year'];

        // Compose deduction_month date as first day of month
        $deduction_month = date('Y-m-d', strtotime("first day of $month $year"));

        // Get loan_amount from loan_requests
        $sql_loan = "SELECT loan_amount FROM loan_requests WHERE loan_id = $loan_id_input";
        $result_loan = $conn->query($sql_loan);
        if ($result_loan && $result_loan->num_rows > 0) {
            $row_loan = $result_loan->fetch_assoc();
            $loan_amount = floatval($row_loan['loan_amount']);
            $sql_update = "UPDATE loan_balance SET deduction_amount = $deduction_amount_input WHERE loan_id = $loan_id_input AND deduction_month = '$deduction_month'";
            if ($conn->query($sql_update) === TRUE) {
                // Recalculate total deduction amount for this loan
                $sql_sum = "SELECT SUM(deduction_amount) as total_deduction FROM loan_balance WHERE loan_id = $loan_id_input";
                $result_sum = $conn->query($sql_sum);
                $total_deduction = 0;
                if ($result_sum && $result_sum->num_rows > 0) {
                    $row_sum = $result_sum->fetch_assoc();
                    $total_deduction = floatval($row_sum['total_deduction']);
                }
                $remaining_balance = $loan_amount - $total_deduction;

                // Update remaining balance in loan_balance for all entries of this loan
                $sql_update_balance = "UPDATE loan_balance SET remaining_balance = $remaining_balance WHERE loan_id = $loan_id_input";
                $conn->query($sql_update_balance);

                $message = "Deduction amount updated successfully for Loan ID $loan_id_input for $month/$year.";
                $show_readjust_form = false;
            } else {
                $message = "Error updating deduction amount: " . $conn->error;
                $show_readjust_form = true;
            }
        } else {
            $message = "Invalid Loan ID.";
            $show_readjust_form = true;
        }
    } else if (isset($_POST['process_loan_slip'])) {
        // Process loan slip generation for given month and year
        $month = $_POST['month'];
        $year = $_POST['year'];

        // Validate month and year inputs
        if (empty($month) || empty($year)) {
            $message = "Please provide both month and year.";
        } else {
            // Compose deduction_month date as first day of month
            $deduction_month = date('Y-m-d', strtotime("first day of $month $year"));
            process_loan_slips($conn, $deduction_month, $message, $show_readjust_form);
        }
    } else if (isset($_POST['generate_loan_slip'])) {
        // Process loan slip generation if not already done, then generate PDF
        $month = $_POST['month'];
        $year = $_POST['year'];

        if (empty($month) || empty($year)) {
            $message = "Please provide both month and year.";
        } else {
            $deduction_month = date('Y-m-d', strtotime("first day of $month $year"));

            // Check if loan_balance entries exist for the deduction_month
            $sql_check_balance = "SELECT COUNT(*) as count FROM loan_balance WHERE deduction_month = '$deduction_month'";
            $result_check_balance = $conn->query($sql_check_balance);
            $count = 0;
            if ($result_check_balance && $result_check_balance->num_rows > 0) {
                $row_check = $result_check_balance->fetch_assoc();
                $count = intval($row_check['count']);
            }

            if ($count == 0) {
                // Process loan slips if not already done
                process_loan_slips($conn, $deduction_month, $message, $show_readjust_form);
            }

            // Now generate the PDF
            // Fetch all employees who have approved loans
            $sql_employees = "
                SELECT DISTINCT lr.employee_id, ce.emp_code, CONCAT(ce.first_name, ' ', ce.last_name) AS employee_name, ce.designation
                FROM loan_requests lr
                JOIN cdbl_employees ce ON lr.employee_id = ce.employee_id
                JOIN loan_balance lb ON lr.loan_id = lb.loan_id
                WHERE lr.loan_status = 'approved' AND lb.deduction_month = '$deduction_month'
                ORDER BY ce.emp_code
            ";
            $result_employees = $conn->query($sql_employees);

            if ($result_employees && $result_employees->num_rows > 0) {
                // Include TCPDF for PDF generation
                require_once(dirname(__FILE__) . '/TCPDF/tcpdf.php');

                // Create new PDF document
                $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetCreator('CDBL Payroll System');
                $pdf->SetAuthor('CDBL VAS Team');
                $pdf->SetTitle('Loan Slip Statements');
                $pdf->SetMargins(15, 20, 15);
                $pdf->SetAutoPageBreak(TRUE, 20);
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);

                $pdf->SetFont('helvetica', '', 12);

                $date_today = date('jS F Y');

                while ($employee = $result_employees->fetch_assoc()) {
                    $employee_id = $employee['employee_id'];
                    $employee_name = $employee['employee_name'];
                    $designation = $employee['designation'];

                    // Fetch all approved loans for this employee
                    $sql_loans_pdf = "
                        SELECT 
                            lr.loan_id,
                            lr.loan_amount,
                            lr.loan_installment_amount,
                            lr.reason AS loan_name,
                            lc.category_name AS loan_category
                        FROM loan_requests lr
                        LEFT JOIN loan_categories lc ON lr.category_id = lc.category_id
                        WHERE lr.loan_status = 'approved' AND lr.employee_id = ?
                        ORDER BY lr.loan_id
                    ";
                    $stmt_loans_pdf = $conn->prepare($sql_loans_pdf);
                    $stmt_loans_pdf->bind_param("s", $employee_id);
                    $stmt_loans_pdf->execute();
                    $res_loans_pdf = $stmt_loans_pdf->get_result();

                    if ($res_loans_pdf->num_rows == 0) {
                        // Skip employee if no approved loans
                        continue;
                    }

                    // Calculate total repayment for the selected month
                    $sql_total_repayment = "
                        SELECT SUM(loan_installment_amount) as total_repayment
                        FROM loan_requests
                        WHERE employee_id = ? AND loan_status = 'approved'
                    ";
                    $stmt_total_repayment = $conn->prepare($sql_total_repayment);
                    $stmt_total_repayment->bind_param("s", $employee_id);
                    $stmt_total_repayment->execute();
                    $res_total_repayment = $stmt_total_repayment->get_result();
                    $total_repayment = 0;
                    if ($res_total_repayment && $res_total_repayment->num_rows > 0) {
                        $row_total_repayment = $res_total_repayment->fetch_assoc();
                        $total_repayment = floatval($row_total_repayment['total_repayment']);
                    }
                    $stmt_total_repayment->close();

                    // Calculate total outstanding loan amount as of the selected month
                    $sql_total_outstanding = "
                        SELECT SUM(loan_amount) as total_outstanding
                        FROM loan_requests
                        WHERE employee_id = ? AND loan_status = 'approved'
                    ";
                    $stmt_total_outstanding = $conn->prepare($sql_total_outstanding);
                    $stmt_total_outstanding->bind_param("s", $employee_id);
                    $stmt_total_outstanding->execute();
                    $res_total_outstanding = $stmt_total_outstanding->get_result();
                    $total_outstanding = 0;
                    if ($res_total_outstanding && $res_total_outstanding->num_rows > 0) {
                        $row_total_outstanding = $res_total_outstanding->fetch_assoc();
                        $total_outstanding = floatval($row_total_outstanding['total_outstanding']);
                    }
                    $stmt_total_outstanding->close();

                    $pdf->AddPage();

                    // Debug: log $month and $year before generating HTML
                    error_log("Generating PDF for month: $month, year: $year");

                    // Formal letter header
                    $html = '<p style="text-align:right;">' . $date_today . '</p>';
                    $html .= '<h3 style="text-align:center;">TO WHOM IT MAY CONCERN</h3>';
                    $html .= '<p>This is to certify that <b>' . htmlspecialchars($employee_name) . '</b> - ' . htmlspecialchars($designation) . ' has outstanding loans as of <b>' . htmlspecialchars(date('jS F Y', strtotime($deduction_month))) . '</b>.</p>';

                    $html .= '<p>The total outstanding loan amount is Tk. <b>' . number_format($total_outstanding, 2) . '</b> and the total repayment for the month of <b>' . htmlspecialchars($month . ', ' . $year) . '</b> is Tk. <b>' . number_format($total_repayment, 2) . '</b>.</p>';

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

                    while ($loan = $res_loans_pdf->fetch_assoc()) {
                        $loan_id = intval($loan['loan_id']);
                        $loan_amount = floatval($loan['loan_amount']);
                        $installment_amount = floatval($loan['loan_installment_amount']);
                        $loan_category = $loan['loan_category'];
                        $loan_name = $loan['loan_name'];

                        // Total deduction for the selected month
                        $sql_deduction = "SELECT SUM(deduction_amount) as total_deduction FROM loan_balance WHERE loan_id = ? AND DATE_FORMAT(deduction_month, '%Y-%m') = ?";
                        $stmt_ded = $conn->prepare($sql_deduction);
                        $month_year = date('Y-m', strtotime("$year-$month-01"));
                        $stmt_ded->bind_param("is", $loan_id, $month_year);
                        $stmt_ded->execute();
                        $res_ded = $stmt_ded->get_result();
                        $total_deduction = 0;
                        if ($res_ded && $res_ded->num_rows > 0) {
                            $row_ded = $res_ded->fetch_assoc();
                            $total_deduction = floatval($row_ded['total_deduction']);
                        }
                        $stmt_ded->close();

                        // Remaining balance (fetch from loan_balance for current deduction_month)
                        $sql_remaining_balance = "SELECT remaining_balance FROM loan_balance WHERE loan_id = ? AND DATE_FORMAT(deduction_month, '%Y-%m') = ?";
                        $stmt_remaining = $conn->prepare($sql_remaining_balance);
                        $month_year = date('Y-m', strtotime("$year-$month-01"));
                        $stmt_remaining->bind_param("is", $loan_id, $month_year);
                        $stmt_remaining->execute();
                        $res_remaining = $stmt_remaining->get_result();
                        $remaining_balance = 0;
                        if ($res_remaining && $res_remaining->num_rows > 0) {
                            $row_remaining = $res_remaining->fetch_assoc();
                            $remaining_balance = floatval($row_remaining['remaining_balance']);
                        }
                        $stmt_remaining->close();

                        if ($remaining_balance < 0) $remaining_balance = 0;

                        // Format amounts
                        $loan_amount_fmt = number_format($loan_amount, 2);
                        $installment_amount_fmt = number_format($installment_amount, 2);
                        $total_deduction_fmt = number_format($total_deduction, 2);
                        $remaining_balance_fmt = number_format($remaining_balance, 2);

                        $html .= '<tr>
                        <td>' . htmlspecialchars($loan_category) . '</td>
                        <td>' . htmlspecialchars($loan_name) . '</td>
                        <td style="text-align:right;">' . $loan_amount_fmt . '</td>
                        <td style="text-align:right;">' . $installment_amount_fmt . '</td>
                        <td style="text-align:right;">' . $total_deduction_fmt . '</td>
                        <td style="text-align:right;">' . $remaining_balance_fmt . '</td>
                        </tr>';
                    }

                    $html .= '</tbody></table>';

                    $html .= '<br><br><p>Date: ' . $date_today . '</p>';
                    $html .= '<p>Jayanta Biswun Mondal<br>Senior Assistant General Manager<br>Finance & Accounts</p>';

                    // Check if $html is empty or not before writing to PDF
                    if (empty(trim($html))) {
                        $html = '<p>No loan details available to display.</p>';
                    }

                    $pdf->writeHTML($html, true, false, true, false, '');

                    $stmt_loans_pdf->close();
                }

                $conn->close();

                // Output PDF
                $pdf->Output('loan_slip_statements_' . $month . '_' . $year . '.pdf', 'I');
                exit;
            } else {
                $message = "No employees with approved loans found for $month/$year.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <title>Loan Slip Generate - Payroll</title>

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
                <h1>Loan Slip Generate</h1>
                <ol class="breadcrumb">
                    <li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Loan Slip Generate</li>
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
                                <label for="month">Month</label>
                                <select class="form-control" id="month" name="month" required>
                                    <option value="">Select Month</option>
                                    <?php
                                    $months = [
                                        'January', 'February', 'March', 'April', 'May', 'June',
                                        'July', 'August', 'September', 'October', 'November', 'December'
                                    ];
                                    foreach ($months as $m) {
                                        $selected = ($m == $month) ? 'selected' : '';
                                        echo "<option value=\"$m\" $selected>$m</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="year">Year</label>
                                <select class="form-control" id="year" name="year" required>
                                    <option value="">Select Year</option>
                                    <?php
                                    $currentYear = date('Y');
                                    for ($y = $currentYear - 5; $y <= $currentYear + 5; $y++) {
                                        $selected = ($y == $year) ? 'selected' : '';
                                        echo "<option value=\"$y\" $selected>$y</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary" name="process_loan_slip">Loan Slip Process</button>
                        <button type="submit" class="btn btn-success" name="generate_loan_slip" style="margin-top: 10px;">Generate Loan Slip</button>
                    </form>
                    <!-- Removed the separate Generate Loan Slip button that calls generate_loan_slip.php -->
                    <!-- Added Generate Loan Slip button integrated with loan slip process -->

                        <?php if ($show_readjust_form) { ?>
                            <hr>
                            <h4>Readjust Loan Deduction Amount</h4>
                            <form method="POST" action="">
                                <input type="hidden" name="month" value="<?php echo htmlspecialchars($month); ?>">
                                <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">

                                <div class="form-group">
                                    <label for="loan_id">Loan ID</label>
                                    <input type="number" class="form-control" id="loan_id" name="loan_id" required value="<?php echo htmlspecialchars($loan_id_input); ?>" />
                                </div>

                                <div class="form-group">
                                    <label for="deduction_amount">New Deduction Amount</label>
                                    <input type="number" step="0.01" class="form-control" id="deduction_amount" name="deduction_amount" required value="<?php echo htmlspecialchars($deduction_amount_input); ?>" />
                                </div>

                                <button type="submit" class="btn btn-warning" name="readjust_submit">Update Deduction</button>
                            </form>
                        <?php } ?>
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
