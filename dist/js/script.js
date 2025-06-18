/* Login Form Submit Script Start */
if ( $('#login-form').length > 0 ) {
    $(document).on('submit', '#login-form', function(e) {
        e.preventDefault();
        
        var form = $(this);
        $.ajax({
            type     : "POST",
            dataType : "json",
            async    : true,
            cache    : false,
            url      : baseurl + "ajax/?case=LoginProcessHandler",
            data     : form.serialize(),
            success  : function(result) {
                if ( result.code == 0 ) {
                    window.location.href = result.result;
                } else {
                    $.notify({
                        icon: 'glyphicon glyphicon-remove-circle',
                        message: result.result,
                    },{
                        allow_dismiss: false,
                        type: "danger",
                        placement: {
                            from: "top",
                            align: "right"
                        },
                        z_index: 9999,
                    });
                }
            }
        });
    });
}
/* End of Script */

/* Attendance Form Submit Script Start */
if ( $('#attendance-form').length > 0 ) {
    $(document).on('submit', '#attendance-form', function(e) {
        e.preventDefault();
        
        var form = $(this);
        $.ajax({
            type     : "POST",
            dataType : "json",
            async    : true,
            cache    : false,
            url      : baseurl + "ajax/?case=AttendanceProcessHandler",
            data     : form.serialize(),
            success  : function(result) {
                if ( result.code == 0 ) {
                    form[0].reset();
                    $('#action_btn').text(result.next);
                    if ( result.complete == 2 ) {
                        form.remove();
                    }
                    $.notify({
                        icon: 'glyphicon glyphicon-ok-circle',
                        message: result.result,
                    },{
                        allow_dismiss: false,
                        type: "success",
                        placement: {
                            from: "bottom",
                            align: "right"
                        },
                        z_index: 9999,
                    });
                } else {
                    $.notify({
                        icon: 'glyphicon glyphicon-remove-circle',
                        message: result.result,
                    },{
                        allow_dismiss: false,
                        type: "danger",
                        placement: {
                            from: "bottom",
                            align: "right"
                        },
                        z_index: 9999,
                    });
                }
            }
        });
    });
}
/* End of Script */

