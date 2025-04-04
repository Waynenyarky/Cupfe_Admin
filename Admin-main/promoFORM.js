// promoFORM.js - Handles Add and Edit Promo Form Logic
import { addPromo, updatePromo } from "./promoAPI.js";

// Wait until the DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
    // General DOM References
    const addPromoFormContainer = document.getElementById("promo-add-item-form");
    const addPromoForm = addPromoFormContainer?.querySelector("form");

    const editPromoFormContainer = document.getElementById("edit-promo-item-section");
    const editPromoForm = editPromoFormContainer?.querySelector("form");

    const addPromoBtn = document.getElementById("promo-add-item-btn");
    const cancelAddPromoBtn = document.getElementById("promo-cancel-add-item-btn");
    const cancelEditPromoBtn = document.getElementById("promo-cancel-edit-item-btn");

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

    // Add Promo Form Behavior
    addPromoBtn?.addEventListener("click", () => {
        console.log("Add Promo button clicked.");
        toggleFormVisibility(addPromoFormContainer, true);
    });

    cancelAddPromoBtn?.addEventListener("click", () => {
        console.log("Add Promo Cancel button clicked.");
        toggleFormVisibility(addPromoFormContainer, false);
    });

    addPromoForm?.addEventListener("submit", async (event) => {
        event.preventDefault();
        console.log("Submitting Add Promo form...");
        const formData = new FormData(addPromoForm);

        // Handle `is_active` checkbox explicitly
        const isActive = addPromoForm.elements["is_active"].checked ? 1 : 0;
        formData.set("is_active", isActive);

        // Convert FormData to a regular object for the API call
        const promoData = Object.fromEntries(formData.entries());

        // Debugging: Log the JSON body for API submission
        console.log("JSON body for Add Promo API submission:", promoData);

        try {
            const response = await addPromo(promoData); // Call API
            console.log("Add Promo response:", response);
            alert("Promo added successfully!");
            toggleFormVisibility(addPromoFormContainer, false);
            window.location.reload(); // Refresh the page to show updated promos
        } catch (error) {
            console.error("Error adding promo:", error);
            alert("Failed to add promo. Please try again.");
        }
    });

    /**
     * Event Listener for Edit Button
     */
    document.addEventListener("click", (event) => {
        if (event.target.classList.contains("edit-btn")) {
            const promoData = JSON.parse(event.target.dataset.item);
            console.log("Edit button clicked for:", promoData);

            if (!editPromoFormContainer) {
                console.error("Edit Promo Form Container not found.");
                return;
            }

            // Populate form fields with promo data
            const editFormElements = editPromoForm.elements;

            if (editFormElements["id"]) {
                editFormElements["id"].value = promoData.id || "";
            }
            if (editFormElements["code"]) {
                editFormElements["code"].value = promoData.code || "";
            }
            if (editFormElements["discount"]) {
                editFormElements["discount"].value = promoData.discount || "";
            }
            if (editFormElements["is_active"]) {
                editFormElements["is_active"].checked = promoData.is_active === 1;
            }

            // Show the Edit Form
            toggleFormVisibility(editPromoFormContainer, true);
        }
    });

    /**
     * Attach Submit Logic for Edit Promo
     */
    editPromoForm?.addEventListener("submit", async (event) => {
        event.preventDefault();
        console.log("Submitting Edit Promo form...");
        const formData = new FormData(editPromoForm);

        // Handle `is_active` checkbox explicitly
        const isActive = editPromoForm.elements["is_active"].checked ? 1 : 0;
        formData.set("is_active", isActive);

        // Convert FormData to a regular object
        const promoData = Object.fromEntries(formData.entries());

        // Debugging: Log the JSON body for API submission
        console.log("JSON body for Edit Promo API submission:", promoData);

        try {
            const response = await updatePromo(promoData); // Call API
            console.log("Edit Promo response:", response);
            alert("Promo updated successfully!");
            toggleFormVisibility(editPromoFormContainer, false);
            window.location.reload(); // Refresh the promo list
        } catch (error) {
            console.error("Error updating promo:", error);
            alert("Failed to update promo. Please try again.");
        }
    });

    // Cancel button logic for Edit Promo form
    cancelEditPromoBtn?.addEventListener("click", () => {
        console.log("Edit Promo Cancel button clicked.");
        toggleFormVisibility(editPromoFormContainer, false); // Hide the Edit form
    });
});