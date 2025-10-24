<?php
require('fpdf.php');
session_start();


$pdf = new FPDF();
$pdf->AddPage();
$pdf->AddFont('DejaVu','','DejaVuSans.php');
$pdf->SetFont('DejaVu','',14);


$full_name = iconv("UTF-8", "CP1250", $_SESSION['full_name']);
$email     = $_SESSION['email'] ?? 'user@example.com';
$balance   = $_SESSION['balance'] ?? 0.0;

$tickets = $_POST['tickets'] ?? [];

$pdf->SetFont('DejaVu','',16);
$pdf->Cell(0,10,"Kullanici Profili",0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('DejaVu','',12);
$pdf->Cell(40,10,"Isim:",0,0);
$pdf->Cell(0,10,$full_name,0,1);

$pdf->Cell(40,10,"Email:",0,0);
$pdf->Cell(0,10,$email,0,1);

$pdf->Cell(40,10,"Bakiye:",0,0);
$pdf->Cell(0,10,"$".$balance,0,1);

$pdf->Ln(10);

$pdf->SetFont('DejaVu','',12);
$baş = iconv("UTF-8", "CP1250", 'Başlangiç');
$bit = iconv("UTF-8", "CP1250", 'Bitiş');
$pdf->Cell(40,10, $baş ,1,0,'C');
$pdf->Cell(40,10, $bit,1,0,'C');
$pdf->Cell(30,10,'Tarih',1,0,'C');
$pdf->Cell(25,10,'Saat',1,0,'C');
$pdf->Cell(35,10,'Durum',1,1,'C');


$pdf->SetFont('DejaVu','',12);
foreach ($tickets as $ticket) {
    $pdf->Cell(40,10,$ticket['departure_city'],1,0,'C');
    $pdf->Cell(40,10,$ticket['destination_city'],1,0,'C');
    $pdf->Cell(30,10,$ticket['date'],1,0,'C');
    $pdf->Cell(25,10,$ticket['time'],1,0,'C');
    $pdf->Cell(35,10,$ticket['status'] ?? 'Pending',1,1,'C');
}


$pdf->Output('I', 'profile.pdf');
