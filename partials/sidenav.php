<?php
$page_name = basename($_SERVER["SCRIPT_FILENAME"], '.php');

global $db, $userData;

$attendanceROW = 0;
$action_name = 'Start';

if (isset($userData['emp_code'])) {
    $attendanceSQL = mysqli_query($db, "SELECT * FROM `" . DB_PREFIX . "attendance` WHERE `emp_code` = '" . $userData['emp_code'] . "' AND `attendance_date` = '" . date('Y-m-d') . "'");
    if ($attendanceSQL) {
        $attendanceROW = mysqli_num_rows($attendanceSQL);
        if ($attendanceROW == 0) {
            $action_name = 'Start';
        } else {
            $attendanceDATA = mysqli_fetch_assoc($attendanceSQL);
            if ($attendanceDATA['action_name'] == 'punchin') {
                $action_name = 'stop';
            } else {
                $action_name = 'Start';
            }
        }
    }
}
?>

<aside class="main-sidebar">
	<section class="sidebar">
		<div class="user-panel">
			<div class="pull-left image">
				<?php if ( isset($_SESSION['Login_Type']) && $_SESSION['Login_Type'] == 'admin' ) { ?>
					<img src="<?php echo BASE_URL; ?>dist/img/admin.png" class="img-circle" alt="User Image">
				<?php } else { ?>
					<img src="<?php echo REG_URL; ?>photos/<?php echo isset($userData['photo']) ? $userData['photo'] : ''; ?>" class="img-circle" alt="User Image">
				<?php } ?>
			</div>
			<div class="pull-left info">
				<?php if ( isset($_SESSION['Login_Type']) && $_SESSION['Login_Type'] == 'admin' ) { ?>
					<p><?php echo isset($userData['admin_name']) ? $userData['admin_name'] : ''; ?></p>
				<?php } else { ?>
					<p><?php echo isset($userData['first_name']) ? $userData['first_name'] : ''; ?> <?php echo isset($userData['last_name']) ? $userData['last_name'] : ''; ?></p>
				<?php } ?>
				<a href="#"><i class="fa fa-circle text-success"></i> Online</a>
			</div>
		</div>
		<?php if ( isset($_SESSION['Login_Type']) && $_SESSION['Login_Type'] != 'admin' ) { ?>
			<?php if ( $attendanceROW < 2 ) { ?>
				<form method="POST" class="employee sidebar-form" role="form" id="attendance-form">
	                <div class="input-group">
	                    <input type="text" class="form-control" id="desc" name="desc" placeholder="Comment (if any)" />
	                    <span class="input-group-btn">
	                    	<button type="submit" id="action_btn" class="btn btn-warning"><?php echo $action_name; ?></button>
	                    </span>
	                </div>
	            </form>
	        <?php } ?>
	    <?php } ?>

		<ul class="sidebar-menu">
			<li class="header">NAVIGATION</li>
<?php if ( $_SESSION['Login_Type'] == 'admin' ) { ?>
<!--<li class="<?php echo $page_name == "attendance" ? 'active' : ''; ?>">
	<a href="<?php echo BASE_URL; ?>attendance/">
		<i class="fa fa-calendar"></i> <span>Project Task Record</span>
	</a>
</li>-->
				<li class="<?php echo $page_name == "salary_structure" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>salary_structure/">
						<i class="fa fa-money"></i> <span>Pay Structure</span>
					</a>
				</li>
				<li class="<?php echo $page_name == "payheads" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>payheads/">
						<i class="fa fa-gratipay"></i> Pay Heads
					</a>
				</li>
				<li class="<?php echo $page_name == "employees" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>employees/">
						<i class="fa fa-users"></i> <span>Employees Section</span>
					</a>
				</li>
				<li class="<?php echo $page_name == "loan_slip_assign" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>loan_slip_assign.php">
						<i class="fa fa-file-text"></i> <span>Loan Process</span>
					</a>
				</li>
				<li class="<?php echo $page_name == "monthly_salary" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>monthly_salary.php">
						<i class="fa fa-briefcase"></i> <span>Payslip Process</span>
					</a>
				</li>
				<li class="<?php echo $page_name == "salaries" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>salaries/">
						<i class="fa fa-money"></i> <span>Payslips View</span>
					</a>
				</li>
				<li class="<?php echo $page_name == "admin_loan_approval" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>admin_loan_approval.php">
						<i class="fa fa-money"></i> <span>Loan Approval</span>
					</a>
				</li>
				
				<li class="<?php echo $page_name == "leaves" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>leaves/">
						<i class="fa fa-sign-out"></i> <span>Leave Management</span>
					</a>
				</li>
				
							
				<li class="<?php echo $page_name == "pfund" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>ajax/pfund.php">
						<i class="fa fa-gratipay"></i> Provident Fund
					</a>
				</li>
				<li class="<?php echo $page_name == "holidays" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>holidays/">
						<i class="fa fa-calendar-check-o"></i> <span>List Holidays</span>
					</a>
				</li>
			<?php } else { ?>
				<li class="<?php echo $page_name == "salaries" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>salaries/">
						<i class="fa fa-money"></i> <span>Pay Slips</span>
					</a>
				</li>
				
				<li class="<?php echo $page_name == "loan_request" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>loan_request.php">
						<i class="fa fa-money"></i> <span>Loan Request</span>
					</a>
				</li>
				<li class="<?php echo $page_name == "loan_balance" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>loan_balance.php">
						<i class="fa fa-money"></i> <span>Loan Balance</span>
					</a>
				</li>
				<li class="<?php echo $page_name == "leaves" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>leaves/">
						<i class="fa fa-sign-out"></i> <span>Leave Manage</span>
					</a>
				</li>
				
				<li class="<?php echo $page_name == "holidays" ? 'active' : ''; ?>">
					<a href="<?php echo BASE_URL; ?>holidays/">
						<i class="fa fa-calendar-check-o"></i> <span>Holidays</span>
					</a>
				</li>
			<?php } ?>
		</ul>
	</section>
</aside>
