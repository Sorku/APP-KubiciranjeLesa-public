<?php
/**
 * Razred SignUp - Skrbi za dodajanje in preverjanje uporabnikov v bazi podatkov
 * Razširja osnovni razred Dbh za povezavo z bazo
 */
class SignUp extends Dbh {
    /**
     * Metoda za dodajanje novega uporabnika v bazo
     * 
     * @param string $uid Uporabniško ime
     * @param string $pwd Geslo v čistopisu
     * @param string $email Email uporabnika
     * @return void
     */
    protected function setUser($uid, $pwd, $email) {
        // Priprava SQL stavka za vstavljanje uporabnika
        $stmt = $this->connect()->prepare('INSERT INTO users (users_uid, users_pwd, users_email) VALUES (?,?,?);');

        // Zgoščevanje gesla pred shranjevanjem za varnost
        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);

        // Izvedba SQL stavka in preverjanje uspešnosti
        if(!$stmt->execute(array($uid, $hashedPwd, $email))) {
            echo "Napaka pri izvajanju SQL poizvedbe.";
            $stmt = null;
            header("location:../index.php?error=stmtfailed");
            exit();
        }
        $stmt = null;
    }

    /**
     * Metoda za preverjanje, ali uporabnik z danim uporabniškim imenom ali emailom že obstaja
     * 
     * @param string $uid Uporabniško ime za preverjanje
     * @param string $email Email za preverjanje
     * @return bool True če uporabnik ne obstaja, False če že obstaja
     */
    protected function checkUser($uid, $email) {
        // Priprava SQL stavka za preverjanje obstoječega uporabnika
        $stmt = $this->connect()->prepare('SELECT users_uid FROM users WHERE users_uid = ? OR users_email = ?;');

        // Preverjanje uspešnosti izvedbe poizvedbe
        if(!$stmt->execute(array($uid, $email))) {
            $stmt = null;
            header("location:../index.php?error=stmtfailed");
            exit();
        }

        // Preverjanje rezultatov
        $resultCheck = false;
        if($stmt->rowCount() > 0) {
            $resultCheck = false; // Uporabnik že obstaja
        }
        else {
            $resultCheck = true; // Uporabnik še ne obstaja
        }

        return $resultCheck;
    }
}
?>