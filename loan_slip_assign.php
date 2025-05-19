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
    } else {
        // Process loan slip generation for given month and year
        $month = $_POST['month'];
        $year = $_POST['year'];

        // Validate month and year inputs
        if (empty($month) || empty($year)) {
            $message = "Please provide both month and year.";
        } else {
            // Compose deduction_month date as first day of month
            $deduction_month = date('Y-m-d', strtotime("first day of $month $year"));

$sql_loans = "SELECT loan_id, loan_amount, loan_installment_amount, effective_date FROM loan_requests WHERE loan_status = 'approved'";
$result_loans = $conn->query($sql_loans);

if ($result_loans && $result_loans->num_rows > 0) {
    $already_processed_loans = [];
    $newly_processed_loans = [];

    while ($loan = $result_loans->fetch_assoc()) {
        $loan_id = intval($loan['loan_id']);
        $loan_amount = floatval($loan['loan_amount']);
        $installment_amount = floatval($loan['loan_installment_amount']);
        $effective_date = $loan['effective_date'];

        // Check if input deduction_month is greater than effective_date
        if (strtotime($deduction_month) <= strtotime($effective_date)) {
            // Skip processing this loan as the input month/year is not greater than effective_date
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
            } else {
                $message .= "Error inserting loan slip for Loan ID $loan_id: " . $conn->error . "<br>";
            }
        }
    }

                if (count($already_processed_loans) > 0) {
                    $message .= "Loan slip already processed for Loan IDs: " . implode(', ', $already_processed_loans) . " for $month/$year. ";
                    $message .= "If you want to readjust the loan deduction amount, please provide the details below.";
                    $show_readjust_form = true;
                }

                if (count($newly_processed_loans) > 0) {
                    $message .= "Loan slip processed successfully for Loan IDs: " . implode(', ', $newly_processed_loans) . " for $month/$year.";
                }
            } else {
                $message = "No active loans found to process.";
            }
        }
    }

    $conn->close();
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
                        </form>
                        <form method="GET" action="generate_loan_slip.php" target="_blank" style="margin-top: 10px;">
                            <button type="submit" class="btn btn-success">Generate Loan Slip</button>
                        </form>

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
