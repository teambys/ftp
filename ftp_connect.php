<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $port = intval($_POST['port']);

    // Attempt to connect to the FTP server
    $conn_id = @ftp_connect($host, $port, 10); // 10 second timeout

    if ($conn_id) {
        // Try to login
        if (@ftp_login($conn_id, $username, $password)) {
            $_SESSION['ftp_logged_in'] = true;
            $_SESSION['ftp_host'] = $host;
            $_SESSION['ftp_username'] = $username;
            $_SESSION['ftp_password'] = $password;
            $_SESSION['ftp_port'] = $port;

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Login failed. Please check your credentials.']);
        }
        ftp_close($conn_id);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not connect to FTP server. Please check your host and port.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>

