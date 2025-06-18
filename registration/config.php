<?php

define('BASE_URL',   'https://www.cdbl.com.bd/payroll/');

define('DB_SERVER', 	'localhost');
define('DB_USER', 		'root');
define('DB_PASSWORD', 'rrr@VAS&cdbl#2004');
define('DB_NAME', 		'payroll_mdb');
define('DB_PREFIX', 	'cdbl_');

error_reporting(1);

date_default_timezone_set("Asia/dhaka");

$db = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
if ( mysqli_connect_errno() ) {
  die("Failed to connect to MySQL: " . mysqli_connect_error());
}

session_start();
