<?php require_once(dirname(__FILE__) . '/config.php');
if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] != 'admin') {
	header('location:' . BASE_URL);
}
// if ( !isset($_GET['emp_code']) || empty($_GET['emp_code']) || !isset($_GET['month']) || empty($_GET['month']) || !isset($_GET['year']) || empty($_GET['year']) ) {
// 	header('location:' . BASE_URL);
// }
$path = $_SERVER['REQUEST_URI'];
$segments = explode('/', trim($path, '/'));
$emp_id     = $segments[2] ?? '';  // 89
$emp_month     = $segments[3] ?? '';  // 89
$emp_year    = $segments[4] ?? '';  // 89


$empData = GetEmployeeDataByEmpCode($emp_id);
$month = $emp_month . ', ' . $emp_year;
$empLeave = GetEmployeeLWPDataByEmpCodeAndMonth($emp_id, $month);
$flag = 0;
$totalEarnings = 0;
$totalDeductions = 0;
$checkSalarySQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "salaries` WHERE `emp_code` = '" . $empData['emp_code'] . "' AND `pay_month` = '$month'");

if ($checkSalarySQL) {
	$checkSalaryROW = mysqli_num_rows($checkSalarySQL);
	if ($checkSalaryROW > 0) {
		$flag = 1;
		$empSalary = GetEmployeeSalaryByEmpCodeAndMonth($empData['emp_code'], $month);
	} else {
		$empHeads = GetEmployeePayheadsByEmpCode($empData['emp_code']);
	}
}
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

	<title>Salary for <?php echo $month; ?> - Payroll</title>

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
				<h1>Salary for <?php echo $month; ?></h1>
				<ol class="breadcrumb">
					<li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
					<li class="active">Salary for <?php echo $month; ?></li>
				</ol>
			</section>

			<section class="content">
				<div class="row">
					<div class="col-xs-12">
						<div class="box">
							<div class="box-body">
								<?php if ($flag == 0) { ?>
									<form method="POST" role="form" id="payslip-form">
										<input type="hidden" name="emp_code" id="emp_code" value="<?php echo $empData['emp_code']; ?>" />
										<input type="hidden" name="pay_month" value="<?php echo $month; ?>" />
										<div class="table-responsive">
											<table class="table table-bordered">
												<tr>
													<td width="20%">Employee ID</td>
													<td width="30%"><?php echo strtoupper($empData['employee_id']); ?></td>
												</tr>
												<tr>
													<td>Employee Name</td>
													<td><?php echo ucwords($empData['first_name'] . ' ' . $empData['last_name']); ?></td>
													<td>Bank Account</td>
													<td><?php echo $empData['account_no']; ?></td>
												</tr>
												<tr>
													<td>Designation</td>
													<td><?php echo ucwords($empData['designation']); ?></td>

												</tr>
												<tr>
													<td>Gender</td>
													<td><?php echo ucwords($empData['gender']); ?></td>
												</tr>

												<tr>
													<td>Department</td>
													<td><?php echo ucwords($empData['department']); ?></td>
													<td>Payable/Working Days</td>
													<td><?php echo ($empLeave['workingDays'] - $empLeave['withoutPay']); ?>/<?php echo $empLeave['workingDays']; ?> Days</td>
												</tr>
												<tr>
													<td>Date of Joining</td>
													<td><?php echo date('d-m-Y', strtotime($empData['joining_date'])); ?></td>
													<td>Taken/Remaining Leaves</td>
													<td><?php echo $empLeave['payLeaves']; ?>/<?php echo ((isset($empLeave['totalLeaves']) ? $empLeave['totalLeaves'] : 0) - $empLeave['payLeaves']); ?> Days</td>
												</tr>
											</table>
											<table class="table table-bordered">
												<thead>
													<tr>
														<th width="35%">Earnings</th>
														<th width="15%" class="text-right">Amount (BDT.)</th>
														<th width="35%">Deductions</th>
														<th width="15%" class="text-right">Amount (BDT.)</th>
													</tr>
												</thead>
												<tbody>
													<?php if (!empty($empHeads)) { ?>
														<tr>
															<td colspan="2" style="padding:0">
																<table class="table table-bordered table-striped" style="margin:0">
																	<?php foreach ($empHeads as $head) { ?>
																		<?php if ($head['payhead_type'] == 'earnings') { ?>
																			<?php $totalEarnings += $head['default_salary']; ?>
																			<tr>
																				<td width="70%">
																					<?php echo $head['payhead_name']; ?>
																				</td>
																				<td width="30%" class="text-right">
																					<input type="hidden" name="earnings_heads[]" value="<?php echo $head['payhead_name']; ?>" />
																					<input type="text" name="earnings_amounts[]" value="<?php echo number_format($head['default_salary'], 2, '.', ''); ?>" class="form-control text-right" data-payhead-name="<?php echo $head['payhead_name']; ?>" />
																				</td>
																			</tr>
																		<?php } ?>
																	<?php } ?>
																</table>
															</td>
															<td colspan="2" style="padding:0">
																<table class="table table-bordered table-striped" style="margin:0">
																	<?php foreach ($empHeads as $head) { ?>
																		<?php if ($head['payhead_type'] == 'deductions') { ?>
																			<?php $totalDeductions += $head['default_salary']; ?>
																			<tr>
																				<td width="70%">
																					<?php echo $head['payhead_name']; ?>
																				</td>
																				<td width="30%" class="text-right">
																					<input type="hidden" name="deductions_heads[]" value="<?php echo $head['payhead_name']; ?>" />
																					<input type="text" name="deductions_amounts[]" value="<?php echo number_format($head['default_salary'], 2, '.', ''); ?>" class="form-control text-right" data-payhead-name="<?php echo $head['payhead_name']; ?>" />
																				</td>
																			</tr>
																		<?php } ?>
																	<?php } ?>
																</table>
															</td>
														</tr>
													<?php } else { ?>
														<tr>
															<td colspan="4">No payheads are assigned for this employee</td>
														</tr>
													<?php } ?>
												</tbody>
												<tfoot>
													<tr>
														<td><strong>Gross Salary</strong></td>
														<td class="text-right">
															<strong id="totalEarnings">
																<?php echo number_format($totalEarnings, 2, '.', ''); ?>
															</strong>
														</td>
														<td><strong>Total Deductions</strong></td>
														<td class="text-right">
															<strong id="totalDeductions">
																<?php echo number_format($totalDeductions, 2, '.', ''); ?>
															</strong>
														</td>
													</tr>
												</tfoot>
											</table>
										</div>
										<div class="row">
											<div class="col-sm-6">
												<h3 class="text-success" style="margin-top:0">
													Net Salary Payable:
													<span id="netSalary"><?php echo number_format(($totalEarnings - $totalDeductions), 2, '.', ''); ?></span>
												</h3>
											</div>
											<div class="col-sm-6 text-right">
												<?php if (!empty($empHeads)) { ?>
													<button type="submit" class="btn btn-info">
														<i class="fa fa-plus"></i> Generate PaySlip
													</button>
												<?php } ?>
											</div>
										</div>
									</form>
								<?php } else { ?>
									<div class="table-responsive">
										<table class="table table-bordered">
											<thead>
												<tr>
													<th width="35%">Earnings</th>
													<th width="15%" class="text-right">Amount (Bdt.)</th>
													<th width="35%">Deductions</th>
													<th width="15%" class="text-right">Amount (Bdt.)</th>
												</tr>
											</thead>
											<tbody>
<?php if (!empty($empSalary)) { ?>
													<tr>
														<td colspan="2" style="padding:0">
															<table class="table table-bordered table-striped" style="margin:0">
																<?php foreach ($empSalary as $salary) { ?>
																	<?php if (isset($salary['pay_type']) && $salary['pay_type'] == 'earnings') { ?>
																		<?php $totalEarnings += $salary['pay_amount']; ?>
																		<tr>
																			<td width="70%">
																				<?php echo $salary['payhead_name']; ?>
																			</td>
																			<td width="30%" class="text-right">
																				<?php echo number_format($salary['pay_amount'], 2, '.', ','); ?>
																			</td>
																		</tr>
																	<?php } ?>
																<?php } ?>
															</table>
														</td>
														<td colspan="2" style="padding:0">
															<table class="table table-bordered table-striped" style="margin:0">
																<?php foreach ($empSalary as $salary) { ?>
																	<?php if (isset($salary['pay_type']) && $salary['pay_type'] == 'deductions') { ?>
																		<?php $totalDeductions += $salary['pay_amount']; ?>
																		<tr>
																			<td width="70%">
																				<?php echo $salary['payhead_name']; ?>
																			</td>
																			<td width="30%" class="text-right">
																				<?php echo number_format($salary['pay_amount'], 2, '.', ','); ?>
																			</td>
																		</tr>
																	<?php } ?>
																<?php } ?>
															</table>
														</td>
													</tr>
												<?php } else { ?>
													<tr>
														<td colspan="4">No payheads are assigned for this employee</td>
													</tr>
												<?php } ?>
											</tbody>
											<tfoot>
												<tr>
													<td><strong>Total Earnings</strong></td>
													<td class="text-right">
														<strong id="totalEarnings">
															<?php echo number_format($totalEarnings, 2, '.', ','); ?>
														</strong>
													</td>
													<td><strong>Total Deductions</strong></td>
													<td class="text-right">
														<strong id="totalDeductions">
															<?php echo number_format($totalDeductions, 2, '.', ','); ?>
														</strong>
													</td>
												</tr>
											</tfoot>
										</table>
									</div>
									<div class="row">
										<div class="col-sm-6">
											<h3 class="text-success" style="margin-top:0">
												Net Salary Payable:
												Bdt.<?php echo number_format(($totalEarnings - $totalDeductions), 2, '.', ','); ?>
												<small>(In words: <?php echo ucfirst(ConvertNumberToWords(($totalEarnings - $totalDeductions))); ?>)</small>
											</h3>
										</div>
										<div class="col-sm-6 text-right">
											<button type="button" class="btn btn-success" onclick="openInNewTab('<?php echo BASE_URL; ?>payslips/<?php echo $empData['employee_id']; ?>/<?php echo str_replace(', ', '-', $month); ?>/<?php echo str_replace(', ', '-', $month); ?>.pdf');">
												<i class="fa fa-download"></i> Show PaySlip (PDF Version)
											</button>
											<button type="button" class="btn btn-info" onclick="sendPaySlipByMail('<?php echo $empData['employee_id']; ?>', '<?php echo $month; ?>');">
												<i class="fa fa-envelope"></i> Send to Employee
											</button>
										</div>
									</div>
								<?php } ?>
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
	<script type="text/javascript">
		var baseurl = '<?php echo BASE_URL; ?>';
	</script>
	<script src="<?php echo BASE_URL; ?>dist/js/script.js?rand=<?php echo rand(); ?>"></script>
	<?php if (isset($_SESSION['PaySlipMsg'])) { ?>
		<script type="text/javascript">
			$.notify({
				icon: 'glyphicon glyphicon-ok-circle',
				message: '<?php echo $_SESSION['PaySlipMsg']; ?>',
			}, 
			{
				allow_dismiss: false,
				type: "success",
				placement: {
					from: "top",
					align: "right"
				},
				z_index: 9999,
			});
		</script>
	<?php } ?>
</body>

</html>
<?php unset($_SESSION['PaySlipMsg']); ?>