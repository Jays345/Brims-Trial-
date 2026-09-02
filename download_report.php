<?php
// download_report.php
include 'db_connect.php';
$format = $_GET['format'] ?? 'csv';
$period = $_GET['period'] ?? 'monthly';

// Use same logic as reports_data.php to get $salesData
ob_start();
include 'reports_data.php'; // reuses the data
$data = ob_get_clean();
$data = json_decode($data,true);

if($format=='csv'){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="report_'.$period.'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out,['Product','Quantity Sold','Revenue','Profit']);
    foreach($data['sales'] as $row){
        fputcsv($out,[$row['product'],$row['quantity'],$row['revenue'],$row['profit']]);
    }
    fclose($out);
    exit;
}

// PDF generation using FPDF
if($format=='pdf'){
    require('fpdf/fpdf.php'); // Make sure FPDF library exists
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,10,"Report ($period)",0,1,'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial','',12);
    $pdf->Cell(0,8,"Total Revenue: $".$data['totalRevenue'],0,1);
    $pdf->Cell(0,8,"Total Expenses: $".$data['totalExpenses'],0,1);
    $pdf->Cell(0,8,"Total Profit: $".$data['totalProfit'],0,1);
    $pdf->Ln(5);
    $pdf->Cell(0,8,"Most Profitable: ".$data['mostProfitable'],0,1);
    $pdf->Cell(0,8,"Least Profitable: ".$data['leastProfitable'],0,1);
    $pdf->Ln(5);
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(60,8,'Product',1);
    $pdf->Cell(30,8,'Qty',1);
    $pdf->Cell(40,8,'Revenue',1);
    $pdf->Cell(40,8,'Profit',1);
    $pdf->Ln();
    $pdf->SetFont('Arial','',12);
    foreach($data['sales'] as $row){
        $pdf->Cell(60,8,$row['product'],1);
        $pdf->Cell(30,8,$row['quantity'],1);
        $pdf->Cell(40,8,'$'.$row['revenue'],1);
        $pdf->Cell(40,8,'$'.$row['profit'],1);
        $pdf->Ln();
    }
    $pdf->Output("D","report_$period.pdf");
    exit;
}
