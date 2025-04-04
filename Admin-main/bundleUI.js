// bundleUI.js - Handles UI logic and integrates with bundleAPI.js
import {
    fetchAllBundles,
    fetchBundleByReferenceNumber,
    fetchBundlesByType
} from "./bundleAPI.js"; // Importing updated API functions

// DOM Elements
const bundleContainer = document.getElementById("bundle-items-container");
const refreshButton = document.getElementById("refresh-bundles-btn");
const bundleTypeFilter = document.getElementById("bundle-type");
const searchInput = document.getElementById("bundle-search-input");

// Store all bundles for real-time filtering
let allBundles = [];

/**
 * Render bundles in the container
 * @param {Array} bundles - The list of bundles
 */
const renderBundles = (bundles) => {
    console.log("Rendering bundles...");
    bundleContainer.innerHTML = ""; // Clear container

    if (bundles.length === 0) {
        bundleContainer.innerHTML = "<p>No bundles found.</p>";
        return;
    }

    bundles.forEach(bundle => {
        const paymentStatusClass = bundle.payment_status === "Paid" ? "paid" : "unpaid";

        const bundleElement = document.createElement("div");
        bundleElement.classList.add("bundle-item-card");
        bundleElement.innerHTML = `
            <div class="bundle-item-details">
                <p><label>Reference #: </label><span>${bundle.reference_number}</span></p>
                <p><label>Customer: </label><span>${bundle.username} (${bundle.email || "N/A"})</span></p>
                <p><label>Reservation Date: </label><span>${bundle.reservation_date || "N/A"}</span></p>
                <p><label>Reservation Time: </label><span>${bundle.reservation_time || "N/A"}</span></p>
                <p><label>Bundle Type: </label><span>${bundle.Bundle || "N/A"}</span></p>
                <p><label>Total Amount: </label><span>Php${Number(bundle.amount).toFixed(2)}</span></p>
                <p>
                    <label>Payment Status: </label>
                    <span class="bundle-status ${paymentStatusClass}">${bundle.payment_status}</span>
                </p>
            </div>
        `;

        bundleContainer.appendChild(bundleElement);
    });
};

/**
 * Fetch and Render All Bundles
 */
const refreshBundles = async () => {
    console.log("Fetching all bundles...");
    bundleContainer.innerHTML = "<p>Loading bundles...</p>";
    try {
        allBundles = await fetchAllBundles(); // Cache all bundles for dynamic filtering
        renderBundles(allBundles);
    } catch (error) {
        console.error("Error fetching bundles:", error);
        bundleContainer.innerHTML = "<p>Failed to load bundles.</p>";
    }
};

/**
 * Search bundles by reference number
 * @param {string} referenceNumber - The reference number to search
 */
const searchBundleByReferenceNumber = async (referenceNumber) => {
    console.log(`Searching bundle by reference number: "${referenceNumber}"`);
    try {
        const filteredBundles = await fetchBundleByReferenceNumber(referenceNumber);
        renderBundles(filteredBundles);
    } catch (error) {
        console.error("Error searching bundle by reference number:", error);
        bundleContainer.innerHTML = "<p>Failed to load bundles.</p>";
    }
};

/**
 * Fetch and Render Bundles by Type
 * @param {string} bundleType - The type of bundle to filter
 */
const fetchAndRenderBundlesByType = async (bundleType) => {
    console.log(`Fetching bundles by type: "${bundleType}"`);
    try {
        const filteredBundles = await fetchBundlesByType(bundleType);
        renderBundles(filteredBundles);
    } catch (error) {
        console.error("Error fetching bundles by type:", error);
        bundleContainer.innerHTML = "<p>Failed to load bundles.</p>";
    }
};

/** Event Listeners */

// Refresh Button
refreshButton?.addEventListener("click", refreshBundles);

// Search Input
searchInput?.addEventListener("input", async () => {
    const query = searchInput.value.trim();
    console.log(`Searching bundles dynamically for reference number: "${query}"`);

    // If the query is empty, display all bundles
    if (!query) {
        renderBundles(allBundles);
        return;
    }

    await searchBundleByReferenceNumber(query); // Search by reference number
});

// Bundle Type Filter Dropdown
bundleTypeFilter?.addEventListener("change", async () => {
    const selectedType = bundleTypeFilter.value;
    console.log(`Bundle type selected: "${selectedType}"`);

    // If no type is selected, display all bundles
    if (!selectedType) {
        renderBundles(allBundles);
        return;
    }

    await fetchAndRenderBundlesByType(selectedType); // Fetch and render bundles by type
});

// Trigger fetch on page load
refreshBundles();