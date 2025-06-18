<?php
function BulkEmployeeUpload()
{
    $result = array();
    global $db;

    if (!isset($_FILES['employeeExcelFile']) || $_FILES['employeeExcelFile']['error'] !== UPLOAD_ERR_OK) {
        $result['code'] = 1;
        $result['result'] = 'No file uploaded or upload error.';
        echo json_encode($result);
        return;
    }

    $uploadedFile = $_FILES['employeeExcelFile']['tmp_name'];
    $extension = pathinfo($_FILES['employeeExcelFile']['name'], PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), ['xls', 'xlsx'])) {
        $result['code'] = 2;
        $result['result'] = 'Invalid file type. Please upload an Excel file (.xls or .xlsx).';
        echo json_encode($result);
        return;
    }

    // require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

    // Use fully qualified class name
    $IOFactory = '\PhpOffice\PhpSpreadsheet\IOFactory';

    try {
        $spreadsheet = $IOFactory::load($uploadedFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Assuming first row is header, start from second row
        $successCount = 0;
        $failCount = 0;
        $failMessages = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Map columns to employee fields - adjust indexes as per your Excel format
            $emp_code = isset($row[0]) ? trim($row[0]) : '';
            $first_name = isset($row[1]) ? trim($row[1]) : '';
            $last_name = isset($row[2]) ? trim($row[2]) : '';
            $dob = isset($row[3]) ? trim($row[3]) : '';
            $email = isset($row[4]) ? trim($row[4]) : '';
            $mobile = isset($row[5]) ? trim($row[5]) : '';
            $emp_grade = isset($row[6]) ? trim($row[6]) : '';
            $empsal_grade = isset($row[7]) ? trim($row[7]) : '';
            $joining_date = isset($row[8]) ? trim($row[8]) : '';
            $blood_group = isset($row[9]) ? trim($row[9]) : '';
            $employment_type = isset($row[10]) ? trim($row[10]) : '';

            // Additional required fields with default values or empty strings
            $gender = isset($row[11]) ? trim($row[11]) : 'male';
            $marital_status = isset($row[12]) ? trim($row[12]) : 'Single';
            $address = isset($row[13]) ? trim($row[13]) : '';
            $paraddress = isset($row[14]) ? trim($row[14]) : '';
            $national_id = isset($row[15]) ? trim($row[15]) : '';
            $verification = isset($row[16]) ? trim($row[16]) : 'Not Verified';
            $employee_id = isset($row[17]) ? intval($row[17]) : 0;
            $employment_status = isset($row[18]) ? trim($row[18]) : 'Active';
            $department = isset($row[19]) ? trim($row[19]) : '';
            $designation = isset($row[20]) ? trim($row[20]) : '';
            $photo = isset($row[21]) ? trim($row[21]) : 'default.jpg';
            $emp_password = isset($row[22]) ? trim($row[22]) : sha1('defaultpassword');
            $insurance_id = isset($row[23]) ? trim($row[23]) : '';
            $account_no = isset($row[24]) ? trim($row[24]) : '';
            $etin_no = isset($row[25]) ? trim($row[25]) : '';

            // Validate required fields
            if (empty($emp_code) || empty($first_name) || empty($last_name) || empty($email) || empty($mobile) || empty($national_id)) {
                $failCount++;
                $failMessages[] = "Row " . ($i + 1) . ": Missing required fields.";
                continue;
            }

            // Check for unique constraints: email, mobile, national_id
            $email_esc = mysqli_real_escape_string($db, $email);
            $mobile_esc = mysqli_real_escape_string($db, $mobile);
            $national_id_esc = mysqli_real_escape_string($db, $national_id);

            $uniqueCheckSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "employees` WHERE `email` = '$email_esc' OR `mobile` = '$mobile_esc' OR `national_id` = '$national_id_esc' LIMIT 1");
            if ($uniqueCheckSQL && mysqli_num_rows($uniqueCheckSQL) > 0) {
                $failCount++;
                $failMessages[] = "Row " . ($i + 1) . ": Duplicate email, mobile, or national ID.";
                continue;
            }

            // Check if employee exists by emp_code
            $emp_code_esc = mysqli_real_escape_string($db, $emp_code);
            $checkSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "employees` WHERE `emp_code` = '$emp_code_esc' LIMIT 1");
            if ($checkSQL && mysqli_num_rows($checkSQL) > 0) {
                // Update existing employee
                $updateSQL = "UPDATE `" . DB_PREFIX . "employees` SET 
                    `first_name` = '" . mysqli_real_escape_string($db, $first_name) . "',
                    `last_name` = '" . mysqli_real_escape_string($db, $last_name) . "',
                    `dob` = '" . mysqli_real_escape_string($db, $dob) . "',
                    `email` = '" . mysqli_real_escape_string($db, $email) . "',
                    `mobile` = '" . mysqli_real_escape_string($db, $mobile) . "',
                    `emp_grade` = '" . mysqli_real_escape_string($db, $emp_grade) . "',
                    `empsal_grade` = '" . mysqli_real_escape_string($db, $empsal_grade) . "',
                    `joining_date` = '" . mysqli_real_escape_string($db, $joining_date) . "',
                    `blood_group` = '" . mysqli_real_escape_string($db, $blood_group) . "',
                    `employment_type` = '" . mysqli_real_escape_string($db, $employment_type) . "',
                    `gender` = '" . mysqli_real_escape_string($db, $gender) . "',
                    `marital_status` = '" . mysqli_real_escape_string($db, $marital_status) . "',
                    `address` = '" . mysqli_real_escape_string($db, $address) . "',
                    `paraddress` = '" . mysqli_real_escape_string($db, $paraddress) . "',
                    `national_id` = '" . mysqli_real_escape_string($db, $national_id) . "',
                    `verification` = '" . mysqli_real_escape_string($db, $verification) . "',
                    `employee_id` = " . intval($employee_id) . ",
                    `employment_status` = '" . mysqli_real_escape_string($db, $employment_status) . "',
                    `department` = '" . mysqli_real_escape_string($db, $department) . "',
                    `designation` = '" . mysqli_real_escape_string($db, $designation) . "',
                    `photo` = '" . mysqli_real_escape_string($db, $photo) . "',
                    `emp_password` = '" . mysqli_real_escape_string($db, $emp_password) . "',
                    `insurance_id` = '" . mysqli_real_escape_string($db, $insurance_id) . "',
                    `account_no` = '" . mysqli_real_escape_string($db, $account_no) . "',
                    `etin_no` = '" . mysqli_real_escape_string($db, $etin_no) . "'
                    WHERE `emp_code` = '$emp_code_esc'";
                $updateResult = mysqli_query($db, $updateSQL);
                if ($updateResult) {
                    $successCount++;
                } else {
                    $failCount++;
                    $failMessages[] = "Row " . ($i + 1) . ": Failed to update employee.";
                }
            } else {
                // Insert new employee
                $insertSQL = "INSERT INTO `" . DB_PREFIX . "employees` 
                    (`emp_code`, `first_name`, `last_name`, `dob`, `email`, `mobile`, `emp_grade`, `empsal_grade`, `joining_date`, `blood_group`, `employment_type`, `gender`, `marital_status`, `address`, `paraddress`, `national_id`, `verification`, `employee_id`, `employment_status`, `department`, `designation`, `photo`, `emp_password`, `insurance_id`, `account_no`, `etin_no`) VALUES (
                    '" . mysqli_real_escape_string($db, $emp_code) . "',
                    '" . mysqli_real_escape_string($db, $first_name) . "',
                    '" . mysqli_real_escape_string($db, $last_name) . "',
                    '" . mysqli_real_escape_string($db, $dob) . "',
                    '" . mysqli_real_escape_string($db, $email) . "',
                    '" . mysqli_real_escape_string($db, $mobile) . "',
                    '" . mysqli_real_escape_string($db, $emp_grade) . "',
                    '" . mysqli_real_escape_string($db, $empsal_grade) . "',
                    '" . mysqli_real_escape_string($db, $joining_date) . "',
                    '" . mysqli_real_escape_string($db, $blood_group) . "',
                    '" . mysqli_real_escape_string($db, $employment_type) . "',
                    '" . mysqli_real_escape_string($db, $gender) . "',
                    '" . mysqli_real_escape_string($db, $marital_status) . "',
                    '" . mysqli_real_escape_string($db, $address) . "',
                    '" . mysqli_real_escape_string($db, $paraddress) . "',
                    '" . mysqli_real_escape_string($db, $national_id) . "',
                    '" . mysqli_real_escape_string($db, $verification) . "',
                    " . intval($employee_id) . ",
                    '" . mysqli_real_escape_string($db, $employment_status) . "',
                    '" . mysqli_real_escape_string($db, $department) . "',
                    '" . mysqli_real_escape_string($db, $designation) . "',
                    '" . mysqli_real_escape_string($db, $photo) . "',
                    '" . mysqli_real_escape_string($db, $emp_password) . "',
                    '" . mysqli_real_escape_string($db, $insurance_id) . "',
                    '" . mysqli_real_escape_string($db, $account_no) . "',
                    '" . mysqli_real_escape_string($db, $etin_no) . "'
                )";
                $insertResult = mysqli_query($db, $insertSQL);
                if ($insertResult) {
                    $successCount++;
                } else {
                    $failCount++;
                    $failMessages[] = "Row " . ($i + 1) . ": Failed to insert employee.";
                }
            }
        }

        $result['code'] = 0;
        $result['result'] = "Bulk upload completed. Success: $successCount, Failures: $failCount.";
        if ($failCount > 0) {
            $result['result'] .= " Details: " . implode(' ', $failMessages);
        }
    } catch (Exception $e) {
        $result['code'] = 3;
        $result['result'] = 'Error reading Excel file: ' . $e->getMessage();
    }

    echo json_encode($result);
}
?>
