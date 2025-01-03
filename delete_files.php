<?php
session_start();

if (!isset($_SESSION['ftp_logged_in']) || $_SESSION['ftp_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$path = $data['path'] ?? '/';
$files = $data['files'] ?? [];

$conn_id = ftp_connect($_SESSION['ftp_host'], $_SESSION['ftp_port']);
if (@ftp_login($conn_id, $_SESSION['ftp_username'], $_SESSION['ftp_password'])) {
    ftp_pasv($conn_id, true);
    $success = true;
    $message = '';

    foreach ($files as $file) {
        $full_path = $path . '/' . $file;
        if (!ftp_delete($conn_id, $full_path)) {
            // If delete fails, try to remove directory
            if (!ftp_rmdir($conn_id, $full_path)) {
                $success = false;
                $message .= "Failed to delete $file. ";
            }
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

