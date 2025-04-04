import CONFIG from "./config.js"; // Importing the dynamic config

// Define the base URL dynamically using CONFIG.API_BASE_URL
export const PROMO_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/promos`;

/** Fetch all promos from the API */
const fetchAllPromos = async () => {
    try {
        const response = await fetch(PROMO_API_URL);
        console.log("Fetching all promos, response:", response); // Debugging line
        if (!response.ok) throw new Error("Failed to fetch promotions.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching promotions:", error); // Log exact error
        throw error;
    }
};

/** Fetch promos by is_active status */
const fetchPromosByIsActive = async (isActive) => {
    try {
        const response = await fetch(`${PROMO_API_URL}/by-is-active?is_active=${encodeURIComponent(isActive)}`);
        console.log(`Fetching promos with active status (${isActive}), response:`, response); // Debugging line
        if (!response.ok) throw new Error("Failed to fetch promotions by active status.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching promotions by active status:", error);
        throw error;
    }
};

/** Search for promos by promo code */
const searchPromos = async (query) => {
    try {
        const response = await fetch(`${PROMO_API_URL}/search?search=${encodeURIComponent(query)}`);
        console.log(`Searching promos with query "${query}", response:`, response); // Debugging line
        if (!response.ok) throw new Error("Failed to search promotions.");
        return await response.json();
    } catch (error) {
        console.error("Error searching promotions:", error);
        throw error;
    }
};

/** Delete a promo */
const deletePromo = async (promoId) => {
    try {
        const response = await fetch(PROMO_API_URL, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: promoId }) // Send JSON data
        });
        console.log(`Deleting promo with ID: ${promoId}, response:`, response); // Debugging line
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`Failed to delete promotion: ${errorData.message}`);
        }
    } catch (error) {
        console.error("Error deleting promotion:", error);
        throw error;
    }
};

/** Add a new promo */
const addPromo = async (promoData) => {
    try {
        console.log("Adding new promo, data:", promoData); // Debugging line
        const response = await fetch(PROMO_API_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(promoData) // Convert object to JSON
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`Failed to add promotion: ${errorData.message}`);
        }

        return await response.json(); // Return the API response
    } catch (error) {
        console.error("Error adding promotion:", error);
        throw error;
    }
};

/** Update an existing promo */
const updatePromo = async (promoData) => {
    try {
        console.log("Updating promo, data:", promoData); // Debugging line
        const response = await fetch(PROMO_API_URL, {
            method: "PUT",
            headers: { "Content-Type": "application/json" }, // Use JSON
            body: JSON.stringify(promoData) // Convert object to JSON string
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`Failed to update promotion: ${errorData.message}`);
        }

        return await response.json(); // Return the API response
    } catch (error) {
        console.error("Error updating promotion:", error);
        throw error;
    }
};

// Exporting API functions for use in promoUI.js
export {
    fetchAllPromos,
    fetchPromosByIsActive,
    searchPromos,
    deletePromo,
    addPromo,
    updatePromo
};