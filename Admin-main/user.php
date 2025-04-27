<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_token'])) {
    header('Location: /expresso-cafe/Admin-main/admin_login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - CupFe Expresso</title>
    <link rel="stylesheet" href="user.css"> <!-- Page-specific styles -->
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
                <li class="menu-item active">
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
                <h1>User Management</h1>
            </header>

            <!-- Controls: Add User, Fetch All, Filters -->
            <div class="user-controls-container">
                <button id="user-add-item-btn" class="user-action-btn user-add-btn">Add User</button>
                <button id="user-fetch-all-btn" class="user-action-btn">Fetch All Users</button>
                <select id="user-role-filter" class="user-filter-select">
                    <option value="">Filter by Role</option>
                    <option value="admin">Admin</option>
                    <option value="employee">Employee</option>
                    <option value="customer">Customer</option>
                </select>
          
                <div class="user-search-container">
                    <input type="text" id="user-search-input" class="user-filter-search" placeholder="Search users by username...">
                    <i class="fas fa-search user-search-icon"></i>
                </div>
            </div>

            <!-- User Items Container -->
            <div class="user-items-container" id="user-items-container">
                <!-- Users will be dynamically rendered here -->
            </div>

            <!-- Add User Form Section -->
            <div id="user-add-item-form" class="user-form-container hidden">
               <h2>Add New User</h2>
               <form>
                    <input type="text" id="user-username" name="username" placeholder="Username" required>
                    <input type="email" id="user-email" name="email" placeholder="Email" required>
                    <input type="password" id="user-password" name="password" placeholder="Password" required>

                    <!-- Show Password Toggle Below Password Field -->
                    <select id="user-role" name="role" class="user-role-select" required>
                         <option value="">Select Role</option>
                         <option value="admin">Admin</option>
                         <option value="employee">Employee</option>
                    </select>

                    <label>
                         <input type="checkbox" id="user-is-active-checkbox" name="is_active" value="1"> Active
                    </label>

                    <button type="submit" class="user-action-btn user-save-btn">Save User</button>
                    <button type="button" id="user-cancel-add-item-btn" class="user-action-btn user-cancel-btn">Cancel</button>
               </form>
          </div>

           <!-- Edit User Form Section -->
           <div id="edit-user-item-section" class="user-form-container hidden">
               <h2>Edit User</h2>
               <form id="edit-user-item-form">
                    <input type="hidden" id="edit-user-item-id" name="id">

                    <label for="edit-user-username">Username</label>
                    <input type="text" id="edit-user-username" name="username" placeholder="Username" required>

                    <label for="edit-user-email">Email</label>
                    <input type="email" id="edit-user-email" name="email" placeholder="Email" required>

                    <label for="edit-user-role">Role</label>
                    <select id="edit-user-role" name="role" class="user-role-select" required>
                         <option value="">Select Role</option>
                         <option value="admin">Admin</option>
                         <option value="employee">Employee</option>
                    </select>

                    <div style="margin-bottom: 10px;">
                         <input type="checkbox" id="edit-user-is-active-checkbox" name="is_active" value="1">
                         <label for="edit-user-is-active-checkbox">Active</label>
                    </div>

                    <button type="submit" id="save-edit-user-item-btn" class="user-action-btn user-save-btn">Save Changes</button>
                    <button type="button" id="user-cancel-edit-item-btn" class="user-action-btn user-cancel-btn">Cancel</button>
               </form>
          </div>
          
         <!-- Change Password Form Section -->
          <div id="change-password-form-section" class="user-form-container hidden">
               <h2>Change Password</h2>
               <form id="change-password-form">
                    <!-- Email Input -->
                    <label for="change-password-email">Email</label>
                    <input type="email" id="change-password-email" name="email" placeholder="Enter Email" required>

                    <!-- New Password Input -->
                    <label for="change-password-new-password">New Password</label>
                    <input type="password" id="change-password-new-password" name="new_password" placeholder="Enter New Password" required>


                    <!-- CAPTCHA Section -->
                    <div id="captcha-container" style="margin: 10px 0;">
                         <div class="g-recaptcha" data-sitekey="6LcNav8qAAAAADGU0569NV3Dmss7LXcbYVSKX28x"></div>
                    </div>

                    <!-- Submit and Cancel Buttons -->
                    <button type="submit" id="change-password-submit-btn" class="user-action-btn user-save-btn">Change Password</button>
                    <button type="button" id="change-password-cancel-btn" class="user-action-btn user-cancel-btn">Cancel</button>
               </form>
          </div>

     
        </main>
    </div>

    <!-- Scripts -->
    <script src="sidebar.js"></script> <!-- Centralized sidebar logic -->
    <script type="module" src="userUI.js"></script> <!-- User UI logic -->
    <script type="module" src="userFORM.js"></script> <!-- User form logic -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script> <!-- Google reCAPTCHA script -->
    <script type="module" src="websocket.js"></script> <!-- Shared WebSocket logic -->
        <script type="module">
            import { connectWebSocket } from './websocket.js';

            // Connect WebSocket when the page loads
            connectWebSocket();
        </script>

</body>

</html>