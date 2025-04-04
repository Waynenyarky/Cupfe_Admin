import CONFIG from "./config.js"; // Importing the dynamic config

// Define the base URL dynamically using CONFIG.API_BASE_URL
export const API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/items`;

/** Fetch all menu items from the API */
const fetchAllMenuItems = async () => {
    try {
        const response = await fetch(API_URL);
        if (!response.ok) throw new Error("Failed to fetch menu items.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching menu items:", error);
        throw error;
    }
};

/** Fetch menu items by category */
const fetchMenuItemsByCategory = async (category) => {
    try {
        const response = await fetch(`${API_URL}/category?category=${encodeURIComponent(category)}`);
        if (!response.ok) throw new Error("Failed to fetch menu items by category.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching menu items by category:", error);
        throw error;
    }
};

/** Fetch menu items by subcategory */
const fetchMenuItemsBySubCategory = async (subcategory) => {
    try {
        const response = await fetch(`${API_URL}/subcategory?subcategory=${encodeURIComponent(subcategory)}`);
        if (!response.ok) throw new Error("Failed to fetch menu items by subcategory.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching menu items by subcategory:", error);
        throw error;
    }
};

/** Search for menu items by name */
const searchMenuItems = async (query) => {
    try {
        const response = await fetch(`${API_URL}/search?search=${encodeURIComponent(query)}`);
        if (!response.ok) throw new Error("Failed to search menu items.");
        return await response.json();
    } catch (error) {
        console.error("Error searching menu items:", error);
        throw error;
    }
};

/** Delete a menu item */
const deleteMenuItem = async (itemId) => {
    try {
        const response = await fetch(API_URL, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: itemId }),
        });
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`Failed to delete menu item: ${errorData.message}`);
        }
        return await response.json();
    } catch (error) {
        console.error("Error deleting menu item:", error);
        throw error;
    }
};

/** Add or Update a menu item */
const saveMenuItem = async (menuItemData) => {
    try {
        console.log("Sending FormData to API:", Object.fromEntries(menuItemData.entries())); // Debugging payload

        const response = await fetch(API_URL, {
            method: "POST", // Unified POST method for both Create and Update
            body: menuItemData, // Send FormData directly
        });

        if (!response.ok) {
            const errorData = await response.json();
            console.error("API Error Response:", errorData);
            throw new Error(`Failed to save menu item: ${errorData.message}`);
        }

        return await response.json();
    } catch (error) {
        console.error("Error saving menu item:", error);
        throw error;
    }
};

// Exporting API functions
export {
    fetchAllMenuItems,
    fetchMenuItemsByCategory,
    fetchMenuItemsBySubCategory,
    searchMenuItems,
    deleteMenuItem,
    saveMenuItem, // Combined function for Add and Update
};