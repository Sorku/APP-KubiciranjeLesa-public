<?php
session_start();
// Preveri, če je uporabnik prijavljen
if (!isset($_SESSION['userid'])) {
    header("Location: index.php");
    exit();
}
// Preveri, če je bil izbran račun za odpiranje
if (isset($_GET['odpri'])) {
    $_SESSION['racun_id'] = $_GET['odpri'];
}
// Preveri, če je izbran račun
if (!isset($_SESSION['racun_id'])) {
    header("Location: novracun.php?error=noracun");
    exit();
}
// Vključitev potrebnih datotek
require_once 'strani/dbh.stran.php';
require_once 'classes/IzracunManager.php';
// Inicializacija upravitelja izračunov in pridobitev vseh izračunov za trenutni račun
$izracunManager = new IzracunManager();
$izracuni = $izracunManager->pridobiIzracune($_SESSION['racun_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/racunanje.css">
    <title>Računanje</title>
</head>
<body>
    <!-- Navigacija -->
    <nav>
        <a href="novracun.php">← Nazaj na račune</a>
        <form action="programi/logout.program.php" method="post" style="display: inline;">
            <button type="submit" name="submit">LOGOUT</button>
        </form>
    </nav>

    <div class="card fade-in">
        <h2 class="card-title">Kubiciranje lesa</h2>
        
        <!-- Prikaz sporočil o uspehih in napakah -->
        <?php if (isset($_GET['success'])): ?>
            <div class="message success">
                <?php 
                switch($_GET['success']) {
                    case 'deleted':
                        echo "Izračun je bil uspešno izbrisan.";
                        break;
                    case 'emailsent':
                        echo "Email je bil uspešno poslan.";
                        break;
                    default:
                        echo "Operacija je bila uspešna.";
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="message error">
                <?php 
                switch($_GET['error']) {
                    case 'deletefailed':
                        echo "Brisanje izračuna ni uspelo.";
                        break;
                    case 'emailfailed':
                        echo "Pošiljanje emaila ni uspelo.";
                        break;
                    case 'noparameters':
                        echo "Ni najdenih parametrov za izbrano kombinacijo dolžine in premera.";
                        break;
                    case 'noracun':
                        echo "Najprej izberite račun.";
                        break;
                    case 'savefailed':
                        echo "Shranjevanje izračuna ni uspelo.";
                        if (isset($_GET['msg'])) {
                            echo " Napaka: " . htmlspecialchars($_GET['msg']);
                        }
                        break;
                    default:
                        echo "Prišlo je do napake.";
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Obrazec za vnos podatkov za izračun -->
        <div class="measurement-card">
            <h3>Izračun kubature lesa</h3>
            <form action="programi/racunanje.program.php" method="post" class="content-form">
                <input type="hidden" name="racun_id" value="<?php echo $_SESSION['racun_id']; ?>">
                
                <div class="form-group">
                    <label for="dolzina">Dolžina (m):</label>
                    <input type="number" step="0.01" id="dolzina" name="dolzina" min="3" max="10" required>
                    <span class="help-tooltip" data-tip="Vnesite dolžino hloda v metrih (3m - 10m)">?</span>
                    <span class="form-control-help">Veljavne dolžine: od 3m do 10m</span>
                </div>
                
                <div class="form-group">
                    <label for="premer">Premer (cm):</label>
                    <input type="number" step="0.1" id="premer" name="premer" min="8" max="100" required>
                    <span class="help-tooltip" data-tip="Vnesite premer hloda v centimetrih (8cm - 100cm)">?</span>
                    <span class="form-control-help">Veljavni premeri: od 8cm do 100cm</span>
                </div>
                
                <div class="form-group">
                    <label for="kolicina">Količina (kos):</label>
                    <input type="number" id="kolicina" name="kolicina" required>
                    <span class="form-control-help">Vnesite število kosov</span>
                </div>

                <div class="form-group">
                    <label for="vrsta_lesa">Vrsta lesa:</label>
                    <select id="vrsta_lesa" name="vrsta_lesa" required>
                        <option value="">Izberi vrsto lesa</option>
                        <option value="smreka">Smreka</option>
                        <option value="bukev">Bukev</option>
                        <option value="gaber">Gaber</option>
                        <option value="hrast">Hrast</option>
                        <option value="drugo">Ostalo</option>
                    </select>
                    <span class="form-control-help">Izberite vrsto lesa za izračun volumna brez lubja ali "Drugo" če vaša vrsta ni navedena</span>
                </div>

                <div class="calculation-result" id="preview-result">
                    Predogled: <span>0.00</span> m<sup>3</sup>
                </div>
                
                <div class="calculation-result" id="preview-result-brez-lubja">
                    Predogled brez lubja: <span>0.00</span> m<sup>3</sup>
                </div>
                
                <button type="submit" name="submit">Izračunaj in shrani</button>
            </form>
        </div>

        <!-- Prikaz rezultata izračuna -->
        <?php if (isset($_GET['rezultat'])): ?>
            <div class="calculation-result">
                <h3>Rezultat izračuna: <?php echo htmlspecialchars($_GET['rezultat']); ?> m<sup>3</sup></h3>
                <?php if (isset($_GET['rezultat_brez_lubja'])): ?>
                    <h4>Rezultat brez lubja: <?php echo htmlspecialchars($_GET['rezultat_brez_lubja']); ?> m<sup>3</sup></h4>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        
        
        <!-- Tabela z vsemi izračuni -->
        <table class="sortable pdf-ready-table">
            <thead>
                <tr>
                    <th data-sort="numeric">Dolžina (m)</th>
                    <th data-sort="numeric">Premer (cm)</th>
                    <th data-sort="numeric">Količina (kos)</th>
                    <th data-sort="numeric">Volumen (m<sup>3</sup>)</th>
                    <th data-sort="text">Vrsta lesa</th>
                    <th data-sort="numeric">Volumen brez lubja (m<sup>3</sup>)</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0; // Inicializacija skupne vsote za izračune
                $total_kolicina = 0; // Inicializacija skupne vsote za količine
                $total_brez_lubja = 0; // Inicializacija skupne vsote za izračune brez lubja
                foreach ($izracuni as $row) {
                    $total += floatval($row['izracun_izracun']); // Prištevanje vrednosti k skupni vsoti
                    $total_kolicina += floatval($row['izracun_kolicina']); // Prištevanje količin k skupni vsoti
                    $total_brez_lubja += floatval($row['izracun_brez_lubja'] ?? 0); // Prištevanje vrednosti brez lubja k skupni vsoti
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['izracun_dolzina']); ?></td>
                        <td><?php echo htmlspecialchars($row['izracun_premer']); ?></td>
                        <td><?php echo htmlspecialchars($row['izracun_kolicina']); ?></td>
                        <td><?php echo htmlspecialchars($row['izracun_izracun']); ?></td>
                        <td><?php echo htmlspecialchars($row['izracun_vrsta_lesa'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['izracun_brez_lubja'] ?? '0.00'); ?></td>
                        <td>
                            <!-- Akcija za brisanje izračuna - lepše oblikovan gumb -->
                            <a href="#" class="delete" 
                               onclick="confirmDelete(<?php echo htmlspecialchars($row['izracun_id']); ?>)">
                                Izbriši
                            </a>
                        </td>
                    </tr>
                <?php } ?>
                <!-- Vrstica s skupnimi vsotami -->
                <tr>
                    <td colspan="2" style="text-align: right;"><strong>Skupaj:</strong></td>
                    <td><strong><?php echo htmlspecialchars(number_format($total_kolicina, 0)); ?></strong></td>
                    <td><strong><?php echo htmlspecialchars(number_format($total, 2)); ?></strong></td>
                    <td></td>
                    <td><strong><?php echo htmlspecialchars(number_format($total_brez_lubja, 2)); ?></strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <div class="word-export-form">
            <a href="/programi/generate_pdf.program.php" target="_blank" class="btn-secondary export-btn">Izvozi PDF</a>
        </div>
    </div>

    <!-- Skriti obrazec za brisanje - poslal se bo ob potrditvi -->
    <form id="delete-form" action="programi/delete.program.php" method="post" style="display: none;">
        <input type="hidden" name="racun_id" value="<?php echo $_SESSION['racun_id']; ?>">
        <input type="hidden" id="delete-izracun-id" name="izracun_id" value="">
        <input type="hidden" name="delete_izracun" value="1">
    </form>

    <script>
        // Funkcija za razvrščanje tabele
        document.addEventListener('DOMContentLoaded', function() {
            const sortableTables = document.querySelectorAll('.sortable');
            
            sortableTables.forEach(function(table) {
                const headers = table.querySelectorAll('th');
                
                headers.forEach(function(header, index) {
                    if (!header.getAttribute('data-sort')) return;
                    
                    header.addEventListener('click', function() {
                        sortTable(table, index, header.getAttribute('data-sort'));
                        
                        // Odstrani obstoječe razrede sortiranja
                        headers.forEach(h => {
                            h.classList.remove('sort-asc', 'sort-desc');
                        });
                        
                        // Dodaj nov razred sortiranja
                        if (this.asc) {
                            this.classList.add('sort-asc');
                            this.asc = false;
                        } else {
                            this.classList.add('sort-desc');
                            this.asc = true;
                        }
                    });
                    
                    // Začetno stanje
                    header.asc = true;
                });
            });
            
            function sortTable(table, column, type) {
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                // Izločimo zadnjo vrstico (skupna vsota)
                const lastRow = rows.pop();
                const header = table.querySelectorAll('th')[column];
                const isAscending = header.asc;
                
                const sortedRows = rows.sort((a, b) => {
                    let aValue = a.querySelectorAll('td')[column].textContent.trim();
                    let bValue = b.querySelectorAll('td')[column].textContent.trim();
                    
                    if (type === 'numeric') {
                        aValue = parseFloat(aValue) || 0;
                        bValue = parseFloat(bValue) || 0;
                    }
                    
                    if (aValue < bValue) {
                        return isAscending ? -1 : 1;
                    }
                    if (aValue > bValue) {
                        return isAscending ? 1 : -1;
                    }
                    return 0;
                });
                
                // Izprazni tabelo
                while (tbody.firstChild) {
                    tbody.removeChild(tbody.firstChild);
                }
                
                // Dodaj sortirane vrstice
                sortedRows.forEach(row => {
                    tbody.appendChild(row);
                });
                
                // Dodaj nazaj zadnjo vrstico (skupna vsota)
                if (lastRow) {
                    tbody.appendChild(lastRow);
                }
            }
            
            // Predogled izračuna (uporabljamo približno oceno, končni izračun se naredi na strežniku)
            const dolzinaInput = document.getElementById('dolzina');
            const premerInput = document.getElementById('premer');
            const kolicinaInput = document.getElementById('kolicina');
            const vrstaLesaInput = document.getElementById('vrsta_lesa');
            const previewSpan = document.querySelector('#preview-result span');
            const previewBrezLubjaSpan = document.querySelector('#preview-result-brez-lubja span');
            
            function updatePreview() {
                const dolzina = parseFloat(dolzinaInput.value) || 0;
                const premer = parseFloat(premerInput.value) || 0;
                const kolicina = parseInt(kolicinaInput.value) || 0;
                const vrstaLesa = vrstaLesaInput.value;
                
                // Približna ocena (dejanski izračun se izvede na strežniku iz tabele)
                const priblizenVolumen = Math.PI * Math.pow(premer/200, 2) * dolzina * kolicina;
                
                previewSpan.textContent = priblizenVolumen.toFixed(3) + " (približna ocena)";
                
                // Izračun premera brez lubja za predogled
                let premerBrezLubja = premer;
                
                switch (vrstaLesa) {
                    case 'smreka':
                        if (premer >= 10 && premer <= 38) {
                            premerBrezLubja = premer - 1;
                        } else if (premer > 38 && premer <= 50) {
                            premerBrezLubja = premer - 2;
                        } else if (premer > 50) {
                            premerBrezLubja = premer - 3;
                        }
                        break;
                    case 'bukev':
                        if (premer >= 10 && premer <= 50) {
                            premerBrezLubja = premer - 1;
                        } else if (premer > 50) {
                            premerBrezLubja = premer - 2;
                        }
                        break;
                    case 'gaber':
                        if (premer >= 10 && premer <= 50) {
                            premerBrezLubja = premer - 1;
                        } else if (premer > 50) {
                            premerBrezLubja = premer - 2;
                        }
                        break;
                    case 'hrast':
                        if (premer >= 20 && premer <= 50) {
                            premerBrezLubja = premer - 3;
                        } else if (premer > 50) {
                            premerBrezLubja = premer - 5;
                        }
                        break;
                    case 'drugo':
                    default:
                        // Ne odbijamo lubja
                        premerBrezLubja = premer;
                        break;
                }
                
                // Preverjamo, da premer brez lubja ni negativen
                premerBrezLubja = Math.max(0, premerBrezLubja);
                
                // Približna ocena volumna brez lubja
                const volumenBrezLubja = Math.PI * Math.pow(premerBrezLubja/200, 2) * dolzina * kolicina;
                
                previewBrezLubjaSpan.textContent = volumenBrezLubja.toFixed(3) + " (približna ocena)";
                
                // Dodamo opozorilo o predogledu
                const predogledOpozorilo = document.getElementById('predogled-opozorilo');
                if (!predogledOpozorilo) {
                    const opozorilo = document.createElement('div');
                    opozorilo.id = 'predogled-opozorilo';
                    opozorilo.className = 'message info';
                    opozorilo.innerHTML = 'Predogled je samo približna ocena. Dejanski izračun uporablja vrednosti iz tabele.';
                    const form = document.querySelector('.content-form');
                    form.insertBefore(opozorilo, form.querySelector('button[type="submit"]'));
                }
            }
            
            if (dolzinaInput && premerInput && kolicinaInput && vrstaLesaInput) {
                dolzinaInput.addEventListener('input', updatePreview);
                premerInput.addEventListener('input', updatePreview);
                kolicinaInput.addEventListener('input', updatePreview);
                vrstaLesaInput.addEventListener('change', updatePreview);
            }
        });
        
        // Funkcija za potrditev in izvedbo brisanja
        function confirmDelete(izracunId) {
            if (confirm('Ali ste prepričani, da želite izbrisati ta izračun?')) {
                document.getElementById('delete-izracun-id').value = izracunId;
                document.getElementById('delete-form').submit();
            }
        }
    </script>

    <footer class="footer">
        <div class="social-icons">
            <a href="https://www.facebook.com" target="_blank">
                <img src="slike/facebook-icon.png" alt="Facebook" />
            </a>
            <a href="https://www.twitter.com" target="_blank">
                <img src="slike/twitter-icon.png" alt="Twitter" />
            </a>
            <a href="https://www.instagram.com" target="_blank">
                <img src="slike/instagram-icon.png" alt="Instagram" />
            </a>
        </div>
    </footer>
</body>
</html>