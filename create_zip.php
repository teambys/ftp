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
    
    $temp_dir = sys_get_temp_dir() . '/ftp_download_' . uniqid();
    mkdir($temp_dir);

    foreach ($files as $file) {
        $remote_file = $path . '/' . $file;
        $local_file = $temp_dir . '/' . $file;
        ftp_get($conn_id, $local_file, $remote_file, FTP_BINARY);
    }

    $zip_file = tempnam(sys_get_temp_dir(), 'zip');
    $zip = new ZipArchive();
    if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
        $dir = new RecursiveDirectoryIterator($temp_dir);
        $ite = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($ite, '/.*/', RegexIterator::GET_MATCH);
        foreach ($files as $file) {
            $localpath = $file[0];
            $zip->addFile($localpath, basename($localpath));
        }
        $zip->close();

        $zip_url = 'download.php?file=' . basename($zip_file);
        echo json_encode(['success' => true, 'zipUrl' => $zip_url]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create zip file']);
    }

    // Clean up
    array_map('unlink', glob("$temp_dir/*.*"));
    rmdir($temp_dir);
    ftp_close($conn_id);
} else {
    echo json_encode(['success' => false, 'message' => 'FTP connection failed']);
}
?>

