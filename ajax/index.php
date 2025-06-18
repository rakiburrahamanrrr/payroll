<?php
/* require_once __DIR__ . '/../vendor/autoload.php';  // This is correct relative to your 'ajax' folder
include(dirname(dirname(__FILE__)) . '/config.php'); */
include(dirname(dirname(__FILE__)) . '/vendor/autoload.php');
//require_once __DIR__ . '/../vendor/autoload.php';  // This is correct relative to your 'ajax' folder
include(dirname(dirname(__FILE__)) . '/config.php');

define('DEBUG_MODE', true);
$case = isset($_GET['case']) ? $_GET['case'] : '';
switch ($case) {
	case 'LoginProcessHandler':
		LoginProcessHandler();
		break;
	// case 'AttendanceProcessHandler':
	// 	AttendanceProcessHandler();
	// 	break;
	// case 'LoadingAttendance':
	// 	LoadingAttendance();
	// 	break;
	case 'LoadingSalaries':
		LoadingSalaries();
		break;
	case 'LoadingPayscaleGrade':
		LoadingPayscaleGrade();
		break;

	case 'LoadingEmployees':
		LoadingEmployees();
		break;
	case 'AssignPayheadsToEmployee':
		AssignPayheadsToEmployee();
		break;
	case 'InsertUpdateHolidays':
		InsertUpdateHolidays();
		break;
	case 'GetHolidayByID':
		GetHolidayByID();
		break;
	case 'DeleteHolidayByID':
		DeleteHolidayByID();
		break;
	case 'LoadingHolidays':
		LoadingHolidays();
		break;
	case 'InsertUpdatePayheads':
		InsertUpdatePayheads();
		break;
	case 'GetPayheadByID':
		GetPayheadByID();
		break;
	case 'DeletePayheadByID':
		DeletePayheadByID();
		break;
	case 'LoadingPayheads':
		LoadingPayheads();
		break;
	case 'GetAllPayheadsExceptEmployeeHave':
		GetAllPayheadsExceptEmployeeHave();
		break;
	case 'GetEmployeePayheadsByID':
		GetEmployeePayheadsByID();
		break;
	case 'GetEmployeeByID':
		GetEmployeeByID();
		break;
	case 'DeleteEmployeeByID':
		DeleteEmployeeByID();
		break;
	case 'EditEmployeeDetailsByID':
		EditEmployeeDetailsByID();
		break;
	case 'GeneratePaySlip':
		GeneratePaySlip();
		break;
		// case 'SendPaySlipByMail':
		// 	SendPaySlipByMail();
		break;
	case 'EditProfileByID':
		EditProfileByID();
		break;
	// case 'EditLoginDataByID':
	// 	EditLoginDataByID();
	// break;
	case 'LoadingAllLeaves':
		LoadingAllLeaves();
		break;
	case 'LoadingMyLeaves':
		LoadingMyLeaves();
		break;
	case 'ApplyLeaveToAdminApproval':
		ApplyLeaveToAdminApproval();
		break;
	case 'ApproveLeaveApplication':
		ApproveLeaveApplication();
		break;
	case 'RejectLeaveApplication':
		RejectLeaveApplication();
		break;
	case 'UpdatePayscaleGrade':
		InsertUpdatePayscaleGrade();
		break;
	case 'InsertUpdatePayscaleGrade':
		InsertUpdatePayscaleGrade();
		break;
	case 'BulkEmployeeUpload':
		BulkEmployeeUpload();
		break;
	default:
		echo '404! Page Not Found.';
		break;
}

function InsertUpdatePayscaleGrade()
{
	$result = array();
	global $db;

	$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
	$emp_grade = isset($_POST['emp_grade']) ? trim($_POST['emp_grade']) : '';
	$empsal_grade = isset($_POST['empsal_grade']) ? trim($_POST['empsal_grade']) : '';
	$basic_salary = isset($_POST['basic_salary']) ? floatval($_POST['basic_salary']) : 0;
	$house_rent = isset($_POST['house_rent']) ? floatval($_POST['house_rent']) : 0;
	$conveyance_allowance = isset($_POST['conveyance_allowance']) ? floatval($_POST['conveyance_allowance']) : 0;
	$medical_allowance = isset($_POST['medical_allowance']) ? floatval($_POST['medical_allowance']) : 0;
	$driver_allowance = isset($_POST['driver_allowance']) ? floatval($_POST['driver_allowance']) : 0;
	$car_allowance = isset($_POST['car_allowance']) ? floatval($_POST['car_allowance']) : 0;

	// Validate required fields
	if (empty($emp_grade) || empty($empsal_grade)) {
		$result['code'] = 2;
		$result['result'] = 'Employee grade and salary grade are required.';
		echo json_encode($result);
		return;
	}

	// Check if updating or inserting
	if ($id > 0) {
		// Update existing record
		$updateSQL = "UPDATE `" . DB_PREFIX . "payscale_grade` SET 
			`emp_grade` = '" . mysqli_real_escape_string($db, $emp_grade) . "',
			`empsal_grade` = '" . mysqli_real_escape_string($db, $empsal_grade) . "',
			`basic_salary` = $basic_salary,
			`house_rent` = $house_rent,
			`conveyance_allowance` = $conveyance_allowance,
			`medical_allowance` = $medical_allowance,
			`driver_allowance` = $driver_allowance,
			`car_allowance` = $car_allowance
			WHERE `id` = $id";

		$updateResult = mysqli_query($db, $updateSQL);
		if ($updateResult) {
			$result['code'] = 0;
			$result['result'] = 'Payscale grade record has been successfully updated.';
		} else {
			$result['code'] = 1;
			$result['result'] = 'Failed to update payscale grade record: ' . mysqli_error($db);
		}
	} else {
		// Insert new record
		$insertSQL = "INSERT INTO `" . DB_PREFIX . "payscale_grade` 
			(`emp_grade`, `empsal_grade`, `basic_salary`, `house_rent`, `conveyance_allowance`, `medical_allowance`, `driver_allowance`, `car_allowance`) VALUES (
			'" . mysqli_real_escape_string($db, $emp_grade) . "',
			'" . mysqli_real_escape_string($db, $empsal_grade) . "',
			$basic_salary,
			$house_rent,
			$conveyance_allowance,
			$medical_allowance,
			$driver_allowance,
			$car_allowance
		)";

		$insertResult = mysqli_query($db, $insertSQL);
		if ($insertResult) {
			$result['code'] = 0;
			$result['result'] = 'Payscale grade record has been successfully inserted.';
		} else {
			$result['code'] = 1;
			$result['result'] = 'Failed to insert payscale grade record: ' . mysqli_error($db) . ' | SQL: ' . $insertSQL;
		}
	}

	echo json_encode($result);
}

function LoginProcessHandler()
{
	$result = array();
	global $db;

	/* $code = isset($_POST['code']) ? addslashes($db, $_POST['code']) : '';
	$password = isset($_POST['password']) ? addslashes($db, $_POST['password']) : ''; */
	$code = mysqli_real_escape_string($db, $_POST['code']); //isset($_POST['code']) ? $db, trim($_POST['code']) : '';
	$password = mysqli_real_escape_string($db, $_POST['password']); //isset($_POST['password']) ? $db, trim($_POST['password']) : '';
	if (!empty($code) && !empty($password)) {
		$adminCheck = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "admin` WHERE `admin_code` = '$code' AND `admin_password` = '" . sha1($password) . "' LIMIT 0, 1");
		if ($adminCheck) {
			if (mysqli_num_rows($adminCheck) == 1) {
				$adminData = mysqli_fetch_assoc($adminCheck);
				$_SESSION['Admin_ID'] = $adminData['admin_id'];
				$_SESSION['Login_Type'] = 'admin';
				$result['result'] = BASE_URL . 'employees/';
				$result['code'] = 0;
			} else {
				$empCheck = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "employees` WHERE `emp_code` = '$code' AND `emp_password` = '" . sha1($password) . "' LIMIT 0, 1");
				if ($empCheck) {
					if (mysqli_num_rows($empCheck) == 1) {
						$empData = mysqli_fetch_assoc($empCheck);
						$_SESSION['Admin_ID'] = $empData['employee_id'];
						$_SESSION['Login_Type'] = 'emp';
						$result['result'] = BASE_URL . 'profile/';
						$result['code'] = 0;
					} else {
						$result['result'] = 'Invalid Login Details.';
						$result['code'] = 1;
					}
				} else {
					$result['result'] =  'Something went wrong, please try again.';
					$result['code'] = 2;
				}
			}
		} else {
			$result['result'] =  'Something went wrong, please try again.';
			$result['code'] = 2;
		}
	} else {
		$result['result'] = 'Login Details should not be blank.';
		$result['code'] = 3;
	}

	echo json_encode($result);
}

/* function AttendanceProcessHandler()
{
	global $userData, $db;
	$result = array();

	$emp_code = $userData['emp_code'];
	$attendance_date = date('Y-m-d');
	$attendanceSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "attendance` WHERE `emp_code` = '$emp_code' AND `attendance_date` = '$attendance_date'");
	if ($attendanceSQL) {
		$attendanceROW = mysqli_num_rows($attendanceSQL);
		if ($attendanceROW == 0) {
			$action_name = 'punchin';
		} else {
			$attendanceDATA = mysqli_fetch_assoc($attendanceSQL);
			if ($attendanceDATA['action_name'] == 'punchin') {
				$action_name = 'punchout';
			} else {
				$action_name = 'punchin';
			}
		}
	} else {
		$attendanceROW = 0;
		$action_name = 'punchin';
	}
	$action_time = date('H:i:s');
	$emp_desc = addslashes($_POST['desc']);

	$insertSQL = mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "attendance`(`emp_code`, `attendance_date`, `action_name`, `action_time`, `emp_desc`) VALUES ('$emp_code', '$attendance_date', '$action_name', '$action_time', '$emp_desc')");
	if ($insertSQL) {
		$result['next'] = ($action_name == 'punchin' ? 'Punch Out' : 'Punch In');
		$result['complete'] = $attendanceROW + 1;
		$result['result'] = 'You have successfully punched in.';
		$result['code'] = 0;
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 1;
	}

	echo json_encode($result);
} */

