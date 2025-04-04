import { saveMenuItem } from "./menuAPI.js"; // Unified function for both Add and Edit

// Wait until the DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
    // Subcategories Mapping
    const subcategories = {
        coffee: ["Hot", "Iced", "Non-Coffee"],
        food: ["Pastry", "Pasta", "Sandwich"],
    };

    // General DOM References
    const addMenuItemFormContainer = document.getElementById("menu-add-item-form");
    const addMenuItemForm = addMenuItemFormContainer?.querySelector("form");

    const editMenuItemFormContainer = document.getElementById("edit-menu-item-section");
    const editMenuItemForm = editMenuItemFormContainer?.querySelector("form");

    const changeImageFormContainer = document.getElementById("change-menu-item-image-section");
    const changeImageForm = changeImageFormContainer?.querySelector("form");
    const cancelChangeImageBtn = document.getElementById("cancel-change-menu-item-image-btn");

    const addMenuItemBtn = document.getElementById("menu-add-item-btn");
    const cancelAddMenuItemBtn = document.getElementById("menu-cancel-add-item-btn");
    const cancelEditMenuItemBtn = document.getElementById("menu-cancel-edit-item-btn");

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

    // Add Menu Item Form Behavior
    addMenuItemBtn?.addEventListener("click", () => {
        console.log("Add Menu Item button clicked.");
        toggleFormVisibility(addMenuItemFormContainer, true);
    });

    cancelAddMenuItemBtn?.addEventListener("click", () => {
        console.log("Add Menu Item Cancel button clicked.");
        toggleFormVisibility(addMenuItemFormContainer, false);
    });

    /** Add Menu Item Form Logic **/
    addMenuItemForm?.addEventListener("submit", async (event) => {
        event.preventDefault();
        console.log("Submitting Add Menu Item form...");
        const formData = new FormData(addMenuItemForm);

        // Ensure `is_available` is sent even when unchecked
        const isAvailable = addMenuItemForm.elements["is_available"].checked ? 1 : 0;
        formData.set("is_available", isAvailable);

        try {
            const responseData = await saveMenuItem(formData); // Use the parsed JSON returned by saveMenuItem
            console.log("Add Menu Item response:", responseData);

            alert(responseData.message || "Menu item added successfully!");
            toggleFormVisibility(addMenuItemFormContainer, false);
            window.location.reload(); // Refresh the menu
        } catch (error) {
            console.error("Error adding menu item:", error);
            alert("Failed to add menu item. Please try again.");
        }
    });

    /** 
     * Event Listener for Clicking an Item Image
     */
    document.addEventListener("click", (event) => {
        if (event.target.classList.contains("menu-item-image")) {
            const menuItemData = JSON.parse(event.target.dataset.item);
            console.log("Image clicked for:", menuItemData);

            if (!changeImageFormContainer) {
                console.error("Change Image Form Container not found.");
                return;
            }

            // Populate the Change Image Form with item's data
            const changeImageFormElements = changeImageForm.elements;
            changeImageFormElements["id"].value = menuItemData.id || "";
            changeImageFormElements["name"].value = menuItemData.name || "";
            changeImageFormElements["description"].value = menuItemData.description || "";
            changeImageFormElements["price_small"].value = menuItemData.price_small || "";
            changeImageFormElements["price_medium"].value = menuItemData.price_medium || "";
            changeImageFormElements["price_large"].value = menuItemData.price_large || "";
            changeImageFormElements["category"].value = menuItemData.category || "";
            changeImageFormElements["subcategory"].value = menuItemData.subcategory || "";
            changeImageFormElements["is_available"].value = menuItemData.is_available ? "1" : "0";

            // Show the Change Image Form
            toggleFormVisibility(changeImageFormContainer, true);
        }
    });

    /** 
     * Change Image Form Submit Logic
     */
    changeImageForm?.addEventListener("submit", async (event) => {
        event.preventDefault();
        console.log("Submitting Change Image form...");
        const formData = new FormData(changeImageForm);

        try {
            const responseData = await saveMenuItem(formData); // Use the parsed JSON returned by saveMenuItem
            console.log("Change Image response:", responseData);

            alert(responseData.message || "Image updated successfully!");
            toggleFormVisibility(changeImageFormContainer, false);
            window.location.reload(); // Refresh the menu
        } catch (error) {
            console.error("Error updating image:", error);
            alert("Failed to update image. Please try again.");
        }
    });

    // Cancel button logic for Change Image form
    cancelChangeImageBtn?.addEventListener("click", () => {
        console.log("Change Image Cancel button clicked.");
        toggleFormVisibility(changeImageFormContainer, false);
    });

    /** 
     * Event Listener for Edit Button
     */
    document.addEventListener("click", (event) => {
        if (event.target.classList.contains("edit-btn")) {
            const menuItemData = JSON.parse(event.target.dataset.item);
            console.log("Edit button clicked for:", menuItemData);

            if (!editMenuItemFormContainer) {
                console.error("Edit Menu Item Form Container not found.");
                return;
            }

            // Populate form fields
            const editFormElements = editMenuItemForm.elements;
            editFormElements["id"].value = menuItemData.id || "";
            editFormElements["name"].value = menuItemData.name || "";
            editFormElements["description"].value = menuItemData.description || "";
            editFormElements["price_small"].value = menuItemData.price_small || "";
            editFormElements["price_medium"].value = menuItemData.price_medium || "";
            editFormElements["price_large"].value = menuItemData.price_large || "";
            editFormElements["category"].value = menuItemData.category || "";
            editFormElements["image_url"].value = menuItemData.image_url || "";
            editFormElements["subcategory"].value = menuItemData.subcategory || "";
            editFormElements["is_available"].checked = menuItemData.is_available === 1;

            // Show the Edit Form
            toggleFormVisibility(editMenuItemFormContainer, true);
        }
    });

    /** 
     * Edit Menu Item Form Submit Logic
     */
    editMenuItemForm?.addEventListener("submit", async (event) => {
        event.preventDefault();
        console.log("Submitting Edit Menu Item form...");
        const formData = new FormData(editMenuItemForm);

        // Ensure `is_available` is sent even when unchecked
        const isAvailable = editMenuItemForm.elements["is_available"].checked ? 1 : 0;
        formData.set("is_available", isAvailable);

        try {
            const responseData = await saveMenuItem(formData); // Use the parsed JSON returned by saveMenuItem
            console.log("Edit Menu Item response:", responseData);

            alert(responseData.message || "Menu item updated successfully!");
            toggleFormVisibility(editMenuItemFormContainer, false);
            window.location.reload(); // Refresh the menu
        } catch (error) {
            console.error("Error updating menu item:", error);
            alert("Failed to update menu item. Please try again.");
        }
    });

    // Cancel button logic for Edit Menu Item form
    cancelEditMenuItemBtn?.addEventListener("click", () => {
        console.log("Edit Menu Item Cancel button clicked.");
        toggleFormVisibility(editMenuItemFormContainer, false);
    });
});