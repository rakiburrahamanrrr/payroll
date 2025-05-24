<?php
require_once(dirname(__FILE__, 2) . '/config.php');

if (!isset($_SESSION['Admin_ID']) || !isset($_SESSION['Login_Type'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$conn = $db; // Assuming $db is the mysqli connection from config.php

// Function to calculate monthly PF contribution
function calculatePfContribution($conn, $employee_id) {
    // Fetch basic_salary and employee_type
    $sql = "SELECT ps.basic_salary, emp.employee_type FROM cdbl_payscale_grade AS ps
            JOIN cdbl_employees AS emp ON emp.emp_grade = ps.emp_grade AND emp.empsal_grade = ps.empsal_grade
            WHERE emp.employee_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [0, 0];
    }
    $stmt->bind_param('i', $employee_id);
    $stmt->execute();
    $basic_salary = null;
    $employee_type = null;
    $stmt->bind_result($basic_salary, $employee_type);
    $stmt->fetch();
    $stmt->close();

    if (!isset($basic_salary) || $basic_salary === null) {
        return [0, 0];
    }

    // Define contribution rates based on employee_type
    // Example rates: type A = 10%, type B = 8%, type C = 5%, default = 10%
    $contribution_rate = 0.10; // default 10%
    if ($employee_type === 'A') {
        $contribution_rate = 0.10;
    } elseif ($employee_type === 'B') {
        $contribution_rate = 0.08;
    } elseif ($employee_type === 'C') {
        $contribution_rate = 0.05;
    }

    // Calculate contributions based on rate
    $employee_contribution = $basic_salary * $contribution_rate;
    $company_contribution = $employee_contribution;

    return [$employee_contribution, $company_contribution];
}

// Function to update the PF balance for the employee
function updatePfBalance($conn, $employee_id, $month, $employee_contribution, $company_contribution) {
    $sql = "SELECT total_contribution FROM pf_balance WHERE employee_id = ? AND month = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('is', $employee_id, $month);
    $stmt->execute();
    $total_contribution = null;
    $stmt->bind_result($total_contribution);
    $stmt->fetch();
    $stmt->close();

    if (isset($total_contribution) && $total_contribution !== null) {
        $new_total_contribution = $total_contribution + $employee_contribution + $company_contribution;
        $sql = "UPDATE pf_balance SET employee_contribution = ?, company_contribution = ?, total_contribution = ? WHERE employee_id = ? AND month = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('dddis', $employee_contribution, $company_contribution, $new_total_contribution, $employee_id, $month);
        $stmt->execute();
        $stmt->close();
    } else {
        $total_contribution = $employee_contribution + $company_contribution;
        $sql = "INSERT INTO pf_balance (employee_id, month, employee_contribution, company_contribution, total_contribution, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('isdds', $employee_id, $month, $employee_contribution, $company_contribution, $total_contribution);
        $stmt->execute();
        $stmt->close();
    }
    return true;
}

// Function to get dashboard data
function getDashboardData($conn, $employee_id) {
    $data = [
        'yearly_contribution' => 0,
        'current_balance' => 0
    ];

    $sql = "SELECT SUM(company_contribution) AS yearly_contribution FROM pf_balance WHERE employee_id = ? AND MONTH(created_at) BETWEEN 1 AND 12";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $employee_id);
        $stmt->execute();
    $yearly_contribution = null;
    $stmt->bind_result($yearly_contribution);
    $stmt->fetch();
    $stmt->close();
    $data['yearly_contribution'] = isset($yearly_contribution) && $yearly_contribution !== null ? $yearly_contribution : 0;
    }

    $sql = "SELECT total_contribution FROM pf_balance WHERE employee_id = ? ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $employee_id);
        $stmt->execute();
    $current_balance = null;
    $stmt->bind_result($current_balance);
    $stmt->fetch();
    $stmt->close();
    $data['current_balance'] = isset($current_balance) && $current_balance !== null ? $current_balance : 0;
    }

    return $data;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
    $month = isset($_POST['month']) ? $_POST['month'] : '';

    if ($employee_id <= 0 || !$month) {
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    list($employee_contribution, $company_contribution) = calculatePfContribution($conn, $employee_id);

    if ($employee_contribution == 0 && $company_contribution == 0) {
        echo json_encode(['error' => 'Unable to calculate contributions. Check employee and payscale data.']);
        exit;
    }

    $updated = updatePfBalance($conn, $employee_id, $month, $employee_contribution, $company_contribution);

    if (!$updated) {
        echo json_encode(['error' => 'Failed to update PF balance']);
        exit;
    }

    $dashboard = getDashboardData($conn, $employee_id);

    echo json_encode([
        'success' => true,
        'employee_contribution' => number_format($employee_contribution, 2),
        'company_contribution' => number_format($company_contribution, 2),
        'yearly_contribution' => number_format($dashboard['yearly_contribution'], 2),
        'current_balance' => number_format($dashboard['current_balance'], 2)
    ]);
    exit;
}

echo json_encode(['error' => 'Invalid request method']);
exit;
?>
