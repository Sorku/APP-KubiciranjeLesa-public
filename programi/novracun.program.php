<?php
/**
 * Program za upravljanje računov
 * Omogoča dodajanje novih računov in brisanje obstoječih
 */
session_start();

// Preveri, ali je uporabnik prijavljen
if (!isset($_SESSION['userid'])) {
    header("Location: ../index.php");
    exit();
}

require_once '../strani/dbh.stran.php';
require_once '../classes/RacunManager.php';

// Ustvarimo upravitelja računov
$manager = new RacunManager();

// Obdelava obrazca za nov račun
if (isset($_POST['submit'])) {
    // Pridobivanje podatkov iz obrazca
    $racun = $_POST['racun'];  // Ime računa
    $userid = $_SESSION['userid'];  // ID trenutno prijavljenega uporabnika
    
    if ($manager->addRacun($racun, $userid)) {
        // Preusmeritev ob uspešnem dodajanju
        header("Location: ../novracun.php?success=added");
    } else {
        // Preusmeritev ob napaki
        header("Location: ../novracun.php?error=addfailed");
    }
    exit();
}

// Obdelava zahteve za brisanje računa
if (isset($_GET['izbrisi'])) {
    // Pretvorba ID-ja v celo število za varnost
    $racun_id = (int)$_GET['izbrisi'];
    $userid = (int)$_SESSION['userid'];
    
    if ($manager->deleteRacun($racun_id, $userid)) {
        // Preusmeritev ob uspešnem brisanju
        header("Location: ../novracun.php?success=deleted");
    } else {
        // Preusmeritev ob napaki
        header("Location: ../novracun.php?error=deletefailed");
    }
    exit();
}

// Če ni nobene od zgornjih akcij, preusmeri nazaj na glavno stran
header("Location: ../novracun.php");
exit();
?>