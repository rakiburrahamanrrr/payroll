<?php require_once(dirname(__FILE__, 2) . '/config.php'); 
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
		
		<?php require_once(dirname(__FILE__, 2) . '/partials/topnav.php'); ?>

		<?php require_once(dirname(__FILE__, 2) . '/partials/sidenav.php'); ?>

		<div class="content-wrapper">
			<section class="content-header">
				<h1>Provident Fund</h1>
				<ol class="breadcrumb">
					<li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
					<li class="active">Provident Fund</li>
				</ol>
			</section>

			<section class="content">
				<div class="row">
        			<div class="col-xs-12">
						<div class="box">
							<div class="box-header">
								<h3 class="box-title">Provident Fund Calculation</h3>
							</div>
							<div class="box-body">
								<form id="pfund-form" class="form-inline">
									<div class="form-group">
										<label for="employee_id">Employee:</label>
										<select id="employee_id" name="employee_id" class="form-control" required>
											<option value="">Select Employee</option>
											<?php
											// Fetch employees from database
											$sql = "SELECT employee_id, emp_code, first_name, last_name FROM cdbl_employees ORDER BY first_name, last_name";
											$result = $db->query($sql);
											if ($result && $result->num_rows > 0) {
												while ($row = $result->fetch_assoc()) {
													$emp_name = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
													echo "<option value=\"" . intval($row['employee_id']) . "\">" . $emp_name . " (" . htmlspecialchars($row['emp_code']) . ")</option>";
												}
											}
											?>
										</select>
									</div>
									<div class="form-group" style="margin-left: 10px;">
										<label for="month">Month:</label>
										<input type="month" id="month" name="month" class="form-control" required>
									</div>
									<button type="submit" class="btn btn-primary" style="margin-left: 10px;">Calculate PF</button>
								</form>
								<hr>
								<div id="pfund-result" style="display:none;">
									<h4>Provident Fund Dashboard</h4>
									<p><strong>Employee Contribution:</strong> <span id="employee_contribution"></span> BDT</p>
									<p><strong>Company Contribution:</strong> <span id="company_contribution"></span> BDT</p>
									<p><strong>Total Yearly Contribution (Company):</strong> <span id="yearly_contribution"></span> BDT</p>
									<p><strong>Current PF Balance:</strong> <span id="current_balance"></span> BDT</p>
								</div>
								<div id="pfund-error" class="alert alert-danger" style="display:none;"></div>
							</div>
						</div>
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
	<script>
		$(document).ready(function() {
			$('#pfund-form').on('submit', function(e) {
				e.preventDefault();
				$('#pfund-error').hide();
				$('#pfund-result').hide();

				var employee_id = $('#employee_id').val();
				var month = $('#month').val();

				if (!employee_id || !month) {
					$('#pfund-error').text('Please select both employee and month.').show();
					return;
				}

				$.ajax({
					url: baseurl + 'ajax/pfund_process.php',
					type: 'POST',
					dataType: 'json',
					data: {
						employee_id: employee_id,
						month: month
					},
					success: function(response) {
						if (response.success) {
							$('#employee_contribution').text(response.employee_contribution);
							$('#company_contribution').text(response.company_contribution);
							$('#yearly_contribution').text(response.yearly_contribution);
							$('#current_balance').text(response.current_balance);
							$('#pfund-result').show();
						} else if (response.error) {
							$('#pfund-error').text(response.error).show();
						} else {
							$('#pfund-error').text('Unexpected error occurred.').show();
						}
					},
					error: function() {
						$('#pfund-error').text('Failed to communicate with server.').show();
					}
				});
			});
		});
	</script>
</body>
</html>
