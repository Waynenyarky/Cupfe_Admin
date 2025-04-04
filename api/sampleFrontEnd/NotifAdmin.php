<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>
<body>
    <h1>Admin Panel</h1>

    <!-- Admin Login Section -->
    <div id="loginSection">
        <h2>Admin Login</h2>
        <form id="adminLoginForm">
            <label for="adminEmail">Email:</label>
            <input type="email" id="adminEmail" placeholder="Enter your email" required>

            <label for="adminPassword">Password:</label>
            <input type="password" id="adminPassword" placeholder="Enter your password" required>

            <button type="submit">Login</button>
        </form>

        <div id="loginMessage"></div>
    </div>

    <!-- Notification Panel Section (Initially Hidden) -->
    <div id="notificationPanel" style="display: none;">
        <h2>Send Notifications</h2>
        <form id="notificationForm">
            <label for="email">User Email:</label>
            <input type="email" id="email" placeholder="Enter user email" required>

            <label for="message">Notification Message:</label>
            <textarea id="message" placeholder="Enter notification message" required></textarea>

            <button type="submit">Send Notification</button>
        </form>

        <div id="responseMessage"></div>
    </div>

    <script>
        const loginApiUrl = 'http://192.168.18.9/expresso-cafe/api/users/login'; // Admin login API endpoint
        const notificationApiUrl = 'http://192.168.18.9/expresso-cafe/api/notifications'; // Notification API endpoint
        const wsUrl = 'ws://192.168.18.9:8080'; // WebSocket server URL
        let ws; // WebSocket connection
        let token; // JWT token for admin session
        let reconnectAttempts = 0; // Track reconnect attempts

        // Function to establish WebSocket connection
        function connectWebSocket() {
            console.log('[WebSocket] Connecting...');
            ws = new WebSocket(`${wsUrl}?token=${encodeURIComponent(token)}`);

            ws.onopen = () => {
                console.log('[WebSocket] Connection established.');
                reconnectAttempts = 0; // Reset reconnect attempts on successful connection
            };

            ws.onmessage = (event) => {
                const notification = JSON.parse(event.data);
                console.log('[WebSocket] Message received:', notification);

                const { message, dateTime } = notification; // Extract message and dateTime
                console.log(`[WebSocket] Notification: ${message} (Received on: ${dateTime})`);

                alert(`New Notification: ${message}\nTime: ${dateTime}`);
            };

            ws.onclose = () => {
                console.warn('[WebSocket] Connection closed. Reconnecting...');
                attemptReconnect();
            };

            ws.onerror = (error) => {
                console.error('[WebSocket] Error encountered:', error);
            };
        }

        // Function to attempt WebSocket reconnection
        function attemptReconnect() {
            reconnectAttempts++;
            const delay = Math.min(10000, reconnectAttempts * 1000); // Max delay of 10 seconds
            console.log(`[WebSocket] Reconnecting in ${delay / 1000} seconds...`);

            setTimeout(() => {
                console.log(`[WebSocket] Reconnection attempt #${reconnectAttempts}`);
                connectWebSocket();
            }, delay);
        }

        // Admin Login Handler
        document.getElementById('adminLoginForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const email = document.getElementById('adminEmail').value;
            const password = document.getElementById('adminPassword').value;

            console.log('[Login] Attempting login with email:', email);

            const loginData = { email: email, password: password };

            fetch(loginApiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(loginData)
            })
            .then(response => response.json())
            .then(data => {
                const loginMessageDiv = document.getElementById('loginMessage');

                console.log('[Login] API Response:', data);

                if (data.token) { // Token received
                    token = data.token; // Store the JWT token
                    loginMessageDiv.textContent = 'Login successful! Accessing notification panel...';
                    loginMessageDiv.style.color = 'green';

                    console.log('[Login] Token received:', token);

                    // Show the notification panel and hide the login section
                    document.getElementById('loginSection').style.display = 'none';
                    document.getElementById('notificationPanel').style.display = 'block';

                    // Establish WebSocket connection using token
                    connectWebSocket();
                } else {
                    loginMessageDiv.textContent = data.message || 'Login failed. Please try again.';
                    loginMessageDiv.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('[Login] Error connecting to the login API:', error);
                const loginMessageDiv = document.getElementById('loginMessage');
                loginMessageDiv.textContent = 'Failed to connect to the login API.';
                loginMessageDiv.style.color = 'red';
            });
        });

        // Notification Form Submission Handler
        document.getElementById('notificationForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const email = document.getElementById('email').value;
            const message = document.getElementById('message').value;

            console.log('[Notification] Sending notification to email:', email, 'with message:', message);

            const notificationData = { email: email, message: message };

            fetch(notificationApiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}` // Attach the JWT token
                },
                body: JSON.stringify(notificationData)
            })
            .then(response => response.json())
            .then(data => {
                console.log('[Notification] API Response:', data);

                const responseDiv = document.getElementById('responseMessage');
                if (data.message) {
                    responseDiv.textContent = data.message;
                    responseDiv.style.color = 'green';

                    console.log('[Notification] Notification successfully sent through API.');

                    // Optionally, send the notification through WebSocket
                    if (ws && ws.readyState === 1) {
                        ws.send(JSON.stringify(notificationData));
                        console.log('[WebSocket] Notification sent through WebSocket:', notificationData);
                    }
                } else {
                    responseDiv.textContent = 'Error occurred while sending notification.';
                    responseDiv.style.color = 'red';
                    console.error('[Notification] Error from API:', data.message);
                }
            })
            .catch(error => {
                console.error('[Notification] Error sending notification:', error);
                const responseDiv = document.getElementById('responseMessage');
                responseDiv.textContent = 'Failed to send notification.';
                responseDiv.style.color = 'red';
            });
        });
    </script>
</body>
</html>
