import { fetchAllOrderItems, fetchAllTableReservations } from './mainAPI.js'; // Importing API functions

// Global Chart Instances
let menuChart;
let bundleChart;

// Helper function to process best seller data
const processBestSellers = (data, key) => {
    const itemsMap = {};

    // Group items by name and sum their occurrences
    data.forEach(item => {
        if (!itemsMap[item[key]]) {
            itemsMap[item[key]] = 0;
        }
        itemsMap[item[key]] += 1; // Increment count
    });

    // Convert to array and sort by count in descending order
    const sortedItems = Object.entries(itemsMap).sort((a, b) => b[1] - a[1]);

    // Prepare data for the chart
    const labels = sortedItems.map(entry => entry[0]);
    const quantities = sortedItems.map(entry => entry[1]);

    return { labels, quantities };
};

// Helper function to filter data by date range
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

// Function to render a bar chart
const renderBarChart = (ctx, labels, quantities, chartInstance, label) => {
    // Destroy existing chart instance if exists
    if (chartInstance) chartInstance.destroy();

    // Create a new Chart.js instance
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels, // Item names as X-axis
            datasets: [{
                label: `${label} Best Sellers`,
                data: quantities, // Quantities sold
                backgroundColor: '#d9a064', // Bar color
                borderColor: '#6f4e37',
                borderWidth: 1,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                },
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: `${label} Items`,
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: 'Quantity Sold',
                    },
                    beginAtZero: true,
                },
            },
        },
    });
};

// Function to render both menu and bundle bar charts
const renderBestSellersCharts = async (filter = 'all') => {
    try {
        // Fetch data
        const [orderItems, tableReservations] = await Promise.all([
            fetchAllOrderItems(),
            fetchAllTableReservations(),
        ]);

        // Filter data based on the selected range
        const filteredOrderItems = filter === 'all' ? orderItems : filterByDateRange(orderItems, filter);
        const filteredReservations = filter === 'all' ? tableReservations : filterByDateRange(tableReservations, filter);

        console.log("Filtered Order Items:", filteredOrderItems); // Debugging: Check filtered data
        console.log("Filtered Reservations:", filteredReservations); // Debugging: Check filtered data

        // Process data for menu and bundles
        const menuData = processBestSellers(filteredOrderItems, 'item_name');
        const bundleData = processBestSellers(filteredReservations, 'Bundle');

        console.log("Menu Data:", menuData); // Debugging: Check menu data
        console.log("Bundle Data:", bundleData); // Debugging: Check bundle data

        // Render menu bar chart
        const menuCtx = document.getElementById('food-bar-chart').getContext('2d');
        menuChart = renderBarChart(menuCtx, menuData.labels, menuData.quantities, menuChart, 'Menu');

        // Render bundle bar chart
        const bundleCtx = document.getElementById('coffee-bar-chart').getContext('2d');
        bundleChart = renderBarChart(bundleCtx, bundleData.labels, bundleData.quantities, bundleChart, 'Bundle');
    } catch (error) {
        console.error('Error rendering best sellers charts:', error);
    }
};

// Event listener for radio buttons
document.querySelectorAll('input[name="date-filter"]').forEach(radio => {
    radio.addEventListener('change', (event) => {
        const filter = event.target.value;
        renderBestSellersCharts(filter);
    });
});

// Initialize the charts on page load
document.addEventListener('DOMContentLoaded', () => renderBestSellersCharts('all'));