<?php
// admin_loan_approval.php
require 'config.php';

// Check admin rights
if (!isset($_SESSION['Admin_ID']) || ($_SESSION['Login_Type'] ?? '') !== 'admin') {
    die("Access denied.");
}

// Fetch all pending loan requests with employee and category details
$sql = "
  SELECT lr.loan_id, lr.employee_id, e.first_name, e.last_name,
         lc.category_name, lr.loan_amount, lr.loan_installment_amount,
         lr.reason, lr.requested_date
    FROM loan_requests lr
    LEFT JOIN loan_categories lc ON lr.category_id = lc.category_id
    LEFT JOIN cdbl_employees   e  ON lr.employee_id = e.employee_id
   WHERE lr.loan_status = 'pending'
   ORDER BY lr.requested_date DESC
";
$result = mysqli_query($db, $sql);
$requests = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $requests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pending Loan Requests</title>
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
      <h1>Pending Loan Requests</h1>
    </section>

    <section class="content">
      <?php if (empty($requests)): ?>
        <div class="alert alert-info">No pending requests.</div>
      <?php else: ?>
        <div class="box">
          <div class="box-body table-responsive no-padding">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>Loan ID</th>
                  <th>Employee</th>
                  <th>Type</th>
                  <th>Amount</th>
                  <th>Installment</th>
                  <th>Reason</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($requests as $r): ?>
                <tr>
                  <td><?= htmlspecialchars($r['loan_id']) ?></td>
                  <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                  <td><?= htmlspecialchars($r['category_name']) ?></td>
                  <td><?= number_format($r['loan_amount'], 2) ?></td>
                  <td><?= number_format($r['loan_installment_amount'], 2) ?></td>
                  <td><?= nl2br(htmlspecialchars($r['reason'])) ?></td>
                  <td><?= htmlspecialchars($r['requested_date']) ?></td>
                  <td>
                    <form method="post" action="process_loan_action.php" style="display:inline;">
                      <input type="hidden" name="loan_id" value="<?= htmlspecialchars($r['loan_id']) ?>">
                      <input type="hidden" name="action" value="approve">
                      <input type="hidden" name="effective_date" value="<?= date('Y-m-d') ?>">
                      <button class="btn btn-success btn-xs" type="submit">Approve</button>
                    </form>
                    <form method="post" action="process_loan_action.php" style="display:inline;">
                      <input type="hidden" name="loan_id" value="<?= htmlspecialchars($r['loan_id']) ?>">
                      <button class="btn btn-danger btn-xs" name="action" value="reject">Reject</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
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
  $(document).ready(function() {
    console.log("Document ready"); // Global debug log
  });
</script>
</body>
</html>

