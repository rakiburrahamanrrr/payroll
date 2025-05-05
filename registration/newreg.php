<?php require(dirname(__FILE__) . '/config.php');

$errors 	= array();
$expensions = array("jpeg", "jpg", "png");
$target_dir = dirname(__FILE__) . "/photos/";

if ( isset($_POST['submit']) ) {

	// Generate employee code
	$selectSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "employees` ORDER BY `employee_id` DESC LIMIT 1");
	if ( $selectSQL && mysqli_num_rows($selectSQL) > 0 ) {
		$lastEmp = mysqli_fetch_assoc($selectSQL);
		$curEmpID = 'cdbl' . sprintf("%02d", $lastEmp['employee_id'] + 1);
	} else {
		$curEmpID = 'cdbl01';
	}

	if ( empty($_POST['first_name']) ) {
		$errors['first_name'] = '<span class="text-danger">Please enter your first name!</span>';
	}
	if ( empty($_POST['last_name']) ) {
		$errors['last_name'] = '<span class="text-danger">Please enter your last name!</span>';
	}
	if ( empty($_POST['dob']) ) {
		$errors['dob'] = '<span class="text-danger">Please enter your date of birth!</span>';
	}
	if ( empty($_POST['gender']) ) {
		$errors['gender'] = '<span class="text-danger">Please select your gender!</span>';
	}
	if ( empty($_POST['marital_status']) ) {
		$errors['marital_status'] = '<span class="text-danger">Please choose your marital status!</span>';
	}
	if ( empty($_POST['national_id']) ) {
		$errors['national_id'] = '<span class="text-danger">Please enter your NID!</span>';
	}
	if ( empty($_POST['address']) ) {
		$errors['address'] = '<span class="text-danger">Please enter your Present address!</span>';
	}
	if ( empty($_POST['email']) ) {
		$errors['email'] = '<span class="text-danger">Please enter your email id!</span>';
	}
	if ( empty($_POST['mobile']) ) {
		$errors['mobile'] = '<span class="text-danger">Please enter your mobile number!</span>';
	}
	// if ( empty($_POST['identification']) ) {
	// 	$errors['identification'] = '<span class="text-danger">Please choose your identification document!</span>';
	// }
	// if ( empty($_POST['id_no']) ) {
	// 	$errors['id_no'] = '<span class="text-danger">Please enter your identification number!</span>';
	// }
	if ( empty($_POST['employment_type']) ) {
		$errors['employment_type'] = '<span class="text-danger">Please choose your employment type!</span>';
	}
	if ( empty($_POST['employment_status']) ) {
		$errors['employment_status'] = '<span class="text-danger">Please choose your employment Status!</span>';
	}
	if ( empty($_POST['joining_date']) ) {
		$errors['joining_date'] = '<span class="text-danger">Please enter your joining date!</span>';
	}
	if ( empty($_POST['bloodgrp']) ) {
		$errors['bloodgrp'] = '<span class="text-danger">Please enter your blood group!</span>';
	}
	if ( empty($_POST['emp_password']) ) {
		$errors['emp_password'] = '<span class="text-danger">Please set employee password!</span>';
	} 
	else {
		$emp_password = addslashes($_POST['emp_password']);
	}
	
	if ( empty($_FILES['photo']['name']) ) {
		$errors['photo'] = '<span class="text-danger">Please upload your recent photograph!</span>';
	} else {
		$file_tmp 	= $_FILES['photo']['tmp_name'];
		$file_type 	= $_FILES['photo']['type'];
		$file_ext 	= strtolower(end(explode('.', $_FILES['photo']['name'])));

		$photocopy 	= $curEmpID . '.' . $file_ext;
		if ( in_array($file_ext, $expensions) === false ) {
		 	$errors['photo'] = '<span class="text-danger">Extension not allowed, please choose a JPEG or PNG file!</span>';
		}
	}

	if ( empty($errors) == true ) {
	 	if ( move_uploaded_file($file_tmp, $target_dir . $photocopy) ) {
			
	 		extract($_POST);
			// Convert employee_id to integer
			$employee_id = (int)$employee_id;
			// Convert dates to MySQL format
			$dob_mysql = !empty($dob) ? "STR_TO_DATE('$dob', '%m/%d/%Y')" : "NULL";
			$joining_date_mysql = !empty($joining_date) ? "STR_TO_DATE('$joining_date', '%m/%d/%Y')" : "NULL";
			$confirmation_date_mysql = !empty($confirmation_date) ? "STR_TO_DATE('$confirmation_date', '%m/%d/%Y')" : "NULL";
			$resign_date_mysql = !empty($resign_date) ? "STR_TO_DATE('$resign_date', '%m/%d/%Y')" : "NULL";
			
$insertSQL = mysqli_query($db, "INSERT INTO `" . DB_PREFIX . "employees`(
                `emp_code`, `first_name`, `last_name`, `dob`, `gender`, `marital_status`, 
                `address`, `paraddress`, `email`, `mobile`, `telephone`, 
                `national_id`, `verification`, `employee_id`, `employment_type`, 
                `employment_status`, `department`, `designation`, `emp_grade`, 
                `empsal_grade`, `joining_date`, `confirmation_date`, `resign_date`,
                `job_loc`, `blood_group`, `insurance_id`, `service_period`,
                `academic_qualifications`, `emp_action`, `photo`, `emp_password`, 
                `account_no`, `etin_no`, `created_at`
            ) VALUES (
                '$curEmpID', '$first_name', '$last_name', $dob_mysql, '$gender', '$marital_status', 
                '$address', '$paraddress', '$email', '$mobile', '$telephone', 
                '$national_id', '$verification', $employee_id, '$employment_type', 
                '$employment_status', '$department', '$designation', '$emp_grade', 
                '$empsal_grade', $joining_date_mysql, $confirmation_date_mysql, $resign_date_mysql,
                '$job_loc', '$bloodgrp', '$insurance_id', '$service_period',
                '$academic_qualifications', '$emp_action', '$photocopy', '" . sha1($emp_password) . "', 
                '$account_no', '$etin_no', NOW()
            )");
            if (!$insertSQL) {
                $errors['sql_error'] = '<span class="text-danger">Database insert error: ' . mysqli_error($db) . '</span>';
            } else {
                $_SESSION['success'] = '<p class="text-center"><span class="text-success">Employee registration successfully!</span></p>';
                header('location:report.php?');
                exit;
            }
	 	} else {
	 		$errors['photo'] = '<span class="text-danger">Photo is not uploaded, please try again!</span>';
	 	}
	}
} ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
  	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

	<title>Employee Registration - Payroll</title>

	<link rel="stylesheet" href="<?php echo BASE_URL; ?>bootstrap/css/bootstrap.min.css">
  	<link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/datepicker/datepicker3.css">

	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body class="hold-transition register-page">
	<div class="container">
		<div class="register-box">
		  	<div class="register-logo">
		    	<a href="<?php echo BASE_URL; ?>"><b>CDBL Payroll</b> Management</a>
		    	<small>Employee Registration Form</small>
		  	</div>
		</div>
		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">Fill the below form</h3>
				<div class="box-tools pull-right">
					<span class="text-red">All fields are mandatory</span>
				</div>
			</div>
			<form class="form-horizontal" method="post" enctype="multipart/form-data" novalidate="">
                <?php if (!empty($errors['sql_error'])): ?>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <?php echo $errors['sql_error']; ?>
                        </div>
                    </div>
                <?php endif; ?>
				<div class="box-body">
					<div class="form-group">
						<label for="first_name" class="col-sm-2 control-label">Full Name</label>
						<div class="col-sm-5">
							<input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" value="<?php echo $_POST['first_name']; ?>" required />
							<?php echo $errors['first_name']; ?>
						</div>
						<div class="col-sm-5">
							<input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" value="<?php echo $_POST['last_name']; ?>" required />
							<?php echo $errors['last_name']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="dob" class="col-sm-2 control-label">DOB</label>
						<div class="col-sm-5">
							<div class="input-group">
								<input type="text" class="form-control" id="dob" name="dob" placeholder="MM/DD/YYYY" value="<?php echo $_POST['dob']; ?>" required />
								<span class="input-group-addon">
									<i class="glyphicon glyphicon-calendar"></i>
								</span>
							</div>
							<?php echo $errors['dob']; ?>
						</div>
					</div>
			        <div class="form-group">
				        <label class="col-xs-2 control-label">Gender</label>
				        <div class="col-xs-10">
				            <div class="btn-group" data-toggle="buttons">
				                <label class="btn btn-default <?php echo $_POST['gender']=='male' ? 'active' : ''; ?>">
				                    <input type="radio" name="gender" value="male" <?php echo $_POST['gender']=='male' ? 'checked' : ''; ?> required /> Male
				                </label>
				                <label class="btn btn-default <?php echo $_POST['gender']=='female' ? 'active' : ''; ?>">
				                    <input type="radio" name="gender" value="female" <?php echo $_POST['gender']=='female' ? 'checked' : ''; ?> required /> Female
				                </label>
				            </div><br />
				            <?php echo $errors['gender']; ?>
				        </div>
				    </div>
					<div class="form-group">
						<label for="marital_status" class="col-sm-2 control-label">Marital status</label>
						<div class="col-sm-5">
							<select class="form-control" id="marital_status" name="marital_status" required>
								<option value="">Please make a choice</option>
								<option <?php echo $_POST['marital_status']=='Single' ? 'selected' : ''; ?> value="Single">Single</option>
								<option <?php echo $_POST['marital_status']=='Married' ? 'selected' : ''; ?> value="Married">Married</option>
								<option <?php echo $_POST['marital_status']=='Divorced' ? 'selected' : ''; ?> value="Divorced">Divorced</option>
							</select>
							<?php echo $errors['marital_status']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="national_id" class="col-sm-2 control-label">National ID</label>
						<div class="col-sm-5">
							<input type="text" class="form-control" id="national_id" name="national_id" placeholder="National ID" value="<?php echo $_POST['national_id']; ?>" required />
							<?php echo $errors['national_id']; ?>
						</div>
					</div>
					<hr />
					<div class="form-group">
						<label for="address" class="col-sm-2 control-label">Present Address</label>
						<div class="col-sm-10">
							<textarea class="form-control" id="address" name="address" placeholder="Address" required><?php echo $_POST['address']; ?></textarea>
							<?php echo $errors['address']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="paraddress" class="col-sm-2 control-label">Permanent Address</label>
						<div class="col-sm-10">
							<textarea class="form-control" id="paraddress" name="paraddress" placeholder="Permanent Address" required><?php echo $_POST['paraddress']; ?></textarea>
							<?php echo $errors['paraddress']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="email" class="col-sm-2 control-label">Email Id</label>
						<div class="col-sm-10">
							<input type="email" class="form-control" id="email" name="email" placeholder="Email Id" value="<?php echo $_POST['email']; ?>" required />
							<?php echo $errors['email']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="mobile" class="col-sm-2 control-label">Contact No</label>
						<div class="col-sm-5">
							<input type="text" class="form-control" id="mobile" name="mobile" placeholder="Mobile No" value="<?php echo $_POST['mobile']; ?>" required />
							<?php echo $errors['mobile']; ?>
						</div>
						<div class="col-sm-5">
							<input type="text" class="form-control" id="telephone" name="telephone" value="<?php echo $_POST['telephone']; ?>" placeholder="Emergency Contact No" />
						</div>
					</div>
					<div class="form-group">
						<label for="verification" class="col-sm-2 control-label">Verification Status</label>
						<div class="col-sm-10">
							<select class="form-control" id="verification" name="verification" required>
								<option value="">Please make a choice</option>
								<option <?php echo $_POST['verification']=='Verified' ? 'selected' : ''; ?> value="Verified">Verified</option>
								<option <?php echo $_POST['verification']=='Not Verified' ? 'selected' : ''; ?> value="Not Verified">Not Verified</option>
								<option <?php echo $_POST['verification']=='Under Process' ? 'selected' : ''; ?> value="Under Process">Under Process</option>
							</select>
							<?php echo $errors['verification']; ?>
						</div>
					</div>
					
					<hr />
					<div class="form-group">
						<label for="employee_id" class="col-sm-2 control-label">Employee Id</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="Employee Id No" value="<?php echo $_POST['employee_id']; ?>" required />
							<?php echo $errors['employee_id']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="employment_type" class="col-sm-2 control-label">Employee Type</label>
						<div class="col-sm-10">
							<select class="form-control" id="employment_type" name="employment_type">
								<option value="">Please make a choice</option>
								<option <?php echo $_POST['employment_type']=='Provision' ? 'selected' : ''; ?> value="Provision">Provision</option>
								<option <?php echo $_POST['employment_type']=='Contractual' ? 'selected' : ''; ?> value="Contractual">Contractual</option>
								<option <?php echo $_POST['employment_type']=='Permanent' ? 'selected' : ''; ?> value="Permanent">Permanent position</option>
							</select>
							<?php echo $errors['employment_type']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="employment_status" class="col-sm-2 control-label">Employee status</label>
						<div class="col-sm-10">
							<select class="form-control" id="employment_status" name="employment_status">
								<option value="">Please make a choice</option>
								<option <?php echo $_POST['employment_status']=='Active' ? 'selected' : ''; ?> value="Active">Active</option>
								<option <?php echo $_POST['employment_status']=='Inactive' ? 'selected' : ''; ?> value="Inactive">Inactive</option>
								<option <?php echo $_POST['employment_status']=='Leave without pay' ? 'selected' : ''; ?> value="Leavewithoutpay">Leave Without Pay</option>
							</select>
							<?php echo $errors['employment_status']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="department" class="col-sm-2 control-label">Department</label>
						<div class="col-sm-10">
							<select class="form-control" id="department" name="department">
								<option value="">Please make a choice</option>
								<option <?php echo $_POST['department']=='Value Added Services' ? 'selected' : ''; ?> value="Value Added Services">Value Added Services</option>
								<option <?php echo $_POST['department']=='Accounts' ? 'selected' : ''; ?> value="Accounts">Accounts</option>
								<option <?php echo $_POST['department']=='Compliance' ? 'selected' : ''; ?> value="Compliance">Compliance</option>
							</select>
							<?php echo $errors['department']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="designation" class="col-sm-2 control-label">Designation</label>
						<div class="col-sm-5">
							<input type="text" class="form-control" id="designation" name="designation" placeholder="Designation" value="<?php echo $_POST['designation']; ?>" required />
							<?php echo $errors['designation']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="emp_grade" class="col-sm-2 control-label">Employee Grade</label>
						<div class="col-sm-5">
							<input type="text" class="form-control" id="emp_grade" name="emp_grade" placeholder="Employee Grade" value="<?php echo $_POST['emp_grade']; ?>" required />
							<?php echo $errors['emp_grade']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="empsal_grade" class="col-sm-2 control-label">Employee Salary Grade</label>
						<div class="col-sm-5">
							<input type="text" class="form-control" id="empsal_grade" name="empsal_grade" placeholder="Employee Salary Grade" value="<?php echo $_POST['empsal_grade']; ?>" required />
							<?php echo $errors['empsal_grade']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="joining_date" class="col-sm-2 control-label">Joining Date</label>
						<div class="col-sm-5">
							<div class="input-group">
								<input type="text" class="form-control" id="joining_date" name="joining_date" placeholder="MM/DD/YYYY" value="<?php echo $_POST['joining_date']; ?>" required />
								<span class="input-group-addon">
									<i class="glyphicon glyphicon-calendar"></i>
								</span>
							</div>
							<?php echo $errors['joining_date']; ?>
						</div>
					</div>

					<div class="form-group">
						<label for="confirmation_date" class="col-sm-2 control-label">Confirmation Date</label>
						<div class="col-sm-5">
							<div class="input-group">
								<input type="text" class="form-control" id="confirmation_date" name="confirmation_date" placeholder="MM/DD/YYYY" value="<?php echo $_POST['confirmation_date']; ?>" required />
								<span class="input-group-addon">
									<i class="glyphicon glyphicon-calendar"></i>
								</span>
							</div>
							<?php echo $errors['confirmation_date']; ?>
						</div>
					</div>

					<div class="form-group">
						<label for="resign_date" class="col-sm-2 control-label">Resignation Date</label>
						<div class="col-sm-5">
							<div class="input-group">
								<input type="text" class="form-control" id="resign_date" name="resign_date" placeholder="MM/DD/YYYY" value="<?php echo $_POST['resign_date']; ?>" />
								<span class="input-group-addon">
									<i class="glyphicon glyphicon-calendar"></i>
								</span>
							</div>
							<?php echo $errors['resign_date']; ?>
						</div>
					</div>

					<div class="form-group">
						<label for="job_loc" class="col-sm-2 control-label">Job Location</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="job_loc" name="job_loc" placeholder="Job Location" value="<?php echo $_POST['job_loc']; ?>" />
							<?php echo $errors['job_loc']; ?>
						</div>
					</div>	
					<div class="form-group">
						<label for="bloodgrp" class="col-sm-2 control-label">Blood Group</label>
						<div class="col-sm-4">
							<input type="text" class="form-control" id="bloodgrp" name="bloodgrp" placeholder="Blood Group" value="<?php echo $_POST['bloodgrp']; ?>" required />
							<?php echo $errors['bloodgrp']; ?>
						</div>
					</div><div class="form-group">
						<label for="insurance_id" class="col-sm-2 control-label">Insurance ID</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="insurance_id" name="insurance_id" placeholder="Insurance ID Number" value="<?php echo $_POST['insurance_id']; ?>" required />
							<?php echo $errors['insurance_id']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="service_period" class="col-sm-2 control-label">Service Period</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="service_period" name="service_period" placeholder="Service period" value="<?php echo $_POST['service_period']; ?>" />
							<?php echo $errors['service_period']; ?>
						</div>
					</div>

					<div class="form-group">
						<label for="academic_qualifications" class="col-sm-2 control-label">Academic Qualifications</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="academic_qualifications" name="academic_qualifications" placeholder="academic_qualifications" value="<?php echo $_POST['academic_qualifications']; ?>" />
							<?php echo $errors['academic_qualifications']; ?>
						</div>
					</div>


					<div class="form-group">
						<label for="photo" class="col-sm-2 control-label">Photograph</label>
						<div class="col-sm-10">
							<input type="file" class="form-control" id="photo" name="photo" accept="image/*" placeholder="Photograph" required style="height:auto" />
							<?php echo $errors['photo']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="emp_action" class="col-sm-2 control-label">Disciplinary Actions</label>
						<div class="col-sm-5">
							<select class="form-control" id="emp_action" name="emp_action">
								<option value="">Please make a choice</option>
								<option <?php echo $_POST['emp_action']=='Yes' ? 'selected' : ''; ?> value="Yes">Yes</option>
								<option <?php echo $_POST['emp_action']=='No' ? 'selected' : ''; ?> value="No">No</option>
							</select>
							<?php echo $errors['emp_action']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="account_no" class="col-sm-2 control-label">Salary Account No</label>
						<div class="col-sm-5">
							<input type="text" class="form-control" id="account_no" name="account_no" placeholder="enter employee Salary Account No" value="<?php echo $_POST['account_no']; ?>" required />
							<?php echo $errors['account_no']; ?>
						</div>
					</div>
					<div class="form-group">
						<label for="etin_no" class="col-sm-2 control-label">ETIN</label>
						<div class="col-sm-5">
							<input type="text" class="form-control" id="etin_no" name="etin_no" placeholder="enter ETIN No" value="<?php echo $_POST['etin_no']; ?>" required />
							<?php echo $errors['etin_no']; ?>
						</div>
					</div>

					<div class="form-group">
						<label for="password" class="col-sm-2 control-label">Password</label>
						<div class="col-sm-4">
							<input type="password" class="form-control" id="emp_password" name="emp_password" placeholder="Password" value="<?php echo $_POST['emp_password']; ?>" required />
							<?php echo $errors['emp_password']; ?>
						</div>
					</div>
				</div>
				<div class="box-footer">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" class="btn btn-primary" name="submit">Submit</button>
					</div>
				</div>
			</form>
		</div>
	</div>

	<script src="<?php echo BASE_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
	<script src="<?php echo BASE_URL; ?>bootstrap/js/bootstrap.min.js"></script>
	<script src="<?php echo BASE_URL; ?>plugins/datepicker/bootstrap-datepicker.js"></script>
	<script type="text/javascript">
	$('#dob, #joining_date, #confirmation_date, #resign_date').datepicker();
	</script>
</body>
</html>
