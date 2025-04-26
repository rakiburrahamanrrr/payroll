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

	<title>Salaries - Payroll</title>

	<link rel="stylesheet" href="<?php echo BASE_URL; ?>bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/datatables/dataTables.bootstrap.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/datatables/jquery.dataTables_themeroller.css">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>dist/css/AdminLTE.css">
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
				<h1>Salaries</h1>
				<ol class="breadcrumb">
					<li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
					<li class="active">Salary Payscale Grade</li>
				</ol>
			</section>

			<section class="content">
				<div class="row">
        			<div class="col-xs-12">
						<div class="box">
							<div class="box-header">
								<h3 class="box-title">Pay Scale Grade</h3>
								<?php if ( $_SESSION['Login_Type'] == 'admin' ) { ?>
									<button type="button" class="btn btn-success btn-sm pull-right" id="add-payscale-btn" style="margin-top: -25px;">Add Payscale Grade</button>
								<?php } ?>
							</div>
							<div class="box-body">
								<?php if ( $_SESSION['Login_Type'] == 'admin' ) { ?>
									<table id="admin-payscale-grade" class="table table-bordered table-stripe" style="margin-top: 30px;">
										<thead>
											<tr>
												<th>Id</th>
												<th>Employee Grade</th>
												<th>Employee Salary Grade</th>
												<th>Basic Salary</th>
												<th>House Rent</th>
												<th>Conveyance Allowance</th>
												<th>Medical Allowance</th>
												<th>Driver Allowance</th>
												<th>Car Allowance</th>
												<th>Actions</th>
											</tr>
										</thead>
									</table>
								<?php } else { ?>
									<table id="admin-payscale-grade" class="table table-bordered table-stripe">
										<thead>
											<tr>
												<th>ACTIONS</th>
											</tr>
										</thead>
									</table>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>

				<!-- Payscale Grade Edit Modal -->
				<div class="modal fade" id="EditPayscaleModal" tabindex="-1" role="dialog" aria-labelledby="EditPayscaleModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<form id="edit-payscale-form">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="EditPayscaleModalLabel">Edit Payscale Grade</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<input type="hidden" id="payscale_id" name="id" />
									<div class="form-group">
										<label for="emp_grade">Employee Grade</label>
										<input type="text" class="form-control" id="emp_grade" name="emp_grade" required />
									</div>
									<div class="form-group">
										<label for="empsal_grade">Employee Salary Grade</label>
										<input type="text" class="form-control" id="empsal_grade" name="empsal_grade" required />
									</div>
									<div class="form-group">
										<label for="basic_salary">Basic Salary</label>
										<input type="number" step="0.01" class="form-control" id="basic_salary" name="basic_salary" required />
									</div>
									<div class="form-group">
										<label for="house_rent">House Rent</label>
										<input type="number" step="0.01" class="form-control" id="house_rent" name="house_rent" required />
									</div>
									<div class="form-group">
										<label for="conveyance_allowance">Conveyance Allowance</label>
										<input type="number" step="0.01" class="form-control" id="conveyance_allowance" name="conveyance_allowance" required />
									</div>
									<div class="form-group">
										<label for="medical_allowance">Medical Allowance</label>
										<input type="number" step="0.01" class="form-control" id="medical_allowance" name="medical_allowance" required />
									</div>
									<div class="form-group">
										<label for="driver_allowance">Driver Allowance</label>
										<input type="number" step="0.01" class="form-control" id="driver_allowance" name="driver_allowance" required />
									</div>
									<div class="form-group">
										<label for="car_allowance">Car Allowance</label>
										<input type="number" step="0.01" class="form-control" id="car_allowance" name="car_allowance" required />
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
									<button type="submit" class="btn btn-primary">Save changes</button>
								</div>
							</div>
						</form>
					</div>
				</div>

				<!-- Payscale Grade Add Modal -->
				<div class="modal fade" id="AddPayscaleModal" tabindex="-1" role="dialog" aria-labelledby="AddPayscaleModalLabel" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<form id="add-payscale-form">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="AddPayscaleModalLabel">Add Payscale Grade</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<div class="form-group">
										<label for="emp_grade_add">Employee Grade</label>
										<input type="text" class="form-control" id="emp_grade_add" name="emp_grade" required />
									</div>
									<div class="form-group">
										<label for="empsal_grade_add">Employee Salary Grade</label>
										<input type="text" class="form-control" id="empsal_grade_add" name="empsal_grade" required />
									</div>
									<div class="form-group">
										<label for="basic_salary_add">Basic Salary</label>
										<input type="number" step="0.01" class="form-control" id="basic_salary_add" name="basic_salary" required />
									</div>
									<div class="form-group">
										<label for="house_rent_add">House Rent</label>
										<input type="number" step="0.01" class="form-control" id="house_rent_add" name="house_rent" required />
									</div>
									<div class="form-group">
										<label for="conveyance_allowance_add">Conveyance Allowance</label>
										<input type="number" step="0.01" class="form-control" id="conveyance_allowance_add" name="conveyance_allowance" required />
									</div>
									<div class="form-group">
										<label for="medical_allowance_add">Medical Allowance</label>
										<input type="number" step="0.01" class="form-control" id="medical_allowance_add" name="medical_allowance" required />
									</div>
									<div class="form-group">
										<label for="driver_allowance_add">Driver Allowance</label>
										<input type="number" step="0.01" class="form-control" id="driver_allowance_add" name="driver_allowance" required />
									</div>
									<div class="form-group">
										<label for="car_allowance_add">Car Allowance</label>
										<input type="number" step="0.01" class="form-control" id="car_allowance_add" name="car_allowance" required />
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
									<button type="submit" class="btn btn-primary">Add Payscale Grade</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</section>
		</div>

		<footer class="main-footer">
		<strong> &copy; CDBL Payroll Management System | </strong> Developed By CDBL VAS Team 2025
		</footer>
	</div>

	<script src="<?php echo BASE_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
	<script src="<?php echo BASE_URL; ?>bootstrap/js/bootstrap.min.js"></script>
	<script src="<?php echo BASE_URL; ?>plugins/datatables/jquery.dataTables.min.js"></script>
	<script src="<?php echo BASE_URL; ?>plugins/datatables/dataTables.bootstrap.min.js"></script>
	<script src="<?php echo BASE_URL; ?>plugins/jquery-validator/validator.min.js"></script>
	<script src="<?php echo BASE_URL; ?>plugins/bootstrap-notify/bootstrap-notify.min.js"></script>
	<script src="<?php echo BASE_URL; ?>dist/js/app.min.js"></script>
	<script type="text/javascript">var baseurl = '<?php echo BASE_URL; ?>';</script>
	<script src="<?php echo BASE_URL; ?>dist/js/script.js?rand=<?php echo rand(); ?>"></script>
</body>
</html>