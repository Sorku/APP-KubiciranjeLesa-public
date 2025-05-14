<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: index.php");
    exit();
}

require_once 'strani/dbh.stran.php';
require_once 'classes/RacunManager.php';

$racunManager = new RacunManager();
$racuni = $racunManager->pridobiRacune($_SESSION['userid']); // Updated to only show user's računi
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/novracun.css">
    <title>Nov račun</title>
</head>
<body>
    <!-- Navigacija -->
    <nav>
        <a href="index.php">Domov</a>
        <form action="programi/logout.program.php" method="post" style="display: inline;">
            <button type="submit" name="submit">LOGOUT</button>
        </form>
    </nav>

    <div class="card fade-in">
        <h2 class="card-title">Ustvarjanje in upravljanje računov</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="message error">
                <?php 
                switch($_GET['error']) {
                    case 'noracun':
                        echo "Prosim, izberite račun pred nadaljevanjem.";
                        break;
                    case 'deletefailed':
                        echo "Brisanje računa ni uspelo.";
                        break;
                    case 'addfailed':
                        echo "Dodajanje računa ni uspelo.";
                        break;
                    default:
                        echo "Prišlo je do napake.";
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="message success">
                <?php 
                switch($_GET['success']) {
                    case 'deleted':
                        echo "Račun je bil uspešno izbrisan.";
                        break;
                    case 'added':
                        echo "Račun je bil uspešno dodan.";
                        break;
                    default:
                        echo "Operacija je bila uspešna.";
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="measurement-card">
            <h3>Dodaj nov račun</h3>
            <form action="programi/novracun.program.php" method="post" class="content-form"> 
                <div class="form-group">
                    <label for="racun-name">Ime računa:</label>
                    <input type="text" id="racun-name" name="racun" placeholder="Ime računa" required>
                    <span class="form-control-help">Vnesite ime novega računa (npr. Janez Novak, maj 2023)</span>
                </div>
                <button type="submit" name="submit">Dodaj nov račun</button>
            </form>
        </div>
        
        <table class="sortable">
            <thead>
                <tr>
                    <th data-sort="numeric">ŠT_Računa</th>
                    <th data-sort="text">Ime Računa</th>
                    <th data-sort="date">Datum</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($racuni as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['racun_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['racun']); ?></td>
                        <td><?php 
                            // Check if date is valid before formatting
                            if (!empty($row['created_at']) && strtotime($row['created_at']) > 0) {
                                echo date('d.m.Y', strtotime($row['created_at']));
                            } else {
                                echo "Ni datuma"; // Or any other placeholder you prefer
                            }
                        ?></td>
                        <td>
                            <a class="open" href="racunanje.php?odpri=<?php echo htmlspecialchars($row['racun_id']); ?>">Odpri</a>
                            <a class="delete" href="programi/novracun.program.php?izbrisi=<?php echo htmlspecialchars($row['racun_id']); ?>" 
                               onclick="return confirm('Ste prepričani, da želite izbrisati ta račun?')">
                                Izbriši
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

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
                const header = table.querySelectorAll('th')[column];
                const isAscending = header.asc;
                
                const sortedRows = rows.sort((a, b) => {
                    let aValue = a.querySelectorAll('td')[column].textContent.trim();
                    let bValue = b.querySelectorAll('td')[column].textContent.trim();
                    
                    if (type === 'numeric') {
                        aValue = parseFloat(aValue) || 0;
                        bValue = parseFloat(bValue) || 0;
                    } else if (type === 'date') {
                        // Posebna obravnava za "Ni datuma" vrednosti
                        if (aValue === "Ni datuma" && bValue === "Ni datuma") {
                            return 0;
                        } else if (aValue === "Ni datuma") {
                            return isAscending ? 1 : -1; // "Ni datuma" naj bo na koncu
                        } else if (bValue === "Ni datuma") {
                            return isAscending ? -1 : 1;
                        }
                        
                        // Convert European date format (dd.mm.yyyy) to sortable format
                        const [aDay, aMonth, aYear] = aValue.split('.');
                        const [bDay, bMonth, bYear] = bValue.split('.');
                        
                        aValue = new Date(`${aYear}-${aMonth}-${aDay}`);
                        bValue = new Date(`${bYear}-${bMonth}-${bDay}`);
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
            }
        });
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