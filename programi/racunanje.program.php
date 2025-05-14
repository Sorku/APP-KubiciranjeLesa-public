<?php
/**
 * Program za obdelavo izračuna kubature lesa
 */
session_start();

// Preveri, ali je uporabnik prijavljen
if (!isset($_SESSION['userid'])) {
    header("Location: ../index.php");
    exit();
}

// Preveri, ali je izbran račun
if (!isset($_SESSION['racun_id'])) {
    header("Location: ../novracun.php?error=noracun");
    exit();
}

require_once '../strani/dbh.stran.php';
require_once '../classes/IzracunManager.php';

// Če je bil poslan obrazec
if (isset($_POST['submit'])) {
    // Validiraj vhodne podatke
    $dolzina = isset($_POST['dolzina']) ? floatval($_POST['dolzina']) : 0;
    $premer = isset($_POST['premer']) ? floatval($_POST['premer']) : 0;
    $kolicina = isset($_POST['kolicina']) ? intval($_POST['kolicina']) : 0;
    $vrsta_lesa = isset($_POST['vrsta_lesa']) ? $_POST['vrsta_lesa'] : '';
    $racun_id = isset($_POST['racun_id']) ? intval($_POST['racun_id']) : $_SESSION['racun_id'];
    
    // Dodatna validacija - preveri omejitve dolžine in premera
    if ($dolzina < 3 || $dolzina > 10 || $premer < 8 || $premer > 100 || $kolicina <= 0 || empty($vrsta_lesa)) {
        $error_msg = "Neveljavni podatki. Dolžina mora biti med 0.5m in 30m, premer med 10cm in 120cm.";
        header("Location: ../racunanje.php?error=invalidinput&msg=" . urlencode($error_msg));
        exit();
    }
    
    try {
        // Inicializiraj upravitelja izračunov
        $izracunManager = new IzracunManager();
        
        // Izvedi izračun in shranjevanje
        $rezultat = $izracunManager->izracunajInShrani(
            $racun_id, $dolzina, $premer, $kolicina, $vrsta_lesa
        );
        
        // Preusmeri z rezultati
        header("Location: ../racunanje.php?rezultat={$rezultat['volumen']}&rezultat_brez_lubja={$rezultat['volumenBrezLubja']}&success=saved");
        exit();
    } catch (Exception $e) {
        // Beleži napako
        error_log('Napaka pri izračunu: ' . $e->getMessage());
        
        // Preveri, če gre za napako "ni najdenih vrednosti"
        if (strpos($e->getMessage(), 'V bazi ni najdenih vrednosti') !== false) {
            // Posebna obravnava za napako, ko ni najdenih vrednosti v tabeli
            header("Location: ../racunanje.php?error=noparameters&msg=" . urlencode($e->getMessage()));
        } else {
            // Splošna napaka pri shranjevanju
            header("Location: ../racunanje.php?error=savefailed&msg=" . urlencode($e->getMessage()));
        }
        exit();
    }
}

// Če ni bilo poslano preko obrazca
header("Location: ../racunanje.php");
exit();
?>