/* function LoadingAttendance()
{
	global $db;
	$requestData = $_REQUEST;
	$columns = array(
		0 => 'attendance_date',
		1 => 'emp_code',
		2 => 'first_name',
		3 => 'last_name',
		4 => 'action_time',
		5 => 'emp_desc'
	);

	$sql  = "SELECT `attendance_id`, `emp_code`, `attendance_date`, GROUP_CONCAT(`action_time`) AS `times`, GROUP_CONCAT(`emp_desc`) AS `descs` FROM `" . DB_PREFIX . "attendance` GROUP BY `emp_code`, `attendance_date`";
	$query = mysqli_query($db, $sql);
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;

	$sql  = "SELECT `emp`.`emp_code`, `emp`.`first_name`, `emp`.`last_name`, `att`.`attendance_id`, `att`.`emp_code`, `att`.`attendance_date`, GROUP_CONCAT(`att`.`action_time`) AS `times`, GROUP_CONCAT(`att`.`emp_desc`) AS `descs`";
	$sql .= " FROM `" . DB_PREFIX . "employees` AS `emp`, `" . DB_PREFIX . "attendance` AS `att` WHERE `emp`.`emp_code` = `att`.`emp_code`";
	if (!empty($requestData['search']['value'])) {
		$sql .= " AND (`att`.`attendance_date` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR CONCAT(TRIM(`emp`.`first_name`), ' ', TRIM(`emp`.`last_name`)) LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `att`.`times` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `att`.`descs` LIKE '" . $requestData['search']['value'] . "%')";
	}
	$sql .= " GROUP BY `emp`.`emp_code`, `att`.`attendance_date`";

	$query = mysqli_query($db, $sql);
	$totalFiltered = mysqli_num_rows($query);
	$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "";
	$query = mysqli_query($db, $sql);

	$data = array();
	$i = 1 + $requestData['start'];
	while ($row = mysqli_fetch_assoc($query)) {
		$nestedData = array();
		$nestedData[] = date('d-m-Y', strtotime($row['attendance_date']));
		$nestedData[] = $row["emp_code"];
		$nestedData[] = '<a target="_blank" href="' . REG_URL . 'reports/' . $row["emp_code"] . '/">' . $row["first_name"] . ' ' . $row["last_name"] . '</a>';
		$times = explode(',', $row["times"]);
		$descs = explode(',', $row["descs"]);
		$nestedData[] = isset($times[0]) ? date('h:i:s A', strtotime($times[0])) : '';
		$nestedData[] = isset($descs[0]) ? $descs[0] : '';
		$nestedData[] = isset($times[1]) ? date('h:i:s A', strtotime($times[1])) : '';
		$nestedData[] = isset($descs[1]) ? $descs[1] : '';

		if (isset($times[0]) && isset($times[1]) && !empty($times[0]) && !empty($times[1])) {
			$datetime1 = new DateTime($times[0]);
			$datetime2 = new DateTime($times[1]);
			$interval = $datetime1->diff($datetime2);
			$nestedData[] = $interval->format('%h') . " Hrs  |" . $interval->format('%i') . " Min";
		} else {
			$nestedData[] = "0H";
		}

		$data[] = $nestedData;
		$i++;
	}
	$json_data = array(
		"draw"            => intval($requestData['draw']),
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	echo json_encode($json_data);
} */
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
					$failMessages[] = "Row " . ($i + 1) . ": Failed to update employee. Error: " . mysqli_error($db);
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
					$failMessages[] = "Row " . ($i + 1) . ": Failed to insert employee. Error: " . mysqli_error($db);
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

