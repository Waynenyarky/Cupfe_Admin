<?php
// Include database connection and necessary models
include_once __DIR__ . '/../config/database.php';

echo "Starting DeleteUnpaidOrders script...\n";

try {
    // Create a database connection
    $database = new Database();
    $db = $database->getConnection();

    echo "[" . date('Y-m-d H:i:s') . "] Database connection established.\n";

    // Infinite loop to check and delete unpaid orders
    while (true) {
        try {
            // Query to delete unpaid orders older than 30 minutes
            $query = "DELETE FROM orders WHERE payment_status = 'Unpaid' AND created_at < NOW() - INTERVAL 2 MINUTE";
            $stmt = $db->prepare($query);
            $stmt->execute();

            // Log the number of deleted rows
            $deletedRows = $stmt->rowCount();
            echo "[" . date('Y-m-d H:i:s') . "] Deleted $deletedRows unpaid orders.\n";
        } catch (Exception $e) {
            // Log any errors
            echo "[" . date('Y-m-d H:i:s') . "] Error during deletion: " . $e->getMessage() . "\n";
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
