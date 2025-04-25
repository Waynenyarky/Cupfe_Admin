<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
</head>
<body>
    <h1>Customer Login</h1>
    <form id="loginForm">
        <label for="email">Email:</label>
        <input type="email" id="email" placeholder="Enter your email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" placeholder="Enter your password" required>

        <button type="submit">Login</button>
    </form>

    <div id="loginMessage"></div>

    <!-- New section for tracking order status -->
    <div id="orderStatusSection" style="display: none;">
        <h2>Track Order</h2>
        <label for="referenceNumber">Reference Number:</label>
        <input type="text" id="referenceNumber" placeholder="Enter reference number">
        <button id="trackOrderButton">Track Order</button>
        <div id="orderStatusContainer">
            <!-- Order status will be displayed dynamically here -->
        </div>
    </div>

    <div id="notifications">
        <!-- Notifications will be dynamically added here -->
    </div>
    <button id="fetchNotifications" style="display: none;">Fetch All Notifications</button>

    <script>
        const apiUrl = 'http://192.168.1.20/expresso-cafe/api/users/login'; // Login API URL
        const orderStatusApiUrl = 'http://192.168.1.20/expresso-cafe/api/orders/search-by-reference-number'; // Order status API URL
        const notificationsApiUrl = 'http://192.168.1.20/expresso-cafe/api/notifications'; // Notifications API URL
        const wsUrl = 'ws://192.168.1.20:8080'; // WebSocket server URL
        let ws; // WebSocket connection
        let reconnectAttempts = 0; // Track reconnect attempts
        let userEmail; // Store user email for fetching notifications

        // Establish WebSocket connection using JWT token
        function connectWebSocket(token) {
            console.log(`[WebSocket] Attempting to connect with token: ${token}`);

            const webSocketWithTokenUrl = `${wsUrl}?token=${encodeURIComponent(token)}`;
            ws = new WebSocket(webSocketWithTokenUrl);

            ws.onopen = () => {
                console.log('[WebSocket] Connection established.');
                reconnectAttempts = 0; // Reset reconnect attempts
            };

            ws.onmessage = (event) => {
                console.log('[WebSocket] Raw message received:', event.data);

                try {
                    const messageData = JSON.parse(event.data);
                    console.log('[WebSocket] Parsed message:', messageData);

                    // Update order status container if the message relates to order status
                    if (messageData.type === "orderStatus" && messageData.reference_number && messageData.status) {
                        console.log('[WebSocket] Updating order status dynamically.');
                        const orderStatusContainer = document.getElementById('orderStatusContainer');
                        orderStatusContainer.textContent = `Order Status: ${messageData.status}`;
                        console.log(`[WebSocket] Order status updated for reference number: ${messageData.reference_number}`);
                    }

                    // Handle other types of notifications (existing functionality)
                    if (messageData.type === "notification") {
                        const notificationsDiv = document.getElementById('notifications');
                        const notificationElement = document.createElement('div');
                        notificationElement.textContent = `Notification: ${messageData.message} | Time: ${messageData.dateTime}`;
                        notificationsDiv.appendChild(notificationElement);
                        console.log('[WebSocket] Notification displayed.');
                    }
                } catch (error) {
                    console.error('[WebSocket] Error parsing message:', error);
                }
            };

            ws.onclose = () => {
                console.warn('[WebSocket] Connection closed. Attempting to reconnect...');
                reconnectWebSocket(token); // Attempt reconnection
            };

            ws.onerror = (error) => {
                console.error('[WebSocket] Error encountered:', error);
            };
        }

        // Reconnect WebSocket with exponential backoff
        function reconnectWebSocket(token) {
            reconnectAttempts++;
            const delay = Math.min(10000, reconnectAttempts * 1000); // Max delay of 10 seconds
            console.log(`[WebSocket] Reconnecting in ${delay / 1000} seconds...`);
            setTimeout(() => {
                console.log(`[WebSocket] Reconnecting attempt #${reconnectAttempts}`);
                connectWebSocket(token); // Re-establish connection
            }, delay);
        }

        // Fetch order status based on reference number
        function fetchOrderStatus(referenceNumber) {
            console.log(`[Fetch] Fetching order status for reference number: ${referenceNumber}`);
            fetch(`${orderStatusApiUrl}?reference_number=${encodeURIComponent(referenceNumber)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.reference_number && data.status) {
                    console.log('[Fetch] Order status retrieved:', data);
                    const orderStatusContainer = document.getElementById('orderStatusContainer');
                    orderStatusContainer.textContent = `Order Status: ${data.status}`;
                } else {
                    console.log('[Fetch] No order status found for reference number:', referenceNumber);
                    const orderStatusContainer = document.getElementById('orderStatusContainer');
                    orderStatusContainer.textContent = 'No order status found.';
                }
            })
            .catch(error => {
                console.error('[Fetch] Error fetching order status:', error);
                const orderStatusContainer = document.getElementById('orderStatusContainer');
                orderStatusContainer.textContent = 'Error fetching order status.';
            });
        }

        // Form submission handler for login
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            console.log('[Login] Collected email and password from form:', email);
            userEmail = email; // Store email for fetching notifications

            const loginData = { email: email, password: password };

            // Send a POST request to the API for login
            fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(loginData)
            })
            .then(response => response.json())
            .then(data => {
                const loginMessageDiv = document.getElementById('loginMessage');
                loginMessageDiv.textContent = '';
                const notificationsDiv = document.getElementById('notifications');
                notificationsDiv.innerHTML = ''; // Clear notifications on login

                console.log('[Login] API response:', data);

                if (data.token) { // Check if JWT token is provided
                    loginMessageDiv.textContent = 'Login successful. Connecting to WebSocket...';
                    loginMessageDiv.style.color = 'green';

                    // Show track order section
                    const orderStatusSection = document.getElementById('orderStatusSection');
                    orderStatusSection.style.display = 'block';

                    // Connect to WebSocket with the token
                    connectWebSocket(data.token);
                } else {
                    loginMessageDiv.textContent = data.message || 'Login failed.';
                    loginMessageDiv.style.color = 'red';
                }
            })
            .catch(error => {
                console.error('[Login] Error connecting to the API:', error);
                const loginMessageDiv = document.getElementById('loginMessage');
                loginMessageDiv.textContent = 'Failed to connect to the API.';
                loginMessageDiv.style.color = 'red';
            });
        });

        // Event listener for track order button
        document.getElementById('trackOrderButton').addEventListener('click', function() {
            const referenceNumber = document.getElementById('referenceNumber').value;
            if (referenceNumber) {
                fetchOrderStatus(referenceNumber);
            } else {
                console.log('[Track Order] Reference number is required.');
            }
        });
    </script>
</body>
</html>
