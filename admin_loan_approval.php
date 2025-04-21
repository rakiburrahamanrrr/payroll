<?php
require_once('config.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as an admin
if (!isset($_SESSION['Login_Type']) || $_SESSION['Login_Type'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch pending loan requests
$loan_requests = [];
$query = "SELECT lr.loan_id, lr.employee_id, lr.loan_amount, lr.installment, lr.loan_payment_amount, lr.interest_amount, lr.loan_status, lc.category_name
          FROM loan_requests lr
          JOIN loan_categories lc ON lr.category_id = lc.category_id
          WHERE lr.loan_status = 'Pending'";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $loan_requests[] = $row;
}

// Handle loan approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
    $loan_id = $_POST['loan_id'];
    $loan_approved_date = date('Y-m-d');
    $effective_date = date('Y-m-d', strtotime('+1 month')); // Example: Set effective date to 1 month from approval

    $query = "UPDATE loan_requests 
              SET loan_status = 'Loan Approved', loan_approved_date = '$loan_approved_date', effective_date = '$effective_date'
              WHERE loan_id = '$loan_id'";

    if (mysqli_query($db, $query)) {
        $message = "Loan approved successfully!";
    } else {
        $message = "Error approving loan: " . mysqli_error($db);
    }
}

// Handle loan completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete'])) {
    $loan_id = $_POST['loan_id'];

    $query = "UPDATE loan_requests SET loan_status = 'Loan Completed' WHERE loan_id = '$loan_id'";
    
    if (mysqli_query($db, $query)) {
        $query_history = "INSERT INTO loan_history (loan_id, action, action_date) 
                          VALUES ('$loan_id', 'Loan Completed', NOW())";
        mysqli_query($db, $query_history);
        $message = "Loan completed successfully!";
    } else {
        $message = "Error completing loan: " . mysqli_error($db);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <title>Admin Loan Approval - Payroll</title>
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
      <h1>Loan Approval Requests</h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Loan Approval</li>
      </ol>
    </section>
    <section class="content">
      <?php if (isset($message)) { echo '<div class="alert alert-info">' . htmlspecialchars($message) . '</div>'; } ?>
      <div class="box">
        <div class="box-body table-responsive no-padding">
          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <th>Loan ID</th>
                <th>Employee ID</th>
                <th>Loan Category</th>
                <th>Loan Amount</th>
                <th>Installment</th>
                <th>Payment Amount</th>
                <th>Interest Amount</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($loan_requests as $loan) { ?>
                <tr>
                  <td><?= htmlspecialchars($loan['loan_id']); ?></td>
                  <td><?= htmlspecialchars($loan['employee_id']); ?></td>
                  <td><?= htmlspecialchars($loan['category_name']); ?></td>
                  <td><?= htmlspecialchars($loan['loan_amount']); ?></td>
                  <td><?= htmlspecialchars($loan['installment']); ?></td>
                  <td><?= htmlspecialchars($loan['loan_payment_amount']); ?></td>
                  <td><?= htmlspecialchars($loan['interest_amount']); ?></td>
                  <td><?= htmlspecialchars($loan['loan_status']); ?></td>
                  <td>
                    <form action="admin_loan_approval.php" method="POST" style="display:inline;">
                      <input type="hidden" name="loan_id" value="<?= htmlspecialchars($loan['loan_id']); ?>">
                      <input type="submit" name="approve" value="Approve" class="btn btn-success btn-xs">
                    </form>
                    <form action="admin_loan_approval.php" method="POST" style="display:inline;">
                      <input type="hidden" name="loan_id" value="<?= htmlspecialchars($loan['loan_id']); ?>">
                      <input type="submit" name="complete" value="Complete" class="btn btn-primary btn-xs">
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
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
