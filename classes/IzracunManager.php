<?php
require_once __DIR__ . '/../strani/dbh.stran.php';

class IzracunManager extends Dbh {
    // Metoda za shranjevanje novega izračuna
    public function shraniIzracun($racun_id, $dolzina, $premer, $kolicina, $izracun, $vrsta_lesa = '', $izracun_brez_lubja = 0) {
        try {
            // Pridobimo uporabniški ID iz seje
            $user_id = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
            
            // Preverjamo, da je uporabniški ID prisoten
            if ($user_id === null) {
                throw new Exception('Uporabnik ni prijavljen');
            }
            
            // Pripravimo SQL stavek
            $sql = "INSERT INTO izracun (racun_id, izracun_dolzina, izracun_premer, izracun_kolicina, izracun_izracun, izracun_vrsta_lesa, izracun_brez_lubja, user_id) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$racun_id, $dolzina, $premer, $kolicina, $izracun, $vrsta_lesa, $izracun_brez_lubja, $user_id];
            
            // Izvedemo SQL stavek
            $stmt = $this->connect()->prepare($sql);
            $success = $stmt->execute($params);
            
            if (!$success) {
                throw new Exception('SQL execution failed: ' . implode(', ', $stmt->errorInfo()));
            }
            
            return true;
            
        } catch (PDOException $e) {
            // Beleženje napake
            error_log("PDO napaka pri shranjevanju izračuna: " . $e->getMessage());
            throw new Exception('Napaka pri shranjevanju v bazo: ' . $e->getMessage());
        }
    }
    
    // Metoda za pridobivanje vseh izračunov za določen račun
    public function pridobiIzracune($racun_id) {
        try {
            $stmt = $this->connect()->prepare("SELECT * FROM izracun WHERE racun_id = ? ORDER BY izracun_id DESC");
            $stmt->execute([$racun_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Beleženje napake
            error_log("Napaka pri pridobivanju izračunov: " . $e->getMessage());
            return [];
        }
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
     * Izračunaj volumen hloda
     * 
     * @param float $dolzina Dolžina hloda v metrih
     * @param float $premer Premer hloda v centimetrih
     * @param int $kolicina Količina kosov
     * @return float Volumen v kubičnih metrih
     * @throws Exception Če ni najdene ustrezne telesne v tabeli
     */
    public function izracunajVolumen($dolzina, $premer, $kolicina) {
        try {
            // Poiščemo točno vrednost v tabeli Telesna
            $stmt = $this->connect()->prepare(
                "SELECT telesna_masa FROM Telesna 
                 WHERE telesna_dolzina = ? AND telesna_premer = ?
                 LIMIT 1"
            );
            $stmt->execute([$dolzina, $premer]);
            $rezultat = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($rezultat) {
                // Če smo našli telesno, jo pomnožimo s količino
                return $rezultat['telesna_masa'] * $kolicina;
            } else {
                // Če točne vrednosti ni, vrnemo napako brez iskanja približne vrednosti
                throw new Exception("V bazi ni najdenih vrednosti za dolžino $dolzina m in premer $premer cm");
            }
        } catch (PDOException $e) {
            error_log("Napaka pri iskanju telesne iz baze: " . $e->getMessage());
            throw new Exception("Napaka pri iskanju v bazi: " . $e->getMessage());
        }
    }
    
    /**
     * Izračunaj premer brez lubja glede na vrsto lesa
     * 
     * @param float $premer Izvorni premer v cm
     * @param string $vrstaLesa Vrsta lesa (smreka, bukev, gaber, hrast, drugo)
     * @return float Premer brez lubja v cm
     */
    public function izracunajPremerBrezLubja($premer, $vrstaLesa) {
        $premerBrezLubja = $premer;
        
        switch ($vrstaLesa) {
            case 'smreka':
                if ($premer >= 10 && $premer <= 38) {
                    $premerBrezLubja = $premer - 1;
                } else if ($premer > 38 && $premer <= 50) {
                    $premerBrezLubja = $premer - 2;
                } else if ($premer > 50) {
                    $premerBrezLubja = $premer - 3;
                }
                break;
            case 'bukev':
                if ($premer >= 10 && $premer <= 50) {
                    $premerBrezLubja = $premer - 1;
                } else if ($premer > 50) {
                    $premerBrezLubja = $premer - 2;
                }
                break;
            case 'gaber':
                if ($premer >= 10 && $premer <= 50) {
                    $premerBrezLubja = $premer - 1;
                } else if ($premer > 50) {
                    $premerBrezLubja = $premer - 2;
                }
                break;
            case 'hrast':
                if ($premer >= 20 && $premer <= 50) {
                    $premerBrezLubja = $premer - 3;
                } else if ($premer > 50) {
                    $premerBrezLubja = $premer - 5;
                }
                break;
        }
        
        // Preprečujemo negativne vrednosti
        return max(0, $premerBrezLubja);
    }
    
    /**
     * Izvedi celoten izračun in shrani v bazo
     * 
     * @param int $racunId ID računa
     * @param float $dolzina Dolžina v metrih
     * @param float $premer Premer v cm
     * @param int $kolicina Količina kosov
     * @param string $vrstaLesa Vrsta lesa
     * @return array Rezultati izračuna (volumen, volumenBrezLubja)
     * @throws Exception Če ni najdenih vrednosti v tabeli Telesna
     */
    public function izracunajInShrani($racunId, $dolzina, $premer, $kolicina, $vrstaLesa) {
        try {
            // Izračunaj osnovni volumen
            $volumen = $this->izracunajVolumen($dolzina, $premer, $kolicina);
            
            // Izračunaj premer brez lubja
            $premerBrezLubja = $this->izracunajPremerBrezLubja($premer, $vrstaLesa);
            
            // Izračunaj volumen brez lubja
            $volumenBrezLubja = $this->izracunajVolumen($dolzina, $premerBrezLubja, $kolicina);
            
            // Zaokrožitev na 2 decimalni mesti
            $volumen = round($volumen, 2);
            $volumenBrezLubja = round($volumenBrezLubja, 2);
            
            // Shrani v bazo
            $this->shraniIzracun($racunId, $dolzina, $premer, $kolicina, $volumen, $vrstaLesa, $volumenBrezLubja);
            
            return [
                'volumen' => $volumen,
                'volumenBrezLubja' => $volumenBrezLubja
            ];
        } catch (Exception $e) {
            // Ponovno vržemo izjemo navzgor, da jo lahko ujamemo v kodi, ki kliče to metodo
            throw $e;
        }
    }
}
?>