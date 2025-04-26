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

// Fetch loan categories from the database
$categories = [];
$query = "SELECT * FROM loan_categories";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the loan request submission
    $employee_id = $_SESSION['Admin_ID'];
    $category_id = $_POST['category_id'];
    $loan_amount = $_POST['loan_amount'];
    $installment = $_POST['installment'];
    $loan_payment_amount = $_POST['loan_payment_amount'];
    $interest_amount = $_POST['interest_amount'];
    
    $query = "INSERT INTO loan_requests (employee_id, category_id, loan_amount, installment, loan_payment_amount, interest_amount, loan_status, created_at) 
              VALUES ('$employee_id', '$category_id', '$loan_amount', '$installment', '$loan_payment_amount', '$interest_amount', 'Pending', NOW())";
    if (mysqli_query($db, $query)) {
        $message = "Loan request submitted successfully!";
    } else {
        $message = "Error submitting loan request: " . mysqli_error($db);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <title>Loan Request - Payroll</title>
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
      <h1>Loan Request</h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Loan Request</li>
      </ol>
    </section>
    <section class="content">
      <?php if (isset($message)) { echo '<div class="alert alert-info">' . htmlspecialchars($message) . '</div>'; } ?>
      <form action="loan_request.php" method="POST" class="form-horizontal">
        <div class="form-group">
          <label for="category_id" class="col-sm-2 control-label">Loan Category:</label>
          <div class="col-sm-10">
            <select name="category_id" id="category_id" class="form-control" required>
              <?php foreach ($categories as $category) { ?>
                <option value="<?= htmlspecialchars($category['category_id']); ?>"><?= htmlspecialchars($category['category_name']); ?></option>
              <?php } ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="loan_amount" class="col-sm-2 control-label">Loan Amount:</label>
          <div class="col-sm-10">
            <input type="number" name="loan_amount" id="loan_amount" class="form-control" required>
          </div>
        </div>
        <div class="form-group">
          <label for="installment" class="col-sm-2 control-label">Installment:</label>
          <div class="col-sm-10">
            <input type="number" name="installment" id="installment" class="form-control" required>
          </div>
        </div>
        <div class="form-group">
          <label for="loan_payment_amount" class="col-sm-2 control-label">Loan Payment Amount:</label>
          <div class="col-sm-10">
            <input type="number" name="loan_payment_amount" id="loan_payment_amount" class="form-control" required>
          </div>
        </div>
        <div class="form-group">
          <label for="interest_amount" class="col-sm-2 control-label">Interest Amount:</label>
          <div class="col-sm-10">
            <input type="number" name="interest_amount" id="interest_amount" class="form-control" required>
          </div>
        </div>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <input type="submit" value="Submit Loan Request" class="btn btn-primary">
          </div>
        </div>
      </form>
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
