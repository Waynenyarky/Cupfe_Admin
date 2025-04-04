// receiptUI.js - Handles UI logic and integrates with receiptAPI.js
import {
    fetchAllReceipts,
    fetchReceiptByReferenceNumber,
    fetchReceiptsByEmail,
    fetchReceiptsByReceiptFor
} from "./receiptAPI.js"; // Importing updated API functions

// DOM Elements
const receiptContainer = document.querySelector("#receipt-items-container");
const refreshButton = document.querySelector("#refresh-receipts-btn");
const searchInput = document.querySelector("#receipt-search-input");
const emailFilterInput = document.querySelector("#receipt-email-input");
const receiptForFilter = document.querySelector("#receipt-for-select"); // Dropdown to filter by receipt_for

// Store all receipts for real-time filtering
let allReceipts = [];

/**
 * Render receipts in the container
 * @param {Array} receipts - The list of receipts
 */
const renderReceipts = (receipts) => {
    console.log("Rendering receipts...");
    receiptContainer.innerHTML = ""; // Clear container

    if (receipts.length === 0) {
        receiptContainer.innerHTML = "<p>No receipts found.</p>";
        return;
    }

    receipts.forEach(receipt => {
        const receiptElement = document.createElement("div");
        receiptElement.classList.add("receipt-item-card");
        receiptElement.innerHTML = `
            <div class="receipt-item-overview">
                <p><label>Receipt #: </label><span>${receipt.reference_number}</span></p>
                <p><label>Email: </label><span>${receipt.email || "N/A"}</span></p>
                <p><label>Created At: </label><span>${receipt.created_at || "N/A"}</span></p>
                <p><label>Receipt For: </label><span>${receipt.receipt_for || "N/A"}</span></p>
                <button class="view-details-btn">View Details</button>
            </div>
            <div class="receipt-item-details hidden">
                <pre>${formatReceiptDetails(receipt.receipt_text || "No details available.")}</pre>
            </div>
        `;

        const viewDetailsButton = receiptElement.querySelector(".view-details-btn");
        const detailsContainer = receiptElement.querySelector(".receipt-item-details");

        // Attach click event to toggle visibility of receipt details
        viewDetailsButton.addEventListener("click", () => {
            const isHidden = detailsContainer.classList.toggle("hidden");
            viewDetailsButton.textContent = isHidden ? "View Details" : "Hide Details";
        });

        receiptContainer.appendChild(receiptElement);
    });
};

/**
 * Format receipt details
 * @param {string} details - Raw receipt details text
 * @returns {string} - Nicely formatted receipt details
 */
const formatReceiptDetails = (details) => {
    return details.replace(/\n/g, "<br>"); // Converts newlines to HTML line breaks
};

/**
 * Fetch and Render All Receipts
 */
const refreshReceipts = async () => {
    console.log("Fetching all receipts...");
    receiptContainer.innerHTML = "<p>Loading receipts...</p>";
    try {
        allReceipts = await fetchAllReceipts(); // Cache all receipts for dynamic filtering
        renderReceipts(allReceipts);
    } catch (error) {
        console.error("Error fetching receipts:", error);
        receiptContainer.innerHTML = "<p>Failed to load receipts.</p>";
    }
};

/** Debounce function to limit API calls */
const debounce = (func, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func(...args), delay);
    };
};

/** Dynamic Search by Reference Number */
const dynamicSearchByReferenceNumber = async () => {
    const query = searchInput.value.trim();
    console.log(`Dynamic search for reference number: "${query}"`);

    if (!query) {
        renderReceipts(allReceipts);
        return;
    }

    try {
        const filteredReceipts = await fetchReceiptByReferenceNumber(query);
        renderReceipts(filteredReceipts);
    } catch (error) {
        console.error("Error dynamically searching receipt by reference number:", error);
        receiptContainer.innerHTML = "<p>Failed to load receipts.</p>";
    }
};

/** Dynamic Search by Email */
const dynamicSearchByEmail = async () => {
    const email = emailFilterInput.value.trim();
    console.log(`Dynamic search for email: "${email}"`);

    if (!email) {
        renderReceipts(allReceipts);
        return;
    }

    try {
        const filteredReceipts = await fetchReceiptsByEmail(email);
        renderReceipts(filteredReceipts);
    } catch (error) {
        console.error("Error dynamically searching receipts by email:", error);
        receiptContainer.innerHTML = "<p>Failed to load receipts.</p>";
    }
};

/** Fetch and Render Receipts by Receipt For */
const fetchAndRenderReceiptsByReceiptFor = async (receiptFor) => {
    console.log(`Filtering receipts dynamically by receipt_for: "${receiptFor}"`);
    try {
        const filteredReceipts = await fetchReceiptsByReceiptFor(receiptFor);
        renderReceipts(filteredReceipts);
    } catch (error) {
        console.error("Error fetching receipts by receipt_for:", error);
        receiptContainer.innerHTML = "<p>Failed to load receipts.</p>";
    }
};

/** Event Listeners */

// Refresh Button
refreshButton?.addEventListener("click", refreshReceipts);

// Search Input (Debounced for dynamic updates)
searchInput?.addEventListener("input", debounce(dynamicSearchByReferenceNumber, 300));

// Email Filter Input (Debounced for dynamic updates)
emailFilterInput?.addEventListener("input", debounce(dynamicSearchByEmail, 300));

// Receipt For Filter Dropdown
receiptForFilter?.addEventListener("change", async () => {
    const receiptFor = receiptForFilter.value;
    console.log(`Filtering receipts dynamically by receipt_for: "${receiptFor}"`);

    if (!receiptFor) {
        renderReceipts(allReceipts); // Show all receipts if no specific filter is selected
        return;
    }

    await fetchAndRenderReceiptsByReceiptFor(receiptFor); // Fetch and render filtered receipts
});

// Trigger fetch on page load
refreshReceipts();