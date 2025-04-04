import {
    fetchAllUsers,
    fetchAllItems,
    fetchAllPromos,
    fetchAllOrders,
    fetchAllTableReservations,
} from './mainAPI.js'; // Importing API functions

// Function to filter data by date range
const filterByDateRange = (data, range) => {
    const now = new Date();
    return data.filter(item => {
        const itemDate = new Date(item.created_at); // Assuming `created_at` is the date field
        if (range === 'today') {
            return itemDate.toDateString() === now.toDateString();
        } else if (range === 'this-week') {
            const startOfWeek = new Date(now.setDate(now.getDate() - now.getDay()));
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);
            return itemDate >= startOfWeek && itemDate <= endOfWeek;
        } else if (range === 'this-month') {
            return itemDate.getMonth() === now.getMonth() && itemDate.getFullYear() === now.getFullYear();
        }
        return true; // Default: no filtering
    });
};

// Function to render stat card totals
const renderStatCards = async (filter = 'all') => {
    try {
        // Fetch data from APIs
        const [users, items, promos, orders, reservations] = await Promise.all([
            fetchAllUsers(),
            fetchAllItems(),
            fetchAllPromos(),
            fetchAllOrders(),
            fetchAllTableReservations(),
        ]);

        // Filter data based on the selected range
        const filteredUsers = filter === 'all' ? users : filterByDateRange(users, filter);
        const filteredItems = filter === 'all' ? items : filterByDateRange(items, filter);
        const filteredPromos = filter === 'all' ? promos : filterByDateRange(promos, filter);
        const filteredOrders = filter === 'all' ? orders : filterByDateRange(orders, filter);
        const filteredReservations = filter === 'all' ? reservations : filterByDateRange(reservations, filter);

        // Update the stat cards with filtered data
        document.getElementById('total-users').textContent = filteredUsers.length;
        document.getElementById('total-orders').textContent = filteredOrders.length;
        document.getElementById('total-reservations').textContent = filteredReservations.length;
        document.getElementById('total-menu-items').textContent = filteredItems.length;
        document.getElementById('total-promos').textContent = filteredPromos.length;

        // Add "New" label for users if filtering by day, week, or month
        const userStatCard = document.getElementById('total-users');
        if (filter !== 'all') {
            const newUsersCount = filteredUsers.length;
            userStatCard.textContent = `${newUsersCount} New`;
        }

    } catch (error) {
        console.error('Error rendering stat cards:', error);
    }
};

// Event listener for radio buttons
document.querySelectorAll('input[name="date-filter"]').forEach(radio => {
    radio.addEventListener('change', (event) => {
        const filter = event.target.value;
        renderStatCards(filter);
    });
});

// Initialize the stats on page load
document.addEventListener('DOMContentLoaded', () => renderStatCards('all'));