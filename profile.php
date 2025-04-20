<?php require_once(dirname(__FILE__) . '/config.php');
if ( !isset($_SESSION['Admin_ID']) || !isset($_SESSION['Login_Type']) ) {
   	header('location:' . BASE_URL);
} ?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

	<title>My Profile - Payroll</title>

	<link rel="stylesheet" href="<?php echo BASE_URL; ?>bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/datepicker/datepicker3.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/skins/_all-skins.min.css">

	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">
	<div class="wrapper">

		<?php require_once(dirname(__FILE__) . '/partials/topnav.php'); ?>

		<?php require_once(dirname(__FILE__) . '/partials/sidenav.php'); ?>

		<div class="content-wrapper">
			<section class="content-header">
				<h1>My Profile</h1>
				<ol class="breadcrumb">
					<li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
					<li class="active">My Profile</li>
				</ol>
			</section>

			<section class="content">
				<div class="row">
					<?php
					if ( $_SESSION['Login_Type'] == 'admin' ) {
						$query = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "admin` WHERE `admin_id` = " . $_SESSION['Admin_ID']);
						if ( $query ) {
							if ( mysqli_num_rows($query) == 1 ) {
								$data = mysqli_fetch_assoc($query); ?>
			        			<div class="col-lg-6">
									<div class="box">
										<div class="box-header">
											<h3 class="box-title">Edit Profile Details</h3>
										</div>
										<div class="box-body">
											<form method="POST" role="form" data-toggle="validator" id="profile-form">
												<div class="form-group">
													<label for="admin_name">Name: </label>
													<input type="text" class="form-control" name="admin_name" id="admin_name" value="<?php echo $data['admin_name']; ?>" required />
												</div>
												<div class="form-group">
													<label for="admin_email">Email: </label>
													<input type="email" class="form-control" name="admin_email" id="admin_email" value="<?php echo $data['admin_email']; ?>" required />
												</div>
												<div class="form-group">
													<button type="submit" class="btn btn-primary">Submit</button>
												</div>
											</form>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="box">
										<div class="box-header">
											<h3 class="box-title">Change Login Details</h3>
										</div>
										<div class="box-body">
											<form method="POST" role="form" data-toggle="validator" id="password-form">
												<div class="form-group">
													<label for="admin_code">Login ID: </label>
													<input type="text" class="form-control" name="admin_code" id="admin_code" value="<?php echo $data['admin_code']; ?>" required />
												</div>
												<div class="row">
													<div class="col-lg-6">
														<div class="form-group">
															<label for="admin_password">Password: </label>
															<input type="password" class="form-control" name="admin_password" id="admin_password" required />
														</div>
													</div>
													<div class="col-lg-6">
														<div class="form-group">
															<label for="admin_password_conf">Confirm Password: </label>
															<input type="password" class="form-control" name="admin_password_conf" id="admin_password_conf" required />
														</div>
													</div>
												</div>
												<div class="form-group">
													<button type="submit" class="btn btn-primary">Submit</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							<?php
							}
						}
					} else {
						$query = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "employees` WHERE `employee_id` = " . $_SESSION['Admin_ID']);
						if ( $query ) {
							if ( mysqli_num_rows($query) == 1 ) {
								$data = mysqli_fetch_assoc($query); ?>
								<div class="col-lg-9">
									<div class="box">
										<div class="box-header">
											<h3 class="box-title">Edit Profile Details</h3>
										</div>
										<div class="box-body">
											<form method="POST" role="form" data-toggle="validator" id="profile-form">
												<div class="row">
													<div class="col-lg-3">
														<div class="form-group">
															<label for="first_name">First Name </label>
															<input type="text" class="form-control" name="first_name" id="first_name" value="<?php echo $data['first_name']; ?>" required />
														</div>
													</div>
													<div class="col-lg-3">
														<div class="form-group">
															<label for="last_name">Last Name </label>
															<input type="text" class="form-control" name="last_name" id="last_name" value="<?php echo $data['last_name']; ?>" required />
														</div>
													</div>
													<div class="col-lg-3">
														<div class="form-group">
															<label for="dob">Date of Birth (MM/DD/YYYY) </label>
															<input type="text" class="form-control datepicker" name="dob" id="dob" value="<?php echo $data['dob']; ?>" required />
														</div>
													</div>
													<div class="col-lg-3">
														<div class="form-group">
															<label for="gender">Gender </label>
															<select class="form-control" name="gender" id="gender" required>
																<option value="">Please make a choice</option>
																<option value="male" <?php echo $data['gender']=='male'?'selected':''; ?>>
																	Male
																</option>
																<option value="female" <?php echo $data['gender']=='female'?'selected':''; ?>>
																	Female
																</option>
															</select>
														</div>
													</div>
													
													
													
													
													<div class="col-lg-3">
														<div class="form-group">
															<label for="marital_status">Marital Status </label>
															<input type="text" class="form-control" name="marital_status" id="marital_status" value="<?php echo $data['marital_status']; ?>" required />
														</div>
													</div>
													
													<div class="col-lg-3">
														<div class="form-group">
															<label for="email">Email </label>
															<input type="email" class="form-control" name="email" id="email" value="<?php echo $data['email']; ?>" required />
														</div>
													</div>
													<div class="col-lg-3">
														<div class="form-group">
															<label for="mobile">Mobile </label>
															<input type="text" class="form-control" name="mobile" id="mobile" value="<?php echo $data['mobile']; ?>" required />
														</div>
													</div>
													<div class="col-lg-3">
														<div class="form-group">
															<label for="telephone">Emergency Contact </label>
															<input type="text" class="form-control" name="telephone" id="telephone" value="<?php echo $data['telephone']; ?>" />
														</div>
													</div>

												
										
												
													<div class="col-lg-3">
														<div class="form-group">
															<label for="blood_group">Blood Group</label>
															<input type="text" class="form-control" name="blood_group" id="blood_group" value="<?php echo $data['blood_group']; ?>" required />
														</div>
													</div>
													<div class="col-lg-3">
														<div class="form-group">
															<label for="designation">Designation</label>
															<input type="text" class="form-control" name="designation" id="designation" value="<?php echo $data['designation']; ?>" required />
														</div>
													</div>
													<div class="col-lg-3">
														<div class="form-group">
															<label for="department">Department</label>
															<input type="text" class="form-control" name="department" id="department" value="<?php echo $data['department']; ?>" required />
														</div>
													</div>
												
													<div class="col-lg-3">
														<div class="form-group">
															<label for="account_no">Bank A/C No.</label>
															<input type="text" class="form-control" name="account_no" id="account_no" value="<?php echo $data['account_no']; ?>" required />
														</div>
													</div>
													
												</div>
												<div class="form-group">
													<button type="submit" class="btn btn-primary">Submit</button>
												</div>
											</form>
										</div>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="box">
										<div class="box-header">
											<h3 class="box-title">Change Password</h3>
										</div>
										<div class="box-body">
											<form method="POST" role="form" data-toggle="validator" id="password-form">
												<div class="form-group">
													<label for="old_password">Existing Password: </label>
													<input type="password" class="form-control" name="old_password" id="old_password" required />
												</div>
												<div class="form-group">
													<label for="new_password">New Password: </label>
													<input type="password" class="form-control" name="new_password" id="new_password" required />
												</div>
												<div class="form-group">
													<label for="password_conf">Confirm Password: </label>
													<input type="password" class="form-control" name="password_conf" id="password_conf" required />
												</div>
												<div class="form-group">
													<button type="submit" class="btn btn-primary">Submit</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							<?php
							}
						}
					} ?>
				</div>
			</section>
		</div>

		<footer class="main-footer">
		<strong> &copy; CDBL Payroll Management System | </strong> Developed By CDBL VAS Team 2025
		</footer>
	</div>

	<script src="<?php echo BASE_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
	<script src="<?php echo BASE_URL; ?>bootstrap/js/bootstrap.min.js"></script>
	<script src="<?php echo BASE_URL; ?>plugins/jquery-validator/validator.min.js"></script>
	<script src="<?php echo BASE_URL; ?>plugins/bootstrap-notify/bootstrap-notify.min.js"></script>
	<script src="<?php echo BASE_URL; ?>plugins/datepicker/bootstrap-datepicker.js"></script>
	<script src="<?php echo BASE_URL; ?>dist/js/app.min.js"></script>
	<script type="text/javascript">var baseurl = '<?php echo BASE_URL; ?>';</script>
	<script src="<?php echo BASE_URL; ?>dist/js/script.js?rand=<?php echo rand(); ?>"></script>
</body>
</html>