$(document).ready(function() {

    // Dynamic calculation for Payscale Grade form, prefix is '' for edit form, '_add' for add form
    function calculatePayscaleAllowances(prefix) {
        prefix = prefix || '';
        var basicSalary = parseFloat($('#basic_salary' + prefix).val().replace(/,/g, '')) || 0;
        var empGrade = parseInt($('#emp_grade' + prefix).val()) || 0;
        var houseRent = 0;
        var medicalAllowance = 0;
        var conveyanceAllowance = 0;
        var driverAllowance = 0;
        var carAllowance = 0;

        // House Rent: 45% of basic salary
        houseRent = basicSalary * 0.45;

        // Medical Allowance calculation
        if (empGrade >= 4 && empGrade <= 7) {
            medicalAllowance = basicSalary * 0.10;
            if (medicalAllowance > 10000) {
                medicalAllowance = 10000;
            }
        } else if (empGrade > 7 && empGrade <= 15) {
            medicalAllowance = basicSalary * 0.05;
        } else {
            medicalAllowance = basicSalary * 0.05;
        }

        // Conveyance, Driver Allowance, Car Allowance calculation
        if (empGrade >= 2 && empGrade <= 5) {
            driverAllowance = 23000;
            carAllowance = 48440;
            conveyanceAllowance = 0;
        } else if (empGrade == 6 || empGrade == 7) {
            driverAllowance = 23000;
            carAllowance = 20000;
            conveyanceAllowance = 0;
        } else {
            driverAllowance = 0;
            carAllowance = 0;
            conveyanceAllowance = basicSalary * 0.05;
        }

        // Update the form fields with calculated values, rounded to 2 decimals
        $('#house_rent' + prefix).val(houseRent.toFixed(2));
        $('#medical_allowance' + prefix).val(medicalAllowance.toFixed(2));
        $('#conveyance_allowance' + prefix).val(conveyanceAllowance.toFixed(2));
        $('#driver_allowance' + prefix).val(driverAllowance.toFixed(2));
        $('#car_allowance' + prefix).val(carAllowance.toFixed(2));
    }

    // Attach event listeners to basic_salary and emp_grade inputs in Edit Payscale Grade form
    $('#basic_salary, #emp_grade').on('input change', function() {
        calculatePayscaleAllowances('');
    });

    // Attach event listeners to basic_salary_add and emp_grade_add inputs in Add Payscale Grade form
    $('#basic_salary_add, #emp_grade_add').on('input change', function() {
        calculatePayscaleAllowances('_add');
    });

    // Also calculate on Edit modal show to initialize values if any
    $('#EditPayscaleModal').on('shown.bs.modal', function() {
        calculatePayscaleAllowances('');
    });

    // Also calculate on Add modal show to initialize values if any
    $('#AddPayscaleModal').on('shown.bs.modal', function() {
        calculatePayscaleAllowances('_add');
    });
    /* Attendance Table Script Start */
    if ( $('#attendance').length > 0 ) {
        var att_table = $('#attendance').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": baseurl + "ajax/?case=LoadingAttendance",
            "order": [0, 'desc'],
            "columnDefs": [{
                "targets": 0,
                "className": "dt-center"
            }, {
                "targets": 1,
                "className": "dt-center"
            }]
        });
    }
    /* End of Script */

    /* Salary Table Script Start */
    if ( $('#admin-salary').length > 0 ) {
        var admin_sal_table = $('#admin-salary').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": baseurl + "ajax/?case=LoadingSalaries",
            "order": [0, 'desc'],
            "columnDefs": [{
                "targets": 0,
                "className": "dt-center"
            }]
        });
    }
    if ( $('#admin-payscale-grade').length > 0 ) {
        var admin_payscale_table = $('#admin-payscale-grade').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": baseurl + "ajax/?case=LoadingPayscaleGrade",
            "order": [0, 'asc'],
            "columnDefs": [{
                "targets": 0,
                "className": "dt-center"
            }, {
                "targets": -1,
                "data": null,
                "className": "dt-center",
                "defaultContent": '<button class="btn btn-primary btn-xs editPayscale"><i class="fa fa-edit"></i></button>'
            }]
        });

        $('#admin-payscale-grade tbody').on('click', '.editPayscale', function() {
            var data = admin_payscale_table.row($(this).parents('tr')).data();
            // Populate modal fields
            $('#payscale_id').val(data[0]);
            $('#emp_grade').val(data[1]);
            $('#empsal_grade').val(data[2]);
            $('#basic_salary').val(data[3].replace(/,/g, ''));
            $('#house_rent').val(data[4].replace(/,/g, ''));
            $('#conveyance_allowance').val(data[5].replace(/,/g, ''));
            $('#medical_allowance').val(data[6].replace(/,/g, ''));
            $('#driver_allowance').val(data[7].replace(/,/g, ''));
            $('#car_allowance').val(data[8].replace(/,/g, ''));
            $('#EditPayscaleModal').modal('show');
        });

        $('#edit-payscale-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                type: "POST",
                url: baseurl + "ajax/?case=UpdatePayscaleGrade",
                data: form.serialize(),
                dataType: "json",
                success: function(result) {
                    if (result.code === 0) {
                        $.notify({
                            icon: 'glyphicon glyphicon-ok-circle',
                            message: result.result,
                        }, {
                            allow_dismiss: false,
                            type: "success",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                        $('#EditPayscaleModal').modal('hide');
                        admin_payscale_table.ajax.reload(null, false);
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        }, {
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                },
                error: function() {
                    $.notify({
                        icon: 'glyphicon glyphicon-remove-circle',
                        message: 'An error occurred while updating the record.',
                    }, {
                        allow_dismiss: false,
                        type: "danger",
                        placement: {
                            from: "top",
                            align: "right"
                        },
                        z_index: 9999,
                    });
                }
            });
        });

        // Add Payscale Grade button click handler
        $('#add-payscale-btn').on('click', function() {
            $('#AddPayscaleModal').modal('show');
        });

        // Add Payscale Grade form submission
            $('#add-payscale-form').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                // console.log("Submitting add-payscale-form with data:", form.serialize());
                $.ajax({
                    type: "POST",
                    url: baseurl + "ajax/?case=InsertUpdatePayscaleGrade",
                    data: form.serialize(),
                    dataType: "json",
                    success: function(result) {
                        if (result.code === 0) {
                            $.notify({
                                icon: 'glyphicon glyphicon-ok-circle',
                                message: result.result,
                            }, {
                                allow_dismiss: false,
                                type: "success",
                                placement: {
                                    from: "top",
                                    align: "right"
                                },
                                z_index: 9999,
                            });
                            $('#AddPayscaleModal').modal('hide');
                            admin_payscale_table.ajax.reload(null, false);
                            form[0].reset();
                        } else {
                            $.notify({
                                icon: 'glyphicon glyphicon-remove-circle',
                                message: result.result,
                            }, {
                                allow_dismiss: false,
                                type: "danger",
                                placement: {
                                    from: "top",
                                    align: "right"
                                },
                                z_index: 9999,
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // console.error("AJAX error:", xhr.responseText);
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: 'An error occurred while adding the record.',
                        }, {
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                });
            });
    }

    if ( $('#emp-salary').length > 0 ) {
        var emp_sal_table = $('#emp-salary').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": baseurl + "ajax/?case=LoadingSalaries",
            "order": [0, 'desc']
        });
    }
    /* End of Script */

    if ( $('#employees').length > 0 ) {
        /* Employee Table Script Start */
        var emp_table = $('#employees').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": baseurl + "ajax/?case=LoadingEmployees",
            "columnDefs": [{
                "targets": 0,
                "className": "dt-center"
            }, {
                "targets": 1,
                "orderable": false,
                "className": "dt-center"
            }, {
                "targets": -1,
                "orderable": false,
                "data": null,
                "className": "dt-center",
                "defaultContent": '<button class="btn btn-warning btn-xs manageSalary" title="Manage Salary"><i class="fa fa-money"></i></button> <button class="btn btn-primary btn-xs addSalary" title="Add Salary"><i class="fa fa-gratipay"></i></button> <button class="btn btn-success btn-xs editEmp" title="Edit Employee Details"><i class="fa fa-edit"></i></button> <button class="btn btn-danger btn-xs deleteEmp" title="Delete Employee"><i class="fa fa-trash"></i></button>'
            }]
        });
        /* End of Script */
