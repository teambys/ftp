<?php
session_start();

if (!isset($_SESSION['ftp_logged_in']) || $_SESSION['ftp_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
    exit();
}

$path = $_POST['path'] ?? '/';
$file = $_FILES['file'];

$conn_id = ftp_connect($_SESSION['ftp_host'], $_SESSION['ftp_port']);
if (@ftp_login($conn_id, $_SESSION['ftp_username'], $_SESSION['ftp_password'])) {
    $remote_file = $path . '/' . $file['name'];
    
    if (ftp_put($conn_id, $remote_file, $file['tmp_name'], FTP_BINARY)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    }
    
    ftp_close($conn_id);
} else {
    echo json_encode(['success' => false, 'message' => 'FTP connection failed']);
}
?>

