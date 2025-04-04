// menuUI.js - Handles UI logic and integrates with menuAPI.js
import {
    fetchAllMenuItems,
    fetchMenuItemsByCategory,
    fetchMenuItemsBySubCategory,
    deleteMenuItem,
    searchMenuItems
} from "./menuAPI.js";

// Subcategory Data (Mapping Subcategories to Categories)
const subcategories = {
    coffee: ["Hot", "Iced", "Non-Coffee"],
    food: ["Pastry", "Pasta", "Sandwich"],
};

// DOM Elements
const menuOverlay = document.createElement("div");
const menuContainer = document.getElementById("menu-items-container");
const fetchAllButton = document.getElementById("menu-fetch-all-btn");
const categorySelect = document.getElementById("menu-category");
const subcategorySelect = document.getElementById("menu-subcategory");
const searchInput = document.getElementById("menu-search-input"); // Search input box

// Append overlay to the body
menuOverlay.classList.add("menu-overlay");
document.body.appendChild(menuOverlay);

// Utility Functions

/**
 * Update Subcategory Options Based on the Selected Category
 * @param {HTMLSelectElement} subcategoryDropdown - The subcategory dropdown to update
 * @param {string} category - The selected category
 */
const updateSubcategoryOptions = (subcategoryDropdown, category) => {
    console.log(`Updating subcategories for category: ${category}`);
    subcategoryDropdown.innerHTML = "<option value=''>Select Subcategory</option>"; // Clear current options

    if (subcategories[category]) {
        subcategories[category].forEach((subcategory) => {
            const option = document.createElement("option");
            option.value = subcategory.toLowerCase().replace(/ /g, "-");
            option.textContent = subcategory; // Display text
            subcategoryDropdown.appendChild(option);
        });
    } else {
        console.log("No subcategories available for this category.");
    }
};

/**
 * Render menu items in the container
 * @param {Array} items - The list of menu items
 */
const renderMenuItems = (items) => {
    console.log("Rendering menu items...");
    menuContainer.innerHTML = ""; // Clear container
    items.forEach(item => {
        const availabilityClass = item.is_available ? "available" : "unavailable";
        const availabilityText = item.is_available ? "Available" : "Unavailable";

        const menuItemElement = document.createElement("div");
        menuItemElement.classList.add("menu-item-card");
        menuItemElement.innerHTML = `
            <div class="menu-item-image-container">
                <img src="${item.image_url}" alt="${item.name}" class="menu-item-image" data-item='${JSON.stringify(item)}' />
                <p class="menu-item-image-label" style="font-size: 12px; color: #888; margin-top: 5px;">Click to change <br> image</p> <!-- Smaller Label -->
            </div>
            <div class="menu-item-details">
                <p><strong>Name:</strong> ${item.name}</p>
                <p><strong>Description:</strong> ${item.description || "No description available."}</p>
                <p><strong>Price (Small):</strong> ${item.price_small || "N/A"}</p>
                <p><strong>Price (Medium):</strong> ${item.price_medium || "N/A"}</p>
                <p><strong>Price (Large):</strong> ${item.price_large || "N/A"}</p>
                <p><strong>Category:</strong> ${item.category}</p>
                <p><strong>Subcategory:</strong> ${item.subcategory || "N/A"}</p>
                <p class="menu-availability ${availabilityClass}"><strong id="availability">${availabilityText}</strong></p>
            </div>
            <div class="menu-item-actions">
                <button class="menu-action-btn edit-btn" data-item='${JSON.stringify(item)}'>
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <button class="menu-action-btn delete-btn" data-id="${item.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        menuContainer.appendChild(menuItemElement);

        // Attach Delete Button Logic
        menuItemElement.querySelector(".delete-btn").addEventListener("click", async () => {
            if (confirm(`Are you sure you want to delete item "${item.name}"?`)) {
                try {
                    await deleteMenuItem(item.id);
                    alert("Menu item deleted successfully!");
                    fetchAllButton?.click(); // Refresh items
                } catch (error) {
                    console.error("Error deleting menu item:", error);
                    alert("Failed to delete menu item.");
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
        // If the query is empty, fetch and display all menu items
        fetchAndRenderMenuItems();
        return;
    }

    try {
        console.log(`Searching menu items for query: "${query}"`);
        const searchResults = await searchMenuItems(query); // Fetch search results
        console.log("Search results:", searchResults); // Debug log
        renderMenuItems(searchResults); // Render the search results
    } catch (error) {
        console.error("Error while searching menu items:", error);
        menuContainer.innerHTML = "<p>Failed to fetch search results.</p>";
    }
});

/**
 * Fetch and Render All Menu Items on Page Load
 */
const fetchAndRenderMenuItems = async () => {
    console.log("Fetching all menu items on page load...");
    menuContainer.innerHTML = "<p>Loading menu items...</p>";
    try {
        const items = await fetchAllMenuItems();
        renderMenuItems(items);
    } catch (error) {
        console.error("Error fetching menu items on page load:", error);
        menuContainer.innerHTML = "<p>Failed to load menu items.</p>";
    }
};

// Trigger fetch on page load
fetchAndRenderMenuItems();

/**
 * Fetch All Items on Button Click
 */
fetchAllButton?.addEventListener("click", fetchAndRenderMenuItems);

/**
 * Event Listener for Category Dropdown
 */
categorySelect?.addEventListener("change", async () => {
    const selectedCategory = categorySelect.value;
    console.log(`Category selected: ${selectedCategory}`);
    updateSubcategoryOptions(subcategorySelect, selectedCategory); // Update subcategories

    try {
        const filteredItems = await fetchMenuItemsByCategory(selectedCategory);
        renderMenuItems(filteredItems); // Render items based on the selected category
    } catch (error) {
        console.error("Error fetching menu items by category:", error);
    }
});

/**
 * Event Listener for Subcategory Dropdown
 */
subcategorySelect?.addEventListener("change", async () => {
    const selectedSubcategory = subcategorySelect.value;
    console.log(`Subcategory selected: ${selectedSubcategory}`);

    try {
        const filteredItems = await fetchMenuItemsBySubCategory(selectedSubcategory);
        renderMenuItems(filteredItems); // Render items based on the selected subcategory
    } catch (error) {
        console.error("Error fetching menu items by subcategory:", error);
    }
});