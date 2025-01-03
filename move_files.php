<?php
session_start();

if (!isset($_SESSION['ftp_logged_in']) || $_SESSION['ftp_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$sourcePath = $data['sourcePath'] ?? '/';
$targetPath = $data['targetPath'] ?? '/';
$files = $data['files'] ?? [];
$operation = $data['operation'] ?? 'move'; // 'move' or 'copy'

$conn_id = ftp_connect($_SESSION['ftp_host'], $_SESSION['ftp_port']);
if (@ftp_login($conn_id, $_SESSION['ftp_username'], $_SESSION['ftp_password'])) {
    ftp_pasv($conn_id, true);
    $success = true;
    $message = '';

    foreach ($files as $file) {
        $source = $sourcePath . '/' . $file;
        $target = $targetPath . '/' . $file;

        if ($operation === 'copy') {
            // For copying, download then upload
            $temp = tempnam(sys_get_temp_dir(), 'ftp');
            if (ftp_get($conn_id, $temp, $source, FTP_BINARY)) {
                if (!ftp_put($conn_id, $target, $temp, FTP_BINARY)) {
                    $success = false;
                    $message .= "Failed to copy $file. ";
                }
            } else {
                $success = false;
                $message .= "Failed to read $file. ";
            }
            unlink($temp);
        } else {
            // For moving, use rename
            if (!ftp_rename($conn_id, $source, $target)) {
                $success = false;
                $message .= "Failed to move $file. ";
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

