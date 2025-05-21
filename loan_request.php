<?php
// create_loan_request.php
require 'config.php';

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Ensure the session is started for user login checks
}

// Check if the user is an employee (logged in and with 'emp' login type)
if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] !== 'emp') {
    $notAuthorized = true;
} else {
    $notAuthorized = false;
    $empCode = $_SESSION['Admin_ID'];  // Use $_SESSION['Admin_ID'] for employee identification
}

// Handle the request if the user is authorized
if (!$notAuthorized) {
    // Fetch loan categories
    $result = $db->query("SELECT category_id, category_name, description FROM loan_categories");
    $categories = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    // Handle the loan request submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $categoryId    = (int) $_POST['category_id'];
        $loanAmount    = (float) $_POST['loan_amount'];
        $installAmount = (float) $_POST['loan_installment_amount'];
        $reason        = trim($_POST['reason']);

        // Basic validation
        $errors = [];
        if ($loanAmount <= 0) {
            $errors[] = "Loan amount must be positive.";
        }
        if ($installAmount <= 0) {
            $errors[] = "Installment amount must be positive.";
        }
        if ($installAmount > $loanAmount) {
            $errors[] = "Installment cannot exceed total loan amount.";
        }
        if (!$reason) {
            $errors[] = "Please provide a reason for the loan.";
        }

        if (empty($errors)) {
            // Sanitize inputs
            $reason = htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');

            // Prepare the SQL statement
            $sql = "INSERT INTO loan_requests (employee_id, category_id, loan_amount, loan_installment_amount, reason, requested_date)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $reqDate = date('Y-m-d');
            if ($stmt) {
                // Bind parameters, ensure the correct types for each value
                $stmt->bind_param("siddss", $empCode, $categoryId, $loanAmount, $installAmount, $reason, $reqDate);
                $stmt->execute();

                // Redirect to a confirmation page
                header('Location: loan_request_confirm.php');
                exit;
            } else {
                $errors[] = "Database error: Unable to submit your loan request.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Request a Loan</title>
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.css">
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php require_once(dirname(__FILE__) . '/partials/topnav.php'); ?>
  <?php require_once(dirname(__FILE__) . '/partials/sidenav.php'); ?>

  <div class="content-wrapper" style="min-height: 916px;">
    <section class="content-header">
      <h1>Loan Request</h1>
    </section>

    <section class="content">
      <?php if ($notAuthorized): ?>
        <div class="alert alert-warning" role="alert">
          Access denied. You do not have permission to request a loan.
        </div>
      <?php else: ?>
        <div class="row">
          <div class="col-md-8">
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                  <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="post" class="needs-validation" novalidate>
              <div class="form-group">
                <label for="category_id">Loan Type:</label>
                <select id="category_id" name="category_id" class="form-control" required>
                  <option value="">— Select —</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>">
                      <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <div class="help-block with-errors"></div>
              </div>

              <div class="form-group">
                <label for="loan_amount">Loan Amount:</label>
                <input type="number" id="loan_amount" name="loan_amount" step="0.01" class="form-control" required>
                <div class="help-block with-errors"></div>
              </div>

              <div class="form-group">
                <label for="loan_installment_amount">Monthly Installment:</label>
                <input type="number" id="loan_installment_amount" name="loan_installment_amount" step="0.01" class="form-control" required>
                <div class="help-block with-errors"></div>
              </div>

              <div class="form-group">
                <label for="reason">Reason:</label>
                <textarea id="reason" name="reason" rows="4" class="form-control" required></textarea>
                <div class="help-block with-errors"></div>
              </div>

              <button type="submit" class="btn btn-primary">Submit Request</button>
            </form>

            <?php
            // Query to fetch loan requests for the current user
            $empCode = $_SESSION['Admin_ID'];
            $sql = "SELECT lr.loan_id, lc.category_name, lr.loan_amount, lr.loan_installment_amount, lr.reason, lr.requested_date, lr.loan_status
                    FROM loan_requests lr
                    JOIN loan_categories lc ON lr.category_id = lc.category_id
                    WHERE lr.employee_id = ?
                    ORDER BY lr.requested_date DESC";
            $stmt = $db->prepare($sql);
            $loanRequests = [];
            if ($stmt) {
                $stmt->bind_param("s", $empCode);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $loanRequests[] = $row;
                }
                $stmt->close();
            }
            ?>

            <?php if (!empty($loanRequests)): ?>
              <h3>Your Loan Requests</h3>
              <div class="table-responsive">
                <table class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <th>Loan ID</th>
                      <th>Type</th>
                      <th>Amount</th>
                      <th>Installment</th>
                      <th>Reason</th>
                      <th>Date</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($loanRequests as $loan): ?>
                      <tr>
                        <td><?= htmlspecialchars($loan['loan_id']) ?></td>
                        <td><?= htmlspecialchars($loan['category_name']) ?></td>
                        <td><?= number_format($loan['loan_amount'], 2) ?></td>
                        <td><?= number_format($loan['loan_installment_amount'], 2) ?></td>
                        <td><?= nl2br(htmlspecialchars($loan['reason'])) ?></td>
                        <td><?= htmlspecialchars($loan['requested_date']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($loan['loan_status'])) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p>No previous loan requests found.</p>
            <?php endif; ?>
          </div>
          <div class="col-md-4">
            <h3>Loan Category Details</h3>
            <ul class="list-group">
              <?php foreach ($categories as $cat): ?>
                <li class="list-group-item">
                  <?= htmlspecialchars($cat['category_name']) ?>
                  <?php if (isset($cat['description'])): ?>
                    <br><small><?= htmlspecialchars($cat['description']) ?></small>
                  <?php endif; ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>
    </section>
  </div>

  <?php require_once(dirname(__FILE__) . '/partials/footer.php'); ?>
</div>

<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="dist/js/app.min.js"></script>
<script>
  // Bootstrap form validation
  (function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
      .forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
  })()
</script>
</body>
</html>
