// orderUI.js - Handles UI logic and integrates with orderAPI.js
import {
    fetchAllOrders,
    fetchOrdersByStatus,
    fetchOrdersByPaymentStatus,
    fetchOrdersByOrderType,
    updateOrderStatus,
    updatePaymentStatus,
    deleteOrder,
    createOrderReceipt,
    generateNotifOrder,
    generateNotifReceipt
} from "./orderAPI.js";

// DOM Elements
const orderContainer = document.getElementById("order-items-container");
const refreshButton = document.getElementById("refresh-orders-btn");
const statusFilter = document.getElementById("order-status");
const paymentStatusFilter = document.getElementById("order-payment-status");
const orderTypeFilter = document.getElementById("order-type");
const searchInput = document.getElementById("order-search-input");

// Store all orders for real-time filtering
let allOrders = [];

// Utility Functions

/**
 * Render orders in the container
 * @param {Array} orders - The list of orders
 */
const renderOrders = (orders) => {
    console.log("Rendering orders...");
    orderContainer.innerHTML = ""; // Clear container

    if (orders.length === 0) {
        orderContainer.innerHTML = "<p>No orders found.</p>";
        return;
    }

    orders.forEach(order => {
        const paymentStatusClass = order.payment_status === "Paid" ? "paid" : "unpaid";
        const orderStatusClass = order.status.replace(/\s/g, "-").toLowerCase(); // Normalize status string

        const orderElement = document.createElement("div");
        orderElement.classList.add("order-item-card");
        orderElement.innerHTML = `
            <div class="order-item-details">
                <p><label>Reference #: </label><span>${order.reference_number}</span></p>
                <p><label>Customer: </label><span>${order.username} (${order.email || "N/A"})</span></p>
                <p><label>Date: </label><span>${order.created_at || "N/A"}</span></p>
                <p><label>Order Type: </label><span>${order.order_type || "N/A"}</span></p>
                <p><label>Total Amount: </label><span>₱${Number(order.total_amount).toFixed(2)}</span></p>
                <p>
                    <label>Payment Status: </label>
                    <span class="order-status ${paymentStatusClass}">${order.payment_status}</span>
                    <select class="order-update-dropdown" data-id="${order.reference_number}" data-type="payment">
                        <option value="" disabled selected>Update Payment Status</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                    </select>
                </p>
                <p>
                    <label>Order Status: </label>
                    <span class="order-status ${orderStatusClass}">${order.status}</span>
                    <select class="order-update-dropdown" data-id="${order.reference_number}" data-type="status">
                        <option value="" disabled selected>Update Order Status</option>
                        <option value="pending">Pending</option>
                        <option value="preparing">Preparing</option>
                        <option value="serving">serving</option>
                        <option value="completed">Completed</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </p>
            </div>
            <div class="order-item-actions">
                <button class="order-action-btn create-receipt-btn" data-id="${order.reference_number}" data-email="${order.email}">Create Receipt</button>
                <button class="order-action-btn delete-btn" data-id="${order.id}">
                    <i class="fas fa-trash"></i> <!-- Font Awesome trash icon -->
                </button>
            </div>
        `;

        orderContainer.appendChild(orderElement);

        // Attach Event Listeners for Buttons
        orderElement.querySelector(".create-receipt-btn").addEventListener("click", async () => {
            try {
                const email = order.email; // Get customer email
                const referenceNumber = order.reference_number; // Get reference number

                if (!email || email === "N/A") {
                    alert("Cannot create receipt: Email is missing for this order.");
                    return;
                }

                const receiptData = {
                    reference_number: referenceNumber,
                    receipt_for: "Order",
                    email: email,
                };

                console.log("Creating receipt...");
                await createOrderReceipt(receiptData); // Call the function from orderAPI.js

                console.log("Generating notification for receipt creation...");
                const token = localStorage.getItem("userToken"); // Retrieve the token for notification
                if (!token) {
                    throw new Error("User token is missing. Please log in again.");
                }

                await generateNotifReceipt({
                    email,
                    reference_number: referenceNumber,
                    created_at: new Date().toISOString(), // Current timestamp
                }, token);

                alert("Receipt created and notification sent successfully!");
                refreshOrders(); // Refresh the order list
            } catch (error) {
                console.error("Error creating receipt or sending notification:", error);
                alert(`Failed to create receipt or send notification: ${error.message}`);
            }
        });

        orderElement.querySelector(".delete-btn").addEventListener("click", async () => {
            if (confirm(`Are you sure you want to delete order "${order.reference_number}"?`)) {
                try {
                    await deleteOrder(order.id);
                    alert("Order deleted successfully!");
                    refreshOrders(); // Refresh the order list
                } catch (error) {
                    console.error("Error deleting order:", error);
                    alert("Failed to delete order.");
                }
            }
        });

        orderElement.querySelectorAll(".order-update-dropdown").forEach((dropdown) => {
            dropdown.addEventListener("change", async (event) => {
                const updatedValue = event.target.value; // New status or payment status
                const type = dropdown.dataset.type; // Type of update (status or payment)
                const referenceNumber = dropdown.dataset.id; // Order reference number
        
                try {
                    const token = localStorage.getItem("userToken"); // Retrieve the token
                    if (!token) {
                        throw new Error("User token is missing. Please log in again.");
                    }
        
                    const email = order.email; // Customer email for notifications
        
                    if (type === "status") {
                        console.log("Updating order status...");
                        await updateOrderStatus(referenceNumber, updatedValue, email, token); // Call the function from orderAPI.js
        
                        // Introduce a delay to ensure order status is fully updated before proceeding
                        console.log("Applying delay...");
                        await new Promise(resolve => setTimeout(resolve, 500)); // Delay of 0.5 second
        
                        console.log("Generating notification for order status update...");
                        await generateNotifOrder({
                            email,
                            reference_number: referenceNumber,
                            status: updatedValue,
                            created_at: new Date().toISOString(), // Current timestamp
                        }, token);
        
                        alert("Order status updated and notification sent successfully!");
                    } else if (type === "payment") {
                        console.log("Updating payment status...");
        
                        // Pass all required arguments along with the authorization token
                        await updatePaymentStatus(referenceNumber, updatedValue, order.email, order.username, token); // Added token parameter
        
                        alert("Payment status updated successfully!");
                    }
        
                    refreshOrders(); // Refresh the order list
                } catch (error) {
                    console.error(`Error updating ${type}:`, error);
                    alert(`Failed to update ${type}.`);
                }
            });
        });
    });
};

