import {
    fetchAllUsers,
    fetchUsersByRole,
    searchUsers,
    deleteUser
} from "./userAPI.js";

// DOM Elements
const userContainer = document.getElementById("user-items-container");
const fetchAllButton = document.getElementById("user-fetch-all-btn");
const roleSelect = document.getElementById("user-role-filter"); // Role filter dropdown
const searchInput = document.getElementById("user-search-input"); // Search input box

// Utility Functions

/**
 * Render users in the container
 * @param {Array} users - The list of users
 */
const renderUsers = (users) => {
    console.log("Rendering users...");
    userContainer.innerHTML = ""; // Clear container

    if (users.length === 0) {
        userContainer.innerHTML = "<p>No users found.</p>";
        return;
    }

    users.forEach(user => {
        const isActiveClass = user.active ? "active-status" : "inactive-status"; // Update class
        const isActiveText = user.active ? "Active" : "Inactive";

        const userElement = document.createElement("div");
        userElement.classList.add("user-item-card");
        userElement.innerHTML = `
            <div class="user-item-details">
                <p><strong>Username:</strong> ${user.username}</p>
                <p><strong>Email:</strong> ${user.email}</p>
                <p><strong>Role:</strong> ${user.role}</p>
                <p><strong>Created At:</strong> ${user.created_at}</p>
                <p><strong>Updated At:</strong> ${user.updated_at || "Never Updated"}</p>
                <p class="user-status ${isActiveClass}"><strong>${isActiveText}</strong></p> <!-- Status -->
            </div>
            <div class="user-item-actions">
                ${user.role !== "customer"
                    ? `
                        <button class="user-action-btn change-password-btn" data-email="${user.email}">
                            Change Password
                        </button>
                        <button class="user-action-btn edit-btn" data-item='${JSON.stringify(user)}'>
                            <i class="fas fa-pencil-alt"></i> <!-- Font Awesome edit icon -->
                        </button>
                        <button class="user-action-btn delete-btn" data-email="${user.email}">
                            <i class="fas fa-trash"></i> <!-- Font Awesome trash icon -->
                        </button>
                      `
                    : ""}
            </div>
        `;
        userContainer.appendChild(userElement);

        // Attach Delete Button Logic
        if (user.role !== "customer") {
            userElement.querySelector(".delete-btn")?.addEventListener("click", async () => {
                if (confirm(`Are you sure you want to delete user "${user.username}"?`)) {
                    try {
                        console.log(`Deleting user with email: ${user.email}`);
                        await deleteUser(user.email); // Call deleteUser with email instead of ID
                        alert("User deleted successfully!");
                        fetchAllButton?.click(); // Refresh users
                    } catch (error) {
                        console.error("Error deleting user:", error);
                        alert("Failed to delete user.");
                    }
                }
            });
        }
    });
};

/**
 * Toggle Form Visibility
 * @param {HTMLDivElement} formContainer - The form container element
 * @param {boolean} isVisible - Visibility state
 */
const toggleFormVisibility = (formContainer, isVisible) => {
    console.log(`Toggling visibility for ${formContainer.id}. Visible: ${isVisible}`);
    if (isVisible) {
        formContainer.classList.add("visible");
        formContainer.classList.remove("hidden");
    } else {
        formContainer.classList.add("hidden");
        formContainer.classList.remove("visible");
        formContainer.querySelector("form").reset(); // Reset the form when hidden
    }
};

/**
 * Handle Search Input
 */
searchInput?.addEventListener("input", async () => {
    const query = searchInput.value.trim(); // Get the search query
    console.log(`Search query: "${query}"`); // Debugging query

    try {
        if (query.length === 0) {
            console.log("Search query is empty, fetching all users.");
            await fetchAndRenderUsers(); // Fetch all users when the query is empty
            return;
        }

        console.log(`Fetching search results for query: "${query}"`);
        const searchResults = await searchUsers(query);
        console.log("Search results:", searchResults);
        renderUsers(searchResults); // Render the search results
    } catch (error) {
        console.error("Error during search:", error);
        userContainer.innerHTML = "<p>Failed to fetch search results.</p>";
    }
});

/**
 * Fetch and Render All Users on Page Load
 */
const fetchAndRenderUsers = async () => {
    console.log("Fetching all users on page load...");
    userContainer.innerHTML = "<p>Loading users...</p>";
    try {
        const users = await fetchAllUsers();
        console.log("Fetched users:", users);
        renderUsers(users);
    } catch (error) {
        console.error("Error fetching users on page load:", error);
        userContainer.innerHTML = "<p>Failed to load users.</p>";
    }
};

// Trigger fetch on page load
fetchAndRenderUsers();

/**
 * Fetch All Users on Button Click
 */
fetchAllButton?.addEventListener("click", async () => {
    console.log("Fetch All button clicked.");
    await fetchAndRenderUsers();
});

/**
 * Event Listener for Role Dropdown
 */
roleSelect?.addEventListener("change", async () => {
    const selectedRole = roleSelect.value;
    console.log(`Role selected: ${selectedRole}`);

    try {
        console.log("Fetching users by role...");
        const filteredUsers = await fetchUsersByRole(selectedRole);
        console.log("Filtered users by role:", filteredUsers);
        renderUsers(filteredUsers); // Render users based on role
    } catch (error) {
        console.error("Error fetching users by role:", error);
        userContainer.innerHTML = "<p>Failed to fetch users by role.</p>";
    }
});