<?php
/**
 * Program za brisanje računov in izračunov
 */
session_start();
require_once "../strani/dbh.stran.php";
require_once "../classes/RacunManager.php";

// Preveri, če je uporabnik prijavljen, sicer ga preusmeri na začetno stran
if (!isset($_SESSION['userid'])) {
    header('Location: ../index.php');
    exit();
}

// Ustvari instanco razreda za upravljanje
$manager = new RacunManager();

// Obdelava zahteve za brisanje računa
if (isset($_POST['delete_racun'])) {
    $racun_id = $_POST['racun_id'];
    if ($manager->deleteRacun($racun_id, $_SESSION['userid'])) {
        header('Location: ../novracun.php?success=deleted');
    } else {
        header('Location: ../novracun.php?error=deletefailed');
    }
    exit();
}

// Obdelava zahteve za brisanje izračuna
if (isset($_POST['delete_izracun'])) {
    $izracun_id = $_POST['izracun_id'];
    if ($manager->deleteIzracun($izracun_id, $_SESSION['userid'])) {
        header('Location: ../racunanje.php?success=deleted');
    } else {
        header('Location: ../racunanje.php?error=deletefailed');
    }
    exit();
}

// Če ni nobene od zgornjih akcij, preusmeri nazaj na glavno stran
header('Location: ../index.php');
exit();
?>