/**
 * Fetch and Render All Orders
 */
const refreshOrders = async () => {
    console.log("Fetching all orders...");
    orderContainer.innerHTML = "<p>Loading orders...</p>";
    try {
        console.log("Calling fetchAllOrders...");
        allOrders = await fetchAllOrders(); // Cache all orders for dynamic search
        console.log("Orders fetched successfully:", allOrders);
        renderOrders(allOrders);
    } catch (error) {
        console.error("Error fetching orders:", error);
        orderContainer.innerHTML = "<p>Failed to load orders.</p>";
    }
};

/**
 * Dynamically filter orders based on query
 */
const filterOrders = (query) => {
    const lowerCaseQuery = query.toLowerCase();

    // Filter orders that match the query in reference number or username
    const filteredOrders = allOrders.filter(order =>
        order.reference_number.toLowerCase().includes(lowerCaseQuery) ||
        order.username.toLowerCase().includes(lowerCaseQuery)
    );

    renderOrders(filteredOrders);
};

/** Event Listeners */

// Refresh Button
refreshButton?.addEventListener("click", refreshOrders);

// Status Filter Dropdown
statusFilter?.addEventListener("change", async () => {
    const selectedStatus = statusFilter.value;
    console.log(`Status selected: ${selectedStatus}`);
    try {
        const filteredOrders = await fetchOrdersByStatus(selectedStatus);
        renderOrders(filteredOrders);
    } catch (error) {
        console.error("Error filtering orders by status:", error);
    }
});

// Payment Status Filter Dropdown
paymentStatusFilter?.addEventListener("change", async () => {
    const selectedPaymentStatus = paymentStatusFilter.value;
    console.log(`Payment status selected: ${selectedPaymentStatus}`);
    try {
        const filteredOrders = await fetchOrdersByPaymentStatus(selectedPaymentStatus);
        renderOrders(filteredOrders);
    } catch (error) {
        console.error("Error filtering orders by payment status:", error);
    }
});

// Order Type Filter Dropdown
orderTypeFilter?.addEventListener("change", async () => {
    const selectedOrderType = orderTypeFilter.value;
    console.log(`Order type selected: ${selectedOrderType}`);
    try {
        const filteredOrders = await fetchOrdersByOrderType(selectedOrderType);
        renderOrders(filteredOrders);
    } catch (error) {
        console.error("Error filtering orders by order type:", error);
    }
});

// Search Input
searchInput?.addEventListener("input", () => {
    const query = searchInput.value.trim();
    console.log(`Searching orders dynamically for query: "${query}"`);

    // If the query is empty, display all orders
    if (!query) {
        renderOrders(allOrders);
        return;
    }

    // Dynamically filter orders
    filterOrders(query);
});

// Trigger fetch on page load
refreshOrders();