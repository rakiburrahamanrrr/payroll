<?php
// loan_balance.php

// Include database connection
require 'config.php';  // Make sure this file contains the correct database connection details

function processLoanPayment($loan_id, $deduction_amount) {
    global $db;

    // Step 1: Retrieve the current remaining balance
    $query = "SELECT remaining_balance FROM loan_balance WHERE loan_id = ? ORDER BY loan_balance_id DESC LIMIT 1";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $loan_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $current_balance);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Check if the loan exists
    if ($current_balance === null) {
        echo "Error: No balance record found for loan_id $loan_id. Please ensure loan balance data exists.";
        return;
    }

    // Step 2: Calculate the new remaining balance
    $new_balance = $current_balance - $deduction_amount;

    // Step 3: Record the payment in loan_history table
    $query_history = "INSERT INTO loan_history (loan_id, deduction_amount, deduction_month) VALUES (?, ?, ?)";
    $stmt_history = mysqli_prepare($db, $query_history);
    $current_date = date('Y-m-d');
    mysqli_stmt_bind_param($stmt_history, "ids", $loan_id, $deduction_amount, $current_date);
    mysqli_stmt_execute($stmt_history);
    mysqli_stmt_close($stmt_history);

    // Step 4: Insert new loan balance record into loan_balance table
    $query_balance = "INSERT INTO loan_balance (loan_id, deduction_amount, deduction_month, remaining_balance) VALUES (?, ?, ?, ?)";
    $stmt_balance = mysqli_prepare($db, $query_balance);
    mysqli_stmt_bind_param($stmt_balance, "idsd", $loan_id, $deduction_amount, $current_date, $new_balance);
    mysqli_stmt_execute($stmt_balance);
    mysqli_stmt_close($stmt_balance);

    // Step 5: Check if the loan is paid off (remaining balance is 0 or less)
    if ($new_balance <= 0) {
        // Step 6: Update loan status to 'completed' in loan_requests table
        $query_update = "UPDATE loan_requests SET loan_status = 'completed', complete_date = NOW() WHERE loan_id = ?";
        $stmt_update = mysqli_prepare($db, $query_update);
        mysqli_stmt_bind_param($stmt_update, "i", $loan_id);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
        echo "Loan fully paid off. Status updated to 'completed'.";
    } else {
        echo "Payment successful. Remaining balance: $new_balance";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get loan ID and deduction amount from the form
    $loan_id = $_POST['loan_id'] ?? null;
    $deduction_amount = $_POST['deduction_amount'] ?? null;

    // Validate input
    if ($loan_id && $deduction_amount) {
        processLoanPayment($loan_id, $deduction_amount);
        // Redirect to avoid form resubmission on refresh
        header("Location: loan_balance.php");
        exit;
    } else {
        echo "Error: Please provide both loan ID and deduction amount.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Loan Payment Deduction</title>
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php require_once(dirname(__FILE__) . '/partials/topnav.php'); ?>
  <?php require_once(dirname(__FILE__) . '/partials/sidenav.php'); ?>

  <div class="content-wrapper" style="min-height: 916px;">
    <!-- <section class="content-header">
      <h1>Loan Payment Deduction</h1>
    </section>

    <section class="content">
      <form action="loan_balance.php" method="POST" class="form-horizontal">
        <div class="form-group">
          <label for="loan_id" class="col-sm-2 control-label">Loan ID:</label>
          <div class="col-sm-10">
            <input type="number" id="loan_id" name="loan_id" class="form-control" required>
          </div>
        </div>

        <div class="form-group">
          <label for="deduction_amount" class="col-sm-2 control-label">Deduction Amount:</label>
          <div class="col-sm-10">
            <input type="number" id="deduction_amount" name="deduction_amount" class="form-control" required>
          </div>
        </div>

        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-primary">Process Payment</button>
          </div>
        </div>
      </form>
    </section> -->

    <section class="content">
      <h3>Loan Summary by Category</h3>
      <?php
      if (isset($_SESSION['Admin_ID'])) {
          $employeeId = $_SESSION['Admin_ID'];

          $summaryQuery = "
            SELECT lc.category_name,
                   lr.loan_amount,
                   SUM(lb_sum.total_deduction) AS total_deduction,
                   SUM(latest_lb.remaining_balance) AS remaining_balance
              FROM loan_requests lr
              JOIN loan_categories lc ON lr.category_id = lc.category_id
              JOIN (
                SELECT loan_id, SUM(deduction_amount) AS total_deduction
                  FROM loan_balance
                 GROUP BY loan_id
              ) lb_sum ON lr.loan_id = lb_sum.loan_id
              JOIN (
                SELECT lb1.loan_id, lb1.remaining_balance
                  FROM loan_balance lb1
                  JOIN (
                    SELECT loan_id, MAX(loan_balance_id) AS max_id
                      FROM loan_balance
                     GROUP BY loan_id
                  ) lb2 ON lb1.loan_id = lb2.loan_id AND lb1.loan_balance_id = lb2.max_id
              ) latest_lb ON lr.loan_id = latest_lb.loan_id
             WHERE lr.employee_id = ?
             GROUP BY lc.category_name, lr.loan_amount
          ";
          $stmt_summary = mysqli_prepare($db, $summaryQuery);
          mysqli_stmt_bind_param($stmt_summary, "i", $employeeId);
          mysqli_stmt_execute($stmt_summary);
          $result_summary = mysqli_stmt_get_result($stmt_summary);

          if ($result_summary && mysqli_num_rows($result_summary) > 0) {
              echo '<table class="table table-bordered">';
              echo '<thead><tr><th>Category</th><th>Loan Amount</th><th>Total Deduction</th><th>Remaining Balance</th></tr></thead><tbody>';
              while ($row = mysqli_fetch_assoc($result_summary)) {
                  echo '<tr>';                  
                  echo '<td>' . htmlspecialchars($row['category_name']) . '</td>';
                  echo '<td>' . number_format($row['loan_amount'], 2) . '</td>';
                  echo '<td>' . number_format($row['total_deduction'], 2) . '</td>';
                  echo '<td>' . number_format($row['remaining_balance'], 2) . '</td>';
                  echo '</tr>';
              }
              echo '</tbody></table>';
          } else {
              echo '<p>No loan summary data available.</p>';
          }
          mysqli_stmt_close($stmt_summary);
      } else {
          echo '<p>Please log in to view loan summary.</p>';
      }
      ?>
    </section>
  </div>

  <?php require_once(dirname(__FILE__) . '/partials/footer.php'); ?>
</div>

<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="dist/js/app.min.js"></script>
</body>
</html>
