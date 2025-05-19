<?php
require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/TCPDF/tcpdf.php');

if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] != 'admin') {
    header('location:' . BASE_URL);
    exit;
}

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get employee_id and month from GET parameters
$employee_id = isset($_GET['employee_id']) ? $_GET['employee_id'] : null;
$month = isset($_GET['month']) ? $_GET['month'] : null;

if (!$employee_id || !$month) {
    die("Employee ID and month are required.");
}

// Validate month format YYYY-MM
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    die("Invalid month format. Use YYYY-MM.");
}

// Process loan slips for the given month and employee before generating PDF
$deduction_month = date('Y-m-01', strtotime($month . '-01'));

// Fetch all approved loans for the employee
$sql_loans = "SELECT loan_id, loan_amount, loan_installment_amount FROM loan_requests WHERE (loan_status = 'approved' OR loan_status = 'pending') AND employee_id = ?";
$stmt_loans = $conn->prepare($sql_loans);
$stmt_loans->bind_param("s", $employee_id);
$stmt_loans->execute();
$res_loans = $stmt_loans->get_result();

if ($res_loans && $res_loans->num_rows > 0) {
    while ($loan = $res_loans->fetch_assoc()) {
        $loan_id = intval($loan['loan_id']);
        $loan_amount = floatval($loan['loan_amount']);
        $installment_amount = floatval($loan['loan_installment_amount']);

        // Calculate total deduction so far excluding current month
        $sql_total_ded = "SELECT SUM(deduction_amount) as total_deduction FROM loan_balance WHERE loan_id = ? AND deduction_month < ?";
        $stmt_total_ded = $conn->prepare($sql_total_ded);
        $stmt_total_ded->bind_param("is", $loan_id, $deduction_month);
        $stmt_total_ded->execute();
        $res_total_ded = $stmt_total_ded->get_result();
        $total_deduction_till_date = 0;
        if ($res_total_ded && $res_total_ded->num_rows > 0) {
            $row_total_ded = $res_total_ded->fetch_assoc();
            $total_deduction_till_date = floatval($row_total_ded['total_deduction']);
        }
        $stmt_total_ded->close();

        $remaining_balance = $loan_amount - $total_deduction_till_date;

        if ($remaining_balance <= 0) {
            // Loan already completed
            continue;
        }

        // Check if loan slip for this loan and month already exists
        $sql_check = "SELECT loan_balance_id FROM loan_balance WHERE loan_id = ? AND deduction_month = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("is", $loan_id, $deduction_month);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        $exists = ($res_check && $res_check->num_rows > 0);
        $stmt_check->close();

        if ($exists) {
            // Already processed for this month, skip
            continue;
        }

        // Insert new loan_balance record
        $deduction_amount = min($installment_amount, $remaining_balance);
        $new_remaining_balance = $remaining_balance - $deduction_amount;

        $stmt_insert = $conn->prepare("INSERT INTO loan_balance (loan_id, deduction_amount, deduction_month, remaining_balance) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("iddd", $loan_id, $deduction_amount, $deduction_month, $new_remaining_balance);
        $stmt_insert->execute();
        $stmt_insert->close();

        // If remaining balance is zero or less, update loan_status to completed
        if ($new_remaining_balance <= 0) {
            $stmt_update = $conn->prepare("UPDATE loan_requests SET loan_status = 'completed', complete_date = CURDATE() WHERE loan_id = ?");
            $stmt_update->bind_param("i", $loan_id);
            $stmt_update->execute();
            $stmt_update->close();
        }
    }
} else {
    die("No approved loans found for this employee.");
}

// Fetch employee info
$sql_employee = "SELECT emp_code, CONCAT(first_name, ' ', last_name) AS employee_name, designation FROM cdbl_employees WHERE emp_code = ?";
$stmt_emp = $conn->prepare($sql_employee);
$stmt_emp->bind_param("s", $employee_id);
$stmt_emp->execute();
$res_emp = $stmt_emp->get_result();
if ($res_emp->num_rows == 0) {
    die("Employee not found.");
}
$employee = $res_emp->fetch_assoc();
$stmt_emp->close();

// Fetch all approved loans for the employee for PDF generation
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
    die("No approved loans found for this employee.");
}

// Create new PDF document
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('CDBL Payroll System');
$pdf->SetAuthor('CDBL VAS Team');
$pdf->SetTitle('Loan Slip Statement');
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(TRUE, 20);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();

$pdf->SetFont('helvetica', '', 12);

$date_today = date('jS F Y');

// Employee info header
$html = '<h3 style="text-align:center;">LOAN SLIP STATEMENT</h3>';
$html .= '<p><b>Employee Name:</b> ' . htmlspecialchars($employee['employee_name']) . '</p>';
$html .= '<p><b>Designation:</b> ' . htmlspecialchars($employee['designation']) . '</p>';
$html .= '<p><b>Month:</b> ' . htmlspecialchars($month) . '</p>';
$html .= '<br>';

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

// For each loan, calculate total deduction for the selected month and remaining balance
while ($loan = $res_loans_pdf->fetch_assoc()) {
    $loan_id = intval($loan['loan_id']);
    $loan_amount = floatval($loan['loan_amount']);
    $installment_amount = floatval($loan['loan_installment_amount']);
    $loan_category = $loan['loan_category'];
    $loan_name = $loan['loan_name'];

    // Total deduction for the selected month
    $sql_deduction = "SELECT SUM(deduction_amount) as total_deduction FROM loan_balance WHERE loan_id = ? AND DATE_FORMAT(deduction_month, '%Y-%m') = ?";
    $stmt_ded = $conn->prepare($sql_deduction);
    $stmt_ded->bind_param("is", $loan_id, $month);
    $stmt_ded->execute();
    $res_ded = $stmt_ded->get_result();
    $total_deduction = 0;
    if ($res_ded && $res_ded->num_rows > 0) {
        $row_ded = $res_ded->fetch_assoc();
        $total_deduction = floatval($row_ded['total_deduction']);
    }
    $stmt_ded->close();

    // Remaining balance (total loan amount - sum of all deductions till date)
    $sql_total_ded = "SELECT SUM(deduction_amount) as total_deduction FROM loan_balance WHERE loan_id = ?";
    $stmt_total_ded = $conn->prepare($sql_total_ded);
    $stmt_total_ded->bind_param("i", $loan_id);
    $stmt_total_ded->execute();
    $res_total_ded = $stmt_total_ded->get_result();
    $total_deduction_till_date = 0;
    if ($res_total_ded && $res_total_ded->num_rows > 0) {
        $row_total_ded = $res_total_ded->fetch_assoc();
        $total_deduction_till_date = floatval($row_total_ded['total_deduction']);
    }
    $stmt_total_ded->close();

    $remaining_balance = $loan_amount - $total_deduction_till_date;
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

$pdf->writeHTML($html, true, false, true, false, '');

$conn->close();

// Output PDF
$pdf->Output('loan_slip_statement_' . $employee_id . '_' . $month . '.pdf', 'I');
?>
