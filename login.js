document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const host = document.getElementById('host').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const port = document.getElementById('port').value;
    const loginMessage = document.getElementById('loginMessage');
    const loadingOverlay = document.getElementById('loadingOverlay');

    loginMessage.textContent = '';
    loadingOverlay.style.display = 'flex';

    fetch('ftp_connect.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `host=${encodeURIComponent(host)}&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}&port=${encodeURIComponent(port)}`
    })
    .then(response => response.json())
    .then(data => {
        loadingOverlay.style.display = 'none';
        if (data.success) {
            window.location.reload();
        } else {
            loginMessage.textContent = 'Connection failed: ' + data.message;
            loginMessage.classList.add('error');
        }
    })
    .catch(error => {
        loadingOverlay.style.display = 'none';
        console.error('Error:', error);
        loginMessage.textContent = 'An error occurred. Please try again.';
        loginMessage.classList.add('error');
    });
});

