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
    <title>Admin Dashboard - CupFe Expresso</title>
    <link rel="stylesheet" href="main.css"> <!-- Page-specific styles -->
    <link rel="stylesheet" href="sidebar.css"> <!-- Centralized sidebar styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js for graphs -->
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
                <li class="menu-item active">
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
                <h1>Dashboard</h1>
            </header>

            <!-- Filter Container -->
            <div class="filter-container">
                <label>
                    <input type="radio" name="date-filter" value="all" checked>
                    All
                </label>
                <label>
                    <input type="radio" name="date-filter" value="today">
                    Today
                </label>
                <label>
                    <input type="radio" name="date-filter" value="this-week">
                    This Week
                </label>
                <label>
                    <input type="radio" name="date-filter" value="this-month">
                    This Month
                </label>
            </div>

            <!-- Quick Stats Cards -->
            <div class="quick-stats-container">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Total Users</h3>
                    <span id="total-users">0</span>
                    <button onclick="window.location.href='user.php'">See More</button>
                </div>
                <div class="stat-card">
                    <i class="fas fa-box"></i>
                    <h3>Total Orders</h3>
                    <span id="total-orders">0</span>
                    <button onclick="window.location.href='order.php'">See More</button>
                </div>
                <div class="stat-card">
                    <i class="fas fa-chair"></i>
                    <h3>Total Table Reservations</h3>
                    <span id="total-reservations">0</span>
                    <button onclick="window.location.href='bundles.php'">See More</button>
                </div>
                <div class="stat-card">
                    <i class="fas fa-mug-hot"></i>
                    <h3>Total Menu Items</h3>
                    <span id="total-menu-items">0</span>
                    <button onclick="window.location.href='menu.php'">See More</button>
                </div>
                <div class="stat-card">
                    <i class="fas fa-tags"></i>
                    <h3>Total Promotions</h3>
                    <span id="total-promos">0</span>
                    <button onclick="window.location.href='promo.php'">See More</button>
                </div>
            </div>

            <!-- Best Sellers: Food and Coffee -->
            <div class="charts-container">
                <div class="chart-section">
                    <h3>Best Sellers: Food</h3>
                    <canvas id="food-bar-chart"></canvas>
                </div>
                <div class="chart-section">
                    <h3>Best Sellers: Coffee</h3>
                    <canvas id="coffee-bar-chart"></canvas>
                </div>

                <!-- Sales Trends Graph -->
                <div class="chart-section">
                    <h3>Sales Trends</h3>
                    <div class="toggle-buttons">
                        <button onclick="showSalesData('orders')">Orders</button>
                        <button onclick="showSalesData('reservations')">Reservations</button>
                        <button onclick="showSalesData('combined')">Combined</button>
                    </div>
                    <canvas id="sales-line-chart"></canvas>
                </div>
            </div>
        </main>
    </div>

       <!-- Existing Scripts -->
       <script src="sidebar.js"></script> <!-- Centralized sidebar logic -->
       <!-- Dashboard Logic -->
        <script type="module" src="mainSTATS.js"></script> <!-- Handles stat cards logic -->
        <script type="module" src="mainGRAPHS.js"></script> <!-- Handles sales line graph logic -->
        <script type="module" src="mainBARCHARTS.js"></script> <!-- Handles bar charts logic -->
        <script type="module" src="websocket.js"></script> <!-- Shared WebSocket logic -->
        <script type="module">
            import { connectWebSocket } from './websocket.js';

            // Connect WebSocket when the page loads
            connectWebSocket();
        </script>
</body>
</html>