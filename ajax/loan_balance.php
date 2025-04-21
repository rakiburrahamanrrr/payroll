<?php
require_once('config.php');
session_start();

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Loan Balance</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Your Loan Balance</h2>
    <?php if (empty($loan_details)) { echo "<p>You don't have any approved loans.</p>"; } else { ?>
        <table border="1">
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
                        <td><?= $loan['loan_id']; ?></td>
                        <td><?= $loan['loan_amount']; ?></td>
                        <td><?= $loan['outstanding_balance']; ?></td>
                        <td><?= $loan['installment']; ?></td>
                        <td><?= $loan['loan_status']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

    <!-- Payment Form -->
    <h3>Make a Payment</h3>
    <form action="loan_balance.php" method="POST">
        <label for="loan_id">Select Loan:</label>
        <select name="loan_id" required>
            <?php foreach ($loan_details as $loan) { ?>
                <option value="<?= $loan['loan_id']; ?>">Loan ID: <?= $loan['loan_id']; ?></option>
            <?php } ?>
        </select><br><br>

        <label for="payment_amount">Payment Amount:</label>
        <input type="number" name="payment_amount" required><br><br>

        <input type="submit" name="pay_loan" value="Pay Loan">
    </form>
</body>
</html>
