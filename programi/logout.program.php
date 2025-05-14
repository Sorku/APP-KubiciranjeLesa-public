<?php
// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once '../strani/dbh.stran.php';
require_once '../classes/AuthManager.php';

if(isset($_POST["submit"])) {
    // If user is logged in, clear their remember token in database
    if(isset($_SESSION["userid"])) {
        $authManager = new AuthManager();
        $authManager->clearRememberToken($_SESSION["userid"]);
    }
    
    // Clear the remember me cookie with all parameters matching the set cookie
    if(isset($_COOKIE['rememberme'])) {
        setcookie('rememberme', '', time() - 3600, '/');
        setcookie('rememberme', '', time() - 3600, '/', '', false, false);
    }
    
    // Clear the session
    session_unset();
    session_destroy();

    header("location:../index.php");
    exit();
}

// Redirect if accessed directly
header("location:../index.php");
exit();
?>