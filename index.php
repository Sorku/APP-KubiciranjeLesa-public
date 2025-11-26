<?php
session_start();

// Load application configuration
require_once 'config.php';

// Vključimo potrebne datoteke
require_once 'strani/dbh.stran.php';
require_once 'classes/AuthManager.php';

// Če smo že prijavljeni, preusmeri na nadzorno ploščo
if(isset($_SESSION['userid'])) {
    header("Location: novracun.php");
    exit();
}

// Preveri za remember me piškotke in avtomatsko prijavi uporabnika
if(isset($_COOKIE['rememberme'])) {
    // Pridobi podatke iz piškotka
    $cookieData = json_decode($_COOKIE['rememberme'], true);
    
    if(isset($cookieData['userid']) && isset($cookieData['token'])) {
        // Ustvari instanco in preveri žeton
        $authManager = new AuthManager();
        $user = $authManager->checkRememberToken($cookieData['userid'], $cookieData['token']);
        
        if($user) {
            // Nastavi spremenljivke seje
            $_SESSION['userid'] = $user['users_id'];
            $_SESSION['useruid'] = $user['users_uid'];
            
            // Preusmeri na nadzorno ploščo
            header("Location: novracun.php");
            exit();
        }
    }
}

// Prikaži sporočilo o uspehu/napaki
$errorMessage = '';
if(isset($_GET['error'])) {
    $errorMessage = urldecode($_GET['error']);
}

$successMessage = '';
if(isset($_GET['success']) && $_GET['success'] === 'registered') {
    $successMessage = "Registracija uspešna. Zdaj se lahko prijavite.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/index.css">
    <title><?php echo $app_title; ?></title>
    <meta name="description" content="<?php echo $app_description; ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo $favicon_ico; ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo $favicon_ico; ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $favicon_32; ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $favicon_16; ?>">
    <link rel="manifest" href="site.webmanifest">
</head>
<body>
    <section class="auth-container">
        <?php if (!empty($errorMessage)): ?>
            <div class="message error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($successMessage)): ?>
            <div class="message success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <!-- Login forma (privzeto vidna) -->
        <div class="auth-box" id="login-box">
            <h4>LOGIN</h4>
            <form action="programi/login.program.php" method="post">
                <input type="text" name="uid" placeholder="Uporabniško ime">
                <input type="password" name="pwd" placeholder="Geslo">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Zapomni si me</label>
                </div>
                <br>
                <button type="submit" name="submit">LOGIN</button>
            </form>
            <p class="switch-form">Nimaš računa? <a href="#" onclick="toggleForms()">Registriraj se tukaj!</a></p>
        </div>

        <!-- Signup forma (privzeto skrita) -->
        <div class="auth-box" id="signup-box" style="display: none;">
            <h4>SIGN UP</h4>
            <form action="programi/signup.program.php" method="post">
                <input type="text" name="uid" placeholder="Uporabniško ime" required>
                <input type="password" name="pwd" placeholder="Geslo" required>
                <input type="password" name="pwdrepeat" placeholder="Ponovi geslo" required>
                <input type="email" name="email" placeholder="E-mail" required>
                <br>
                <button type="submit" name="submit">SIGN UP</button>
            </form>
            <p class="switch-form">Že imaš račun? <a href="#" onclick="toggleForms()">Prijavi se tukaj!</a></p>
        </div>
    </section>

    <script>
        function toggleForms() {
            const loginBox = document.getElementById('login-box');
            const signupBox = document.getElementById('signup-box');

            if (loginBox.style.display === 'none') {
                loginBox.style.display = 'block';
                signupBox.style.display = 'none';
            } else {
                loginBox.style.display = 'none';
                signupBox.style.display = 'block';
            }
        }
    </script>
</body>
</html>