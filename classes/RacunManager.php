<?php
class RacunManager extends Dbh {
    /**
     * Metoda za pridobivanje vseh računov določenega uporabnika
     * 
     * @param int $userid ID uporabnika
     * @return array Seznam računov
     */
    public function pridobiRacune($userid) {
        // Namesto globalnega $conn uporabimo PDO povezavo iz nadrazreda
        $pdo = $this->connect();
        
        // Uporabimo pravilno ime tabele 'novracun'
        $stmt = $pdo->prepare('SELECT * FROM novracun WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$userid]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Pridobi podatke o specifičnem računu
     *
     * @param int $racunId ID računa
     * @return array|null Podatki o računu ali null, če račun ne obstaja
     */
    public function pridobiRacunPodatke($racunId) {
        $stmt = $this->connect()->prepare('SELECT * FROM novracun WHERE racun_id = ?');
        $stmt->execute([$racunId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : null;
    }
    
    /**
     * Doda nov račun v bazo
     * 
     * @param string $racun Ime računa
     * @param int $userid ID uporabnika
     * @return bool Ali je operacija uspela
     */
    public function addRacun($racun, $userid) {
        // Namesto globalnega $conn uporabimo PDO povezavo iz nadrazreda
        $pdo = $this->connect();
        
        // Uporabimo pravilno ime tabele 'novracun' in dodamo created_at
        $stmt = $pdo->prepare('INSERT INTO novracun (racun, user_id, created_at) VALUES (?, ?, NOW())');
        
        return $stmt->execute([$racun, $userid]);
    }

    /**
     * Metoda za brisanje računa in povezanih izračunov
     * 
     * @param int $racun_id ID računa za brisanje
     * @param int $userid ID uporabnika, ki briše račun
     * @return bool True ob uspešnem brisanju, False ob napaki ali nepravilnih pravicah
     */
    public function deleteRacun($racun_id, $userid) {
        try {
            $pdo = $this->connect();
            
            // Najprej preverimo lastništvo računa
            $stmt = $pdo->prepare("SELECT user_id FROM novracun WHERE racun_id = ?");
            $stmt->execute([$racun_id]);
            $racun = $stmt->fetch();
            
            if (!$racun || $racun['user_id'] != $userid) {
                return false;
            }
            
            // Najprej izbrišemo vse povezane izračune (kaskadno brisanje)
            $stmt = $pdo->prepare("DELETE FROM izracun WHERE racun_id = ?");
            $stmt->execute([$racun_id]);
            
            // Nato izbrišemo še račun
            $stmt = $pdo->prepare("DELETE FROM novracun WHERE racun_id = ?");
            $stmt->execute([$racun_id]);
            
            return true;
        } catch (PDOException $e) {
            // Beleženje napake v dnevnik
            error_log("Napaka pri brisanju: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Funkcija za brisanje izračuna
     * 
     * @param int $izracun_id ID izračuna za brisanje
     * @param int $userid ID uporabnika, ki briše izračun
     * @return bool True ob uspešnem brisanju, False ob napaki
     */
    public function deleteIzracun($izracun_id, $userid) {
        try {
            $pdo = $this->connect();
            
            // Preverimo lastništvo izračuna
            $stmt = $pdo->prepare("SELECT user_id FROM izracun WHERE izracun_id = ?");
            $stmt->execute([$izracun_id]);
            $izracun = $stmt->fetch();
            
            if (!$izracun || $izracun['user_id'] != $userid) {
                return false;
            }
            
            // Izbrišemo izračun
            $stmt = $pdo->prepare("DELETE FROM izracun WHERE izracun_id = ?");
            return $stmt->execute([$izracun_id]);
        } catch (PDOException $e) {
            error_log("Napaka pri brisanju izračuna: " . $e->getMessage());
            return false;
        }
    }
}
?>