<?php
require_once('../config.php'); // Assuming config contains database connection

// Get input data
$emp_code = $_POST['emp_code'] ?? '';
$month_year = $_POST['month_year'] ?? '';

// Validate input
if(empty($emp_code) || empty($month_year)) {
    echo json_encode(['code' => 1, 'result' => 'Employee code and month/year are required']);
    exit;
}

// Create directory structure if not exists
$payslip_dir = "../payslips/$emp_code/$month_year";
if(!file_exists($payslip_dir)) {
    mkdir($payslip_dir, 0777, true);
}

// Generate PDF filename
$pdf_file = "$payslip_dir/$month_year.pdf";

try {
    // Try TCPDF first if available
    if(@class_exists('TCPDF')) {
        require_once('tcpdf/tcpdf.php');
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Payroll System');
        $pdf->SetAuthor('Payroll System');
        $pdf->SetTitle("Payslip $month_year");
    } 
    // Fallback to FPDF
    else {
        require_once('fpdf/fpdf.php');
        $pdf = new FPDF();
    }

    // Generate PDF content
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,"Payslip for $emp_code - $month_year",0,1,'C');
    $pdf->Ln(10);
    
    // Add employee details (placeholder - replace with actual data from DB)
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(40,10,'Employee Name:',0,0);
    $pdf->Cell(0,10,'[Employee Name]',0,1);
    $pdf->Cell(40,10,'Designation:',0,0);
    $pdf->Cell(0,10,'[Designation]',0,1);
    $pdf->Ln(10);
    
    // Add salary breakdown (placeholder)
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(0,10,'Earnings',0,1);
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(100,10,'Basic Salary:',0,0);
    $pdf->Cell(0,10,'[Amount]',0,1,'R');
    
    // Save PDF file
    $pdf->Output($pdf_file, 'F');

    echo json_encode(['code' => 0, 'result' => 'Payslip generated successfully']);
} catch(Exception $e) {
    echo json_encode(['code' => 1, 'result' => 'Error generating payslip: '.$e->getMessage()]);
}
