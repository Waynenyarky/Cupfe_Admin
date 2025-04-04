import CONFIG from "./config.js"; // Importing the dynamic config

// Define the base URLs dynamically using CONFIG.API_BASE_URL
export const USERS_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/users`; // Endpoint for users
export const ITEMS_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/items`; // Endpoint for menu items
export const PROMOS_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/promos`; // Endpoint for promotions
export const ORDERS_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/orders`; // Endpoint for orders
export const TABLE_RESERVATIONS_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/table_reservations`; // Endpoint for table reservations
export const ORDER_ITEMS_API_URL = `${CONFIG.API_BASE_URL}/expresso-cafe/api/order_items`; // Endpoint for order items

/** Fetch all users */
const fetchAllUsers = async () => {
    try {
        const response = await fetch(USERS_API_URL);
        if (!response.ok) throw new Error("Failed to fetch all users.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching all users:", error);
        throw error;
    }
};

/** Fetch all items */
const fetchAllItems = async () => {
    try {
        const response = await fetch(ITEMS_API_URL);
        if (!response.ok) throw new Error("Failed to fetch all items.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching all items:", error);
        throw error;
    }
};

/** Fetch all promotions */
const fetchAllPromos = async () => {
    try {
        const response = await fetch(PROMOS_API_URL);
        if (!response.ok) throw new Error("Failed to fetch all promotions.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching all promotions:", error);
        throw error;
    }
};

/** Fetch all orders */
const fetchAllOrders = async () => {
    try {
        const response = await fetch(ORDERS_API_URL);
        if (!response.ok) throw new Error("Failed to fetch all orders.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching all orders:", error);
        throw error;
    }
};

/** Fetch all table reservations */
const fetchAllTableReservations = async () => {
    try {
        const response = await fetch(TABLE_RESERVATIONS_API_URL);
        if (!response.ok) throw new Error("Failed to fetch all table reservations.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching all table reservations:", error);
        throw error;
    }
};

/** Fetch all order items */
const fetchAllOrderItems = async () => {
    try {
        const response = await fetch(ORDER_ITEMS_API_URL);
        if (!response.ok) throw new Error("Failed to fetch all order items.");
        return await response.json();
    } catch (error) {
        console.error("Error fetching all order items:", error);
        throw error;
    }
};

// Exporting API functions for use in dashboardUI.js
export {
    fetchAllUsers,
    fetchAllItems,
    fetchAllPromos,
    fetchAllOrders,
    fetchAllTableReservations,
    fetchAllOrderItems,
};