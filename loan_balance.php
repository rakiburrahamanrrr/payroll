<?php
require_once('config.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as an employee
if (!isset($_SESSION['Login_Type']) || $_SESSION['Login_Type'] != 'emp') {
    header("Location: index.php");
    exit();
}

// Fetch the employee's loan details
$employee_id = $_SESSION['Admin_ID']; // Assuming employee ID is stored in session
$loan_details = [];
$query = "SELECT * FROM loan_requests WHERE employee_id = '$employee_id' AND loan_status = 'Loan Approved'";
$result = mysqli_query($db, $query);

while ($row = mysqli_fetch_assoc($result)) {
    // Fetch the outstanding balance for each loan
    $row['outstanding_balance'] = getOutstandingBalance($row['loan_id']);
    $loan_details[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_loan'])) {
    $loan_id = $_POST['loan_id'];
    $employee_id = $_SESSION['Admin_ID']; // Assuming the employee ID is stored in session
    $payment_amount = $_POST['payment_amount'];

    // Insert the payment into the loan_payments table
    $query = "INSERT INTO loan_payments (loan_id, employee_id, payment_amount, payment_date) 
              VALUES ('$loan_id', '$employee_id', '$payment_amount', NOW())";

    if (mysqli_query($db, $query)) {
        $message = "Payment successfully recorded!";
    } else {
        $message = "Error recording payment: " . mysqli_error($db);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <title>Your Loan Balance - Payroll</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
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
      <h1>Your Loan Balance</h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Loan Balance</li>
      </ol>
    </section>
    <section class="content">
      <?php if (isset($message)) { echo '<div class="alert alert-info">' . htmlspecialchars($message) . '</div>'; } ?>
      <?php if (empty($loan_details)) { ?>
        <div class="alert alert-info">You don't have any approved loans.</div>
      <?php } else { ?>
        <div class="box">
          <div class="box-body table-responsive no-padding">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>Loan ID</th>
                  <th>Loan Amount</th>
                  <th>Outstanding Balance</th>
                  <th>Installment</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($loan_details as $loan) { ?>
                  <tr>
                    <td><?= htmlspecialchars($loan['loan_id']); ?></td>
                    <td><?= htmlspecialchars($loan['loan_amount']); ?></td>
                    <td><?= htmlspecialchars($loan['outstanding_balance']); ?></td>
                    <td><?= htmlspecialchars($loan['installment']); ?></td>
                    <td><?= htmlspecialchars($loan['loan_status']); ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

        <h3>Make a Payment</h3>
        <form action="loan_balance.php" method="POST" class="form-horizontal">
          <div class="form-group">
            <label for="loan_id" class="col-sm-2 control-label">Select Loan:</label>
            <div class="col-sm-10">
              <select name="loan_id" id="loan_id" class="form-control" required>
                <?php foreach ($loan_details as $loan) { ?>
                  <option value="<?= htmlspecialchars($loan['loan_id']); ?>">Loan ID: <?= htmlspecialchars($loan['loan_id']); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="payment_amount" class="col-sm-2 control-label">Payment Amount:</label>
            <div class="col-sm-10">
              <input type="number" name="payment_amount" id="payment_amount" class="form-control" required>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <input type="submit" name="pay_loan" value="Pay Loan" class="btn btn-primary">
            </div>
          </div>
        </form>
      <?php } ?>
    </section>
  </div>

  <footer class="main-footer">
    <strong>&copy; CDBL Payroll Management System | </strong> Developed By CDBL VAS Team 2025
  </footer>
</div>

<script src="<?php echo BASE_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="<?php echo BASE_URL; ?>bootstrap/js/bootstrap.min.js"></script>
<script src="<?php echo BASE_URL; ?>dist/js/app.min.js"></script>
</body>
</html>
