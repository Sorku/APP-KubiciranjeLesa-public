<?php
session_start();
require_once '../strani/dbh.stran.php';
require_once '../classes/AuthManager.php';

// Preverimo če je bil obrazec poslan
if(isset($_POST["submit"])) {
    try {
        // Pridobimo podatke
        $uid = $_POST["uid"];
        $pwd = $_POST["pwd"];
        $pwdRepeat = $_POST["pwdrepeat"];
        $email = $_POST["email"];
        
        // Validacija podatkov
        if(empty($uid) || empty($pwd) || empty($pwdRepeat) || empty($email)) {
            throw new Exception("Prosim izpolnite vsa polja");
        }
        
        if(!preg_match("/^[a-zA-Z0-9]*$/", $uid)) {
            throw new Exception("Uporabniško ime lahko vsebuje samo črke in številke");
        }
        
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Neveljaven e-poštni naslov");
        }
        
        if($pwd !== $pwdRepeat) {
            throw new Exception("Gesli se ne ujemata");
        }
        
        // Registracija uporabnika
        $authManager = new AuthManager();
        $authManager->register($uid, $pwd, $email);
        
        // Preusmeritev v primeru uspeha
        header("location:../index.php?success=registered");
        exit();
    } catch (Exception $e) {
        // V primeru napake preusmerimo z ustreznim sporočilom
        header("location:../index.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Če uporabnik ni prišel preko obrazca, ga preusmerimo nazaj na začetno stran
    header("location:../index.php");
    exit();
}
?>