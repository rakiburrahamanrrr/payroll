<?php
// Extracted lines 340 to 380 from loan_slip_assign.php for analysis

if (count($already_processed_loans) > 0) {
    $message .= "Loan slip already processed for Loan IDs: " . implode(', ', $already_processed_loans) . " for " . date('F Y', strtotime($deduction_month)) . ". ";
    $message .= "If you want to readjust the loan deduction amount, please provide the details below.";
    $show_readjust_form = true;
}

if (count($newly_processed_loans) > 0) {
    $message .= "Loan slip processed successfully for Loan IDs: " . implode(', ', $newly_processed_loans) . " for " . date('F Y', strtotime($deduction_month)) . ".";
}
