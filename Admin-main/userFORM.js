import { addUser, updateUser, changePasswordAdmin } from "./userAPI.js";

// Wait until the DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
    // General DOM References for Forms
    const addUserFormContainer = document.getElementById("user-add-item-form");
    const addUserForm = addUserFormContainer?.querySelector("form");

    const editUserFormContainer = document.getElementById("edit-user-item-section");
    const editUserForm = editUserFormContainer?.querySelector("form");

    const changePasswordFormContainer = document.getElementById("change-password-form-section");
    const changePasswordForm = changePasswordFormContainer?.querySelector("form");

    // Buttons
    const addUserBtn = document.getElementById("user-add-item-btn");
    const cancelAddUserBtn = document.getElementById("user-cancel-add-item-btn");
    const cancelEditUserBtn = document.getElementById("user-cancel-edit-item-btn");
    const cancelChangePasswordBtn = document.getElementById("change-password-cancel-btn");

    // Password Visibility Toggles
    const addUserPasswordInput = document.getElementById("user-password");
    const addUserToggleVisibilityCheckbox = document.getElementById("toggle-password-visibility");

    const changePasswordNewInput = document.getElementById("change-password-new-password");
    const toggleChangePasswordVisibilityCheckbox = document.getElementById("toggle-change-password-visibility");

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

    // Password Visibility Toggle Logic for Add User Form
    addUserToggleVisibilityCheckbox?.addEventListener("change", () => {
        addUserPasswordInput.type = addUserToggleVisibilityCheckbox.checked ? "text" : "password";
    });

    // Password Visibility Toggle Logic for Change Password Form
    toggleChangePasswordVisibilityCheckbox?.addEventListener("change", () => {
        changePasswordNewInput.type = toggleChangePasswordVisibilityCheckbox.checked ? "text" : "password";
    });

    // Add User Form Behavior
    addUserBtn?.addEventListener("click", () => {
        console.log("Add User button clicked.");
        toggleFormVisibility(addUserFormContainer, true);
    });

    cancelAddUserBtn?.addEventListener("click", () => {
        console.log("Add User Cancel button clicked.");
        toggleFormVisibility(addUserFormContainer, false);
    });

    addUserForm?.addEventListener("submit", async (event) => {
        event.preventDefault();
        console.log("Submitting Add User form...");
        const submitButton = addUserForm.querySelector("button[type='submit']");
        submitButton.disabled = true; // Prevent double submission

        const formData = new FormData(addUserForm);
        formData.set("active", addUserForm.elements["is_active"].checked ? 1 : 0);
        formData.delete("is_active"); // Clean up
        const userData = Object.fromEntries(formData.entries());

        try {
            const response = await addUser(userData); // Call API
            console.log("Add User response:", response);
            alert("User added successfully!");
            toggleFormVisibility(addUserFormContainer, false);
            window.location.reload(); // Ensure page refreshes
        } catch (error) {
            console.error("Error adding user:", error);
            alert("Failed to add user. Please try again.");
        } finally {
            submitButton.disabled = false; // Re-enable the button
        }
    });

    /** Event Listener for Edit Button */
    document.addEventListener("click", (event) => {
        const editButton = event.target.closest(".edit-btn"); // Ensure the event is triggered for the button or its child
        if (editButton) {
            const userData = JSON.parse(editButton.dataset.item);
            console.log("Edit button clicked for:", userData);

            if (!editUserFormContainer) {
                console.error("Edit User Form Container not found.");
                return;
            }

            // Populate form fields with user data
            const editFormElements = editUserForm.elements;

            if (editFormElements["id"]) {
                editFormElements["id"].value = userData.id || "";
            }
            if (editFormElements["username"]) {
                editFormElements["username"].value = userData.username || "";
            }
            if (editFormElements["email"]) {
                editFormElements["email"].value = userData.email || "";
            }
            if (editFormElements["role"]) {
                editFormElements["role"].value = userData.role || "";
            }
            if (editFormElements["is_active"]) {
                // Automatically check the "Active" checkbox if the user is active
                editFormElements["is_active"].checked = userData.active === "1" || userData.active === 1;
            }

            toggleFormVisibility(editUserFormContainer, true);
        }
    });

    // Cancel button logic for Edit User form
    cancelEditUserBtn?.addEventListener("click", () => {
        console.log("Edit User Cancel button clicked.");
        toggleFormVisibility(editUserFormContainer, false);
    });

    editUserForm?.addEventListener("submit", async (event) => {
        event.preventDefault();
        console.log("Submitting Edit User form...");
        const submitButton = editUserForm.querySelector("button[type='submit']");
        submitButton.disabled = true; // Prevent double submission

        const formData = new FormData(editUserForm);
        formData.set("active", editUserForm.elements["is_active"].checked ? 1 : 0);
        formData.delete("is_active");
        const userData = Object.fromEntries(formData.entries());

        try {
            const response = await updateUser(userData); // Call API
            console.log("Edit User response:", response);
            alert("User updated successfully!");
            toggleFormVisibility(editUserFormContainer, false);
            window.location.reload(); // Ensure page refreshes
        } catch (error) {
            console.error("Error updating user:", error);
            alert("Failed to update user. Please try again.");
        } finally {
            submitButton.disabled = false; // Re-enable the button
        }
    });

    /** Attach Click Listener for Change Password Button */
    document.addEventListener("click", (event) => {
        if (event.target.classList.contains("change-password-btn")) {
            const email = event.target.dataset.email;
            console.log("Change Password button clicked for email:", email);

            if (!changePasswordFormContainer) {
                console.error("Change Password Form Container not found.");
                return;
            }

            // Pre-fill the email field with the provided email
            const formElements = changePasswordForm.elements;
            if (formElements["email"]) {
                formElements["email"].value = email || "";
            }

            // Show the Change Password form
            toggleFormVisibility(changePasswordFormContainer, true);
        }
    });

    // Cancel button logic for Change Password form
    cancelChangePasswordBtn?.addEventListener("click", () => {
        console.log("Change Password Cancel button clicked.");
        toggleFormVisibility(changePasswordFormContainer, false);
    });

    /** Attach Submit Logic for Change Password Form */
    changePasswordForm?.addEventListener("submit", async (event) => {
        event.preventDefault();
        console.log("Submitting Change Password form...");
        const submitButton = changePasswordForm.querySelector("button[type='submit']");
        submitButton.disabled = true; // Prevent double submission

        // Collect form data
        const formData = new FormData(changePasswordForm);
        const payload = Object.fromEntries(formData.entries());

        // Add CAPTCHA response
        const captchaResponse = grecaptcha.getResponse();
        if (!captchaResponse) {
            alert("Please complete CAPTCHA verification.");
            submitButton.disabled = false; // Re-enable the button
            return;
        }
        payload["captcha"] = captchaResponse;

        try {
            const response = await changePasswordAdmin(payload.email, payload.new_password, payload.captcha);
            console.log("Change Password response:", response);

            alert("Password changed successfully!");
            toggleFormVisibility(changePasswordFormContainer, false); // Hide the form after submission
            window.location.reload(); // Ensure page refreshes
        } catch (error) {
            console.error("Error changing password:", error);
            alert("Failed to change password. Please try again.");
        } finally {
            grecaptcha.reset(); // Reset CAPTCHA after the process
            submitButton.disabled = false; // Re-enable the button
        }
    });
});