/* Pay Salary Script Start */
$('#employees tbody').on('click', '.manageSalary', function(e) {
    e.preventDefault();  // Prevent the default action of the element (e.g., following a link)

    // Get the data for the clicked row (employee data)
    var data = emp_table.row($(this).parents('tr')).data();

    // Store employee code in the modal data attribute
    $('#SalaryMonthModal').data('empcode', data[0]);

    // Show the modal for selecting the salary month
    $('#SalaryMonthModal').modal('show');
});

// Handle click on month links inside the SalaryMonthModal
$(document).on('click', '#SalaryMonthModal .salary-month-link', function(e) {
    e.preventDefault();

    var month = $(this).data('month');
    var year = $(this).data('year');
    var empcode = $('#SalaryMonthModal').data('empcode');

    if (!empcode) {
        alert('Employee code not found.');
        return;
    }

    // Build the pay salary link for the specific employee, month, and year
    var paylink = baseurl + 'pay-salary/' + empcode + '/' + month + '/' + year + '/';

    // Navigate to the pay salary page
    window.location.href = paylink;
});
/* End of Script */

        /* Add Salary Script Start */
        $('#employees tbody').on('click', '.addSalary', function(e) {
            e.preventDefault();

            var data = emp_table.row($(this).parents('tr')).data();

            $('#empcode').val(data[0]);

            // First fetch latest emp_grade and empsal_grade from server
            $.ajax({
                type: "POST",
                dataType: "json",
                url: baseurl + "ajax/?case=GetEmployeeByID",
                data: 'emp_code=' + data[0],
                success: function(empResult) {
                    console.log("GetEmployeeByID result:", empResult);
                    if (empResult.code === 0) {
                        var empGrade = empResult.result.emp_grade;
                        var empSalGrade = empResult.result.empsal_grade;
                        console.log("empGrade:", empGrade, "empSalGrade:", empSalGrade);

                        // Set hidden inputs for emp_grade and empsal_grade in the assign-payhead-form
                        $('#emp_grade_hidden').val(empGrade);
                        $('#empsal_grade_hidden').val(empSalGrade);

                        // Now fetch payheads with latest grades
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            url: baseurl + "ajax/?case=GetAllPayheadsExceptEmployeeHave",
                            data: 'emp_code=' + data[0] + '&emp_grade=' + empGrade + '&salary_grade=' + empSalGrade,
                            success: function(result) {
                                console.log("GetAllPayheadsExceptEmployeeHave result:", result);
                                $('#all_payheads').html('');
                                if (result.code == 0) {
                                    for (var i in result.result) {
                                        var payheadKey = result.result[i].payhead_name.toLowerCase().replace(/\s+/g, '_');
                                        var payheadAmount = 0;
                                        if (result.gradeResult && result.gradeResult.hasOwnProperty(payheadKey)) {
                                            payheadAmount = result.gradeResult[payheadKey];
                                        }
                                        $('#all_payheads').append($("<option></option>")
                                            .attr({
                                                "value": result.result[i].payhead_id,
                                                "alt": payheadAmount
                                            })
                                            .text(
                                                result.result[i].payhead_name + ' (' + jsUcfirst(result.result[i].payhead_type) + ')')
                                            .addClass((result.result[i].payhead_type == 'earnings' ? 'text-success' : 'text-danger'))
                                        );
                                    }
                                }
                            }
                        });

                        // Fetch assigned payheads and amounts
                        $.ajax({
                            type: "POST",
                            dataType: "json",
                            url: baseurl + "ajax/?case=GetEmployeePayheadsByID",
                            data: 'emp_code=' + data[0],
                            success: function(result) {
                                console.log("GetEmployeePayheadsByID result:", result);
                                $('#selected_payheads, #selected_payamount').html('');
                                if (result.code == 0) {
                                    for (var i in result.result) {
                                        $('#selected_payheads').append($("<option></option>")
                                            .attr({
                                                "value": result.result[i].payhead_id,
                                                "selected": "selected"
                                            })
                                            .text(
                                                result.result[i].payhead_name + ' (' + jsUcfirst(result.result[i].payhead_type) + ')'
                                            )
                                            .addClass((result.result[i].payhead_type == 'earnings' ? 'text-success' : 'text-danger'))
                                        );
                                        $('#selected_payamount').append($("<input />")
                                            .attr({
                                                "type": "text",
                                                "name": "pay_amounts[" + result.result[i].payhead_id + "]",
                                                "id": "pay_amounts_" + result.result[i].payhead_id,
                                                "placeholder": result.result[i].payhead_name,
                                                "value": result.result[i].default_salary
                                            })
                                            .addClass('form-control')
                                        );
                                    }
                                }
                            }
                        });

                        $('#ManageModal').modal('show');
                    } else {
                        alert('Failed to fetch employee details.');
                    }
                },
                error: function() {
                    alert('Error fetching employee details.');
                }
            });
        });
        /* End of Script */

        /* Delete Employee Script Start */
        $('#employees tbody').on('click', '.editEmp', function(e) {
            e.preventDefault();

            var data = emp_table.row($(this).parents('tr')).data();
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=GetEmployeeByID",
                data     : 'emp_code=' + data[0],
                success  : function(result) {
                    if ( result.code == 0 ) {
                        $('#employee_id').text(result.result.employee_id);
			$('#employee_code_display').text(result.result.emp_code);
                        $('#emp_code').val(result.result.emp_code);
                        $('#first_name').val(result.result.first_name);
                        $('#last_name').val(result.result.last_name);
                        $('#dob').val(result.result.dob).datepicker('update');
                        $('#gender').val(result.result.gender);
                        $('#marital_status').val(result.result.marital_status);
                        $('#blood_group').val(result.result.blood_group);
                        $('#address').val(result.result.address);
                        $('#paraddress').val(result.result.paraddress);
                        $('#email').val(result.result.email);
                        $('#mobile').val(result.result.mobile);
                        $('#telephone').val(result.result.telephone);
                        $('#national_id').val(result.result.national_id);
                        $('#employment_type').val(result.result.employment_type);
                        $('#employment_status').val(result.result.employment_status);
                        $('#designation').val(result.result.designation);
                        $('#department').val(result.result.department);
                        $('#emp_grade').val(result.result.emp_grade);
                        $('#empsal_grade').val(result.result.empsal_grade);
                        $('#joining_date').val(result.result.joining_date).datepicker('update');
                        $('#confirmation_date').val(result.result.confirmation_date).datepicker('update');
                        $('#resign_date').val(result.result.resign_date).datepicker('update');
                        $('#account_no').val(result.result.account_no);
                        $('#etin_no').val(result.result.etin_no);
                        $('#EditEmpModal').modal('show');
                        
                // Populate the modal fields with data returned from the server
    
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                }
            });
        });
        /* End of Script */

        /* Delete Employee Script Start */
        $('#employees tbody').on('click', '.deleteEmp', function(e) {
            e.preventDefault();

            var conf = confirm('Are you sure you want to delete this employee?');
            if ( conf ) {
                var data = emp_table.row($(this).parents('tr')).data();
                $.ajax({
                    type     : "POST",
                    dataType : "json",
                    async    : true,
                    cache    : false,
                    url      : baseurl + "ajax/?case=DeleteEmployeeByID",
                    data     : 'emp_code=' + data[0],
                    success  : function(result) {
                        if ( result.code == 0 ) {
                            $.notify({
                                icon: 'glyphicon glyphicon-ok-circle',
                                message: result.result,
                            },{
                                allow_dismiss: false,
                                type: "success",
                                placement: {
                                    from: "top",
                                    align: "right"
                                },
                                z_index: 9999,
                            });
                            emp_table.ajax.reload(null, false);
                        } else {
                            $.notify({
                                icon: 'glyphicon glyphicon-remove-circle',
                                message: result.result,
                            },{
                                allow_dismiss: false,
                                type: "danger",
                                placement: {
                                    from: "top",
                                    align: "right"
                                },
                                z_index: 9999,
                            });
                        }
                    }
                });
            }
        });
        /* End of Script */

        /* Add Payhead To Employee Script Start */
        $(document).on('click', '#selectHeads', function() {
            $('#all_payheads').find(':selected').each(function() {
                var val = $(this).val();
                var name = $(this).text();
                var alt = $(this).attr('alt');
                $('#selected_payamount').append($("<input />")
                    .attr({
                        "type": "text",
                        "name": "pay_amounts[" + val + "]",
                        "id": "pay_amounts_" + val,
                        "placeholder": name,
                        "value":alt
                    })
                    .addClass('form-control')
                );
            });
            moveItems('#all_payheads', '#selected_payheads');
        });
        $(document).on('click', '#removeHeads', function() {
            $('#selected_payheads').find(':selected').each(function() {
                var val = $(this).val();
                $('#pay_amounts_' + val).remove();
            });
            moveItems('#selected_payheads', '#all_payheads');
        });
        /* End of Script */
    }

    /* Date Picker Script Start */
    if ( $('.datepicker').length > 0 ) {
        $('.datepicker').datepicker({
            format: 'mm/dd/yyyy',
            autoclose: true
        });
    }
    if ( $('.multidatepicker').length > 0 ) {
        $('.multidatepicker').datepicker({
            format: 'mm/dd/yyyy',
            startDate : new Date(),
            multidate: true,
            autoclose: true
        });
    }
    /* End of Script */

    /* Stylish Radio Input Script Start */ 
    if ( $('input[type="radio"].minimal').length > 0 ) {
        $('input[type="radio"].minimal').iCheck({
            radioClass: 'iradio_minimal-blue'
        });
    }
    /* End of Script */

    /* Holiday Table Script Start */
    if ( $('#empholidays').length > 0 ) {
        $('#empholidays').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": baseurl + "ajax/?case=LoadingHolidays",
            "columnDefs": [{
                "targets": 0,
                "className": "dt-center"
            }, {
                "targets": 3,
                "className": "dt-center"
            }, {
                "targets": 4,
                "className": "dt-center"
            }]
        });
    }
    /* End of Script */

    if ( $('#holidays').length > 0 ) {
        /* Holiday Table Script Start */
        var holi_table = $('#holidays').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": baseurl + "ajax/?case=LoadingHolidays",
            "columnDefs": [{
                "targets": 0,
                "className": "dt-center"
            }, {
                "targets": 3,
                "className": "dt-center"
            }, {
                "targets": 4,
                "className": "dt-center"
            }, {
                "targets": -1,
                "orderable": false,
                "data": null,
                "className": "dt-center",
                "defaultContent": '<button class="btn btn-success btn-xs editHoliday"><i class="fa fa-edit"></i></button> <button class="btn btn-danger btn-xs deleteHoliday"><i class="fa fa-trash"></i></button>'
            }]
        });
        /* End of Script */

        /* Edit Holiday Script Start */
        $('#holidays tbody').on('click', '.editHoliday', function(e) {
            e.preventDefault();

            var data = holi_table.row($(this).parents('tr')).data();
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=GetHolidayByID",
                data     : 'id=' + data[0],
                success  : function(result) {
                    if ( result.code == 0 ) {
                        $("#holiday_id").val(result.result.holiday_id);
                        $("#holiday_title").val(result.result.holiday_title);
                        $("#holiday_desc").val(result.result.holiday_desc);
                        $("#holiday_date").val(result.result.holiday_date).datepicker('update');
                        if ( result.result.holiday_type == 'compulsory' ) {
                            $("#compulsory_holiday").iCheck('check');
                        } else {
                            $("#restricted_holiday").iCheck('check');
                        }
                        $("#HolidayModal").modal('show');
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                }
            });
        });
        /* End of Script */

        /* Delete Holiday Script Start */
        $('#holidays tbody').on('click', '.deleteHoliday', function(e) {
            e.preventDefault();

            var conf = confirm('Are you sure you want to delete this holiday?');
            if ( conf ) {
                var data = holi_table.row($(this).parents('tr')).data();
                $.ajax({
                    type     : "POST",
                    dataType : "json",
                    async    : true,
                    cache    : false,
                    url      : baseurl + "ajax/?case=DeleteHolidayByID",
                    data     : 'id=' + data[0],
                    success  : function(result) {
                        if ( result.code == 0 ) {
                            $.notify({
                                icon: 'glyphicon glyphicon-ok-circle',
                                message: result.result,
                            },{
                                allow_dismiss: false,
                                type: "success",
                                placement: {
                                    from: "top",
                                    align: "right"
                                },
                                z_index: 9999,
                            });
                            holi_table.ajax.reload(null, false);
                        } else {
                            $.notify({
                                icon: 'glyphicon glyphicon-remove-circle',
                                message: result.result,
                            },{
                                allow_dismiss: false,
                                type: "danger",
                                placement: {
                                    from: "top",
                                    align: "right"
                                },
                                z_index: 9999,
                            });
                        }
                    }
                });
            }
        });
        /* End of Script */
    }

    /* Holiday Modal Close Script Start */
    if ( $('#EditEmpModal').length > 0 ) {
        $('#EditEmpModal').on('hidden.bs.modal', function () {
            $("#emp_code").empty();
            $('#edit-emp-form')[0].reset();
        });
    }
    /* End of Script */

    /* Holiday Modal Close Script Start */
    if ( $('#HolidayModal').length > 0 ) {
        $('#HolidayModal').on('hidden.bs.modal', function () {
            $("#holiday_id").val('');
            $("#compulsory_holiday").iCheck('check');
            $('#holiday-form')[0].reset();
        });
    }
    /* End of Script */

    /* Manage Modal Close Script Start */
    if ( $('#ManageModal').length > 0 ) {
        $('#ManageModal').on('hidden.bs.modal', function () {
            $("#empcode").val('');
            $('#selected_payheads').html('');
        });
    }
    /* End of Script */

    /* Assign Payhead to Employee Form Submit Script Start */
    if ( $('#assign-payhead-form').length > 0 ) {
        $('#assign-payhead-form').on('submit', function(e) {
            e.preventDefault();

            // Debugging logs for hidden inputs
            console.log("emp_grade_hidden value:", $('#emp_grade_hidden').val());
            console.log("empsal_grade_hidden value:", $('#empsal_grade_hidden').val());

            var form = $(this);
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=AssignPayheadsToEmployee",
                data     : form.serialize(),
                success  : function(result) {
                    if ( result.code == 0 ) {
                        $.notify({
                            icon: 'glyphicon glyphicon-ok-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "success",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                        $('#ManageModal').modal('hide');
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                }
            });
        });
    }
    /* End of Script */

    /* Holiday Form Submit Script Start */
    if ( $('#holiday-form').length > 0 ) {
        $('#holiday-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=InsertUpdateHolidays",
                data     : form.serialize(),
                success  : function(result) {
                    if ( result.code == 0 ) {
                        $.notify({
                            icon: 'glyphicon glyphicon-ok-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "success",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                        holi_table.ajax.reload(null, false);
                        $('#HolidayModal').modal('hide');
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                }
            });
        });
    }
    /* End of Script */

    /* Employee Edit Form Submit Script Start */
    if ( $('#edit-emp-form').length > 0 ) {
        $('#edit-emp-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=EditEmployeeDetailsByID",
                data     : form.serialize(),
                success  : function(result) {
                    if ( result.code == 0 ) {
                        $.notify({
                            icon: 'glyphicon glyphicon-ok-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "success",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                        emp_table.ajax.reload(null, false);
                        $('#EditEmpModal').modal('hide');
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                }
            });
        });
    }
    /* End of Script */

    if ( $('#payheads').length > 0 ) {
        /* Payhead Table Script Start */
var pay_table = $('#payheads').DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": baseurl + "ajax/?case=LoadingPayheads",
    "columnDefs": [{
        "targets": 0,
        "className": "dt-center"
    }, {
        "targets": 3,
        "className": "dt-center",
"render": function(data, type, row) {
    if (data === 'deductions') {
        return '<span class="bg-red text-white" style="padding: 2px 6px; border-radius: 4px;">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
    } else if (data === 'earnings') {
        return '<span class="bg-green text-white" style="padding: 2px 6px; border-radius: 4px;">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
    } else {
        return data;
    }
}
    }, {
        "targets": -1,
        "orderable": false,
        "data": null,
        "className": "dt-center",
        "defaultContent": '<button class="btn btn-success btn-xs editPayheads"><i class="fa fa-edit"></i></button> <button class="btn btn-danger btn-xs deletePayheads"><i class="fa fa-trash"></i></button>'
    }]
});

        
        $('#payheads tbody').on('click', '.editPayheads', function(e) {
            e.preventDefault();

            var data = pay_table.row($(this).parents('tr')).data();
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=GetPayheadByID",
                data     : 'id=' + data[0],
                success  : function(result) {
                    // console.log(result)
if ( result.code == 0 ) {
    $("#payhead_id").val(result.result.payhead_id);
    $("#payhead_name").val(result.result.payhead_name);
    $("#payhead_desc").val(result.result.payhead_desc);
    $("#payhead_type").val(result.result.payhead_type);
    $("#PayheadsModal").modal('show');
} else {
    $.notify({
        icon: 'glyphicon glyphicon-remove-circle',
        message: result.result,
    },{
        allow_dismiss: false,
        type: "danger",
        placement: {
            from: "top",
            align: "right"
        },
        z_index: 9999,
    });
}
}
});
});
/* End of Script */

        /* Delete Payhead Script Start */
        $('#payheads tbody').on('click', '.deletePayheads', function(e) {
            e.preventDefault();

            var conf = confirm('Are you sure you want to delete this payhead?');
            if ( conf ) {
                var data = pay_table.row($(this).parents('tr')).data();
                $.ajax({
                    type     : "POST",
                    dataType : "json",
                    async    : true,
                    cache    : false,
                    url      : baseurl + "ajax/?case=DeletePayheadByID",
                    data     : 'id=' + data[0],
                    success  : function(result) {
                        if ( result.code == 0 ) {
                            $.notify({
                                icon: 'glyphicon glyphicon-ok-circle',
                                message: result.result,
                            },{
                                allow_dismiss: false,
                                type: "success",
                                placement: {
                                    from: "top",
                                    align: "right"
                                },
                                z_index: 9999,
                            });
                            pay_table.ajax.reload(null, false);
                        } else {
                            $.notify({
                                icon: 'glyphicon glyphicon-remove-circle',
                                message: result.result,
                            },{
                                allow_dismiss: false,
                                type: "danger",
                                placement: {
                                    from: "top",
                                    align: "right"
                                },
                                z_index: 9999,
                            });
                        }
                    }
                });
            }
        });
        /* End of Script */
    }

    /* Payhead Modal Close Script Start */
    if ( $('#PayheadsModal').length > 0 ) {
        $('#PayheadsModal').on('hidden.bs.modal', function () {
            $("#payhead_id").val('');
            $('#payhead-form')[0].reset();
        });
    }
    /* End of Script */

    /* Payhead Form Submit Script Start */
    if ( $('#payhead-form').length > 0 ) {
        $('#payhead-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=InsertUpdatePayheads",
                data     : form.serialize(),
                success  : function(result) {
                    if ( result.code == 0 ) {
                        $.notify({
                            icon: 'glyphicon glyphicon-ok-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "success",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                        pay_table.ajax.reload(null, false);
                        $('#PayheadsModal').modal('hide');
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                }
            });
        });
    }
    /* End of Script */

    /* Salary Form Submit Script Start */
