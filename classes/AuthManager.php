<?php
require_once __DIR__ . '/../strani/dbh.stran.php';

/**
 * Razred za upravljanje avtentikacije uporabnikov
 * Združuje funkcionalnosti za prijavo, odjavo in zapominjanje uporabniške seje
 */
class AuthManager extends Dbh {
    /**
     * Preveri uporabniške podatke in izvede prijavo
     * 
     * @param string $uid Uporabniško ime ali email
     * @param string $pwd Geslo
     * @return array Podatki o uporabniku
     * @throws Exception Če prijava ni uspešna
     */
    public function login($uid, $pwd) {
        // Pridobi podatke o uporabniku
        $stmt = $this->connect()->prepare('SELECT users_pwd, users_id, users_uid FROM users WHERE users_uid = ? OR users_email = ?;');
        
        if (!$stmt->execute(array($uid, $uid))) {
            throw new Exception("Napaka pri preverjanju podatkov. Poskusite znova.");
        }
        
        if ($stmt->rowCount() == 0) {
            throw new Exception("Uporabnik ne obstaja.");
        }
        
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Preveri geslo
        if (!password_verify($pwd, $userData["users_pwd"])) {
            throw new Exception("Napačno geslo.");
        }
        
        // Vrni podatke o uporabniku
        return [
            'id' => $userData["users_id"],
            'username' => $userData["users_uid"]
        ];
    }
    
    /**
     * Nastavi in shrani remember me žeton
     * 
     * @param int $userId ID uporabnika
     * @return string Ustvarjeni žeton
     */
    public function setRememberToken($userId) {
        $token = bin2hex(random_bytes(32));
        
        $stmt = $this->connect()->prepare('UPDATE users SET remember_token = ? WHERE users_id = ?');
        $stmt->execute([$token, $userId]);
        
        return $token;
    }
    
    /**
     * Preveri veljavnost remember me žetona
     * 
     * @param int $userId ID uporabnika
     * @param string $token Žeton za preverjanje
     * @return array|null Podatki o uporabniku ali null, če žeton ni veljaven
     */
    public function checkRememberToken($userId, $token) {
        $stmt = $this->connect()->prepare('SELECT * FROM users WHERE users_id = ? AND remember_token = ?');
        $stmt->execute([$userId, $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Odstrani remember me žeton
     * 
     * @param int $userId ID uporabnika
     * @return bool True ob uspešni odstranitvi
     */
    public function clearRememberToken($userId) {
        $stmt = $this->connect()->prepare('UPDATE users SET remember_token = NULL WHERE users_id = ?');
        return $stmt->execute([$userId]);
    }
    
    /**
     * Registracija novega uporabnika
     * 
     * @param string $uid Uporabniško ime
     * @param string $pwd Geslo
     * @param string $email Email
     * @return bool True ob uspešni registraciji
     * @throws Exception Če pride do napake
     */
    public function register($uid, $pwd, $email) {
        // Preveri, če uporabnik že obstaja
        if (!$this->isUsernameFree($uid, $email)) {
            throw new Exception("Uporabniško ime ali email je že v uporabi");
        }
        
        // Zgoščevanje gesla
        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
        
        // Vstavljanje uporabnika
        $stmt = $this->connect()->prepare('INSERT INTO users (users_uid, users_pwd, users_email) VALUES (?,?,?);');
        if (!$stmt->execute(array($uid, $hashedPwd, $email))) {
            throw new Exception("Napaka pri registraciji. Poskusite znova.");
        }
        
        return true;
    }
    
    /**
     * Preveri, ali sta uporabniško ime in email prosta
     * 
     * @param string $uid Uporabniško ime
     * @param string $email Email
     * @return bool True, če sta uporabniško ime in email prosta
     */
    private function isUsernameFree($uid, $email) {
        $stmt = $this->connect()->prepare('SELECT users_uid FROM users WHERE users_uid = ? OR users_email = ?;');
        $stmt->execute(array($uid, $email));
        return $stmt->rowCount() === 0;
    }
}
?>
