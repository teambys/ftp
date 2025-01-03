<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced FTP File Manager - Login</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="dark-theme">
    <div class="container">
        <div class="login-form">
            <h1><i class="fas fa-terminal"></i> Enhanced FTP File Manager</h1>
            <form id="loginForm">
                <div class="form-group">
                    <label for="host">FTP Host</label>
                    <input type="text" id="host" class="terminal-input" required>
                </div>
                <div class="form-group">
                    <label for="username">Username (optional)</label>
                    <input type="text" id="username" class="terminal-input">
                </div>
                <div class="form-group">
                    <label for="password">Password (optional)</label>
                    <input type="password" id="password" class="terminal-input">
                </div>
                <div class="form-group">
                    <label for="port">Port</label>
                    <input type="number" id="port" class="terminal-input" value="21">
                </div>
                <button type="submit" class="btn primary">Connect</button>
            </form>
            <div id="loginMessage" class="message"></div>
        </div>
    </div>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="terminal-spinner"></div>
    </div>
    <script src="login.js"></script>
</body>
</html>

