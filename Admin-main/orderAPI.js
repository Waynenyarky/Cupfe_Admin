import CONFIG from "./config.js"; // Importing the dynamic config

// Define the base URLs dynamically using CONFIG.API_BASE_URL
export const API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/orders`;
export const RECEIPT_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/receipts`; // Endpoint for receipts
export const NOTIFICATIONS_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/notifications`; // Endpoint for notifications

/** Fetch all orders */
const fetchAllOrders = async () => {
    try {
        const response = await fetch(API_URL);
        if (!response.ok) throw new Error("Failed to fetch all orders.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching all orders:", error);
        throw error;
    }
};

/** Fetch orders by status */
const fetchOrdersByStatus = async (status) => {
    try {
        const response = await fetch(`${API_URL}/by-status?status=${encodeURIComponent(status)}`);
        if (!response.ok) throw new Error("Failed to fetch orders by status.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching orders by status:", error);
        throw error;
    }
};

/** Fetch orders by order type */
const fetchOrdersByOrderType = async (orderType) => {
    try {
        const response = await fetch(`${API_URL}/by-type?order_type=${encodeURIComponent(orderType)}`);
        if (!response.ok) throw new Error("Failed to fetch orders by order type.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching orders by order type:", error);
        throw error;
    }
};

/** Fetch orders by payment status */
const fetchOrdersByPaymentStatus = async (paymentStatus) => {
    try {
        const response = await fetch(`${API_URL}?payment_status=${encodeURIComponent(paymentStatus)}`);
        if (!response.ok) throw new Error("Failed to fetch orders by payment status.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching orders by payment status:", error);
        throw error;
    }
};

/** Search order by reference number */
const searchOrderByReferenceNumber = async (referenceNumber) => {
    try {
        const response = await fetch(`${API_URL}/search-by-reference-number?reference_number=${encodeURIComponent(referenceNumber)}`);
        if (!response.ok) throw new Error("Failed to search order by reference number.");

        const data = await response.json();

        // Handle case where API returns a single object instead of an array
        return Array.isArray(data) ? data : [data];
    } catch (error) {
        console.error("Error searching order by reference number:", error);
        throw error;
    }
};

/** Update payment status */
const updatePaymentStatus = async (referenceNumber, paymentStatus, email, username, token, est_time, reason) => {
    try {
        const response = await fetch(`${API_URL}/update-payment-status`, {
            method: "PUT",
            headers: { 
                "Content-Type": "application/json", 
                "Authorization": `Bearer ${token}` // Include the token for authorization
            },
            body: JSON.stringify({
                reference_number: referenceNumber, // Reference number
                payment_status: paymentStatus,    // Payment status
                email,                            // Customer email
                username,                         // Customer username
                est_time, 
                reason
            }),
        });
        if (!response.ok) throw new Error("Failed to update payment status.");
        return await response.json();
    } catch (error) {
        console.error("Error updating payment status:", error);
        throw error;
    }
};

/** Update order status */
const updateOrderStatus = async (referenceNumber, status, email, token, est_time, reason) => {
    try {
        const response = await fetch(`${API_URL}/update-order-status`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`, // Include the token in the Authorization header
            },
            body: JSON.stringify({ 
                reference_number: referenceNumber, 
                status, 
                email,
                est_time, 
                reason    
            }),
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || "Failed to update order status.");
        }

        return await response.json();
    } catch (error) {
        console.error("Error updating order status:", error);
        throw error;
    }
};

/** Delete an order by ID */
const deleteOrder = async (id) => {
    try {
        const response = await fetch(API_URL, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: id }),
        });
        if (!response.ok) throw new Error("Failed to delete order.");
        return await response.json();
    } catch (error) {
        console.error("Error deleting order:", error);
        throw error;
    }
};

/** Create receipt for an order */
export const createOrderReceipt = async (receiptData) => {
    try {
        const response = await fetch(`${RECEIPT_API_URL}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(receiptData),
        });

        if (!response.ok) {
            const errorResponse = await response.json();
            throw new Error(errorResponse.message || "Failed to create receipt.");
        }

        return await response.json();
    } catch (error) {
        console.error("Error creating receipt:", error);
        throw error;
    }
};

/** Generate notification for order updates */
const generateNotifOrder = async (notifData, token) => {
    try {
        const response = await fetch(`${NOTIFICATIONS_API_URL}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`, // Include the token in the Authorization header
            },
            body: JSON.stringify({
                type: "order", // Notification type
                email: notifData.email,
                reference_number: notifData.reference_number,
                status: notifData.status,
                created_at: notifData.created_at,
                est_time: notifData.est_time, // New: Estimated Time
                reason: notifData.reason      // New: Reason
            }),
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || "Failed to generate order notification.");
        }

        return await response.json();
    } catch (error) {
        console.error("Error generating order notification:", error);
        throw error;
    }
};

/** Generate notification for receipt generation */
const generateNotifReceipt = async (notifData, token) => {
    try {
        const response = await fetch(`${NOTIFICATIONS_API_URL}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`, // Include the token in the Authorization header
            },
            body: JSON.stringify({
                type: "receipt", // Notification type
                email: notifData.email,
                reference_number: notifData.reference_number,
                created_at: notifData.created_at,
            }),
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || "Failed to generate receipt notification.");
        }

        return await response.json();
    } catch (error) {
        console.error("Error generating receipt notification:", error);
        throw error;
    }
};

// Exporting API functions for use in orderUI.js
export {
    fetchAllOrders,
    fetchOrdersByStatus,
    fetchOrdersByOrderType,
    fetchOrdersByPaymentStatus,
    searchOrderByReferenceNumber,
    updatePaymentStatus,
    updateOrderStatus, // Ensure this is exported only once
    deleteOrder,
    generateNotifOrder, // Export for external usage
    generateNotifReceipt, // Export for external usage
};