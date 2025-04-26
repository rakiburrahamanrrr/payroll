-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 02:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `payroll_mdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `cdbl_admin`
--

CREATE TABLE `cdbl_admin` (
  `admin_id` int(11) NOT NULL,
  `admin_code` varchar(255) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cdbl_admin`
--

INSERT INTO `cdbl_admin` (`admin_id`, `admin_code`, `admin_name`, `admin_email`, `admin_password`, `admin_time`) VALUES
(1, 'WY00', 'Admin', 'admin@gmail.com', '7c4a8d09ca3762af61e59520943dc26494f8941b', '2019-04-18 02:22:37');

-- --------------------------------------------------------

--
-- Table structure for table `cdbl_attendance`
--

CREATE TABLE `cdbl_attendance` (
  `attendance_id` int(11) NOT NULL,
  `emp_code` varchar(255) NOT NULL,
  `attendance_date` date NOT NULL,
  `action_name` enum('punchin','punchout') NOT NULL,
  `action_time` time NOT NULL,
  `emp_desc` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cdbl_attendance`
--

INSERT INTO `cdbl_attendance` (`attendance_id`, `emp_code`, `attendance_date`, `action_name`, `action_time`, `emp_desc`) VALUES
(2, 'WY01', '2021-04-13', 'punchin', '10:41:27', '21'),
(3, 'WY01', '2021-04-13', 'punchout', '17:37:36', '220'),
(4, 'WY02', '2021-04-14', 'punchin', '15:05:42', 'D114'),
(5, 'WY02', '2021-04-14', 'punchout', '22:19:14', 'out-144'),
(6, 'WY03', '2021-04-14', 'punchin', '10:30:30', 'IN'),
(7, 'WY03', '2021-04-14', 'punchout', '17:30:52', 'OUT'),
(8, 'WY04', '2021-04-14', 'punchin', '10:00:59', 'IS1'),
(9, 'WY04', '2021-04-14', 'punchout', '14:31:27', 'IS1'),
(10, 'WY05', '2021-04-14', 'punchin', '19:11:29', 'In'),
(11, 'WY05', '2021-04-14', 'punchout', '19:13:02', 'Outt');

-- --------------------------------------------------------

--
-- Table structure for table `cdbl_employees`
--

CREATE TABLE `cdbl_employees` (
  `emp_code` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `marital_status` enum('Single','Married','Divorced') NOT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `paraddress` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `national_id` varchar(50) NOT NULL,
  `verification` enum('Verified','Not Verified','Under Process') NOT NULL,
  `employee_id` int(11) NOT NULL,
  `employment_type` enum('Provision','Contractual','Permanent') NOT NULL,
  `employment_status` enum('Active','Inactive','Leavewithoutpay') NOT NULL,
  `department` varchar(100) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `emp_grade` varchar(50) NOT NULL,
  `empsal_grade` varchar(50) NOT NULL,
  `joining_date` date NOT NULL,
  `confirmation_date` date DEFAULT NULL,
  `resign_date` date DEFAULT NULL,
  `job_loc` varchar(100) DEFAULT NULL,
  `blood_group` varchar(10) NOT NULL,
  `insurance_id` varchar(50) NOT NULL,
  `service_period` varchar(50) DEFAULT NULL,
  `academic_qualifications` text DEFAULT NULL,
  `emp_action` varchar(50) DEFAULT NULL,
  `photo` varchar(100) NOT NULL,
  `emp_password` varchar(255) NOT NULL,
  `account_no` varchar(50) NOT NULL,
  `etin_no` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `cdbl_employees`
--

INSERT INTO `cdbl_employees` (`emp_code`, `first_name`, `last_name`, `dob`, `gender`, `marital_status`, `nationality`, `address`, `paraddress`, `email`, `mobile`, `telephone`, `national_id`, `verification`, `employee_id`, `employment_type`, `employment_status`, `department`, `designation`, `emp_grade`, `empsal_grade`, `joining_date`, `confirmation_date`, `resign_date`, `job_loc`, `blood_group`, `insurance_id`, `service_period`, `academic_qualifications`, `emp_action`, `photo`, `emp_password`, `account_no`, `etin_no`, `created_at`) VALUES
('cdbl90', 'anmona', 'haque', '1999-02-28', 'female', 'Married', '', 'Mohammadpur', 'Dhaka', 'asad@cdbl.com.bd', '01709247401', '', '5104939238', 'Verified', 78, 'Permanent', 'Inactive', 'Value Added Services', 'Junior Officer', '13', '10', '2019-04-01', '2019-06-06', NULL, 'DC', 'O+', '123456', '', 'BSC', 'No', 'cdbl90.jpeg', '7c4a8d09ca3762af61e59520943dc26494f8941b', '020219307811', '1597539', '2025-04-20 07:45:29'),
('cdbl01', 'Rakibur', 'Rahaman', '1998-04-08', 'male', 'Married', '', '230/1, East Kafrul', 'Dhaka', 'rafi@cdbl.com.bd', '01521334588', '01709247401', '5104939235', 'Verified', 89, 'Permanent', 'Active', 'Value Added Services', 'Officer', '12', '10', '2022-09-01', '2023-03-01', NULL, 'DC', 'B+', '147852369', '', 'MSc in cyber security, Bsc in computer science, HSC in Science, SSC in science', 'No', 'cdbl01.jpeg', '7c4a8d09ca3762af61e59520943dc26494f8941b', '020219398811', '159753', '2025-04-06 20:16:45');

-- --------------------------------------------------------

--
-- Table structure for table `cdbl_holidays`
--

CREATE TABLE `cdbl_holidays` (
  `holiday_id` int(11) NOT NULL,
  `holiday_title` varchar(255) NOT NULL,
  `holiday_desc` varchar(255) NOT NULL,
  `holiday_date` varchar(50) NOT NULL,
  `holiday_type` enum('compulsory','restricted') NOT NULL DEFAULT 'compulsory'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cdbl_holidays`
--

INSERT INTO `cdbl_holidays` (`holiday_id`, `holiday_title`, `holiday_desc`, `holiday_date`, `holiday_type`) VALUES
(1, 'Labor Day', 'Labor Day 2020', '05/01/2020', 'compulsory'),
(2, 'Thanksgiving Day', 'Thanksgiving Day 2020', '11/26/2020', 'restricted'),
(9, 'Independence Day', 'Independence Day 2020', '08/15/2020', 'compulsory'),
(16, 'Memorial Day', 'Memorial Day 2020', '05/25/2020', 'restricted'),
(17, 'Martin Luther King, Jr. Birthday', 'Martin Luther King, Jr. Birthday 2020', '01/20/2020', 'compulsory'),
(18, 'Christmas Day', 'Christmas Day 2020', '12/25/2020', 'compulsory'),
(21, 'New Year', 'New Year 2021', '01/01/2021', 'compulsory');

-- --------------------------------------------------------

--
-- Table structure for table `cdbl_leaves`
--

CREATE TABLE `cdbl_leaves` (
  `leave_id` int(11) NOT NULL,
  `emp_code` varchar(255) NOT NULL,
  `leave_subject` varchar(255) NOT NULL,
  `leave_dates` varchar(255) NOT NULL,
  `leave_message` longtext NOT NULL,
  `leave_type` varchar(255) NOT NULL,
  `leave_status` enum('pending','approve','reject') NOT NULL DEFAULT 'pending',
  `apply_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cdbl_leaves`
--

INSERT INTO `cdbl_leaves` (`leave_id`, `emp_code`, `leave_subject`, `leave_dates`, `leave_message`, `leave_type`, `leave_status`, `apply_date`) VALUES
(1, 'cdbl90', 'going hometown', '04/21/2025', 'Going hometown for personal reason', 'Casual Leave', 'pending', '2025-04-20 09:40:04');

-- --------------------------------------------------------

--
-- Table structure for table `cdbl_payheads`
--

CREATE TABLE `cdbl_payheads` (
  `payhead_id` int(11) NOT NULL,
  `payhead_name` varchar(255) NOT NULL,
  `payhead_desc` varchar(255) NOT NULL,
  `payhead_type` enum('earnings','deductions') NOT NULL DEFAULT 'earnings'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cdbl_payheads`
--

INSERT INTO `cdbl_payheads` (`payhead_id`, `payhead_name`, `payhead_desc`, `payhead_type`) VALUES
(1, 'Basic Salary', 'Basic Salary', 'earnings'),
(2, 'Car Allowance', 'Car Allowance', 'earnings'),
(3, 'House Rent Allowance', 'House Rent Allowance', 'earnings'),
(4, 'Conveyance Allowance', 'Conveyance Allowance', 'earnings'),
(5, 'Medical Allowance', 'Medical Allowance', 'earnings'),
(7, 'Overtime', 'Overtime', 'earnings'),
(8, 'Traveling Expenses', 'Traveling Expenses', 'earnings'),
(9, 'Loans & Advance', 'Loans & Advance', 'deductions'),
(10, 'Performance Bonus', 'Performance Bonus', 'earnings'),
(11, 'Professional Tax', 'Professional Tax', 'deductions'),
(12, 'Income Tax', 'Income Tax', 'deductions'),
(13, 'Employee Provident Fund', 'Employee Provident Fund', 'deductions'),
(14, 'Loans Repayment', 'Loans Repayment', 'deductions'),
(15, 'Other Deductions', 'Other Deductions', 'deductions'),
(16, 'Arrear Salary', 'Arrear Salary', 'earnings'),
(17, 'Leave without Pay', 'Leave without Pay', 'deductions'),
(18, 'Driver\'s Allowance', 'Driver\'s Allowance', 'earnings');

-- --------------------------------------------------------

--
-- Table structure for table `cdbl_payscale_grade`
--

CREATE TABLE `cdbl_payscale_grade` (
  `id` int(255) NOT NULL,
  `emp_grade` int(10) NOT NULL,
  `empsal_grade` int(10) NOT NULL,
  `basic_salary` int(50) NOT NULL,
  `house_rent` int(50) NOT NULL,
  `conveyance_allowance` int(50) DEFAULT NULL,
  `medical_allowance` int(50) NOT NULL,
  `driver_allowance` int(50) DEFAULT NULL,
  `car_allowance` int(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `cdbl_payscale_grade`
--

INSERT INTO `cdbl_payscale_grade` (`id`, `emp_grade`, `empsal_grade`, `basic_salary`, `house_rent`, `conveyance_allowance`, `medical_allowance`, `driver_allowance`, `car_allowance`) VALUES
(1, 12, 10, 17000, 7650, 850, 850, 0, 0),
(2, 12, 11, 17850, 8033, 893, 893, 0, 0),
(3, 12, 14, 36550, 16448, 1828, 1828, 0, 0),
(4, 12, 15, 37400, 16830, 1870, 1870, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cdbl_pay_structure`
--

CREATE TABLE `cdbl_pay_structure` (
  `salary_id` int(11) NOT NULL,
  `emp_code` varchar(255) NOT NULL,
  `payhead_id` int(11) NOT NULL,
  `default_salary` float(11,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cdbl_pay_structure`
--

INSERT INTO `cdbl_pay_structure` (`salary_id`, `emp_code`, `payhead_id`, `default_salary`) VALUES
(129, 'WY01', 1, 45000.00),
(130, 'WY01', 15, 5000.00),
(131, 'WY03', 7, 5500.00),
(132, 'WY03', 1, 21000.00),
(133, 'WY05', 1, 51500.00),
(134, 'WY05', 7, 6500.00),
(135, 'WY05', 12, 5510.00),
(136, 'WY04', 1, 39000.00),
(137, 'WY04', 7, 5600.00),
(138, 'WY04', 12, 4250.00),
(139, 'WY02', 1, 21000.00),
(140, 'WY02', 7, 6500.00),
(287, 'cdbl01', 4, 978.00),
(288, 'cdbl01', 1, 170000.00),
(289, 'cdbl01', 3, 12000.00);

-- --------------------------------------------------------

--
-- Table structure for table `cdbl_salaries`
--

CREATE TABLE `cdbl_salaries` (
  `salary_id` int(11) NOT NULL,
  `emp_code` varchar(255) NOT NULL,
  `payhead_name` varchar(255) NOT NULL,
  `pay_amount` float(11,2) NOT NULL,
  `earning_total` float(11,2) NOT NULL,
  `deduction_total` float(11,2) NOT NULL,
  `net_salary` float(11,2) NOT NULL,
  `pay_type` enum('earnings','deductions') NOT NULL,
  `pay_month` varchar(255) NOT NULL,
  `generate_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `cdbl_salaries`
--

INSERT INTO `cdbl_salaries` (`salary_id`, `emp_code`, `payhead_name`, `pay_amount`, `earning_total`, `deduction_total`, `net_salary`, `pay_type`, `pay_month`, `generate_date`) VALUES
(244, 'WY01', 'Basic Salary', 45000.00, 45000.00, 0.00, 45000.00, 'earnings', 'March, 2021', '2021-04-12 13:48:19'),
(245, 'WY03', 'Overtime', 5500.00, 39500.00, 0.00, 39500.00, 'earnings', 'March, 2021', '2021-04-14 16:09:02'),
(246, 'WY03', 'Basic Salary', 34000.00, 39500.00, 0.00, 39500.00, 'earnings', 'March, 2021', '2021-04-14 16:09:02'),
(247, 'WY05', 'Basic Salary', 51500.00, 58000.00, 5510.00, 52490.00, 'earnings', 'April, 2021', '2021-04-14 19:17:13'),
(248, 'WY05', 'Overtime', 6500.00, 58000.00, 5510.00, 52490.00, 'earnings', 'April, 2021', '2021-04-14 19:17:13'),
(249, 'WY05', 'Income Tax', 5510.00, 58000.00, 5510.00, 52490.00, 'deductions', 'April, 2021', '2021-04-14 19:17:14'),
(250, 'WY04', 'Basic Salary', 39000.00, 44600.00, 4250.00, 40350.00, 'earnings', 'April, 2021', '2021-04-14 19:22:25'),
(251, 'WY04', 'Overtime', 5600.00, 44600.00, 4250.00, 40350.00, 'earnings', 'April, 2021', '2021-04-14 19:22:25'),
(252, 'WY04', 'Income Tax', 4250.00, 44600.00, 4250.00, 40350.00, 'deductions', 'April, 2021', '2021-04-14 19:22:25'),
(253, 'cdbl01', 'Basic Salary', 19550.00, 30304.00, 3722.00, 26582.00, 'earnings', 'April, 2025', '2025-04-06 21:26:09'),
(254, 'cdbl01', 'House Rent Allowance', 8798.00, 30304.00, 3722.00, 26582.00, 'earnings', 'April, 2025', '2025-04-06 21:26:09'),
(255, 'cdbl01', 'Conveyance Allowance', 978.00, 30304.00, 3722.00, 26582.00, 'earnings', 'April, 2025', '2025-04-06 21:26:09'),
(256, 'cdbl01', 'Medical Allowance', 978.00, 30304.00, 3722.00, 26582.00, 'earnings', 'April, 2025', '2025-04-06 21:26:09'),
(257, 'cdbl01', 'Income Tax', 417.00, 30304.00, 3722.00, 26582.00, 'deductions', 'April, 2025', '2025-04-06 21:26:09'),
(258, 'cdbl01', 'Employee Provident Fund', 1955.00, 30304.00, 3722.00, 26582.00, 'deductions', 'April, 2025', '2025-04-06 21:26:09'),
(259, 'cdbl01', 'Other Deductions', 1350.00, 30304.00, 3722.00, 26582.00, 'deductions', 'April, 2025', '2025-04-06 21:26:09');

-- --------------------------------------------------------

--
-- Table structure for table `loan_categories`
--

CREATE TABLE `loan_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_certificates`
--

CREATE TABLE `loan_certificates` (
  `certificate_id` int(11) NOT NULL,
  `loan_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `certificate_date` date DEFAULT NULL,
  `outstanding_balance` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_history`
--

CREATE TABLE `loan_history` (
  `history_id` int(11) NOT NULL,
  `loan_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loan_requests`
--

CREATE TABLE `loan_requests` (
  `loan_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `loan_amount` decimal(10,2) DEFAULT NULL,
  `installment` decimal(10,2) DEFAULT NULL,
  `loan_payment_amount` decimal(10,2) DEFAULT NULL,
  `interest_amount` decimal(10,2) DEFAULT NULL,
  `loan_status` enum('pending','approved','completed') DEFAULT 'pending',
  `loan_approved_date` date DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `performance_bonus_deduction` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pf_balance`
--

CREATE TABLE `pf_balance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `month` date DEFAULT NULL,
  `employee_contribution` decimal(10,2) DEFAULT NULL,
  `company_contribution` decimal(10,2) DEFAULT NULL,
  `total_contribution` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `pf_balance`
--

INSERT INTO `pf_balance` (`id`, `employee_id`, `month`, `employee_contribution`, `company_contribution`, `total_contribution`, `created_at`) VALUES
(1, 89, '0000-00-00', 1700.00, 1700.00, 112200.00, '2025-04-20 11:17:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cdbl_admin`
--
ALTER TABLE `cdbl_admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_email` (`admin_email`),
  ADD UNIQUE KEY `admin_code` (`admin_code`);

--
-- Indexes for table `cdbl_attendance`
--
ALTER TABLE `cdbl_attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `emp_code` (`emp_code`);

--
-- Indexes for table `cdbl_employees`
--
ALTER TABLE `cdbl_employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `emp_code` (`emp_code`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobile` (`mobile`),
  ADD UNIQUE KEY `national_id` (`national_id`);

--
-- Indexes for table `cdbl_holidays`
--
ALTER TABLE `cdbl_holidays`
  ADD PRIMARY KEY (`holiday_id`);

--
-- Indexes for table `cdbl_leaves`
--
ALTER TABLE `cdbl_leaves`
  ADD PRIMARY KEY (`leave_id`);

--
-- Indexes for table `cdbl_payheads`
--
ALTER TABLE `cdbl_payheads`
  ADD PRIMARY KEY (`payhead_id`);

--
-- Indexes for table `cdbl_payscale_grade`
--
ALTER TABLE `cdbl_payscale_grade`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cdbl_pay_structure`
--
ALTER TABLE `cdbl_pay_structure`
  ADD PRIMARY KEY (`salary_id`),
  ADD KEY `emp_code` (`emp_code`),
  ADD KEY `payhead_id` (`payhead_id`);

--
-- Indexes for table `cdbl_salaries`
--
ALTER TABLE `cdbl_salaries`
  ADD PRIMARY KEY (`salary_id`),
  ADD KEY `emp_code` (`emp_code`);

--
-- Indexes for table `loan_categories`
--
ALTER TABLE `loan_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `loan_certificates`
--
ALTER TABLE `loan_certificates`
  ADD PRIMARY KEY (`certificate_id`),
  ADD KEY `loan_id` (`loan_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `loan_history`
--
ALTER TABLE `loan_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `loan_id` (`loan_id`);

--
-- Indexes for table `loan_requests`
--
ALTER TABLE `loan_requests`
  ADD PRIMARY KEY (`loan_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `pf_balance`
--
ALTER TABLE `pf_balance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cdbl_admin`
--
ALTER TABLE `cdbl_admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cdbl_attendance`
--
ALTER TABLE `cdbl_attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `cdbl_holidays`
--
ALTER TABLE `cdbl_holidays`
  MODIFY `holiday_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `cdbl_leaves`
--
ALTER TABLE `cdbl_leaves`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cdbl_payheads`
--
ALTER TABLE `cdbl_payheads`
  MODIFY `payhead_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `cdbl_payscale_grade`
--
ALTER TABLE `cdbl_payscale_grade`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cdbl_pay_structure`
--
ALTER TABLE `cdbl_pay_structure`
  MODIFY `salary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=290;

--
-- AUTO_INCREMENT for table `cdbl_salaries`
--
ALTER TABLE `cdbl_salaries`
  MODIFY `salary_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT for table `loan_categories`
--
ALTER TABLE `loan_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_certificates`
--
ALTER TABLE `loan_certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_history`
--
ALTER TABLE `loan_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loan_requests`
--
ALTER TABLE `loan_requests`
  MODIFY `loan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pf_balance`
--
ALTER TABLE `pf_balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `loan_certificates`
--
ALTER TABLE `loan_certificates`
  ADD CONSTRAINT `loan_certificates_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loan_requests` (`loan_id`),
  ADD CONSTRAINT `loan_certificates_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `cdbl_employees` (`employee_id`);

--
-- Constraints for table `loan_history`
--
ALTER TABLE `loan_history`
  ADD CONSTRAINT `loan_history_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loan_requests` (`loan_id`);

--
-- Constraints for table `loan_requests`
--
ALTER TABLE `loan_requests`
  ADD CONSTRAINT `loan_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `cdbl_employees` (`employee_id`),
  ADD CONSTRAINT `loan_requests_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `loan_categories` (`category_id`);

--
-- Constraints for table `pf_balance`
--
ALTER TABLE `pf_balance`
  ADD CONSTRAINT `pf_balance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `cdbl_employees` (`employee_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
