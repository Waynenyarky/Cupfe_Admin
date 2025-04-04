document.addEventListener("DOMContentLoaded", () => {
    // Display the admin username below the café name
    const displayAdminUsername = () => {
        const username = localStorage.getItem("username"); // Retrieve the username from localStorage
        const adminUsernameElement = document.getElementById("adminUsername"); // Target the admin username element
        if (username && adminUsernameElement) {
            adminUsernameElement.textContent = `Hello, ${username}! Have a shot of expresso.`; // Update the content
        } else {
            console.warn("[Sidebar] Username not found or adminUsername element missing.");
        }
    };

    // Display the username on page load
    displayAdminUsername();

    // Handle sidebar toggle functionality
    const sidebar = document.getElementById("sidebar");
    const toggleButton = document.getElementById("sidebarToggle");

    toggleButton.addEventListener("click", () => {
        // Toggle a CSS class to hide/show the sidebar
        sidebar.classList.toggle("collapsed");

        // Update the toggle button icon based on state
        const icon = toggleButton.querySelector("i");
        if (sidebar.classList.contains("collapsed")) {
            icon.className = "fas fa-angle-right"; // Change to 'expand' icon
        } else {
            icon.className = "fas fa-angle-left"; // Change to 'collapse' icon
        }
    });

    // Handle logout button click
    const logoutButton = document.querySelector(".logout-button");

    logoutButton.addEventListener("click", async (event) => {
        event.preventDefault(); // Prevent default link behavior

        // Show confirmation dialog
        const userConfirmed = confirm("Are you sure you want to log out?");
        if (userConfirmed) {
            try {
                // Call the logout PHP endpoint using fetch API
                const response = await fetch("/expresso-cafe/Admin-main/logout.php", {
                    method: "POST", // Ensure it's a POST request
                    headers: {
                        "Content-Type": "application/json"
                    }
                });

                // Check if the logout was successful
                if (response.ok) {
                    // Clear the username from localStorage
                    localStorage.removeItem("username");

                    // Redirect to the admin login page
                    window.location.href = "/expresso-cafe/Admin-main/admin_login.php";
                } else {
                    // Handle errors (e.g., if logout.php fails)
                    alert("Logout failed. Please try again.");
                }
            } catch (error) {
                // Handle any network errors
                console.error("Error during logout:", error);
                alert("An error occurred. Please try again.");
            }
        }
    });
});