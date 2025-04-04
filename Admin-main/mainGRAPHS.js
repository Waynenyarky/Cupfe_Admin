import { fetchAllOrders, fetchAllTableReservations } from './mainAPI.js'; // Importing API functions

// Global Chart Instance
let salesChart;

// Helper function to process sales data
const processSalesData = (orders, reservations, type) => {
    const dates = [];
    const ordersSales = [];
    const reservationsSales = [];
    const combinedSales = [];

    const dataMap = {}; // Temporary object to sum sales per date

    // Process Orders
    orders.forEach(order => {
        const date = new Date(order.created_at).toLocaleDateString();
        if (!dataMap[date]) dataMap[date] = { orders: 0, reservations: 0 };
        dataMap[date].orders += parseFloat(order.total_amount || 0); // Use 'total_amount' for orders
    });

    // Process Reservations
    reservations.forEach(reservation => {
        const date = new Date(reservation.created_at).toLocaleDateString();
        if (!dataMap[date]) dataMap[date] = { orders: 0, reservations: 0 };
        dataMap[date].reservations += parseFloat(reservation.amount || 0); // Use 'amount' for reservations
    });

    // Prepare Arrays for Chart.js
    Object.keys(dataMap).sort((a, b) => new Date(a) - new Date(b)).forEach(date => {
        dates.push(date);
        ordersSales.push(dataMap[date].orders);
        reservationsSales.push(dataMap[date].reservations);
        combinedSales.push(dataMap[date].orders + dataMap[date].reservations);
    });

    if (type === 'orders') return { dates, sales: ordersSales };
    if (type === 'reservations') return { dates, sales: reservationsSales };
    return { dates, sales: combinedSales }; // Combined as default
};

// Function to render the sales line chart
const renderSalesChart = async (type = 'combined') => {
    try {
        const [orders, reservations] = await Promise.all([
            fetchAllOrders(),
            fetchAllTableReservations(),
        ]);

        const { dates, sales } = processSalesData(orders, reservations, type);

        // Destroy existing chart instance if exists
        if (salesChart) salesChart.destroy();

        const ctx = document.getElementById('sales-line-chart').getContext('2d');
        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates, // Dates as X-axis
                datasets: [{
                    label: `${type.charAt(0).toUpperCase() + type.slice(1)} Sales`,
                    data: sales, // Sales data
                    borderColor: type === 'orders' ? '#6f4e37' : type === 'reservations' ? '#d9a064' : '#75412b',
                    backgroundColor: 'rgba(217, 160, 100, 0.3)',
                    fill: true,
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
                            text: 'Dates',
                        },
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Sales (pesos)',
                        },
                        beginAtZero: true,
                    },
                },
            },
        });
    } catch (error) {
        console.error('Error rendering sales chart:', error);
    }
};

// Expose renderSalesChart globally for inline onclick handlers
window.showSalesData = (type) => {
    renderSalesChart(type);
};

// Add event listeners for toggle buttons
document.querySelectorAll('.toggle-buttons button').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        document.querySelectorAll('.toggle-buttons button').forEach(btn => btn.classList.remove('active'));

        // Add active class to the clicked button
        button.classList.add('active');

        // Render the chart based on the selected type
        const type = button.textContent.toLowerCase();
        renderSalesChart(type);
    });
});

// Initialize the chart on page load
document.addEventListener('DOMContentLoaded', () => renderSalesChart('combined'));