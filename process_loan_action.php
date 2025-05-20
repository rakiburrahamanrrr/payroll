<?php
// process_loan_action.php
require 'config.php';

// Check admin session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] !== 'admin') {
    die("Access denied.");
}

// Get loan ID and action from POST
$loanId = (int) $_POST['loan_id'] ?? 0;
$action = $_POST['action'] ?? '';

if ($loanId === 0 || !in_array($action, ['approve', 'reject'])) {
    die("Invalid request.");
}

// Prepare the update query based on action
if ($action === 'approve') {
    // Approve loan request
    $effectiveDate = $_POST['effective_date'] ?? null;
    if ($effectiveDate) {
        $query = "UPDATE loan_requests SET loan_status = 'approved', approved_date = NOW(), effective_date = ? WHERE loan_id = ?";
    } else {
        die("Effective date is required for approval.");
    }
} else {
    // Reject loan request
    $query = "UPDATE loan_requests SET loan_status = 'rejected' WHERE loan_id = ?";
}

// Prepare statement
$stmt = mysqli_prepare($db, $query);
if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($db));
}

if ($action === 'approve') {
    // Bind effective_date and loanId
    mysqli_stmt_bind_param($stmt, 'si', $effectiveDate, $loanId);
} else {
    // Bind only loanId
    mysqli_stmt_bind_param($stmt, 'i', $loanId);
}

if (mysqli_stmt_execute($stmt)) {
    if ($action === 'approve') {
        // Insert initial loan_balance record after approval
        $query_loan = "SELECT loan_amount, loan_installment_amount FROM loan_requests WHERE loan_id = ?";
        $stmt_loan = mysqli_prepare($db, $query_loan);
        mysqli_stmt_bind_param($stmt_loan, "i", $loanId);
        mysqli_stmt_execute($stmt_loan);
        mysqli_stmt_bind_result($stmt_loan, $loanAmount, $installmentAmount);
        mysqli_stmt_fetch($stmt_loan);
        mysqli_stmt_close($stmt_loan);

        $query_insert_balance = "INSERT INTO loan_balance (loan_id, deduction_amount, deduction_month, remaining_balance) VALUES (?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($db, $query_insert_balance);
        $current_date = $effectiveDate;
        $remainingBalance = $loanAmount - $installmentAmount;
        mysqli_stmt_bind_param($stmt_insert, "idsd", $loanId, $installmentAmount, $current_date, $remainingBalance);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
    }
    // After execution, redirect with status
    header("Location: admin_loan_approval.php?status=success");
    exit;
} else {
    // Handle error during execution
    die("Error updating loan status: " . mysqli_error($db));
}

mysqli_stmt_close($stmt);
?>
