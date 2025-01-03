<?php
session_start();

if (!isset($_SESSION['ftp_logged_in']) || $_SESSION['ftp_logged_in'] !== true) {
    echo 'Not logged in';
    exit();
}

$file = $_GET['file'] ?? '';
$file_path = sys_get_temp_dir() . '/' . $file;

if (file_exists($file_path)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="download.zip"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    unlink($file_path);
} else {
    echo 'File not found';
}
?>