function LoadingSalaries()
{
	global $db;
	$requestData = $_REQUEST;

	$totalData = 0;
	$totalFiltered = 0;
	$data = array();

	if ($_SESSION['Login_Type'] == 'admin') {
		$columns = array(
			0 => 'emp_code',
			1 => 'first_name',
			2 => 'last_name',
			3 => 'pay_month',
			4 => 'gross_salary',    // Total Earnings (gross_salary)
			5 => 'total_deduction', // Total Deductions (total_deduction)
			6 => 'net_salary'       // Net Salary Payable (net_salary)
		);

		// Initial query to count the total data
		//$sql  = "SELECT * FROM `cdbl_salaries` GROUP BY `emp_code`, `pay_month`"; // Removed DB_PREFIX from table name
		$sql  = "SELECT * FROM `cdbl_salaries`"; //
		$query = mysqli_query($db, $sql);

		// Check for SQL errors
		if (!$query) {
			die('Error: ' . mysqli_error($db));  // Output error if the query fails
		}

		$totalData = mysqli_num_rows($query);
		$totalFiltered = $totalData;

		// Main query to get the salary information, now fetching the required columns
		$sql  = "SELECT `emp`.`emp_code`, `emp`.`first_name`, `emp`.`last_name`, `salary`.`gross_salary`, `salary`.`total_deduction`, `salary`.`net_salary`, `salary`.`pay_month`";
		$sql .= " FROM `cdbl_salaries` AS `salary`, `cdbl_employees` AS `emp` WHERE `emp`.`emp_code` = `salary`.`emp_code`";  // Corrected the table references

		if (!empty($requestData['search']['value'])) {
			$sql .= " AND (`salary`.`emp_code` LIKE '" . $requestData['search']['value'] . "%'";
			$sql .= " OR CONCAT(TRIM(`emp`.`first_name`), ' ', TRIM(`emp`.`last_name`)) LIKE '" . $requestData['search']['value'] . "%'";
			$sql .= " OR `salary`.`pay_month` LIKE '" . $requestData['search']['value'] . "%'";
			$sql .= " OR `salary`.`gross_salary` LIKE '" . $requestData['search']['value'] . "%'";
			$sql .= " OR `salary`.`total_deduction` LIKE '" . $requestData['search']['value'] . "%'";
			$sql .= " OR `salary`.`net_salary` LIKE '" . $requestData['search']['value'] . "%')";
		}

		//$sql .= " GROUP BY `salary`.`emp_code`, `salary`.`pay_month`";

		$query = mysqli_query($db, $sql);

		// Check for SQL errors
		if (!$query) {
			die('Error: ' . mysqli_error($db));  // Output error if the query fails
		}

		$totalFiltered = mysqli_num_rows($query);

		$data = array();
		$i = 1 + $requestData['start'];
		while ($row = mysqli_fetch_assoc($query)) {
			$nestedData = array();

			// Safely handle missing or null pay_month value
			$pay_month = isset($row['pay_month']) ? $row['pay_month'] : 'Not Available';

			$nestedData[] = $row['emp_code'];
			$nestedData[] = '<a target="_blank" href="' . REG_URL . 'reports/' . $row["emp_code"] . '/">' . $row["first_name"] . ' ' . $row["last_name"] . '</a>';
			$nestedData[] = $pay_month;  // Fallback to "Not Available" if pay_month is null
			$nestedData[] = number_format($row['gross_salary'], 2, '.', ',');  // Total Earnings (gross_salary)
			$nestedData[] = number_format($row['total_deduction'], 2, '.', ',');  // Total Deductions (total_deduction)
			$nestedData[] = number_format($row['net_salary'], 2, '.', ',');  // Net Salary Payable (net_salary)
			// $nestedData[] = '<button type="button" class="btn btn-success btn-xs" onclick="openInNewTab(\'' . BASE_URL . 'payslips/' . $row['emp_code'] . '/' . str_replace(', ', ',', $pay_month) . '/' . str_replace(', ', ',', $pay_month) . '.pdf\');"><i class="fa fa-download"></i></button> 
			$nestedData[] = '<button type="button" class="btn btn-success btn-xs" onclick="openInNewTab(\'' . BASE_URL . '/../payslips/' . $row['emp_code'] . '/' . $pay_month . '/' . $pay_month . '.pdf\');"><i class="fa fa-download"></i></button>   
          <button type="button" class="btn btn-info btn-xs" onclick="sendPaySlipByMail(\'' . $row['emp_code'] . '\', \'' . $pay_month . '\');"><i class="fa fa-envelope"></i></button>';

			$data[] = $nestedData;
			$i++;
		}
	} elseif ($_SESSION['Login_Type'] == 'emp') {
		// For employee users, show only their salary data
		$employee_id = $_SESSION['Admin_ID']; // employee_id from session

		// Get emp_code for this employee_id
		$emp_code = '';
		$empCodeQuery = mysqli_query($db, "SELECT `emp_code` FROM `" . DB_PREFIX . "employees` WHERE `employee_id` = '$employee_id' LIMIT 1");
		if ($empCodeQuery && mysqli_num_rows($empCodeQuery) == 1) {
			$empCodeRow = mysqli_fetch_assoc($empCodeQuery);
			$emp_code = $empCodeRow['emp_code'];
		}

		$columns = array(
			0 => 'emp_code',
			1 => 'first_name',
			2 => 'last_name',
			3 => 'pay_month',
			4 => 'gross_salary',
			5 => 'total_deduction',
			6 => 'net_salary'
		);

		// Initial query to count total data for this employee
		//$sql = "SELECT * FROM `cdbl_salaries` WHERE `emp_code` = '$emp_code' GROUP BY `pay_month`";
		$sql = "SELECT * FROM `cdbl_salaries` WHERE `emp_code` = '$emp_code'";
		$query = mysqli_query($db, $sql);
		if (!$query) {
			die('Error: ' . mysqli_error($db));
		}
		$totalData = mysqli_num_rows($query);
		$totalFiltered = $totalData;

		// Main query to get salary info for this employee
		$sql = "SELECT `emp`.`emp_code`, `emp`.`first_name`, `emp`.`last_name`, `salary`.`gross_salary`, `salary`.`total_deduction`, `salary`.`net_salary`, `salary`.`pay_month`";
		$sql .= " FROM `cdbl_salaries` AS `salary`, `cdbl_employees` AS `emp` WHERE `emp`.`emp_code` = `salary`.`emp_code` AND `salary`.`emp_code` = '$emp_code'";

		if (!empty($requestData['search']['value'])) {
			$sql .= " AND (`salary`.`pay_month` LIKE '" . $requestData['search']['value'] . "%'";
			$sql .= " OR `salary`.`gross_salary` LIKE '" . $requestData['search']['value'] . "%'";
			$sql .= " OR `salary`.`total_deduction` LIKE '" . $requestData['search']['value'] . "%'";
			$sql .= " OR `salary`.`net_salary` LIKE '" . $requestData['search']['value'] . "%')";
		}

		//$sql .= " GROUP BY `salary`.`pay_month`";

		$query = mysqli_query($db, $sql);
		if (!$query) {
			die('Error: ' . mysqli_error($db));
		}
		$totalFiltered = mysqli_num_rows($query);

		$data = array();
		$i = 1 + $requestData['start'];
		while ($row = mysqli_fetch_assoc($query)) {
			$nestedData = array();

			$pay_month = isset($row['pay_month']) ? $row['pay_month'] : 'Not Available';

			$nestedData[] = $row['emp_code'];
			$nestedData[] = '<a target="_blank" href="' . REG_URL . 'reports/' . $row["emp_code"] . '/">' . $row["first_name"] . ' ' . $row["last_name"] . '</a>';
			$nestedData[] = $pay_month;
			$nestedData[] = number_format($row['gross_salary'], 2, '.', ',');
			$nestedData[] = number_format($row['total_deduction'], 2, '.', ',');
			$nestedData[] = number_format($row['net_salary'], 2, '.', ',');
			$nestedData[] = '<button type="button" class="btn btn-success btn-xs" onclick="openInNewTab(\'' . BASE_URL . '/../payslips/' . $row['emp_code'] . '/' . $pay_month . '/' . $pay_month . '.pdf\');"><i class="fa fa-download"></i></button>';

			$data[] = $nestedData;
			$i++;
		}
	} else {
		// For other login types or not logged in, return empty data
		$totalData = 0;
		$totalFiltered = 0;
		$data = array();
	}

	// Return the JSON response with data
	$json_data = array(
		"draw"            => intval($requestData['draw']),
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	echo json_encode($json_data);
}

function LoadingPayscaleGrade()
{
	global $db;
	$requestData = $_REQUEST;
	$columns = array(
		0 => 'id',
		1 => 'emp_grade',
		2 => 'empsal_grade',
		3 => 'basic_salary',
		4 => 'house_rent',
		5 => 'conveyance_allowance',
		6 => 'medical_allowance',
		7 => 'driver_allowance',
		8 => 'car_allowance'
	);

	// --- Total records ---
	$sql = "SELECT `id` FROM `" . DB_PREFIX . "payscale_grade`";
	$query = mysqli_query($db, $sql);
	if (!$query) {
		$response = array(
			"draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
			"recordsTotal"    => 0,
			"recordsFiltered" => 0,
			"data"            => array(),
			"error"           => "Database error in total records query: " . mysqli_error($db)
		);
		ob_clean();
		echo json_encode($response);
		exit;
	}
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;

	// --- Build main query ---
	$sql = "SELECT * FROM `" . DB_PREFIX . "payscale_grade` WHERE 1 = 1";
	if (!empty($requestData['search']['value'])) {
		$value = mysqli_real_escape_string($db, $requestData['search']['value']);
		$sql .= " AND (";
		$sql .= " `id` LIKE '" . $value . "%'";
		$sql .= " OR `emp_grade` LIKE '" . $value . "%'";
		$sql .= " OR `empsal_grade` LIKE '" . $value . "%'";
		$sql .= " OR `basic_salary` LIKE '" . $value . "%'";
		$sql .= " OR `house_rent` LIKE '" . $value . "%'";
		$sql .= " OR `conveyance_allowance` LIKE '" . $value . "%'";
		$sql .= " OR `medical_allowance` LIKE '" . $value . "%'";
		$sql .= " OR `driver_allowance` LIKE '" . $value . "%'";
		$sql .= " OR `car_allowance` LIKE '" . $value . "%'";
		$sql .= " )";
	}

	$query = mysqli_query($db, $sql);
	if (!$query) {
		$response = array(
			"draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
			"recordsTotal"    => 0,
			"recordsFiltered" => 0,
			"data"            => array(),
			"error"           => "Database error in filtered records query: " . mysqli_error($db)
		);
		ob_clean();
		echo json_encode($response);
		exit;
	}
	$totalFiltered = mysqli_num_rows($query);

	// --- Ordering and pagination ---
	if (isset($requestData['order'][0]['column'])) {
		$orderColumn = $columns[$requestData['order'][0]['column']];
		$orderDir    = $requestData['order'][0]['dir'];
	} else {
		$orderColumn = 'id';
		$orderDir    = 'asc';
	}
	$start  = isset($requestData['start']) ? (int)$requestData['start'] : 0;
	$length = isset($requestData['length']) ? (int)$requestData['length'] : 10;
	$sql .= " ORDER BY " . $orderColumn . " " . $orderDir;
	$sql .= " LIMIT " . $start . ", " . $length;

	$query = mysqli_query($db, $sql);
	if (!$query) {
		$response = array(
			"draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
			"recordsTotal"    => 0,
			"recordsFiltered" => 0,
			"data"            => array(),
			"error"           => "Database error in main query: " . mysqli_error($db)
		);
		ob_clean();
		echo json_encode($response);
		exit;
	}

	$data = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$nestedData = array();
		$nestedData[] = $row['id'];
		$nestedData[] = $row['emp_grade'];
		$nestedData[] = $row['empsal_grade'];
		$nestedData[] = number_format($row['basic_salary'], 2, '.', ',');
		$nestedData[] = number_format($row['house_rent'], 2, '.', ',');
		$nestedData[] = number_format($row['conveyance_allowance'], 2, '.', ',');
		$nestedData[] = number_format($row['medical_allowance'], 2, '.', ',');
		$nestedData[] = number_format($row['driver_allowance'], 2, '.', ',');
		$nestedData[] = number_format($row['car_allowance'], 2, '.', ',');
		$data[] = $nestedData;
	}

	$json_data = array(
		"draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	// Clear any stray output before echoing the JSON
	ob_clean();
	echo json_encode($json_data);
	exit;
}


function LoadingEmployees()
{
	global $db;
	// Start output buffering to catch stray output
	ob_start();

	$requestData = $_REQUEST;
	$columns = array(
		0 => 'employee_id',
		1 => 'photo',
		2 => 'first_name',
		3 => 'last_name',
		4 => 'dob',
		5 => 'email',
		6 => 'mobile',
		7 => 'emp_grade',
		8 => 'empsal_grade',
		9 => 'joining_date',
		10 => 'blood_group',
		11 => 'employment_type'
	);

	// --- Total records ---
	// Use a valid column name from your table: employee_id exists
	$sql = "SELECT `employee_id` FROM `" . DB_PREFIX . "employees`";
	$query = mysqli_query($db, $sql);
	if (!$query) {
		$response = array(
			"draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
			"recordsTotal"    => 0,
			"recordsFiltered" => 0,
			"data"            => array(),
			"error"           => "Database error in total records query: " . mysqli_error($db)
		);
		ob_clean();
		echo json_encode($response);
		exit;
	}
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;

	// --- Build main query ---
	$sql = "SELECT * FROM `" . DB_PREFIX . "employees` WHERE 1 = 1";
	if (!empty($requestData['search']['value'])) {
		$value = mysqli_real_escape_string($db, $requestData['search']['value']);
		$sql .= " AND (";
		$sql .= " `employee_id` LIKE '" . $value . "%'";
		$sql .= " OR CONCAT(TRIM(`first_name`), ' ', TRIM(`last_name`)) LIKE '" . $value . "%'";
		$sql .= " OR `dob` LIKE '" . $value . "%'";
		$sql .= " OR `email` LIKE '" . $value . "%'";
		$sql .= " OR `mobile` LIKE '" . $value . "%'";
		$sql .= " OR `emp_grade` LIKE '" . $value . "%'";
		$sql .= " OR `empsal_grade` LIKE '" . $value . "%'";
		$sql .= " OR `joining_date` LIKE '" . $value . "%'";
		$sql .= " OR `blood_group` LIKE '" . $value . "%'";
		$sql .= " OR `employment_type` LIKE '" . $value . "%'";
		$sql .= " )";
	}

	$query = mysqli_query($db, $sql);
	if (!$query) {
		$response = array(
			"draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
			"recordsTotal"    => 0,
			"recordsFiltered" => 0,
			"data"            => array(),
			"error"           => "Database error in filtered records query: " . mysqli_error($db)
		);
		ob_clean();
		echo json_encode($response);
		exit;
	}
	$totalFiltered = mysqli_num_rows($query);

	// --- Ordering and pagination ---
	if (isset($requestData['order'][0]['column'])) {
		$orderColumn = $columns[$requestData['order'][0]['column']];
		$orderDir    = $requestData['order'][0]['dir'];
	} else {
		$orderColumn = 'employee_id';
		$orderDir    = 'asc';
	}
	$start  = isset($requestData['start']) ? (int)$requestData['start'] : 0;
	$length = isset($requestData['length']) ? (int)$requestData['length'] : 10;
	$sql .= " ORDER BY " . $orderColumn . " " . $orderDir;
	$sql .= " LIMIT " . $start . ", " . $length;

	$query = mysqli_query($db, $sql);
	if (!$query) {
		$response = array(
			"draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
			"recordsTotal"    => 0,
			"recordsFiltered" => 0,
			"data"            => array(),
			"error"           => "Database error in main query: " . mysqli_error($db)
		);
		ob_clean();
		echo json_encode($response);
		exit;
	}

	$data = array();
	while ($row = mysqli_fetch_assoc($query)) {
		$nestedData = array();
		// Use field names exactly as defined in your table
		$nestedData[] = isset($row["emp_code"]) ? $row["emp_code"] : '';
		$nestedData[] = '<img width="50" src="' . REG_URL . 'photos/' . $row["photo"] . '" alt="' . $row["emp_code"] . '" />';
		$nestedData[] = '<a target="_blank" href="' . REG_URL . 'reports/' . $row["employee_id"] . '/">' . $row["first_name"] . ' ' . $row["last_name"] . '</a>';
		$nestedData[] = $row["dob"];
		$nestedData[] = $row["email"];
		$nestedData[] = $row["mobile"];
		$nestedData[] = ucwords($row["emp_grade"]);
		$nestedData[] = ucwords($row["empsal_grade"]);
		$nestedData[] = $row["joining_date"];
		$nestedData[] = strtoupper($row["blood_group"]);
		$nestedData[] = ucwords($row["employment_type"]);
		$data[] = $nestedData;
	}

	$json_data = array(
		"draw"            => isset($requestData['draw']) ? intval($requestData['draw']) : 0,
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	// Clear any stray output before echoing the JSON
	ob_clean();
	echo json_encode($json_data);
	exit;
}

function AssignPayheadsToEmployee()
{
	$result = array();
	global $db;

	$payheads = $_POST['selected_payheads'];
	$default_salary = $_POST['pay_amounts'];
	$emp_code = $_POST['empcode'];
	$emp_grade = $_POST['emp_grade'] ?? null;
	$empsal_grade = $_POST['empsal_grade'] ?? null;

	$checkSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "pay_structure` WHERE `emp_code` = '$emp_code'");
	if ($checkSQL) {
		if (!empty($payheads) && !empty($emp_code)) {
			if (mysqli_num_rows($checkSQL) == 0) {
				foreach ($payheads as $payhead) {
					mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "pay_structure`(`emp_code`, `payhead_id`, `default_salary`, `emp_grade`, `empsal_grade`) VALUES ('$emp_code', $payhead, " . (!empty($default_salary[$payhead]) ? $default_salary[$payhead] : 0) . ", " . ($emp_grade !== null ? "'$emp_grade'" : "NULL") . ", " . ($empsal_grade !== null ? "'$empsal_grade'" : "NULL") . ")");
				}
				$result['result'] = 'Payheads are successfully assigned to employee.';
				$result['code'] = 0;
			} else {
				mysqli_query($db, "DELETE FROM `" . DB_PREFIX . "pay_structure` WHERE `emp_code` = '$emp_code'");
				foreach ($payheads as $payhead) {
					mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "pay_structure`(`emp_code`, `payhead_id`, `default_salary`, `emp_grade`, `empsal_grade`) VALUES ('$emp_code', $payhead, " . (!empty($default_salary[$payhead]) ? $default_salary[$payhead] : 0) . ", " . ($emp_grade !== null ? "'$emp_grade'" : "NULL") . ", " . ($empsal_grade !== null ? "'$empsal_grade'" : "NULL") . ")");
				}
				$result['result'] = 'Payheads are successfully re-assigned to employee.';
				$result['code'] = 0;
			}
		} else {
			$result['result'] = 'Please select payheads and employee to assign.';
			$result['code'] = 2;
		}
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 1;
	}

	echo json_encode($result);
}