if ( $('#payslip-form').length > 0 ) {
    $('#payslip-form').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        $.ajax({
            type     : "POST",
            dataType : "json",
            async    : true,
            cache    : false,
            url      : baseurl + "ajax/?case=GeneratePaySlip",
            data     : form.serialize(),
            success  : function(result) {
                if ( result.code == 0 ) {
                    $.notify({
                        icon: 'glyphicon glyphicon-ok-circle',
                        message: result.result,
                    },{
                        allow_dismiss: false,
                        type: "success",
                        placement: {
                            from: "top",
                            align: "right"
                        },
                        z_index: 9999,
                    });
                    // Optionally reload after delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    $.notify({
                        icon: 'glyphicon glyphicon-remove-circle',
                        message: result.result,
                    },{
                        allow_dismiss: false,
                        type: "danger",
                        placement: {
                            from: "top",
                            align: "right"
                        },
                        z_index: 9999,
                    });
                }
            }
        });
    });
}
    /* End of Script */

    /* Profile Edit Form Submit Script Start */
    if ( $('#profile-form').length > 0 ) {
        $('#profile-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=EditProfileByID",
                data     : form.serialize(),
                success  : function(result) {
                    if ( result.code == 0 ) {
                        $.notify({
                            icon: 'glyphicon glyphicon-ok-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "success",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                }
            });
        });
    }
    /* End of Script */

    /* Password Edit Form Submit Script Start */
    if ( $('#password-form').length > 0 ) {
        $('#password-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=EditLoginDataByID",
                data     : form.serialize(),
                success  : function(result) {
                    if ( result.code == 0 ) {
                        form[0].reset();
                        $.notify({
                            icon: 'glyphicon glyphicon-ok-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "success",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                }
            });
        });
    }
    /* End of Script */

    /* Leave Table Script Start */
    if ( $('#allleaves').length > 0 ) {
        var leave_table = $('#allleaves').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": baseurl + "ajax/?case=LoadingAllLeaves",
            "columnDefs": [{
                "targets": 0,
                "className": "dt-center"
            }, {
                "targets": -1,
                "orderable": false,
                "data": null,
                "className": "dt-center",
                "defaultContent": '<button class="btn btn-success btn-xs approveLeave"><i class="fa fa-check"></i></button> <button class="btn btn-danger btn-xs rejectLeave"><i class="fa fa-close"></i></button>'
            }]
        });

        /* Approve Leave Application Script Start */
        $('#allleaves tbody').on('click', '.approveLeave', function(e) {
            e.preventDefault();

            var data = leave_table.row($(this).parents('tr')).data();
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=ApproveLeaveApplication",
                data     : 'id=' + data[0],
                success  : function(result) {
                    if ( result.code == 0 ) {
                        $.notify({
                            icon: 'glyphicon glyphicon-ok-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "success",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                        leave_table.ajax.reload(null, false);
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                }
            });
        });
        /* End of Script */

        /* Approve Leave Application Script Start */
        $('#allleaves tbody').on('click', '.rejectLeave', function(e) {
            e.preventDefault();

            var data = leave_table.row($(this).parents('tr')).data();
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=RejectLeaveApplication",
                data     : 'id=' + data[0],
                success  : function(result) {
                    if ( result.code == 0 ) {
                        $.notify({
                            icon: 'glyphicon glyphicon-ok-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "success",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                        leave_table.ajax.reload(null, false);
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                }
            });
        });
        /* End of Script */
    }
    /* End of Script */

    /* Leave Table Script Start */
    if ( $('#myleaves').length > 0 ) {
        var myleave = $('#myleaves').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": baseurl + "ajax/?case=LoadingMyLeaves",
            "columnDefs": [{
                "targets": 0,
                "className": "dt-center"
            }]
        });
    }
    /* End of Script */

    /* Leave Apply Form Submit Script Start */
    if ( $('#leave-form').length > 0 ) {
        $('#leave-form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            $.ajax({
                type     : "POST",
                dataType : "json",
                async    : true,
                cache    : false,
                url      : baseurl + "ajax/?case=ApplyLeaveToAdminApproval",
                data     : form.serialize(),
                success  : function(result) {
                    if ( result.code == 0 ) {
                        form[0].reset();
                        $.notify({
                            icon: 'glyphicon glyphicon-ok-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "success",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                        myleave.ajax.reload(null, false);
                    } else {
                        $.notify({
                            icon: 'glyphicon glyphicon-remove-circle',
                            message: result.result,
                        },{
                            allow_dismiss: false,
                            type: "danger",
                            placement: {
                                from: "top",
                                align: "right"
                            },
                            z_index: 9999,
                        });
                    }
                }
            });
        });
    }
    /* End of Script */
});

function moveItems(origin, dest) {
    $(origin).find(':selected').appendTo(dest);
}

function jsUcfirst(string) {
    if (typeof string !== 'string' || string.length === 0) {
        return '';
    }
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function openInNewTab(url) {
    // console.log(url);
    var win = window.open(url, '_blank');
    win.focus();
}

function sendPaySlipByMail(emp_code, month) {
    $.ajax({
        type     : "POST",
        dataType : "json",
        async    : true,
        cache    : false,
        url      : baseurl + "ajax/?case=SendPaySlipByMail",
        data     : 'emp_code=' + emp_code + '&month=' + month,
        success  : function(result) {
            if ( result.code == 0 ) {
                $.notify({
                    icon: 'glyphicon glyphicon-ok-circle',
                    message: result.result,
                },{
                    allow_dismiss: false,
                    type: "success",
                    placement: {
                        from: "top",
                        align: "right"
                    },
                    z_index: 9999,
                });
            } else {
                $.notify({
                    icon: 'glyphicon glyphicon-remove-circle',
                    message: result.result,
                },{
                    allow_dismiss: false,
                    type: "danger",
                    placement: {
                        from: "top",
                        align: "right"
                    },
                    z_index: 9999,
                });
            }
        }
    });
}