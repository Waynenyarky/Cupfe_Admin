import CONFIG from "./config.js"; // Importing the dynamic config

// Define the base URLs dynamically using CONFIG.API_BASE_URL
export const BUNDLE_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/table_reservations`;
export const BUNDLE_SEARCH_URL = `${BUNDLE_API_URL}/search`;
export const BUNDLE_BUNDLE_URL = `${BUNDLE_API_URL}/bundle`;

/** Fetch all bundles */
const fetchAllBundles = async () => {
    try {
        const response = await fetch(BUNDLE_API_URL);
        if (!response.ok) throw new Error("Failed to fetch all bundles.");
        return await response.json(); // Return all bundles
    } catch (error) {
        console.error("Error fetching all bundles:", error);
        throw error;
    }
};

/** Search bundle by reference number */
const fetchBundleByReferenceNumber = async (referenceNumber) => {
    try {
        const response = await fetch(`${BUNDLE_SEARCH_URL}?search=${encodeURIComponent(referenceNumber)}`);
        if (!response.ok) throw new Error("Failed to fetch bundle by reference number.");
        return await response.json(); // Return search results
    } catch (error) {
        console.error("Error searching bundle by reference number:", error);
        throw error;
    }
};

/** Fetch bundles by bundle type */
const fetchBundlesByType = async (bundleType) => {
    try {
        const response = await fetch(`${BUNDLE_BUNDLE_URL}?bundle=${encodeURIComponent(bundleType)}`);
        if (!response.ok) throw new Error("Failed to fetch bundles by type.");
        return await response.json(); // Return filtered bundles
    } catch (error) {
        console.error("Error fetching bundles by type:", error);
        throw error;
    }
};

// Exporting the API functions for use in bundleUI.js
export {
    fetchAllBundles,
    fetchBundleByReferenceNumber,
    fetchBundlesByType
};