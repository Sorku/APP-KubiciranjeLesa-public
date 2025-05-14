<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: ../index.php");
    exit();
}

// Preveri, da je racun izbran
if (!isset($_SESSION['racun_id'])) {
    header("Location: ../novracun.php?error=noracun");
    exit();
}

require_once '../fpdf/fpdf.php';
require_once '../strani/dbh.stran.php';
require_once '../classes/IzracunManager.php';

// Razred za generiranje PDF z vsemi podatki iz tabele
class IzracuniPDF extends FPDF {
    // Glava strani
    function Header() {
        global $racunPodatki;
        
        // Logo
        // $this->Image('logo.png', 10, 6, 30);
        
        // Naslov dokumenta
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Izracun kubature lesa', 0, 1, 'C');
        
        // Podatki o racunu
        if ($racunPodatki) {
            $this->SetFont('Arial', '', 12);
            $this->Cell(0, 10, 'Racun: ' . $racunPodatki['racun'], 0, 1);
            $this->Cell(0, 10, 'Datum: ' . date('d.m.Y'), 0, 1);
            $this->Ln(5);
        }
    }
    
    // Noga strani
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Stran ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
    
    // Funkcija za generiranje tabele izracunov
    function IzracuniTabela($header, $data) {
        // sirine stolpcev
        $w = array(25, 25, 25, 30, 35, 40);
        
        // Glava tabele
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(200, 200, 200);
        for($i=0; $i<count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        $this->Ln();
        
        // Podatki
        $this->SetFont('Arial', '', 10);
        $this->SetFillColor(255, 255, 255);
        
        $skupajKolicina = 0;
        $skupajVolumen = 0;
        $skupajVolumenBrezLubja = 0;
        
        foreach($data as $row) {
            $this->Cell($w[0], 6, number_format($row['izracun_dolzina'], 2) . ' m', 1);
            $this->Cell($w[1], 6, number_format($row['izracun_premer'], 1) . ' cm', 1);
            $this->Cell($w[2], 6, $row['izracun_kolicina'] . ' kos', 1);
            $this->Cell($w[3], 6, number_format($row['izracun_izracun'], 2) . ' m3', 1);
            $this->Cell($w[4], 6, isset($row['izracun_vrsta_lesa']) ? $row['izracun_vrsta_lesa'] : '-', 1);
            $this->Cell($w[5], 6, isset($row['izracun_brez_lubja']) ? number_format($row['izracun_brez_lubja'], 2) . ' m3' : '-', 1);
            $this->Ln();
            
            $skupajKolicina += $row['izracun_kolicina'];
            $skupajVolumen += $row['izracun_izracun'];
            $skupajVolumenBrezLubja += isset($row['izracun_brez_lubja']) ? $row['izracun_brez_lubja'] : 0;
        }
        
        // Vrstica s skupnimi vsotami
        $this->SetFont('Arial', 'B', 10);
        $this->Cell($w[0] + $w[1], 6, 'SKUPAJ:', 1);
        $this->Cell($w[2], 6, $skupajKolicina . ' kos', 1);
        $this->Cell($w[3], 6, number_format($skupajVolumen, 2) . ' m3', 1);
        $this->Cell($w[4], 6, '', 1);
        $this->Cell($w[5], 6, number_format($skupajVolumenBrezLubja, 2) . ' m3', 1);
    }
}

// Pridobi podatke o izracunih in racunu
$izracunManager = new IzracunManager();
$izracuni = $izracunManager->pridobiIzracune($_SESSION['racun_id']);
$racunPodatki = $izracunManager->pridobiRacunPodatke($_SESSION['racun_id']);

// Ustvari PDF
$pdf = new IzracuniPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

// Priprava glave tabele
$header = array('Dolzina', 'Premer', 'Kolicina', 'Volumen', 'Vrsta lesa', 'Volumen brez lubja');

// Izpis tabele z izracuni
$pdf->IzracuniTabela($header, $izracuni);

// Informacije o odbitkih za lubje
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Informacije o odbitkih za lubje:', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Smreka: 1 cm za premere 10-38 cm, 2 cm za premere 38-50 cm, 3 cm za premere nad 50 cm', 0, 1);
$pdf->Cell(0, 6, 'Bukev: 1 cm za premere 10-50 cm, 2 cm za premere nad 50 cm', 0, 1);
$pdf->Cell(0, 6, 'Gaber: 1 cm za premere 10-50 cm, 2 cm za premere nad 50 cm', 0, 1);
$pdf->Cell(0, 6, 'Hrast: 3 cm za premere 20-50 cm, 5 cm za premere nad 50 cm', 0, 1);
$pdf->Cell(0, 6, 'Drugo: Brez odbitka za lubje', 0, 1);

// Izpis PDF
$pdf->Output('Izracuni_kubature_lesa.pdf', 'I');
?>
