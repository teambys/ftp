<?php
session_start();

if (!isset($_SESSION['ftp_logged_in']) || $_SESSION['ftp_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$path = $_POST['path'] ?? '/';
$name = $_POST['name'] ?? '';

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Folder name is required']);
    exit();
}

$conn_id = ftp_connect($_SESSION['ftp_host'], $_SESSION['ftp_port']);
if (@ftp_login($conn_id, $_SESSION['ftp_username'], $_SESSION['ftp_password'])) {
    ftp_pasv($conn_id, true);
    $new_dir = $path . '/' . $name;
    
    if (ftp_mkdir($conn_id, $new_dir)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create folder']);
    }
    
    ftp_close($conn_id);
} else {
    echo json_encode(['success' => false, 'message' => 'FTP connection failed']);
}
?>

