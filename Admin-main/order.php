<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_token'])) {
    header('Location: /expresso-cafe/Admin-main/admin_login.php'); // Corrected path
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - CupFe Expresso</title>
    <link rel="stylesheet" href="order.css"> <!-- Page-specific styles -->
    <link rel="stylesheet" href="sidebar.css"> <!-- Centralized sidebar styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <!-- Toggle Button -->
            <button id="sidebarToggle" class="sidebar-toggle-btn">
                <i class="fas fa-angle-left"></i> <!-- Icon for collapse -->
            </button>
            <div class="sidebar-header">
                <img src="main_logo_light.png" alt="Cupfe Expresso" id="logo">
                <p id="adminUsername" class="admin-username"></p>
            </div>
            <ul class="menu">
                <li class="menu-item">
                    <a href="main.php" class="menu-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="menu.php" class="menu-link">
                        <i class="fas fa-mug-hot"></i>
                        <span>Menu</span>
                    </a>
                </li>
                <li class="menu-item active">
                    <a href="order.php" class="menu-link"> 
                        <i class="fas fa-box"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="promo.php" class="menu-link">
                        <i class="fas fa-tags"></i>
                        <span>Promotions</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="bundles.php" class="menu-link">
                        <i class="fas fa-chair"></i>
                        <span>Bundles</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="receipts.php" class="menu-link">
                        <i class="fas fa-receipt"></i>
                        <span>Receipts</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="user.php" class="menu-link">
                        <i class="fas fa-user"></i>
                        <span>User</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link logout-button">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <h1>Order Management</h1>
            </header>

            <!-- Controls: Filter Orders & Search -->
            <div class="order-controls-container">
            <button id="refresh-orders-btn" class="order-action-btn">Refresh Orders</button>
            <select id="order-status" class="order-filter-select">
                <option value="">Filter by Status</option>
                <option value="pending">Pending</option>
                <option value="preparing">Preparing</option>
                <option value="serving">Serving</option>
                <option value="completed">Completed</option>
                <option value="canceled">Canceled</option>
            </select>
            <select id="order-payment-status" class="order-filter-select">
                <option value="">Filter by Payment Status</option>
                <option value="Paid">Paid</option>
                <option value="Unpaid">Unpaid</option>
            </select>
            <select id="order-type" class="order-filter-select">
                <option value="">Filter by Order Type</option>
                <option value="Dine-in">Dine-In</option>
                <option value="Take-out">Take-out</option>
            </select>
            <div class="order-search-container">
                <input type="text" id="order-search-input" class="order-filter-search" placeholder="Search by reference number or username...">
                <i class="fas fa-search order-search-icon"></i>
            </div>
        </div>

            <!-- Orders Table/Container -->
            <div class="order-items-container" id="order-items-container">
                <!-- Orders will be dynamically rendered here -->
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="sidebar.js"></script> <!-- Centralized sidebar logic -->
    <script type="module" src="orderUI.js"></script> <!-- UI logic for orders -->
    <script type="module" src="websocket.js"></script> <!-- Shared WebSocket logic -->
        <script type="module">
            import { connectWebSocket } from './websocket.js';

            // Connect WebSocket when the page loads
            connectWebSocket();
        </script>
</body>

</html>