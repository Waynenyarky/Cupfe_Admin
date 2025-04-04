<?php
session_start();
// If a session already exists, redirect to the admin dashboard
if (isset($_SESSION['user_token'])) {
    header('Location: /expresso-cafe/Admin-main/main.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CupFe Expresso</title>
    <link rel="stylesheet" href="admin_login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <!-- Ionicons for icons -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/@ionic/core/dist/ionic/ionic.esm.js"></script>
    <script nomodule src="https://cdn.jsdelivr.net/npm/@ionic/core/dist/ionic/ionic.js"></script>
</head>

<body>
    <header class="header">
        <a href="#" class="logo">
        <img src="icon_logo_light.png" alt="Cupfe Expresso" id="logo">
            <img src="main_logo_2.png" alt="Cupfe Expresso" id="logo">
        </a>
        <nav class="nav">
        </nav>
    </header>
    <video autoplay loop muted playsinline id="background-video">
        <source src="coffee.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="video-overlay"></div>

    <section class="home">
        <div class="wrapper-login">
            <h2>Admin Login</h2>
            <form onsubmit="handleLogin(event);">
                <div class="input-box">
                    <span class="icon">
                        <ion-icon class="mail-icon" name="mail"></ion-icon>
                    </span>
                    <input type="email" id="email" name="email" required>
                    <label>Enter your email</label>
                </div>
                <div class="input-box">
                <span class="icon">
                    <ion-icon class="password-icon" name="lock-closed"></ion-icon>
                </span>
                <input type="password" id="password" name="password" required>
                <label>Enter your password</label>
                <span class="toggle-password" onclick="togglePassword()">
                    <ion-icon name="eye-off"></ion-icon>
                </span>
            </div>
                <button type="submit" class="submit-btn">Login</button>
            </form>
        </div>
    </section>

   
    <script src="admin_login.js" type="module"></script>
    <script>
    function togglePassword() {
        const passwordField = document.getElementById("password");
        const toggleIcon = document.querySelector(".toggle-password ion-icon");

        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleIcon.setAttribute("name", "eye");
        } else {
            passwordField.type = "password";
            toggleIcon.setAttribute("name", "eye-off");
        }
    }
</script>
</body>

</html>
