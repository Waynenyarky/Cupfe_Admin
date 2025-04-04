import CONFIG from "./config.js"; // Importing the dynamic config

// Define the base URLs dynamically using CONFIG.API_BASE_URL
export const RECEIPT_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/receipts`;
export const RECEIPT_SEARCH_URL = `${RECEIPT_API_URL}/search`;
export const RECEIPT_EMAIL_URL = `${RECEIPT_API_URL}/search-by-email`;
export const RECEIPT_FOR_URL = `${RECEIPT_API_URL}/receipt-for`;

/** Fetch all receipts */
const fetchAllReceipts = async () => {
    try {
        const response = await fetch(RECEIPT_API_URL);
        if (!response.ok) throw new Error("Failed to fetch all receipts.");
        return await response.json(); // Return all receipts
    } catch (error) {
        console.error("Error fetching all receipts:", error);
        throw error;
    }
};

/** Search receipt by reference number */
const fetchReceiptByReferenceNumber = async (referenceNumber) => {
    try {
        const response = await fetch(`${RECEIPT_SEARCH_URL}?reference_number=${encodeURIComponent(referenceNumber)}`);
        if (!response.ok) throw new Error("Failed to fetch receipt by reference number.");
        return await response.json(); // Return search results
    } catch (error) {
        console.error("Error searching receipt by reference number:", error);
        throw error;
    }
};

/** Search receipts by email */
const fetchReceiptsByEmail = async (emailKeyword) => {
    try {
        const response = await fetch(`${RECEIPT_EMAIL_URL}?search_email=${encodeURIComponent(emailKeyword)}`);
        if (!response.ok) throw new Error("Failed to fetch receipts by email.");
        return await response.json(); // Return search results
    } catch (error) {
        console.error("Error searching receipts by email:", error);
        throw error;
    }
};

/** Fetch receipts by receipt_for (e.g., "Order" or "Table") */
const fetchReceiptsByReceiptFor = async (receiptFor) => {
    try {
        const response = await fetch(`${RECEIPT_FOR_URL}?receipt_for=${encodeURIComponent(receiptFor)}`);
        if (!response.ok) throw new Error("Failed to fetch receipts by receipt_for.");
        return await response.json(); // Return receipts filtered by receipt_for
    } catch (error) {
        console.error("Error fetching receipts by receipt_for:", error);
        throw error;
    }
};

// Exporting the API functions for use in receiptUI.js
export {
    fetchAllReceipts,
    fetchReceiptByReferenceNumber,
    fetchReceiptsByEmail,
    fetchReceiptsByReceiptFor
};