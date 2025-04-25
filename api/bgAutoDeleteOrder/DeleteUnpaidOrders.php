<?php
// Include database connection and necessary models
include_once __DIR__ . '/../config/database.php';

echo "Starting UpdateUnpaidOrders script...\n";

try {
    // Create a database connection
    $database = new Database();
    $db = $database->getConnection();

    echo "[" . date('Y-m-d H:i:s') . "] Database connection established.\n";

    // Infinite loop to check and update unpaid orders
    while (true) {
        try {
            // Query to update unpaid orders where est_time is at least 1 hour older than the current time
            $query = "UPDATE orders 
                      SET status = 'cancelled', 
                          reason = 'Failed to pay on time (exceeded 1 hr grace period after est time of arrival)' 
                      WHERE payment_status = 'Unpaid' 
                      AND est_time < SUBTIME(TIME(NOW()), '01:00:00')";
            $stmt = $db->prepare($query);
            $stmt->execute();

            // Log the number of updated rows
            $updatedRows = $stmt->rowCount();
            echo "[" . date('Y-m-d H:i:s') . "] Updated $updatedRows unpaid orders to cancelled based on est_time.\n";
        } catch (Exception $e) {
            // Log any errors
            echo "[" . date('Y-m-d H:i:s') . "] Error during update: " . $e->getMessage() . "\n";
        }

        // Wait 60 seconds before checking again
        sleep(60);
    }
} catch (Exception $e) {
    // Log database connection error
    echo "[" . date('Y-m-d H:i:s') . "] Database connection failed: " . $e->getMessage() . "\n";
    exit(1); // Exit the script with an error code
}
?>