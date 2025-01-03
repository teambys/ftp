<?php
session_start();

if (!isset($_SESSION['ftp_logged_in']) || $_SESSION['ftp_logged_in'] !== true) {
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$path = $_GET['path'] ?? '/';

$conn_id = ftp_connect($_SESSION['ftp_host'], $_SESSION['ftp_port']);
if (@ftp_login($conn_id, $_SESSION['ftp_username'], $_SESSION['ftp_password'])) {
    ftp_pasv($conn_id, true);
    $list = ftp_mlsd($conn_id, $path);
    $files = [];

    foreach ($list as $item) {
        if ($item['name'] != '.' && $item['name'] != '..') {
            $files[] = [
                'name' => $item['name'],
                'type' => $item['type'],
                'size' => $item['size'] ?? '-',
                'modified' => $item['modify'] ?? '-'
            ];
        }
    }

    echo json_encode($files);
    ftp_close($conn_id);
} else {
    echo json_encode(['error' => 'FTP connection failed']);
}
?>

