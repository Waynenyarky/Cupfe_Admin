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
    <title>Promo Management - CupFe Expresso</title>
    <link rel="stylesheet" href="promo.css"> <!-- Page-specific styles -->
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
                <li class="menu-item">
                    <a href="order.php" class="menu-link"> 
                        <i class="fas fa-box"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="menu-item active">
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
                <h1>Promo Management</h1>
            </header>

            <!-- Controls: Add Promo & Fetch All Button -->
            <div class="promo-controls-container">
                <button id="promo-add-item-btn" class="promo-action-btn promo-add-btn">Add Promo</button>
                <button id="promo-fetch-all-btn" class="promo-action-btn">Fetch All Promos</button>
                <select id="promo-is-active" class="promo-filter-select">
                    <option value="">Filter by Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
                <div class="promo-search-container">
                    <input type="text" id="promo-search-input" class="promo-filter-search" placeholder="Search promo codes...">
                    <i class="fas fa-search promo-search-icon"></i>
                </div>
            </div>

            <!-- Promo Items Container -->
            <div class="promo-items-container" id="promo-items-container">
                <!-- Promos will be dynamically rendered here -->
            </div>

            <!-- Add Promo Form Section -->
            <div id="promo-add-item-form" class="promo-form-container hidden">
                <h2>Add New Promo</h2>
                <form>
                    <input type="text" id="promo-code" name="code" placeholder="Promo Code" required>
                    <input type="number" id="promo-discount" name="discount" placeholder="Discount (pesos)" required>
                    <label>
                        <input type="checkbox" id="promo-is-active-checkbox" name="is_active" value="1"> Active
                    </label>
                    <button type="submit" class="promo-action-btn promo-save-btn">Save Promo</button>
                    <button type="button" id="promo-cancel-add-item-btn" class="promo-action-btn promo-cancel-btn">Cancel</button>
                </form>
            </div>

            <!-- Edit Promo Form Section -->
            <div id="edit-promo-item-section" class="promo-form-container hidden">
                <h2>Edit Promo</h2>
                <form id="edit-promo-item-form">
                    <input type="hidden" id="edit-promo-item-id" name="id">
                    <input type="text" id="edit-promo-code" name="code" placeholder="Promo Code" required>
                    <input type="number" id="edit-promo-discount" name="discount" placeholder="Discount (pesos)" required>
                    <label>
                        <input type="checkbox" id="edit-promo-is-active-checkbox" name="is_active" value="1"> Active
                    </label>
                    <button type="submit" id="save-edit-promo-item-btn" class="promo-action-btn promo-save-btn">Save Changes</button>
                    <button type="button" id="promo-cancel-edit-item-btn" class="promo-action-btn promo-cancel-btn">Cancel</button>
                </form>
            </div>
        </main>

    </div>

    <!-- Scripts -->
    <script src="sidebar.js"></script> <!-- Centralized sidebar logic -->
    <script type="module" src="promoUI.js"></script> <!-- Promo UI logic -->
    <script type="module" src="promoFORM.js"></script> <!-- Promo form logic -->
    <script type="module" src="websocket.js"></script> <!-- Shared WebSocket logic -->
        <script type="module">
            import { connectWebSocket } from './websocket.js';

            // Connect WebSocket when the page loads
            connectWebSocket();
        </script>

</body>

</html>