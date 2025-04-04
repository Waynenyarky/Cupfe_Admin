import CONFIG from './config.js'; // Import the configuration file

let ws; // WebSocket instance

function handleLogin(event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    const data = { email: email, password: password };
    console.log("Sending login data:", JSON.stringify(data));

    fetch(`${CONFIG.API_BASE_URL}/expresso-cafe/api/users/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        console.log("Received response:", data);
        if (data.token && data.role === 'admin') {
            // Save the token and email in localStorage
            localStorage.setItem('userToken', data.token);
            localStorage.setItem('userEmail', email);
            localStorage.setItem('username', data.username);

            // Pass token to PHP session handler
            fetch(`${CONFIG.API_BASE_URL}/expresso-cafe/api/session-handler.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ token: data.token })
            }).then(() => {
                // Show success toast
                Toastify({
                    text: "Login successful! Connecting to WebSocket...",
                    duration: 3000,
                    gravity: "top", // Position: top or bottom
                    position: "center", // Position: left, center, or right
                    backgroundColor: "#8B4513", // Simple brown color
                }).showToast();

                // Connect to WebSocket server
                connectWebSocket(data.token);

                // Redirect to the admin dashboard
                setTimeout(() => {
                    window.location.href = "/expresso-cafe/Admin-main/main.php";
                }, 3000); // Wait for the toast to finish
            });
        } else if (data.role !== 'admin') {
            // Show unauthorized role toast
            Toastify({
                text: "Access denied. Only admins are allowed.",
                duration: 3000,
                gravity: "top", // Position: top or bottom
                position: "center", // Position: left, center, or right
                backgroundColor: "#8B4513", // Simple brown color
            }).showToast();
        } else {
            // Show failure toast
            Toastify({
                text: data.message || "Login failed. Please try again.",
                duration: 3000,
                gravity: "top", // Position: top or bottom
                position: "center", // Position: left, center, or right
                backgroundColor: "#8B4513", // Simple brown color
            }).showToast();
        }
    })
    .catch(error => {
        console.error('Error:', error);

        // Show error toast
        Toastify({
            text: "An error occurred. Please try again later.",
            duration: 3000,
            gravity: "top", // Position: top or bottom
            position: "center", // Position: left, center, or right
            backgroundColor: "#8B4513", // Simple brown color
        }).showToast();
    });
}

// Function to establish WebSocket connection
function connectWebSocket(token) {
    const webSocketWithTokenUrl = `${CONFIG.WS_BASE_URL}?token=${encodeURIComponent(token)}`;
    console.log(`[WebSocket] Connecting to: ${webSocketWithTokenUrl}`);

    ws = new WebSocket(webSocketWithTokenUrl);

    ws.onopen = () => {
        console.log('[WebSocket] Connection established.');
        Toastify({
            text: "WebSocket connected successfully!",
            duration: 3000,
            gravity: "top",
            position: "center",
            backgroundColor: "#8B4513",
        }).showToast();
    };

    ws.onclose = () => {
        console.warn('[WebSocket] Connection closed.');
        Toastify({
            text: "WebSocket connection closed.",
            duration: 3000,
            gravity: "top",
            position: "center",
            backgroundColor: "#8B4513",
        }).showToast();
    };

    ws.onerror = (error) => {
        console.error('[WebSocket] Error encountered:', error);
        Toastify({
            text: "WebSocket error occurred. Check the console for details.",
            duration: 3000,
            gravity: "top",
            position: "center",
            backgroundColor: "#8B4513",
        }).showToast();
    };
}

// Attach the handleLogin function to the global scope
window.handleLogin = handleLogin;

// Show/hide password toggle
document.getElementById('show-password').addEventListener('change', function () {
    const passwordInput = document.getElementById('password');
    if (this.checked) {
        passwordInput.type = 'text';
    } else {
        passwordInput.type = 'password';
    }
});