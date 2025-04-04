import CONFIG from "./config.js"; // Import dynamic base URL from config.js

// Full URL for users API
export const USER_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/users`;

/** Fetch all users from the API */
const fetchAllUsers = async () => {
    try {
        const response = await fetch(USER_API_URL);
        console.log("Fetching all users, response:", response); // Debugging line
        if (!response.ok) throw new Error("Failed to fetch users.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching users:", error); // Log exact error
        throw error;
    }
};

/** Fetch users by role */
const fetchUsersByRole = async (role) => {
    try {
        const response = await fetch(`${USER_API_URL}/role?role=${encodeURIComponent(role)}`);
        console.log(`Fetching users with role (${role}), response:`, response); // Debugging line
        if (!response.ok) throw new Error("Failed to fetch users by role.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching users by role:", error);
        throw error;
    }
};

/** Search for users by username */
const searchUsers = async (query) => {
    try {
        const response = await fetch(`${USER_API_URL}/search?username=${encodeURIComponent(query)}`);
        console.log(`Searching users with query "${query}", response:`, response); // Debugging line
        if (!response.ok) throw new Error("Failed to search users.");
        return await response.json();
    } catch (error) {
        console.error("Error searching users:", error);
        throw error;
    }
};

/** Delete a user */
const deleteUser = async (email) => {
    try {
        const response = await fetch(USER_API_URL, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email: email }) // Send email instead of ID
        });
        console.log(`Deleting user with email: ${email}, response:`, response); // Debugging line
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`Failed to delete user: ${errorData.message}`);
        }
    } catch (error) {
        console.error("Error deleting user:", error);
        throw error;
    }
};

/** Add a new user */
const addUser = async (userData) => {
    try {
        console.log("Adding new user, data:", userData); // Debugging line
        const response = await fetch(`${USER_API_URL}/create-for-admin`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(userData), // Send user data as JSON
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || "Failed to add user.");
        }

        // Parse the API response
        const responseData = await response.json();
        return responseData;
    } catch (error) {
        console.error("Error adding user:", error);
        throw error;
    }
};

/** Update an existing user */
const updateUser = async (userData) => {
    try {
        console.log("Updating user, data:", userData); // Debugging line
        const response = await fetch(USER_API_URL, {
            method: "PUT",
            headers: { "Content-Type": "application/json" }, // Use JSON
            body: JSON.stringify(userData) // Convert object to JSON string
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`Failed to update user: ${errorData.message}`);
        }

        return await response.json(); // Return the API response
    } catch (error) {
        console.error("Error updating user:", error);
        throw error;
    }
};

/** Change Password (Admin) */
const changePasswordAdmin = async (email, newPassword, captchaResponse) => {
    try {
        console.log("Changing password for email:", email); // Debugging line
        const response = await fetch(`${USER_API_URL}/change-password-admin`, {
            method: "PUT", // Ensure the correct method
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                email: email,
                new_password: newPassword,
                captcha: captchaResponse // Include CAPTCHA response
            }),
        });

        // Check if the response body is valid
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({})); // Safely handle non-JSON errors
            throw new Error(errorData.message || `Request failed with status ${response.status}`);
        }

        // Handle empty response gracefully
        const responseData = await response.text(); // Read response as text
        return responseData ? JSON.parse(responseData) : {}; // Parse if not empty
    } catch (error) {
        console.error("Error changing password:", error);
        throw error; // Re-throw for calling function to handle
    }
};

// Exporting API functions for use in the UI
export {
    fetchAllUsers,
    fetchUsersByRole,
    searchUsers,
    deleteUser,
    addUser,
    updateUser,
    changePasswordAdmin, // Updated Change Password Admin API function
};