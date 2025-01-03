<?php
// Set session save path for Render
ini_set('session.save_path', '/tmp');

session_start();

// Check if the user is logged in
if (!isset($_SESSION['ftp_logged_in']) || $_SESSION['ftp_logged_in'] !== true) {
    // If not logged in, show the login form
    include 'login.php';
    exit();
}

// If logged in, show the file manager
include 'file_manager.php';
?>

