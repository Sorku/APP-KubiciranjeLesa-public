<?php
 /**
 * SignUpContr class - Controller za prijavo uporabnikov
 * Upravlja postopek registracije, preverjanje vhodnih podatkov in validacijo
 * Razširja osnovni razred SignUp za interakcijo z bazo
 */
class SignUpContr extends SignUp{
    // Zasebne spremenljivke za hranjenje uporabniških podatkov
    private $uid;
    private $pwd;
    private $pwdRepeat;
    private $email;

    /**
     * Konstruktor za inicializacijo podatkov uporabnika
     * 
     * @param string $uid Uporabniško ime
     * @param string $pwd Geslo
     * @param string $pwdRepeat Ponovljeno geslo za preverjanje
     * @param string $email E-poštni naslov
     */
    public function __construct($uid, $pwd, $pwdRepeat, $email) {
        $this->uid = $uid;
        $this->pwd = $pwd;
        $this->pwdRepeat = $pwdRepeat;
        $this->email = $email;
    }

    /**
     * Glavna metoda za registracijo uporabnika
     * Izvede vse potrebne validacije in registrira uporabnika, če so vsi pogoji izpolnjeni
     * 
     * @return void
     * @throws Exception Če pride do napake pri validaciji
     */
    public function signupUser() {
        // Preverjanje praznih vnosnih polj
        if(!$this->emtyInput()){
            throw new Exception("Prosim izpolnite vsa polja");
        }
        
        // Preverjanje veljavnosti uporabniškega imena
        if(!$this->invalidUid()){
            throw new Exception("Uporabniško ime lahko vsebuje samo črke in številke");
        }
        
        // Preverjanje veljavnosti e-poštnega naslova
        if(!$this->invalidEmail()){
            throw new Exception("Neveljaven e-poštni naslov");
        }
        
        // Preverjanje ujemanja gesel
        if(!$this->pwdMatch()){
            throw new Exception("Gesli se ne ujemata");
        }
        
        // Preverjanje ali uporabnik že obstaja
        if(!$this->uidTakenCheck()){
            throw new Exception("Uporabniško ime ali e-pošta je že v uporabi");
        }

        // Registracija uporabnika v bazo
        $this->setUser($this->uid, $this->pwd, $this->email);
    }

    /**
     * Metoda za preverjanje praznih vnosnih polj
     * 
     * @return bool True če so vsa polja izpolnjena, False sicer
     */
    private function emtyInput() {
        return !empty($this->uid) && !empty($this->pwd) && 
               !empty($this->pwdRepeat) && !empty($this->email);
    }

    /**
     * Metoda za preverjanje veljavnosti uporabniškega imena
     * Uporabniško ime lahko vsebuje samo črke in številke
     * 
     * @return bool True če je uporabniško ime veljavno, False sicer
     */
    private function invalidUid(){
        return preg_match("/^[a-zA-Z0-9]*$/", $this->uid);
    }

    /**
     * Metoda za preverjanje veljavnosti e-poštnega naslova
     * 
     * @return bool True če je e-poštni naslov veljaven, False sicer
     */
    private function invalidEmail(){
        return filter_var($this->email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Metoda za preverjanje ujemanja gesel
     * 
     * @return bool True če se gesli ujemata, False sicer
     */
    private function pwdMatch(){
        return $this->pwd === $this->pwdRepeat;
    }

    /**
     * Metoda za preverjanje, ali uporabniško ime ali e-poštni naslov že obstajata
     * 
     * @return bool True če uporabnik še ne obstaja, False sicer
     */
    private function uidTakenCheck(){
        return $this->checkUser($this->uid, $this->email);
    }
}
?>