function InsertUpdateHolidays()
{
	$result = array();
	global $db;

	$holiday_title = stripslashes($_POST['holiday_title']);
	$holiday_desc = stripslashes($_POST['holiday_desc']);
	$holiday_date = stripslashes($_POST['holiday_date']);
	$holiday_type = stripslashes($_POST['holiday_type']);
	if (!empty($holiday_title) && !empty($holiday_desc) && !empty($holiday_date) && !empty($holiday_type)) {
		if (!empty($_POST['holiday_id'])) {
			$holiday_id = mysqli_real_escape_string($db, $_POST['holiday_id']);
			$updateHoliday = mysqli_query($db, "UPDATE `" . DB_PREFIX . "holidays` SET `holiday_title` = '$holiday_title', `holiday_desc` = '$holiday_desc', `holiday_date` = '$holiday_date', `holiday_type` = '$holiday_type' WHERE `holiday_id` = $holiday_id");
			if ($updateHoliday) {
				$result['result'] = 'Holiday record has been successfully updated.';
				$result['code'] = 0;
			} else {
				$result['result'] = 'Something went wrong, please try again.';
				$result['code'] = 1;
			}
		} else {
			$insertHoliday = mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "holidays`(`holiday_title`, `holiday_desc`, `holiday_date`, `holiday_type`) VALUES ('$holiday_title', '$holiday_desc', '$holiday_date', '$holiday_type')");
			if ($insertHoliday) {
				$result['result'] = 'Holiday record has been successfully inserted.';
				$result['code'] = 0;
			} else {
				$result['result'] = 'Something went wrong, please try again.';
				$result['code'] = 1;
			}
		}
	} else {
		$result['result'] = 'Holiday details should not be blank.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function GetHolidayByID()
{
	$result = array();
	global $db;

	$id = $_POST['id'];
	$holiSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "holidays` WHERE `holiday_id` = $id LIMIT 0, 1");
	if ($holiSQL) {
		if (mysqli_num_rows($holiSQL) == 1) {
			$result['result'] = mysqli_fetch_assoc($holiSQL);
			$result['code'] = 0;
		} else {
			$result['result'] = 'Holiday record is not found.';
			$result['code'] = 1;
		}
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function DeleteHolidayByID()
{
	$result = array();
	global $db;

	$id = $_POST['id'];
	$holiSQL = mysqli_query($db, "DELETE FROM `" . DB_PREFIX . "holidays` WHERE `holiday_id` = $id");
	if ($holiSQL) {
		$result['result'] = 'Holiday record is successfully deleted.';
		$result['code'] = 0;
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 1;
	}

	echo json_encode($result);
}

function LoadingHolidays()
{
	global $db;
	$requestData = $_REQUEST;
	$columns = array(
		0 => 'holiday_id',
		1 => 'holiday_title',
		2 => 'holiday_desc',
		3 => 'holiday_date',
		4 => 'holiday_type',
	);

	$sql  = "SELECT `holiday_id` ";
	$sql .= " FROM `" . DB_PREFIX . "holidays`";
	$query = mysqli_query($db, $sql);
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;

	$sql  = "SELECT *";
	$sql .= " FROM `" . DB_PREFIX . "holidays` WHERE 1 = 1";
	if (!empty($requestData['search']['value'])) {
		$sql .= " AND (`holiday_id` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `holiday_title` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `holiday_desc` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `holiday_date` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `holiday_type` LIKE '" . $requestData['search']['value'] . "%')";
	}
	$query = mysqli_query($db, $sql);
	$totalFiltered = mysqli_num_rows($query);
	$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "";
	$query = mysqli_query($db, $sql);

	$data = array();
	$i = 1 + $requestData['start'];
	while ($row = mysqli_fetch_assoc($query)) {
		$nestedData = array();
		$nestedData[] = $row["holiday_id"];
		$nestedData[] = $row["holiday_title"];
		$nestedData[] = $row["holiday_desc"];
		$nestedData[] = date('d-m-Y', strtotime($row["holiday_date"]));
		if ($row["holiday_type"] == 'compulsory') {
			$nestedData[] = '<span class="label label-success">' . ucwords($row["holiday_type"]) . '</span>';
		} else {
			$nestedData[] = '<span class="label label-danger">' . ucwords($row["holiday_type"]) . '</span>';
		}
		$data[] = $nestedData;
		$i++;
	}
	$json_data = array(
		"draw"            => intval($requestData['draw']),
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	echo json_encode($json_data);
}

function InsertUpdatePayheads()
{
	$result = array();
	global $db;

	$payhead_name = stripslashes($_POST['payhead_name']);
	$payhead_desc = stripslashes($_POST['payhead_desc']);
	$payhead_type = stripslashes($_POST['payhead_type']);
	if (!empty($payhead_name) && !empty($payhead_desc) && !empty($payhead_type)) {
		if (!empty($_POST['payhead_id'])) {
			$payhead_id = mysqli_real_escape_string($db, $_POST['payhead_id']);
			$updatePayhead = mysqli_query($db, "UPDATE `" . DB_PREFIX . "payheads` SET `payhead_name` = '$payhead_name', `payhead_desc` = '$payhead_desc', `payhead_type` = '$payhead_type' WHERE `payhead_id` = $payhead_id");
			if ($updatePayhead) {
				$result['result'] = 'Payhead record has been successfully updated.';
				$result['code'] = 0;
			} else {
				$result['result'] = 'Something went wrong, please try again.';
				$result['code'] = 1;
			}
		} else {
			$insertPayhead = mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "payheads`(`payhead_name`, `payhead_desc`, `payhead_type`) VALUES ('$payhead_name', '$payhead_desc', '$payhead_type')");
			if ($insertPayhead) {
				$result['result'] = 'Payhead record has been successfully inserted.';
				$result['code'] = 0;
			} else {
				$result['result'] = 'Something went wrong, please try again.';
				$result['code'] = 1;
			}
		}
	} else {
		$result['result'] = 'Payhead details should not be blank.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function GetPayheadByID()
{
	$result = array();
	global $db;

	$id = $_POST['id'];
	$holiSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "payheads` WHERE `payhead_id` = $id LIMIT 0, 1");
	if ($holiSQL) {
		if (mysqli_num_rows($holiSQL) == 1) {
			$result['result'] = mysqli_fetch_assoc($holiSQL);
			$result['code'] = 0;
		} else {
			$result['result'] = 'Payhead record is not found.';
			$result['code'] = 1;
		}
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function DeletePayheadByID()
{
	$result = array();
	global $db;

	$id = $_POST['id'];
	$holiSQL = mysqli_query($db, "DELETE FROM `" . DB_PREFIX . "payheads` WHERE `payhead_id` = $id");
	if ($holiSQL) {
		$result['result'] = 'Payhead record is successfully deleted.';
		$result['code'] = 0;
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 1;
	}

	echo json_encode($result);
}

function LoadingPayheads()
{
	global $db;
	$requestData = $_REQUEST;
	$columns = array(
		0 => 'payhead_id',
		1 => 'payhead_name',
		2 => 'payhead_desc',
		3 => 'payhead_type'
	);

	$sql  = "SELECT `payhead_id` ";
	$sql .= " FROM `" . DB_PREFIX . "payheads`";
	$query = mysqli_query($db, $sql);
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;

	$sql  = "SELECT *";
	$sql .= " FROM `" . DB_PREFIX . "payheads` WHERE 1 = 1";
	if (!empty($requestData['search']['value'])) {
		$sql .= " AND (`payhead_id` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `payhead_name` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `payhead_desc` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `payhead_type` LIKE '" . $requestData['search']['value'] . "%')";
	}
	$query = mysqli_query($db, $sql);
	$totalFiltered = mysqli_num_rows($query);
	$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "";
	$query = mysqli_query($db, $sql);

	$data = array();
	$arr = 1;
	$i = 1 + $requestData['start'];
	while ($row = mysqli_fetch_assoc($query)) {
		$nestedData = array();
		$nestedData[] = $row["payhead_id"];
		$nestedData[] = $row["payhead_name"];
		$nestedData[] = $row["payhead_desc"];
		if ($row["payhead_type"] == 'earnings') {
			$nestedData[] = '<span class="label label-success">' . ucwords($row["payhead_type"]) . '</span>';
		} else {
			$nestedData[] = '<span class="label label-danger">' . ucwords($row["payhead_type"]) . '</span>';
		}
		$data[] = $nestedData;
		$i++;
		$arr++;
	}
	$json_data = array(
		"draw"            => intval($requestData['draw']),
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	echo json_encode($json_data);
}

function GetAllPayheadsExceptEmployeeHave()
{
	$result = array();
	global $db;

	$emp_code = $_POST['emp_code'];
	$salGrade = $_POST['salary_grade'];
	$empGrade = $_POST['emp_grade'];

	$salarySQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "payheads` Where `payhead_id` NOT IN (SELECT `payhead_id` FROM `" . DB_PREFIX . "pay_structure` WHERE `emp_code` = '$emp_code')");


	$gradeSalarySQL = mysqli_query($db, "SELECT * 
		FROM `cdbl_payscale_grade` 
		WHERE `emp_grade` = '$empGrade' AND `empsal_grade` = '$salGrade'");

	$gradeData = null;
	if ($gradeSalarySQL && mysqli_num_rows($gradeSalarySQL) > 0) {
		$gradeData = mysqli_fetch_assoc($gradeSalarySQL);
	}

	if ($salarySQL) {
		if (mysqli_num_rows($salarySQL) > 0) {
			while ($data = mysqli_fetch_assoc($salarySQL)) {
				// Add default_salary from gradeData if available
				if ($gradeData !== null) {
					$payhead_name_key = strtolower(str_replace(' ', '_', $data['payhead_name']));
					if (isset($gradeData[$payhead_name_key])) {
						$data['default_salary'] = $gradeData[$payhead_name_key];
					} else {
						$data['default_salary'] = 0;
					}
				} else {
					$data['default_salary'] = 0;
				}
				$result['result'][] = $data;
			}
			if ($gradeData !== null) {
				$result['gradeResult'] = $gradeData;
			} else {
				$result['gradeResult'] = null;
			}
			$result['code'] = 0;
		} else {
			$result['result'] = 'Salary record is not found.';
			$result['code'] = 1;
		}
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function GetEmployeePayheadsByID()
{
	$result = array();
	global $db;

	$emp_code = $_POST['emp_code'];
	$salarySQL = mysqli_query($db, "SELECT a.*, c.* FROM cdbl_pay_structure a, cdbl_employees b, cdbl_payheads c WHERE a.emp_code=b.emp_code and a.emp_grade=b.emp_grade and  a.empsal_grade=b.empsal_grade and a.payhead_id=c.payhead_id and a.emp_code ='$emp_code'");

	if ($salarySQL) {
		if (mysqli_num_rows($salarySQL) > 0) {
			while ($data = mysqli_fetch_assoc($salarySQL)) {
				$result['result'][] = $data;
			}
			$result['code'] = 0;
		} else {
			// If no rows found, fetch emp_grade and empsal_grade from cdbl_employees
			$empGradeSQL = mysqli_query($db, "SELECT emp_grade, empsal_grade FROM cdbl_employees WHERE emp_code = '$emp_code' LIMIT 1");
			if ($empGradeSQL && mysqli_num_rows($empGradeSQL) == 1) {
				$gradeRow = mysqli_fetch_assoc($empGradeSQL);
				$emp_grade = $gradeRow['emp_grade'];
				$empsal_grade = $gradeRow['empsal_grade'];

				// Fetch all payheads
				$payheadsSQL = mysqli_query($db, "SELECT * FROM cdbl_payheads");
				// Fetch payscale grade matching emp_grade and empsal_grade
				$payscaleSQL = mysqli_query($db, "SELECT * FROM cdbl_payscale_grade WHERE emp_grade = '$emp_grade' AND empsal_grade = '$empsal_grade' LIMIT 1");
				$payscaleData = null;
				if ($payscaleSQL && mysqli_num_rows($payscaleSQL) == 1) {
					$payscaleData = mysqli_fetch_assoc($payscaleSQL);
				}

				if ($payheadsSQL) {
					while ($payhead = mysqli_fetch_assoc($payheadsSQL)) {
						$payhead_name_key = strtolower(str_replace(' ', '_', $payhead['payhead_name']));
						if ($payscaleData !== null && isset($payscaleData[$payhead_name_key])) {
							$payhead['default_salary'] = $payscaleData[$payhead_name_key];
						} else {
							$payhead['default_salary'] = 0;
						}
						$result['result'][] = $payhead;
					}
					$result['code'] = 0;
				} else {
					$result['result'] = 'No payheads found.';
					$result['code'] = 1;
				}
			} else {
				$result['result'] = 'Employee grade information not found.';
				$result['code'] = 1;
			}
		}
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function GetEmployeeByID()
{
	$result = array();
	global $db;

	$emp_code = $_POST['emp_code'];
	$empSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "employees` WHERE `emp_code` = '$emp_code' LIMIT 0, 1");
	if ($empSQL) {
		if (mysqli_num_rows($empSQL) == 1) {
			$result['result'] = mysqli_fetch_assoc($empSQL);
			$result['code'] = 0;
		} else {
			$result['result'] = 'Employee record is not found.';
			$result['code'] = 1;
		}
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}

function DeleteEmployeeByID()
{
	$result = array();
	global $db;

	$emp_code = $_POST['emp_code'];
	$empSQL = mysqli_query($db, "DELETE FROM `" . DB_PREFIX . "employees` WHERE `emp_code` = '$emp_code'");
	if ($empSQL) {
		$result['result'] = 'Employee record is successfully deleted.';
		$result['code'] = 0;
	} else {
		$result['result'] = 'Something went wrong, please try again.';
		$result['code'] = 1;
	}

	echo json_encode($result);
}

function EditEmployeeDetailsByID()
{
	$result = array();
	global $db;

	$emp_code = isset($_POST['emp_code']) ? stripslashes($_POST['emp_code']) : '';
	$first_name = isset($_POST['first_name']) ? stripslashes($_POST['first_name']) : '';
	$last_name = isset($_POST['last_name']) ? stripslashes($_POST['last_name']) : '';
	$dob = isset($_POST['dob']) ? stripslashes($_POST['dob']) : '';
	$gender = isset($_POST['gender']) ? stripslashes($_POST['gender']) : '';
	$marital_status = isset($_POST['marital_status']) ? stripslashes($_POST['marital_status']) : '';
	$blood_group = isset($_POST['blood_group']) ? stripslashes($_POST['blood_group']) : '';
	$address = isset($_POST['address']) ? stripslashes($_POST['address']) : '';
	$paraddress = isset($_POST['paraddress']) ? stripslashes($_POST['paraddress']) : '';
	$email = isset($_POST['email']) ? stripslashes($_POST['email']) : '';
	$mobile = isset($_POST['mobile']) ? stripslashes($_POST['mobile']) : '';
	$telephone = isset($_POST['telephone']) ? stripslashes($_POST['telephone']) : '';
	$national_id = isset($_POST['national_id']) ? stripslashes($_POST['national_id']) : '';
	$employment_type = isset($_POST['employment_type']) ? stripslashes($_POST['employment_type']) : '';
	$employment_status = isset($_POST['employment_status']) ? stripslashes($_POST['employment_status']) : '';
	$designation = isset($_POST['designation']) ? stripslashes($_POST['designation']) : '';
	$department = isset($_POST['department']) ? stripslashes($_POST['department']) : '';
	$emp_grade = isset($_POST['emp_grade']) ? stripslashes($_POST['emp_grade']) : '';
	$empsal_grade = isset($_POST['empsal_grade']) ? stripslashes($_POST['empsal_grade']) : '';
	$joining_date = isset($_POST['joining_date']) ? stripslashes($_POST['joining_date']) : '';
	$confirmation_date = isset($_POST['confirmation_date']) ? stripslashes($_POST['confirmation_date']) : '';
	$resign_date = isset($_POST['resign_date']) ? stripslashes($_POST['resign_date']) : '';
	$account_no = isset($_POST['account_no']) ? stripslashes($_POST['account_no']) : '';
	$etin_no = isset($_POST['etin_no']) ? stripslashes($_POST['etin_no']) : '';


	// $bank_name = stripslashes($_POST['bank_name']);
	// $account_no = stripslashes($_POST['account_no']);
	// $ifsc_code = stripslashes($_POST['ifsc_code']);
	// $pf_account = stripslashes($_POST['pf_account']);
	$missing_fields = [];
	if (empty($first_name)) $missing_fields[] = 'First Name';
	if (empty($last_name)) $missing_fields[] = 'Last Name';
	if (empty($dob)) $missing_fields[] = 'Date of Birth';
	if (empty($gender)) $missing_fields[] = 'Gender';
	if (empty($marital_status)) $missing_fields[] = 'Marital Status';
	if (empty($blood_group)) $missing_fields[] = 'Blood Group';
	if (empty($address)) $missing_fields[] = 'Address';
	if (empty($paraddress)) $missing_fields[] = 'Parmanent address';
	if (empty($email)) $missing_fields[] = 'Email';
	if (empty($mobile)) $missing_fields[] = 'Mobile';
	if (empty($employment_type)) $missing_fields[] = 'Employment Type';
	if (empty($employment_status)) $missing_fields[] = 'Emp. status';
	if (empty($joining_date)) $missing_fields[] = 'Joining Date';
	if (empty($blood_group)) $missing_fields[] = 'Blood Group';
	if (empty($designation)) $missing_fields[] = 'Designation';
	if (empty($national_id)) $missing_fields[] = 'National Id';
	if (empty($department)) $missing_fields[] = 'Department';
	if (empty($emp_grade)) $missing_fields[] = 'Employee Grade';
	if (empty($empsal_grade)) $missing_fields[] = 'Employee Salary Grade';

	if (count($missing_fields) === 0) {
		$updateSQL = "UPDATE `" . DB_PREFIX . "employees` SET `first_name` = '$first_name', `last_name` = '$last_name', `dob` = '$dob', `gender` = '$gender', 
		`marital_status` = '$marital_status',`blood_group` = '$blood_group', `address` = '$address', `paraddress` = '$paraddress', `email` = '$email',
		 `mobile` = '$mobile', `telephone` = '$telephone',`employment_type` = '$employment_type', `joining_date` = '$joining_date', 
		`employment_status`='$employment_status',`national_id` = '$national_id', `designation` = '$designation', 
		`department` = '$department', `emp_grade`='$emp_grade',`empsal_grade`=$empsal_grade,`account_no` = '$account_no' WHERE `emp_code` = '" . $emp_code . "'";

		$updateEmp = mysqli_query($db, $updateSQL);
		if ($updateEmp) {
			$result['result'] = 'Employee details has been successfully updated.';
			$result['code'] = 0;
		} else {
			$result['result'] = 'Failed to update employee details: ' . mysqli_error($db);
			$result['code'] = 1;
		}
	} else {
		$result['result'] = 'The following fields are mandatory: ' . implode(', ', $missing_fields) . '.';
		$result['code'] = 2;
	}

	echo json_encode($result);
}
function GeneratePaySlip()
{
	global $db;
	$result = array();

	// Get POST data
	$employee_id = isset($_POST['emp_code']) ? $_POST['emp_code'] : '';
	$pay_month = isset($_POST['pay_month']) ? $_POST['pay_month'] : '';
	$earnings_heads = isset($_POST['earnings_heads']) ? $_POST['earnings_heads'] : array();
	$earnings_amounts = isset($_POST['earnings_amounts']) ? $_POST['earnings_amounts'] : array();
	$deductions_heads = isset($_POST['deductions_heads']) ? $_POST['deductions_heads'] : array();
	$deductions_amounts = isset($_POST['deductions_amounts']) ? $_POST['deductions_amounts'] : array();

	// Fetch the predefined pay heads from the cdbl_pay_structure table
	$pay_structure_query = "SELECT * FROM `" . DB_PREFIX . "pay_structure` WHERE `emp_code` = '$employee_id'";
	$pay_structure_result = mysqli_query($db, $pay_structure_query);
	$pay_structure = mysqli_fetch_assoc($pay_structure_result);

	// Check if pay structure exists
	if (!$pay_structure) {
		$result['code'] = 1;
		$result['result'] = 'No pay structure found for this employee.';
		echo json_encode($result);
		return;
	}

	// Set default values for pay heads based on the structure fetched
	$pay_head_values = array(
		'basic_salary' => !empty($pay_structure['basic_salary']) ? $pay_structure['basic_salary'] : 0,
		'car_allowance' => !empty($pay_structure['car_allowance']) ? $pay_structure['car_allowance'] : 0,
		'house_rent' => !empty($pay_structure['house_rent']) ? $pay_structure['house_rent'] : 0,
		'conveyance_allowance' => !empty($pay_structure['conveyance_allowance']) ? $pay_structure['conveyance_allowance'] : 0,
		'medical_allowance' => !empty($pay_structure['medical_allowance']) ? $pay_structure['medical_allowance'] : 0,
		'overtime' => !empty($pay_structure['overtime']) ? $pay_structure['overtime'] : 0,
		'traveling_expenses' => !empty($pay_structure['traveling_expenses']) ? $pay_structure['traveling_expenses'] : 0,
		'loans_repayment' => !empty($pay_structure['loans_repayment']) ? $pay_structure['loans_repayment'] : 0,
		'performance_bonus' => !empty($pay_structure['performance_bonus']) ? $pay_structure['performance_bonus'] : 0,
		'professional_tax' => !empty($pay_structure['professional_tax']) ? $pay_structure['professional_tax'] : 0,
		'income_tax' => 0, // Default values for deductions, if not set
		'employee_provident_fund' => 0,
		'other_deductions' => 0,
		'arrear_salary' => 0,
		'leave_without_pay' => 0,
		'driver_allowance' => 0,
		'net_salary' => null,  // This will be calculated
		'total_deduction' => null,  // This will be calculated
		'gross_salary' => null  // This will be calculated
	);

	// Map earnings heads and amounts to their corresponding columns
	foreach ($earnings_heads as $index => $head) {
		$head_key = strtolower(str_replace(' ', '_', $head));  // Converts "Basic Salary" to "basic_salary"

		if (array_key_exists($head_key, $pay_head_values)) {
			$pay_head_values[$head_key] = isset($earnings_amounts[$index]) && $earnings_amounts[$index] !== '' ? $earnings_amounts[$index] : 0;
		}
	}

	// Map deductions heads and amounts to their corresponding columns
	foreach ($deductions_heads as $index => $head) {
		$head_key = strtolower(str_replace(' ', '_', $head));  // Converts "Income Tax" to "income_tax"

		if (array_key_exists($head_key, $pay_head_values)) {
			$pay_head_values[$head_key] = isset($deductions_amounts[$index]) && $deductions_amounts[$index] !== '' ? $deductions_amounts[$index] : 0;
		}
	}

	// Calculate the total earnings and deductions
	$gross_salary = array_sum(array_filter($earnings_amounts, function ($value) {
		return $value !== '';
	}));
	$total_deduction = array_sum(array_filter($deductions_amounts, function ($value) {
		return $value !== '';
	}));

	// Set the total earnings and deductions to the appropriate columns
	$pay_head_values['gross_salary'] = $gross_salary;
	$pay_head_values['total_deduction'] = $total_deduction;
	$pay_head_values['net_salary'] = $gross_salary - $total_deduction;

	// Prepare columns and values for the SQL query
	$columns = implode(", ", array_map(function ($col) {
		return "`" . $col . "`";  // Enclose column names in backticks
	}, array_keys($pay_head_values)));

	// Escape values to prevent SQL injection
	$escaped_values = array_map(function ($value) use ($db) {
		if (is_numeric($value)) {
			return $value;
		} elseif (is_null($value)) {
			return 'NULL';
		} else {
			return "'" . mysqli_real_escape_string($db, $value) . "'";
		}
	}, array_values($pay_head_values));

	$values = implode(", ", $escaped_values);

	// Generate the SQL query
	$query = "INSERT INTO `cdbl_salaries` 
    (`emp_code`, `pay_month`, `generate_date`, $columns) 
    VALUES 
    ('$employee_id', '$pay_month', NOW(), $values)";

	// Execute the query
	if (!mysqli_query($db, $query)) {
		$result['code'] = 1;
		$result['result'] = 'Error in generating salary record: ' . mysqli_error($db); // Detailed error message
		echo json_encode($result);
		return;
	}

	// TCPDF setup
	// Ensure you have the correct path to tcpdf.php

	$pdf = new TCPDF();
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Your Name');
	$pdf->SetTitle('Salary Slip');
	$pdf->SetSubject('Salary Details for ' . $pay_month);

	$pdf->AddPage();

	// Set up the page style
	$html = '
    <style>
        .header { text-align: center; font-size: 20px; font-weight: bold; margin-top: 30px; margin-bottom: 15px; border: none; }
        .company-name { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .payslip-title { font-size: 16px; font-weight: bold; margin-top: 10px; margin-bottom: 20px; }
        .logo { width: 80px; padding-right: 20px; margin-top: 10px; margin-bottom: 10px; border: none; }
        .logo2 { width: 240px; padding-left: 20px; margin-bottom: 10px; border: none; }
        .employee-info-table { width: 100%; margin-top: 25px; font-size: 13px; border-collapse: collapse; }
        .employee-info-table td { padding: 8px; vertical-align: middle; border: 1px solid #bbb; }
        .salary-table { width: 100%; margin-top: 25px; font-size: 13px; border-collapse: collapse; }
        .salary-table th, .salary-table td { padding: 10px; border: 1px solid #bbb; }
        .footer { margin-top: 50px; font-size: 13px; }
        .footer td { padding: 8px; }
    </style>
    ';

	// Header Section
	$html .= '<div class="header">';
	$html .= '<img class="logo" src="' . dirname(dirname(__FILE__)) . '/dist/img/cdbllogo.png" alt="Logo" style="width: 100px; height: 80px;  margin-right: 10px;" />';  // Use absolute file path for TCPDF
	$html .= '<img class="logo2" src="' . dirname(dirname(__FILE__)) . '/dist/img/logo-key.jpg" alt="Logo" style="width: 100px; height: 80px; margin-left: 10px;" />';
	$html .= '<div class="company-name">Central Depository Bangladesh Limited</div>';
	$html .= '<div class="payslip-title">Payslip for the Month of ' . $pay_month . '</div>';
	$html .= '</div>';

	// Employee Info Section
	$html .= '<table class="employee-info-table">';
	// Fetch employee details from database
	$employee_query = "SELECT first_name, last_name, designation, department, joining_date FROM `" . DB_PREFIX . "employees` WHERE emp_code = '$employee_id' LIMIT 1";
	$employee_result = mysqli_query($db, $employee_query);
	if (!$employee_result) {
		$emp_name = 'N/A';
		$designation = 'N/A';
		$department = 'N/A';
		$joining_date = 'N/A';
	} else {
		$employee_data = mysqli_fetch_assoc($employee_result);
		$emp_name = trim(($employee_data['first_name'] ?? '') . ' ' . ($employee_data['last_name'] ?? ''));
		if ($emp_name === '') {
			$emp_name = 'N/A';
		}
		$designation = $employee_data['designation'] ?? 'N/A';
		$department = $employee_data['department'] ?? 'N/A';
		$joining_date = !empty($employee_data['joining_date']) ? date('d-M-Y', strtotime($employee_data['joining_date'])) : 'N/A';
	}

	$html .= '<tr><td>Employee Code</td><td>: ' . strtoupper($employee_id) . '</td></tr>';
	$html .= '<tr><td>Employee Name</td><td>: ' . htmlspecialchars($emp_name) . '</td></tr>';
	$html .= '<tr><td>Designation</td><td>: ' . htmlspecialchars($designation) . '</td></tr>';
	$html .= '<tr><td>Department</td><td>: ' . htmlspecialchars($department) . '</td></tr>';
	$html .= '<tr><td>Joining Date</td><td>: ' . $joining_date . '</td></tr><br><>';
	$html .= '</table>';

	// Earnings and Deductions Table
	$html .= '<table class="salary-table">';
	//$html .= '<thead><tr><th>Earnings and Deductions</th></tr></thead>';
	$html .= '<tr><td>Basic Salary</td><td>' . number_format($pay_head_values['basic_salary'], 2) . '</td><td>PF</td><td>' . number_format($pay_head_values['employee_provident_fund'], 2) . '</td></tr>';
	$html .= '<tr><td>House Rent</td><td>' . number_format($pay_head_values['house_rent'], 2) . '</td><td>Income Tax</td><td>' . number_format($pay_head_values['income_tax'], 2) . '</td></tr>';
	$html .= '<tr><td>Car Allowance</td><td>' . number_format($pay_head_values['car_allowance'], 2) . '</td><td>Loan Repayment</td><td>' . number_format($pay_head_values['loans_repayment'], 2) . '</td></tr>';
	$html .= '<tr><td>Medical Allowance</td><td>' . number_format($pay_head_values['medical_allowance'], 2) . '</td><td>Other Deductions</td><td>' . number_format($pay_head_values['other_deductions'], 2) . '</td></tr>';
	$html .= '</table>';

	// Net Salary
	$html .= '<table class="footer">';
	$html .= '<tr><td><strong>Net Salary</strong></td><td>: ' . number_format($pay_head_values['net_salary'], 2) . '</td></tr>';
	$html .= '<tr><td><strong>In Words</strong></td><td>: ' . ucfirst(numberToWords($pay_head_values['net_salary'])) . ' Taka</td></tr>';
	$html .= '</table>';

	// Footer Section
	$html .= '<div class="footer">';
	$html .= '<p>Prepared By: CDBL Payroll Management System</p>';
	$html .= '<p>Approved By: CDBL Accounts & Finanace </p>';
	$html .= '</div>';

	//   Save the PDF
	$pdf->WriteHTML($html);
	$payslip_path = __DIR__ . '/../payslips/' . $employee_id . '/' . $pay_month . '/';  // Using absolute path
	if (!file_exists($payslip_path)) {
		mkdir($payslip_path, 0777, true);  // Create directory if it doesn't exist
	}

	// Create the PDF file path
	$pdf_file_path = $payslip_path . $pay_month . '.pdf';
	if (!file_exists($payslip_path)) {
		if (!mkdir($payslip_path, 0777, true)) {
			echo "Error creating directory.";
			exit;
		}
	}

	// Generate and save the PDF
	$pdf->WriteHTML($html);
	$pdf->Output($pdf_file_path, 'F'); // Output the PDF to the desired file
	$pdf->Output($payslip_path . $pay_month . '.pdf', 'F');

	$result['code'] = 0;
	$_SESSION['PaySlipMsg'] = $pay_month . ' PaySlip has been successfully generated for ' . $employee_id . '.';
	echo json_encode($result);
}


function SendPaySlipByMail()
{
	$result = array();
	global $db;

	$emp_code = $_POST['emp_code'];
	$month 	  = $_POST['month'];
	$empData  = GetEmployeeDataByEmpCode($emp_code);
	if ($empData) {
		$empName  = $empData['first_name'] . ' ' . $empData['last_name'];
		$empEmail = $empData['email'];
		$subject  = 'PaySlip for ' . $month;
		$message  = '<p>Hi ' . $empData['first_name'] . '</p>';
		$message .= '<p>Here is your attached Salary Slip for the period of ' . $month . '.</p>';
		$message .= '<hr/>';
		$message .= '<p>Thank You,<br/>Wisely Online Services Private Limited</p>';
		$attachment[0]['src'] = dirname(dirname(__FILE__)) . '/payslips/' . $emp_code . '/' . str_replace(', ', '-', $month) . '/' . str_replace(', ', '-', $month) . '.pdf';
		$attachment[0]['name'] = str_replace(', ', '-', $month);
		$send = Send_Mail($subject, $message, $empName, $empEmail, FALSE, FALSE, FALSE, FALSE, $attachment);
		if ($send == 0) {
			$result['code'] = 0;
			$result['result'] = 'PaySlip for ' . $month . ' has been successfully send to ' . $empName;
		} else {
			$result['code'] = 1;
			$result['result'] = 'PaySlip is not send, please try again.';
		}
	} else {
		$result['code'] = 2;
		$result['result'] = 'No such employee found.';
	}

	echo json_encode($result);
}

function EditProfileByID()
{
	$result = array();
	global $db;

	if ($_SESSION['Login_Type'] == 'admin') {
		$admin_id = $_SESSION['Admin_ID'];
		$admin_name = mysqli_real_escape_string($db, $_POST['admin_name']);
		$admin_email = mysqli_real_escape_string($db, $_POST['admin_email']);
		if (!empty($admin_name) && !empty($admin_email)) {
			$editSQL = mysqli_query($db, "UPDATE `" . DB_PREFIX . "admin` SET `admin_name` = '$admin_name', `admin_email` = '$admin_email' WHERE `admin_id` = $admin_id");
			if ($editSQL) {
				$result['code'] = 0;
				$result['result'] = 'Profile data has been successfully updated.';
			} else {
				$result['code'] = 1;
				$result['result'] = 'Something went wrong, please try again.';
			}
		} else {
			$result['code'] = 2;
			$result['result'] = 'All fields are mandatory.';
		}
	} else {
		$emp_code = $_SESSION['emp_Code'];
		$first_name = stripslashes($_POST['first_name']);
		$last_name = stripslashes($_POST['last_name']);
		$dob = stripslashes($_POST['dob']);
		$gender = stripslashes($_POST['gender']);
		$marital_status = stripslashes($_POST['marital_status']);
		$email = stripslashes($_POST['email']);
		$mobile = stripslashes($_POST['mobile']);
		$telephone = stripslashes($_POST['telephone']);
		$employment_type = stripslashes($_POST['employment_type']);
		$joining_date = stripslashes($_POST['joining_date']);
		$blood_group = stripslashes($_POST['blood_group']);
		$designation = stripslashes($_POST['designation']);
		$department = stripslashes($_POST['department']);


		$account_no = stripslashes($_POST['account_no']);

		if (!empty($first_name) && !empty($last_name) && !empty($dob) && !empty($gender) && !empty($marital_status) && !empty($nationality) && !empty($address) && !empty($city) && !empty($state) && !empty($country) && !empty($email) && !empty($mobile) && !empty($identity_doc) && !empty($identity_no) && !empty($employment_type) && !empty($joining_date) && !empty($blood_group) && !empty($designation) && !empty($department) && !empty($pan_no) && !empty($bank_name) && !empty($account_no) && !empty($ifsc_code) && !empty($pf_account)) {
			$updateEmp = mysqli_query($db, "UPDATE `" . DB_PREFIX . "employees` SET `first_name` = '$first_name', `last_name` = '$last_name', `dob` = '$dob', `gender` = '$gender', `marital_status` = '$marital_status',`address` = '$address', `email` = '$email', `mobile` = '$mobile', `telephone` = '$telephone',  `employment_type` = '$employment_type', `joining_date` = '$joining_date', `blood_group` = '$blood_group', `designation` = '$designation', `department` = '$department', `account_no` = '$account_no' WHERE `emp_code` = $emp_code");
			if ($updateEmp) {
				$result['result'] = 'Profile data has been successfully updated.';
				$result['code'] = 0;
			} else {
				$result['result'] = 'Something went wrong, please try again.';
				$result['code'] = 1;
			}
		} else {
			$result['result'] = 'All fields are mandatory except Telephone.';
			$result['code'] = 2;
		}
	}

	echo json_encode($result);
}
function LoadingAllLeaves()
{
	global $db;
	$empData = GetDataByIDAndType($_SESSION['Admin_ID'], $_SESSION['Login_Type']);
	$requestData = $_REQUEST;
	$columns = array(
		0 => 'leave_id',
		1 => 'emp_code',
		2 => 'leave_subject',
		3 => 'leave_dates',
		4 => 'leave_message',
		5 => 'leave_type',
		6 => 'leave_status'
	);

	$sql  = "SELECT `leave_id` ";
	$sql .= " FROM `" . DB_PREFIX . "leaves`";
	$query = mysqli_query($db, $sql);
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;

	$sql  = "SELECT `leaves`.*, `emp`.`first_name`, `emp`.`last_name`";
	$sql .= " FROM `" . DB_PREFIX . "leaves` AS `leaves`";
	$sql .= " LEFT JOIN `" . DB_PREFIX . "employees` AS `emp` ON `leaves`.`emp_code` = `emp`.`emp_code`";
	if (!empty($requestData['search']['value'])) {
		$sql .= " WHERE (`leaves`.`leave_id` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leaves`.`emp_code` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leaves`.`leave_subject` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leaves`.`leave_dates` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leaves`.`leave_message` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leaves`.`leave_type` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leaves`.`leave_status` LIKE '" . $requestData['search']['value'] . "%')";
	}
	$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "";
	$query = mysqli_query($db, $sql);

	$data = array();
	$i = 1 + $requestData['start'];
	while ($row = mysqli_fetch_assoc($query)) {
		$nestedData = array();
		$nestedData[] = $row["leave_id"];
		$nestedData[] = '<a target="_blank" href="' . REG_URL . 'reports/' . $row["emp_code"] . '/">' . $row["first_name"] . ' ' . $row["last_name"] . '</a>';
		$nestedData[] = $row["leave_subject"];
		$nestedData[] = $row["leave_dates"];
		$nestedData[] = $row["leave_message"];
		$nestedData[] = $row["leave_type"];
		if ($row["leave_status"] == 'pending') {
			$nestedData[] = '<span class="label label-warning">' . ucwords($row["leave_status"]) . '</span>';
		} elseif ($row['leave_status'] == 'approve') {
			$nestedData[] = '<span class="label label-success">' . ucwords($row["leave_status"]) . 'd</span>';
		} elseif ($row['leave_status'] == 'reject') {
			$nestedData[] = '<span class="label label-danger">' . ucwords($row["leave_status"]) . 'ed</span>';
		}
		$data[] = $nestedData;
		$i++;
	}
	$json_data = array(
		"draw"            => intval($requestData['draw']),
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	echo json_encode($json_data);
}

function LoadingMyLeaves()
{
	global $db;
	$empData = GetDataByIDAndType($_SESSION['Admin_ID'], $_SESSION['Login_Type']);
	$requestData = $_REQUEST;
	$columns = array(
		0 => 'leave_id',
		1 => 'leave_subject',
		2 => 'leave_dates',
		3 => 'leave_message',
		4 => 'leave_type',
		5 => 'leave_status'
	);

	$sql  = "SELECT `leave_id` ";
	$sql .= " FROM `" . DB_PREFIX . "leaves` WHERE `emp_code` = '" . $empData['emp_code'] . "'";
	$query = mysqli_query($db, $sql);
	$totalData = mysqli_num_rows($query);
	$totalFiltered = $totalData;

	$sql  = "SELECT *";
	$sql .= " FROM `" . DB_PREFIX . "leaves` WHERE `emp_code` = '" . $empData['emp_code'] . "'";
	if (!empty($requestData['search']['value'])) {
		$sql .= " AND (`leave_id` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_subject` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_dates` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_message` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_type` LIKE '" . $requestData['search']['value'] . "%'";
		$sql .= " OR `leave_status` LIKE '" . $requestData['search']['value'] . "%')";
	}
	$query = mysqli_query($db, $sql);
	$totalFiltered = mysqli_num_rows($query);
	$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT " . $requestData['start'] . " ," . $requestData['length'] . "";
	$query = mysqli_query($db, $sql);

	$data = array();
	$i = 1 + $requestData['start'];
	while ($row = mysqli_fetch_assoc($query)) {
		$nestedData = array();
		$nestedData[] = $row["leave_id"];
		$nestedData[] = $row["leave_subject"];
		$nestedData[] = $row["leave_dates"];
		$nestedData[] = $row["leave_message"];
		$nestedData[] = $row["leave_type"];
		if ($row["leave_status"] == 'pending') {
			$nestedData[] = '<span class="label label-warning">' . ucwords($row["leave_status"]) . '</span>';
		} elseif ($row['leave_status'] == 'approve') {
			$nestedData[] = '<span class="label label-success">' . ucwords($row["leave_status"]) . 'd</span>';
		} elseif ($row['leave_status'] == 'reject') {
			$nestedData[] = '<span class="label label-danger">' . ucwords($row["leave_status"]) . 'ed</span>';
		}
		$data[] = $nestedData;
		$i++;
	}
	$json_data = array(
		"draw"            => intval($requestData['draw']),
		"recordsTotal"    => intval($totalData),
		"recordsFiltered" => intval($totalFiltered),
		"data"            => $data
	);

	echo json_encode($json_data);
}

function ApplyLeaveToAdminApproval()
{
	$result = array();
	global $db;

	$adminData = GetAdminData(1);
	$empData   = GetDataByIDAndType($_SESSION['Admin_ID'], $_SESSION['Login_Type']);

	$leave_subject = mysqli_real_escape_string($db, $_POST['leave_subject']);
	$leave_dates   = mysqli_real_escape_string($db, $_POST['leave_dates']);
	$leave_message = mysqli_real_escape_string($db, $_POST['leave_message']);
	$leave_type    = mysqli_real_escape_string($db, $_POST['leave_type']);
	if (!empty($leave_subject) && !empty($leave_dates) && !empty($leave_message) && !empty($leave_type)) {
		$AppliedDates = '';
		$dates = array();
		if (strpos($leave_dates, ',') !== false) {
			$dates = explode(',', $leave_dates);
		} else {
			$dates[] = $leave_dates;
		}
		foreach ($dates as $date) {
			$date = trim($date);
			$checkLeaveSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "leaves` WHERE FIND_IN_SET('$date', `leave_dates`) > 0 AND `emp_code` = '" . $empData['emp_code'] . "'");
			if ($checkLeaveSQL) {
				if (mysqli_num_rows($checkLeaveSQL) > 0) {
					$AppliedDates .= $date . ', ';
				}
			}
		}
		if (empty($AppliedDates)) {
			$leaveSQL = mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "leaves` (`emp_code`, `leave_subject`, `leave_dates`, `leave_message`, `leave_type`, `apply_date`) VALUES('" . $empData['emp_code'] . "', '$leave_subject', '$leave_dates', '$leave_message', '$leave_type', '" . date('Y-m-d H:i:s') . "')");
			if ($leaveSQL) {
				$insertedId = mysqli_insert_id($db);
				$empName    = $empData['first_name'] . ' ' . $empData['last_name'];
				$empEmail   = $empData['email'];
				$adminEmail = $adminData['admin_email'];
				$subject 	= 'Leave Application: ' . $leave_subject;
				$message    = '<p>Employee: ' . $empName . ' (' . $empData['emp_code'] . ')' . '</p>';
				$message   .= '<p>Leave Message: ' . $leave_message . '</p>';
				$message   .= '<p>Leave Date(s): ' . $leave_dates . '</p>';
				$message   .= '<p>Leave Type: ' . $leave_type . '</p>';
				$message   .= '<hr/>';
				$message   .= '<p>Please click on the buttons below or log into the admin area to get an action:</p>';
				$message   .= '<form method="post" action="' . BASE_URL . 'ajax/?case=ApproveLeaveApplication&id=' . $insertedId . '" style="display:inline;">';
				$message   .= '<input type="hidden" name="id" value="' . $insertedId . '" />';
				$message   .= '<button type="submit" style="background:green; border:1px solid green; color:white; padding:0 5px 3px; cursor:pointer; margin-right:15px;">Approve</button>';
				$message   .= '</form>';
				$message   .= '<form method="post" action="' . BASE_URL . 'ajax/?case=RejectLeaveApplication&id=' . $insertedId . '" style="display:inline;">';
				$message   .= '<input type="hidden" name="id" value="' . $insertedId . '" />';
				$message   .= '<button type="submit" style="background:red; border:1px solid red; color:white; padding:0 5px 3px; cursor:pointer;">Reject</button>';
				$message   .= '</form>';
				$message   .= '<p style="font-size:85%;">After clicking the button, please click on OK and then Continue to make your action complete.</p>';
				$message   .= '<hr/>';
				$message   .= '<p>Thank You<br/>' . $empName . '</p>';
				$adminName 	= $adminData['admin_name'];
				$send = Send_Mail($subject, $message, $adminName, $adminEmail, $empName, $empEmail);
				if ($send == 0) {
					$result['code'] = 0;
					$result['result'] = 'Leave Application has been successfully sent to your employer through mail.';
				} else {
					$result['code'] = 1;
					$result['result'] = 'Notice: Leave Application not sent through E-Mail, please try again.';
				}
			} else {
				$result['code'] = 1;
				$result['result'] = 'Something went wrong, please try again.';
			}
		} else {
			$alreadyDates = substr($AppliedDates, 0, -2);
			$result['code'] = 2;
			$result['result'] = 'You have already applied for leave on ' . $alreadyDates . '. Please change the leave dates.';
		}
	} else {
		$result['code'] = 3;
		$result['result'] = 'All fields are mandatory.';
	}

	echo json_encode($result);
}

function ApproveLeaveApplication()
{
	$result = array();
	global $db;

	$leaveId = $_REQUEST['id'];
	$leaveSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "leaves` WHERE `leave_id` = $leaveId AND `leave_status` = 'pending' LIMIT 0, 1");
	if ($leaveSQL) {
		if (mysqli_num_rows($leaveSQL) == 1) {
			$leaveData = mysqli_fetch_assoc($leaveSQL);
			$update = mysqli_query($db, "UPDATE `" . DB_PREFIX . "leaves` SET `leave_status` = 'approve' WHERE `leave_id` = $leaveId");
			if ($update) {
				$empData  = GetEmployeeDataByEmpCode($leaveData['emp_code']);
				if ($empData) {
					$empName  = $empData['first_name'] . ' ' . $empData['last_name'];
					$empEmail = $empData['email'];
					$subject  = 'Leave Application Approved';
					$message  = '<p>Hi ' . $empData['first_name'] . '</p>';
					$message .= '<p>Your leave application is approved.</p>';
					$message .= '<p>Application Details:</p>';
					$message .= '<p>Subject: ' . $leaveData['leave_subject'] . '</p>';
					$message .= '<p>Leave Date(s): ' . $leaveData['leave_dates'] . '</p>';
					$message .= '<p>Message: ' . $leaveData['leave_message'] . '</p>';
					$message .= '<p>Leave Type: ' . $leaveData['leave_type'] . '</p>';
					$message .= '<p>Status: ' . ucwords($leaveData['leave_status']) . '</p>';
					$message .= '<hr/>';
					$message .= '<p>Thank You,<br/>Wisely Online Services Private Limited</p>';
					$send = Send_Mail($subject, $message, $empName, $empEmail);
					if ($send == 0) {
						$result['code'] = 0;
						$result['result'] = 'Leave Application is successfully approved. An email notification will be send to the employee.';
					} else {
						$result['code'] = 1;
						$result['result'] = 'Leave Application is not approved, please try again.';
					}
				} else {
					$result['code'] = 2;
					$result['result'] = 'No such employee found.';
				}
			} else {
				$result['code'] = 1;
				$result['result'] = 'Something went wrong, please try again.';
			}
		} else {
			$result['code'] = 2;
			$result['result'] = 'This leave application is already verified.';
		}
	} else {
		$result['code'] = 3;
		$result['result'] = 'Something went wrong, please try again.';
	}

	echo json_encode($result);
}

function RejectLeaveApplication()
{
	$result = array();
	global $db;

	$leaveId = $_REQUEST['id'];
	$leaveSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "leaves` WHERE `leave_id` = $leaveId AND `leave_status` = 'pending' LIMIT 0, 1");
	if ($leaveSQL) {
		if (mysqli_num_rows($leaveSQL) == 1) {
			$leaveData = mysqli_fetch_assoc($leaveSQL);
			$update = mysqli_query($db, "UPDATE `" . DB_PREFIX . "leaves` SET `leave_status` = 'reject' WHERE `leave_id` = $leaveId");
			if ($update) {
				$empData  = GetEmployeeDataByEmpCode($leaveData['emp_code']);
				if ($empData) {
					$empName  = $empData['first_name'] . ' ' . $empData['last_name'];
					$empEmail = $empData['email'];
					$subject  = 'Leave Application Rejected';
					$message  = '<p>Hi ' . $empData['first_name'] . '</p>';
					$message .= '<p>Your leave application is rejected.</p>';
					$message .= '<p>Application Details:</p>';
					$message .= '<p>Subject: ' . $leaveData['leave_subject'] . '</p>';
					$message .= '<p>Leave Date(s): ' . $leaveData['leave_dates'] . '</p>';
					$message .= '<p>Message: ' . $leaveData['leave_message'] . '</p>';
					$message .= '<p>Leave Type: ' . $leaveData['leave_type'] . '</p>';
					$message .= '<p>Status: ' . ucwords($leaveData['leave_status']) . '</p>';
					$message .= '<hr/>';
					$message .= '<p>Thank You,<br/>Wisely Online Services Private Limited</p>';
					$send = Send_Mail($subject, $message, $empName, $empEmail);
					if ($send == 0) {
						$result['code'] = 0;
						$result['result'] = 'Leave Application is rejected. An email notification will be send to the employee.';
					} else {
						$result['code'] = 1;
						$result['result'] = 'Leave Application is not rejected, please try again.';
					}
				} else {
					$result['code'] = 2;
					$result['result'] = 'No such employee found.';
				}
			} else {
				$result['code'] = 1;
				$result['result'] = 'Something went wrong, please try again.';
			}
		} else {
			$result['code'] = 2;
			$result['result'] = 'This leave application is already verified.';
		}
	} else {
		$result['code'] = 3;
		$result['result'] = 'Something went wrong, please try again.';
	}

	echo json_encode($result);
}
