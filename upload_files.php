<?php
session_start();

if (!isset($_SESSION['ftp_logged_in']) || $_SESSION['ftp_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$path = $_POST['path'] ?? '/';

$conn_id = ftp_connect($_SESSION['ftp_host'], $_SESSION['ftp_port']);
if (@ftp_login($conn_id, $_SESSION['ftp_username'], $_SESSION['ftp_password'])) {
    ftp_pasv($conn_id, true);
    $success = true;
    $message = '';

    foreach ($_FILES['files']['name'] as $i => $name) {
        if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
            $temp_file = $_FILES['files']['tmp_name'][$i];
            $remote_file = $path . '/' . $name;
            
            if (!ftp_put($conn_id, $remote_file, $temp_file, FTP_BINARY)) {
                $success = false;
                $message .= "Failed to upload $name. ";
            }
        } else {
            $success = false;
            $message .= "Error uploading $name. ";
        }
    }

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $message]);
    }
    
    ftp_close($conn_id);
} else {
    echo json_encode(['success' => false, 'message' => 'FTP connection failed']);
}
?>

