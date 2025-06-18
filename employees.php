<?php 
require_once(dirname(__FILE__) . '/config.php'); 
if (!isset($_SESSION['Admin_ID']) || $_SESSION['Login_Type'] != 'admin') {
    header('location:' . BASE_URL);
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <title>Employees - Payroll</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/datatables/dataTables.bootstrap.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>plugins/datatables/jquery.dataTables_themeroller.css">
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
      <h1>Employees</h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Employees</li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">All Employee List</h3>
<a href="../registration/newreg.php" class="btn btn-primary btn-sm pull-right" >+ Create Employee</a>&nbsp;
<a href="#" class="btn btn-primary btn-sm pull-right" id="bulkEmployeeEntryBtn">++ Bulk Employee Upload</a>             

<!-- Bulk Employee Entry Modal -->
<div class="modal fade" id="bulkEmployeeEntryModal" tabindex="-1" role="dialog" aria-labelledby="bulkEmployeeEntryModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="bulkEmployeeUploadForm" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="bulkEmployeeEntryModalLabel">Bulk Employee Upload</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="employeeExcelFile">Upload Excel File</label>
            <input type="file" class="form-control-file" id="employeeExcelFile" name="employeeExcelFile" accept=".xls,.xlsx" required>
          </div>
          <div id="bulkUploadMessage" class="alert" style="display:none;"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Upload</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Open modal on button click
  document.getElementById('bulkEmployeeEntryBtn').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('bulkUploadMessage').style.display = 'none';
    document.getElementById('employeeExcelFile').value = '';
    $('#bulkEmployeeEntryModal').modal('show');
  });

  // Handle form submission
  document.getElementById('bulkEmployeeUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var bulkUploadMessage = document.getElementById('bulkUploadMessage');
    bulkUploadMessage.classList.remove('alert-success', 'alert-danger');
    bulkUploadMessage.style.display = 'none';

    fetch(baseurl + 'ajax/?case=BulkEmployeeUpload', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(response => {
      if (response.code === 0) {
        bulkUploadMessage.classList.add('alert-success');
        bulkUploadMessage.textContent = response.result;
        bulkUploadMessage.style.display = 'block';
        // Optionally reload or update employee list here
      } else {
        bulkUploadMessage.classList.add('alert-danger');
        bulkUploadMessage.textContent = response.result;
        bulkUploadMessage.style.display = 'block';
      }
    })
    .catch(error => {
      bulkUploadMessage.classList.add('alert-danger');
      bulkUploadMessage.textContent = 'Upload failed: ' + error;
      bulkUploadMessage.style.display = 'block';
    });
  });
});
</script>
</div>
            <div class="box-body">
              <div class="table-responsiove">
                <table id="employees" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>EMPLOYEE CODE</th>
                      <th>IMAGE</th>
                      <th>NAME</th>
                      <th>DOB</th>
                      <th>EMAIL</th>
                      <th>CONTACT</th>
                      <th>Employee Grade</th>
                      <th>Employee Salary Stage</th>
                      <th>JOINING</th>
                      <th>BLOOD</th>
                      <th>EMP TYPE</th>
                      <th width="6%">ACTION</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div><!-- /.box-body -->
          </div><!-- /.box -->
        </div>
      </div>
    </section>
  </div>

  <!-- Salary Month Modal -->
  <div class="modal fade in" id="SalaryMonthModal" tabindex="-1" data-empcode="">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Select Month for Salary</h4>
        </div>
        <div class="modal-body">
          <div class="row">
          <?php 
            $months = array(); 
            $years = array();
            $before2Month = (int)date('m') - 2;
            for ($i = $before2Month; $i < $before2Month + 16; $i++) {
                $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
                $years[$i] = date('Y', mktime(0, 0, 0, $i, 1));
            }
            foreach ($months as $key => $month) { ?>
              <div class="col-sm-3 <?php echo ($month == date('F') && $years[$key] == date('Y')) ? 'bg-danger' : ''; ?>">
              <a href="#" class="salary-month-link" data-month="<?php echo $month; ?>" data-year="<?php echo $years[$key]; ?>">
                <?php echo strtoupper($month); ?><br /><?php echo $years[$key]; ?>
                </a>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Manage Payheads Modal (unchanged) -->
  <div class="modal fade in" id="ManageModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title text-center">Add Payheads to Employee</h4>
        </div>
        <form method="post" role="form" data-toggle="validator" id="assign-payhead-form">
          <div class="modal-body">
            <div class="row">
              <div class="col-sm-4">
                <label for="all_payheads">List of Pay Heads</label>
                <button type="button" id="selectHeads" class="btn btn-success btn-xs pull-right">
                  <i class="fa fa-arrow-circle-right"></i>
                </button>
                <select class="form-control" id="all_payheads" name="all_payheads[]" multiple size="10"></select>
              </div>
              <div class="col-sm-4">
                <label for="selected_payheads">Selected Pay Heads</label>
                <button type="button" id="removeHeads" class="btn btn-danger btn-xs pull-right">
                  <i class="fa fa-arrow-circle-left"></i>
                </button>
                <select class="form-control" id="selected_payheads" name="selected_payheads[]" data-error="Pay Heads is required" multiple size="10" required></select>
              </div>
              <div class="col-sm-4">
                <label for="selected_payamount">Enter Payhead Amount</label>
                <div id="selected_payamount"></div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <input type="hidden" name="empcode" id="empcode" />
            <input type="hidden" name="emp_grade" id="emp_grade_hidden" />
            <input type="hidden" name="empsal_grade" id="empsal_grade_hidden" />
            <button type="submit" name="submit" class="btn btn-primary">Add Pay Heads to Employee</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Employee Modal -->
  <div class="modal fade in" id="EditEmpModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title">Update Employee Details</h4>
        </div>
        <form method="post" role="form" data-toggle="validator" id="edit-emp-form">
          <div class="modal-body">
            <div class="form-group">
              <div class="row">
                <!-- Display Employee ID (hidden) and Employee Code (read-only) -->
                <div class="col-sm-4">
                  <label for="employee_code_display">Employee Code</label>
                  <div class="form-control" id="employee_code_display" name="employee_code_display"></div>
                </div>
                <div class="col-sm-4">
                  <label for="first_name">First Name</label>
                  <input type="text" class="form-control" name="first_name" id="first_name" required />
                </div>
                <div class="col-sm-4">
                  <label for="last_name">Last Name</label>
                  <input type="text" class="form-control" name="last_name" id="last_name" required />
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <div class="row">
                <div class="col-sm-4">
                  <label for="dob">Emp. DOB (MM/DD/YYYY)</label>
                  <input type="text" class="form-control datepicker" name="dob" id="dob" required />
                </div>
                <div class="col-sm-4">
                  <label for="gender">Gender</label>
                  <select class="form-control" name="gender" id="gender" required>
                    <option value="">Please make a choice</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                  </select>
                </div>
                <div class="col-sm-4">
                  <label for="marital_status">Marital Status</label>
                  <input type="text" class="form-control" name="marital_status" id="marital_status" required />
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <div class="row">
                <div class="col-sm-4">
                  <label for="blood_group">Blood Group</label>
                  <input type="text" class="form-control" name="blood_group" id="blood_group" required />
                </div>
                <div class="col-sm-8">
                  <label for="address">Address</label>
                  <textarea class="form-control" name="address" id="address" required></textarea>
                </div>
                <div class="col-sm-12">
                  <label for="paraddress">Permanent Address</label>
                  <textarea class="form-control" id="paraddress" name="paraddress" placeholder="Permanent Address" required></textarea>
                </div>
              </div>
            
              <div class="row">
                <div class="col-sm-4">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" name="email" id="email" required />
                </div>
                <div class="col-sm-4">
                  <label for="mobile">Mobile</label>
                  <input type="text" class="form-control" name="mobile" id="mobile" required />
                </div>
                <div class="col-sm-4">
                  <label for="telephone">Emergency Contact</label>
                  <input type="text" class="form-control" id="telephone" name="telephone" placeholder="Emergency Contact No" />
                </div>
              </div>
            
              <div class="row">
                <div class="col-sm-4">
                  <label for="national_id">National ID</label>
                  <input type="text" class="form-control" name="national_id" id="national_id" required />
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <div class="row">
        <!-- Hidden field for emp_code -->
        <input type="hidden" name="emp_code" id="emp_code" />
                <div class="col-sm-4">
                  <label for="employment_type">Emp. Type</label>
                  <select class="form-control" id="employment_type" name="employment_type">
                    <option value="">Please make a choice</option>
                    <option value="Provision">Probation</option>
                    <option value="Contractual">Contractual</option>
                    <option value="Permanent">Permanent</option>
                  </select>
                </div>
                <div class="col-sm-4">
                  <label for="employment_status">Emp. Status</label>
                  <select class="form-control" id="employment_status" name="employment_status">
                    <option value="">Please make a choice</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                  </select>
                </div>
              </div>                                    
            </div>
            <div class="form-group">
              <div class="row">
                <div class="col-sm-4">
                  <label for="designation">Designation</label>
                  <input type="text" class="form-control" name="designation" id="designation" required />
                </div>
                <div class="col-sm-4">
                  <label for="department">Department</label>
                  <input type="text" class="form-control" name="department" id="department" required />
                </div>
                <div class="col-sm-4">
                  <label for="emp_grade">Employee Grade</label>
                  <input type="text" class="form-control" id="emp_grade" name="emp_grade" placeholder="Employee Grade" required />
                </div>
              </div>
              <div class="row">
                <div class="col-sm-4">
                  <label for="empsal_grade">Employee Salary Stage</label>
                  <input type="text" class="form-control" id="empsal_grade" name="empsal_grade" placeholder="Employee Salary Stage" required />
                </div>
                <div class="col-sm-4">
                  <label for="joining_date">Joining Date</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="joining_date" name="joining_date" placeholder="MM/DD/YYYY" required />
                    <span class="input-group-addon">
                      <i class="glyphicon glyphicon-calendar"></i>
                    </span>
                  </div>
                </div>
                <div class="col-sm-4">
                  <label for="confirmation_date">Confirmation Date</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="confirmation_date" name="confirmation_date" placeholder="MM/DD/YYYY" required />
                    <span class="input-group-addon">
                      <i class="glyphicon glyphicon-calendar"></i>
                    </span>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-4">
                  <label for="resign_date">Resignation Date</label>
                  <div class="input-group">
                    <input type="text" class="form-control" id="resign_date" name="resign_date" placeholder="MM/DD/YYYY" />
                    <span class="input-group-addon">
                      <i class="glyphicon glyphicon-calendar"></i>
                    </span>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-sm-4">
                  <label for="account_no">Bank A/C No.</label>
                  <input type="text" class="form-control" name="account_no" id="account_no" required />
                </div>
                <div class="col-sm-4">
                  <label for="etin_no">E TIN</label>
                  <input type="text" class="form-control" id="etin_no" name="etin_no" placeholder="Enter ETIN No" required />
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="submit" class="btn btn-primary">Update Employee</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <footer class="main-footer">
    <strong>&copy; CDBL Payroll Management System | </strong> Developed By CDBL VAS Team 2025
  </footer>
</div>

<!-- JavaScript Files -->
<script src="<?php echo BASE_URL; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="<?php echo BASE_URL; ?>bootstrap/js/bootstrap.min.js"></script>
<script src="<?php echo BASE_URL; ?>plugins/jquery-validator/validator.min.js"></script>
<script src="<?php echo BASE_URL; ?>plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>plugins/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?php echo BASE_URL; ?>plugins/bootstrap-notify/bootstrap-notify.min.js"></script>
<script src="<?php echo BASE_URL; ?>plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="<?php echo BASE_URL; ?>dist/js/app.min.js"></script>
<script type="text/javascript">var baseurl = '<?php echo BASE_URL; ?>';</script>
<script src="<?php echo BASE_URL; ?>dist/js/script.js?rand=<?php echo rand(); ?>"></script>

<!-- Inline Script for Edit Operation -->
<script>
$(document).on('click', '.edit-employee', function(e) {
    e.preventDefault();
    // Retrieve the employee_id from the button using .attr()
    var employeeId = $(this).attr('data-employee_id');
    console.log("Employee ID from Edit button: " + employeeId);
    if (!employeeId) {
        alert('Employee ID not found!');
        return;
    }
    // Set the hidden field (used for subsequent updates) and show a temporary message in the display field
    $('#emp_code').val(employeeId);
    $('#employee_code_display').text('Loading...');
    $('#employee_id_display').val(employeeId);
    
    
    // Send AJAX request to fetch employee details
    $.ajax({
        url: baseurl + 'ajax/?case=GetEmployeeByID',
        type: 'POST',
        data: { emp_code: employeeId },
        dataType: 'json',
        success: function(resp) {
            console.log("GetEmployeeByID response:", resp);
            if (resp.code === 0) {
                var data = resp.result;
                // Update the displayed employee code (emp_code is used for display)
                $('#employee_code_display').val(data.emp_code);
                // Populate the modal fields with data returned from the server
                $('#first_name').val(data.first_name);
                $('#last_name').val(data.last_name);
                $('#dob').val(data.dob);
                $('#gender').val(data.gender);
                $('#marital_status').val(data.marital_status);
                $('#blood_group').val(data.blood_group);
                $('#address').val(data.address);
                $('#paraddress').val(data.paraddress);
                $('#email').val(data.email);
                $('#mobile').val(data.mobile);
                $('#telephone').val(data.telephone);
                $('#national_id').val(data.national_id);
                $('#employment_type').val(data.employment_type);
                $('#employment_status').val(data.employment_status);
                $('#designation').val(data.designation);
                $('#department').val(data.department);
                $('#emp_grade').val(data.emp_grade);
                $('#empsal_grade').val(data.empsal_grade);
                $('#joining_date').val(data.joining_date);
                $('#confirmation_date').val(data.confirmation_date);
                $('#resign_date').val(data.resign_date);
                $('#account_no').val(data.account_no);
                $('#etin_no').val(data.etin_no);
            } else {
                alert("Error: " + resp.result);
            }
        },
        error: function(xhr, status, error) {
            console.log("AJAX error: " + error);
        }
    });
    // Finally, show the Edit Employee Modal
    $('#EditEmpModal').modal('show');
});
</script>

</body>
</html>
