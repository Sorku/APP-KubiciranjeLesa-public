<?php
/**
 * Dbh (Database handler) razred - Osnovni razred za povezavo z bazo podatkov
 * Uporablja PDO za varno povezovanje z MySQL bazo
 */
class Dbh {
    /**
     * Zaščitena metoda za vzpostavitev povezave z bazo podatkov
     * Uporablja se v vseh razredih, ki razširjajo ta razred
     * 
     * @return PDO Objekt za povezavo z bazo podatkov
     * @throws PDOException V primeru napake pri povezavi
     */
    protected function connect() {
        try {
            // Naloži okoljske spremenljivke iz .env datoteke
            if (file_exists(__DIR__ . '/../.env')) {
                $env = parse_ini_file(__DIR__ . '/../.env');
                $username = $env['DB_USER'];
                $pasword = $env['DB_PASS'];
                $host = $env['DB_HOST'];
                $dbname = $env['DB_NAME'];
            } else {
                throw new Exception("Datoteka .env ne obstaja. Preimenujte .env.example v .env in nastavite podatke za povezavo.");
            }
            
            // Vzpostavitev PDO povezave z MySQL bazo
            $dbh = new PDO("mysql:host=$host;dbname=$dbname", $username, $pasword);
            return $dbh;
        } 
        catch (PDOException $e) {
            // V primeru napake izpiši sporočilo in prekini izvajanje
            print "NAPAKA PRI POVEZAVI Z BAZO: " . $e->getMessage() . "<br/>";
            die();
        }
        catch (Exception $e) {
            // V primeru napake z .env datoteko
            print "NAPAKA: " . $e->getMessage() . "<br/>";
            die();
        }
    }
}
?>