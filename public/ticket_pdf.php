<?php
session_start();
require_once '../src/tripsearch.php';
require_once '../src/helper.php';
require('fpdf.php');
requireLogin();
if (isset($_SESSION['user_id'])) {
    
    

    $pdf = new FPDF('P','mm','A4');
    $pdf->AddPage();
    $pdf->AddFont('DejaVu','','DejaVuSans.php');
    $pdf->SetFont('DejaVu','',14);
    

    $passenger = iconv("UTF-8", "CP1250", $_POST['full_name']);
    $from      = iconv("UTF-8", "CP1250", $_POST['departure_city']);
    $to        = $_POST['destination_city'];
    $company   = iconv("UTF-8", "CP1250", $_POST['name']);
    $status    = iconv("UTF-8", "CP1250", $_POST['status'] ?? 'Pending');
    $date = $_POST['date'];
    $time = $_POST['time'];

    $yolcu  = iconv("UTF-8", "CP1250", 'Yolcu ismi');
    $baş    = iconv("UTF-8", "CP1250", 'Başlangiç');
    $bit    = iconv("UTF-8", "CP1250", 'Bitiş');
    $şir    = iconv("UTF-8", "CP1250", 'Şirket');

    $pdf->Cell(0,10,"Bus Ticket",0,1,'C');
    $pdf->Ln(5);

    $pdf->Rect(10, 25, 190, 80);


    $pdf->SetFont('DejaVu','',12);


    $pdf->Cell(40,10,$yolcu,0,0);
    $pdf->Cell(0,10,$passenger,0,1);


    $pdf->Cell(40,10,$baş,0,0);
    $pdf->Cell(0,10,$from,0,1);

    $pdf->Cell(40,10,$bit,0,0);
    $pdf->Cell(0,10,$to,0,1);


    $pdf->Cell(40,10,"Tarih:",0,0);
    $pdf->Cell(0,10,$date,0,1);

    $pdf->Cell(40,10,"Saat:",0,0);
    $pdf->Cell(0,10,$time,0,1);

    $pdf->Cell(40,10,"Durum:",0,0);
    $pdf->Cell(0,10,$status,0,1);

    $pdf->Ln(5);
    $pdf->SetFont('DejaVu','',10);
    $pdf->Cell(0,10,"$şir: $company",0,1,'C');


    $pdf->Output('I', 'ticket.pdf');
}