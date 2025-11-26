<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header("Location: index.php");
    exit();
}

// Load application configuration
require_once 'config.php';

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
    <title><?php echo $app_title; ?> - Računi</title>
    <meta name="description" content="<?php echo $app_description; ?>">
    <script type="text/javascript" src="darkmode.js" defer></script>
    <link rel="icon" type="image/x-icon" href="<?php echo $favicon_ico; ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $favicon_ico; ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $favicon_32; ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $favicon_16; ?>">
</head>
<body>
    <!-- Navigacija -->
    <nav>
        <a href="index.php">Domov</a>
        <button id="theme-switch">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
            <path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z"/>
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
            <path d="M480-280q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Z"/>
            </svg>
        </button>
        <form action="programi/logout.program.php" method="post" style="display: inline;">
            <button type="submit" name="submit">LOGOUT</button>
        </form>
    </nav>

    <div class="card fade-in">
        <h2 class="card-title"><?php echo $app_title; ?> - Upravljanje računov</h2>
        
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
            <a href="<?php echo $facebook_url; ?>" target="_blank">
                <img src="slike/facebook-icon.png" alt="Facebook" />
            </a>
            <a href="<?php echo $twitter_url; ?>" target="_blank">
                <img src="slike/twitter-icon.png" alt="Twitter" />
            </a>
            <a href="<?php echo $instagram_url; ?>" target="_blank">
                <img src="slike/instagram-icon.png" alt="Instagram" />
            </a>
        </div>
    </footer>
</body>
</html>