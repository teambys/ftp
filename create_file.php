<?php
session_start();

if (!isset($_SESSION['ftp_logged_in']) || $_SESSION['ftp_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$path = $_POST['path'] ?? '/';
$name = $_POST['name'] ?? '';

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'File name is required']);
    exit();
}

$conn_id = ftp_connect($_SESSION['ftp_host'], $_SESSION['ftp_port']);
if (@ftp_login($conn_id, $_SESSION['ftp_username'], $_SESSION['ftp_password'])) {
    ftp_pasv($conn_id, true);
    $new_file = $path . '/' . $name;
    
    $temp = tmpfile();
    fwrite($temp, ''); // Create an empty file
    fseek($temp, 0);
    
    if (ftp_fput($conn_id, $new_file, $temp, FTP_ASCII)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create file']);
    }
    
    fclose($temp);
    ftp_close($conn_id);
} else {
    echo json_encode(['success' => false, 'message' => 'FTP connection failed']);
}
?>

