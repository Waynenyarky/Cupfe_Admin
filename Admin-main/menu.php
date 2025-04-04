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
    <title>Menu Management - CupFe Expresso</title>
    <link rel="stylesheet" href="menu.css"> <!-- Page-specific styles -->
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
                <li class="menu-item active">
                    <a href="menu.php" class="menu-link">
                        <i class="fas fa-mug-hot"></i>
                        <span>Menu</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="order.php" class="menu-link"> <!-- New link for orders -->
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
                <h1>Menu Management</h1>
            </header>

            <!-- Controls: Add Menu Item & Fetch All Button -->
            <div class="menu-controls-container">
                <button id="menu-add-item-btn" class="menu-action-btn menu-add-btn">Add Menu Item</button>
                <button id="menu-fetch-all-btn" class="menu-action-btn">Fetch All Menu</button>
                <select id="menu-category" class="menu-filter-select">
                    <option value="">Select Category</option>
                    <option value="coffee">Coffee</option>
                    <option value="food">Food</option>
                </select>
                <select id="menu-subcategory" class="menu-filter-select">
                    <option value="">Select Subcategory</option>
                    <option value="hot">Hot</option>
                    <option value="iced">Iced</option>
                    <option value="non-coffee">Non-Coffee</option>
                    <option value="pastry">Pastry</option>
                    <option value="pasta">Pasta</option>
                    <option value="sandwich">Sandwich</option>
                </select>
                <div class="menu-search-container">
                <input type="text" id="menu-search-input" class="menu-filter-search" placeholder="Search menu items...">
                <i class="fas fa-search menu-search-icon"></i>
            </div>

            </div>

            <!-- Menu Items Container -->
            <div class="menu-items-container" id="menu-items-container">
                <!-- Menu items will be dynamically rendered here -->
            </div>

            <!-- Add Menu Item Form Section -->
            <div id="menu-add-item-form" class="menu-form-container hidden">
                <h2>Add New Menu Item</h2>
                <form>
                    <input type="text" id="menu-item-name" name="name" placeholder="Item Name" required>
                    <textarea id="menu-item-description" name="description" placeholder="Description" required></textarea>
                    <input type="number" id="menu-price-small" name="price_small" placeholder="Price (Small)" required>
                    <input type="number" id="menu-price-medium" name="price_medium" placeholder="Price (Medium)">
                    <input type="number" id="menu-price-large" name="price_large" placeholder="Price (Large)">
                    <select id="menu-item-category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="coffee">Coffee</option>
                        <option value="food">Food</option>
                    </select>
                    <select id="menu-item-subcategory" name="subcategory" required>
                        <option value="">Select Subcategory</option>
                        <option value="hot">Hot</option>
                        <option value="iced">Iced</option>
                        <option value="non-coffee">Non-Coffee</option>
                        <option value="pastry">Pastry</option>
                        <option value="pasta">Pasta</option>
                        <option value="sandwich">Sandwich</option>
                    </select>
                    <input type="file" id="menu-item-image" name="image" accept="image/*">
                    <label>
                        <input type="checkbox" id="menu-is-available" name="is_available" value="1"> Available
                    </label>
                    <button type="submit" class="menu-action-btn menu-save-btn">Save Item</button>
                    <button type="button" id="menu-cancel-add-item-btn" class="menu-action-btn menu-cancel-btn">Cancel</button>
                </form>
            </div>


            <!-- Edit Menu Item Form Section -->
            <div id="edit-menu-item-section" class="menu-form-container hidden">
                <h2>Edit Menu Item</h2>
                <form id="edit-menu-item-form">
                    <input type="hidden" id="edit-menu-item-id" name="id">
                    <input type="hidden" id="edit-menu-item-img-url" name="image_url"> <!-- Hidden field for image URL -->
                    <input type="text" id="edit-menu-item-name" name="name" placeholder="Item Name" required>
                    <textarea id="edit-menu-item-description" name="description" placeholder="Description"></textarea>
                    <input type="number" id="edit-menu-price-small" name="price_small" placeholder="Price (Small)">
                    <input type="number" id="edit-menu-price-medium" name="price_medium" placeholder="Price (Medium)">
                    <input type="number" id="edit-menu-price-large" name="price_large" placeholder="Price (Large)">
                    <select id="edit-menu-item-category" name="category">
                        <option value="">Select Category</option>
                        <option value="coffee">Coffee</option>
                        <option value="food">Food</option>
                    </select>
                    <select id="edit-menu-item-subcategory" name="subcategory">
                        <option value="">Select Subcategory</option>
                        <option value="hot">Hot</option>
                        <option value="iced">Iced</option>
                        <option value="non-coffee">Non-Coffee</option>
                        <option value="pastry">Pastry</option>
                        <option value="pasta">Pasta</option>
                        <option value="sandwich">Sandwich</option>
                    </select>
                    <label>
                        <input type="checkbox" id="edit-menu-is-available" name="is_available" value="1"> Available
                    </label>
                    <button type="submit" id="save-edit-menu-item-btn" class="menu-action-btn menu-save-btn">Save Changes</button>
                    <button type="button" id="menu-cancel-edit-item-btn" class="menu-action-btn menu-cancel-btn">Cancel</button>
                </form>
            </div>

            <!-- Change Image Form Section -->
            <div id="change-menu-item-image-section" class="menu-form-container hidden">
                <h2>Change Menu Item Image</h2>
                <form id="change-menu-item-image-form">
                    <!-- Hidden fields to store existing item details -->
                    <input type="hidden" id="change-menu-item-id" name="id">
                    <input type="hidden" id="change-menu-item-name" name="name">
                    <input type="hidden" id="change-menu-item-description" name="description">
                    <input type="hidden" id="change-menu-price-small" name="price_small">
                    <input type="hidden" id="change-menu-price-medium" name="price_medium">
                    <input type="hidden" id="change-menu-price-large" name="price_large">
                    <input type="hidden" id="change-menu-item-category" name="category">
                    <input type="hidden" id="change-menu-item-subcategory" name="subcategory">
                    <input type="hidden" id="change-menu-is-available" name="is_available">

                    <!-- Image Upload Field -->
                    <input type="file" id="change-menu-item-image" name="image" accept="image/*" required>
                    
                    <!-- Form Buttons -->
                    <button type="submit" class="menu-action-btn menu-save-btn">Save Image</button>
                    <button type="button" id="cancel-change-menu-item-image-btn" class="menu-action-btn menu-cancel-btn">Cancel</button>
                </form>

            </div>
            </div>
        </main>

    </div>

    <!-- Scripts -->
    <script src="sidebar.js"></script> <!-- Centralized sidebar logic -->
    <script type="module" src="menuUI.js"></script> <!-- UI logic -->
    <script type="module" src="menuFORM.js"></script> <!-- Form logic -->
    <script type="module" src="websocket.js"></script> <!-- Shared WebSocket logic -->
    <script type="module">
        import { connectWebSocket } from './websocket.js';

        // Connect WebSocket when the page loads
        connectWebSocket();
    </script>
    
</body>

</html>
