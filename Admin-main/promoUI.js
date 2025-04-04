// promoUI.js - Handles UI logic and integrates with promoAPI.js
import {
    fetchAllPromos,
    fetchPromosByIsActive,
    searchPromos,
    deletePromo
} from "./promoAPI.js";

// DOM Elements
const promoContainer = document.getElementById("promo-items-container");
const fetchAllButton = document.getElementById("promo-fetch-all-btn");
const isActiveSelect = document.getElementById("promo-is-active");
const searchInput = document.getElementById("promo-search-input"); // Search input box

// Utility Functions

/**
 * Render promos in the container
 * @param {Array} promos - The list of promos
 */
const renderPromos = (promos) => {
    console.log("Rendering promos...");
    promoContainer.innerHTML = ""; // Clear container

    if (promos.length === 0) {
        promoContainer.innerHTML = "<p>No promotions found.</p>";
        return;
    }

    promos.forEach(promo => {
        const isActiveClass = promo.is_active ? "active-status" : "inactive-status"; // Update class
        const isActiveText = promo.is_active ? "Active" : "Inactive";

        const promoElement = document.createElement("div");
        promoElement.classList.add("promo-item-card");
        promoElement.innerHTML = `
            <div class="promo-item-details">
                <p><strong>Promo Code:</strong> ${promo.code}</p>
                <p><strong>Discount:</strong> ${promo.discount} PESOS</p>
                <p><strong>Created At:</strong> ${promo.created_at}</p>
                <p><strong>Updated At:</strong> ${promo.updated_at || "Never Updated"}</p>
                <p class="promo-status ${isActiveClass}"><strong>${isActiveText}</strong></p> <!-- Move status to the bottom -->
            </div>
            <div class="promo-item-actions">
                <button class="promo-action-btn edit-btn" data-item='${JSON.stringify(promo)}'>
                    <i class="fas fa-pencil-alt"></i> <!-- Font Awesome edit icon -->
                </button>
                <button class="promo-action-btn delete-btn" data-id="${promo.id}">
                    <i class="fas fa-trash"></i> <!-- Font Awesome trash icon -->
                </button>
            </div>
        `;
        promoContainer.appendChild(promoElement);

        // Attach Delete Button Logic
        promoElement.querySelector(".delete-btn").addEventListener("click", async () => {
            if (confirm(`Are you sure you want to delete promo "${promo.code}"?`)) {
                try {
                    console.log(`Deleting promo with ID: ${promo.id}`);
                    await deletePromo(promo.id);
                    alert("Promo deleted successfully!");
                    fetchAllButton?.click(); // Refresh promos
                } catch (error) {
                    console.error("Error deleting promo:", error);
                    alert("Failed to delete promo.");
                }
            }
        });
    });
};

/**
 * Event Listener for Search Input
 */
searchInput?.addEventListener("input", async () => {
    const query = searchInput.value.trim(); // Get the search query
    if (query.length === 0) {
        // If the query is empty, fetch and display all promos
        fetchAndRenderPromos();
        return;
    }

    try {
        console.log(`Searching promos for query: "${query}"`);
        const searchResults = await searchPromos(query); // Fetch search results
        console.log("Search results:", searchResults); // Debug log
        renderPromos(searchResults); // Render the search results
    } catch (error) {
        console.error("Error while searching promos:", error);
        promoContainer.innerHTML = "<p>Failed to fetch search results.</p>";
    }
});

/**
 * Fetch and Render All Promos on Page Load
 */
const fetchAndRenderPromos = async () => {
    console.log("Fetching all promos on page load...");
    promoContainer.innerHTML = "<p>Loading promotions...</p>";
    try {
        const promos = await fetchAllPromos();
        console.log("Fetched promos:", promos); // Debugging response
        renderPromos(promos);
    } catch (error) {
        console.error("Error fetching promos on page load:", error);
        promoContainer.innerHTML = "<p>Failed to load promotions.</p>";
    }
};

// Trigger fetch on page load
fetchAndRenderPromos();

/**
 * Fetch All Promos on Button Click
 */
fetchAllButton?.addEventListener("click", async () => {
    console.log("Fetch All button clicked.");
    await fetchAndRenderPromos();
});

/**
 * Event Listener for IsActive Dropdown
 */
isActiveSelect?.addEventListener("change", async () => {
    const isActiveValue = isActiveSelect.value;
    console.log(`Active status selected: ${isActiveValue}`);

    try {
        console.log("Fetching promos by active status...");
        const filteredPromos = await fetchPromosByIsActive(isActiveValue);
        console.log("Filtered promos:", filteredPromos); // Debugging filtered data
        renderPromos(filteredPromos); // Render promos based on active status
    } catch (error) {
        console.error("Error fetching promos by active status:", error);
        promoContainer.innerHTML = "<p>Failed to fetch filtered promotions.</p>";
    }
});