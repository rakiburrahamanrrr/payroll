<?php
// loan_request_confirmation.php

require_once 'config.php';  // Include config.php to define BASE_URL and other constants

// Start the session to check if the user is logged in
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is authorized
if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] !== 'emp') {
    // If not authorized, redirect to the login page or show an error message
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Loan Request Confirmation</title>
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
      <h1>Loan Request Submitted</h1>
    </section>

    <section class="content">
      <div class="alert alert-success" role="alert">
        <p>Your loan request has been successfully submitted and is now pending for approval.</p>
        <p>We will notify you once your request has been processed.</p>
      </div>


      <a href="profile.php" class="btn btn-primary">Go to Profile</a>
    </section>
  </div>

  <?php require_once(dirname(__FILE__) . '/partials/footer.php'); ?>
</div>

<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="dist/js/app.min.js"></script>
</body>
</html>
