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
    <title>Bundles Management - CupFe Expresso</title>
    <link rel="stylesheet" href="bundles.css"> <!-- Page-specific styles -->
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
                <img src="main_logo.png" alt="Cupfe Expresso" id="logo">
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
                <li class="menu-item">
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
                <li class="menu-item active">
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
                <h1>Bundles Management</h1>
            </header>

            <!-- Controls: Filter Bundles & Search -->
            <div class="bundle-controls-container">
                <button id="refresh-bundles-btn" class="bundle-action-btn">Refresh Bundles</button>
                <select id="bundle-type" class="bundle-filter-select">
                    <option value="">Filter by Bundle Type</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                </select>
                <div class="bundle-search-container">
                    <input type="text" id="bundle-search-input" class="bundle-filter-search" placeholder="Search by reference number or username...">
                    <i class="fas fa-search bundle-search-icon"></i>
                </div>
            </div>

            <!-- Bundles Table/Container -->
            <div id="bundle-items-container" class="bundle-items-container">
            <!-- Bundles will be dynamically rendered here -->
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="sidebar.js"></script> <!-- Centralized sidebar logic -->
    <script type="module" src="bundleUI.js"></script> <!-- UI logic for bundles -->
    <script type="module" src="websocket.js"></script> <!-- Shared WebSocket logic -->
    <script type="module">
        import { connectWebSocket } from './websocket.js';

        // Connect WebSocket when the page loads
        connectWebSocket();
    </script>
</body>

</html>