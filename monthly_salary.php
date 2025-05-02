<?php
require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/includes/salary_helper.php');

if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] != 'admin') {
    header('location:' . BASE_URL);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['month']) && isset($_POST['year'])) {
    $selected_month = $_POST['month'];
    $selected_year = $_POST['year'];
    $month_year = "$selected_month, $selected_year";

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

                            <button type="submit" class="btn btn-primary">Generate Salaries & Payslips</button>
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