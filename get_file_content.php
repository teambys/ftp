<?php
session_start();

if (!isset($_SESSION['ftp_logged_in']) || $_SESSION['ftp_logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$path = $_GET['path'] ?? '';

// Function to get MIME type
function getMimeType($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mimeTypes = [
        // Text files
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/plain',
        'css' => 'text/css',
        'js' => 'text/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'ini' => 'text/plain',
        'log' => 'text/plain',
        'md' => 'text/plain',
        
        // Images
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        
        // Audio
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'm4a' => 'audio/mp4',
        
        // Video
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogv' => 'video/ogg',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo',
        
        // Documents
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        
        // Archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        '7z' => 'application/x-7z-compressed',
        'tar' => 'application/x-tar',
        'gz' => 'application/gzip'
    ];
    
    return $mimeTypes[$ext] ?? 'application/octet-stream';
}

// Function to check if file is binary
function isBinaryFile($filename) {
    $textExtensions = [
        'txt', 'htm', 'html', 'php', 'css', 'js', 'json', 'xml', 'ini', 
        'log', 'md', 'csv', 'yml', 'yaml', 'conf', 'config', 'sql'
    ];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return !in_array($ext, $textExtensions);
}

try {
    $conn_id = ftp_connect($_SESSION['ftp_host'], $_SESSION['ftp_port']);
    if (!$conn_id) {
        throw new Exception('Failed to connect to FTP server');
    }

    if (!@ftp_login($conn_id, $_SESSION['ftp_username'], $_SESSION['ftp_password'])) {
        throw new Exception('FTP login failed');
    }

    ftp_pasv($conn_id, true);
    
    // Create a temporary file
    $temp = tempnam(sys_get_temp_dir(), 'ftp');
    if (!$temp) {
        throw new Exception('Failed to create temporary file');
    }

    // Download the file
    if (!ftp_get($conn_id, $temp, $path, FTP_BINARY)) {
        throw new Exception('Failed to download file from FTP server');
    }

    // Get file info
    $mimeType = getMimeType($path);
    $fileSize = filesize($temp);
    $isBinary = isBinaryFile($path);

    // Set appropriate headers
    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('X-Content-Type-Options: nosniff');

    // Output file contents
    if ($isBinary) {
        // For binary files, output directly
        readfile($temp);
    } else {
        // For text files, ensure proper encoding
        $content = file_get_contents($temp);
        // Remove BOM if present
        $content = str_replace("\xEF\xBB\xBF", '', $content);
        // Convert to UTF-8 if needed
        if (!mb_detect_encoding($content, 'UTF-8', true)) {
            $content = mb_convert_encoding($content, 'UTF-8');
        }
        echo $content;
    }

    // Clean up
    unlink($temp);
    ftp_close($conn_id);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>

