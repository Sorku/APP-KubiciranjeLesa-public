<?php
session_start();
require_once '../strani/dbh.stran.php';
require_once '../classes/AuthManager.php';

if(isset($_POST["submit"])) {
    try {
        // Pridobivanje podatkov
        $uid = $_POST["uid"];
        $pwd = $_POST["pwd"];
        $remember = isset($_POST["remember"]) ? true : false;
        
        // Inicializacija upravitelja za avtentikacijo
        $authManager = new AuthManager();
        
        // Poskusi prijaviti uporabnika
        $user = $authManager->login($uid, $pwd);
        
        // Nastavi spremenljivke seje
        $_SESSION["userid"] = $user['id'];
        $_SESSION["useruid"] = $user['username'];
        
        // Če je označeno "zapomni si me", nastavi piškotek
        if($remember) {
            // Ustvari žeton
            $token = $authManager->setRememberToken($_SESSION["userid"]);
            
            // Nastavi piškotek (velja 30 dni)
            $cookieData = json_encode([
                'userid' => $_SESSION["userid"],
                'token' => $token
            ]);
            
            setcookie('rememberme', $cookieData, time() + (86400 * 30), '/', '', false, false);
        }
        
        // Preusmeri na nadzorno ploščo
        header("Location: ../novracun.php");
        exit();
    } catch (Exception $e) {
        // V primeru napake preusmeri z ustreznim sporočilom
        header("Location: ../index.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Če uporabnik ni prišel preko obrazca
    header("Location: ../index.php");
    exit();
}
?>