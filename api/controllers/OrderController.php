<?php
require __DIR__ . '/../../vendor/autoload.php'; // Autoload for WebSocket client
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Order.php';
include_once __DIR__ . '/../config/configURL.php'; // Config for WebSocket base URL

use Ratchet\Client\Connector;
use React\EventLoop\Factory;

$database = new Database();
$db = $database->getConnection();

$order = new Order($db);

// Use the WebSocket base URL from configURL.php
$webSocketBaseUrl = WS_BASE_URL;

// Function to send WebSocket messages
function sendOrderStatusWebSocket($email, $reference_number, $status, $token) {
    global $webSocketBaseUrl;

    // Construct WebSocket URL
    $webSocketUrl = $webSocketBaseUrl . "?token=" . urlencode($token);

    // Log WebSocket URL for debugging
    error_log("Connecting to WebSocket URL: " . $webSocketUrl);

    $loop = Factory::create();
    $connector = new Connector($loop);

    $connector($webSocketUrl)->then(
        function ($conn) use ($email, $reference_number, $status, $loop) {
            $wsData = [
                'type' => 'orderStatus',
                'email' => $email,
                'reference_number' => $reference_number,
                'status' => $status
            ];

            // Log the payload being sent to the WebSocket server
            error_log("Sending to WebSocket Server: " . json_encode($wsData));

            // Send the message to the WebSocket server
            $conn->send(json_encode($wsData));

            // Log successful send attempt
            error_log("Order status forwarded successfully for reference_number: " . $reference_number);

            // Keep the connection open 1 second to ensure the message is sent
            $loop->addTimer(1, function () use ($conn, $loop) {
                $conn->close();
                $loop->stop();
            });
        },
        function ($e) use ($loop) {
            // Log WebSocket connection failure
            error_log("WebSocket connection failed: " . $e->getMessage());
            $loop->stop();
        }
    );

    $loop->run();
}

// Get the request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($request_method) {
    case 'POST':
        // Create a new order with items
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->reference_number) && !empty($data->username) && !empty($data->email) && !empty($data->total_amount) && !empty($data->status) && !empty($data->order_type) && !empty($data->payment_method) && !empty($data->payment_status) && !empty($data->order_items) && is_array($data->order_items)) {
            $order->reference_number = $data->reference_number;
            $order->username = $data->username;
            $order->email = $data->email;
            $order->total_amount = $data->total_amount;
            $order->status = $data->status;
            $order->promo_code = isset($data->promo_code) ? $data->promo_code : null;
            $order->order_type = $data->order_type;
            $order->payment_method = $data->payment_method;
            $order->payment_status = $data->payment_status;

            // Log the incoming data for debugging
            error_log("Creating order: " . json_encode($data));

            if ($order->createWithItems($data->order_items)) {
                http_response_code(201);
                echo json_encode(["message" => "Order and items were created."]);
            } else {
                error_log("Failed to create order with items for reference_number: " . $data->reference_number);
                http_response_code(503);
                echo json_encode(["message" => "Unable to create order and items."]);
            }
        } else {
            error_log("Incomplete data: " . json_encode($data));
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

   case 'PUT':
        $headers = getallheaders();
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

        // Log token usage
        error_log("Token received for WebSocket: " . $token);

        if (!$token) {
            error_log("Access denied: No token provided.");
            http_response_code(401);
            echo json_encode(["message" => "Access denied. Token not provided."]);
            exit();
        }

        $data = json_decode(file_get_contents("php://input"));

        // Handle Order Status Updates
        if (!empty($data->reference_number) && !empty($data->status) && !empty($data->email)) {
            $order->reference_number = $data->reference_number;
            $order->status = $data->status;
            $order->email = $data->email;

            // Log incoming PUT request data
            error_log("Received PUT request: Reference Number = " . $data->reference_number . ", Status = " . $data->status . ", Email = " . $data->email);

            // Update the order status in the database
            if ($order->updateOrderStatus($data->reference_number, $data->status)) {
                // Log database update success
                error_log("Order status updated successfully in the database: Reference Number = " . $data->reference_number);

                // Forward the order status update to the WebSocket server
                sendOrderStatusWebSocket($data->email, $data->reference_number, $data->status, $token);

                http_response_code(200);
                echo json_encode(["message" => "Order status updated successfully and notification sent."]);
            } else {
                error_log("Failed to update order status for reference_number: " . $data->reference_number);
                http_response_code(503);
                echo json_encode(["message" => "Unable to update order status."]);
            }
        } 
        // Handle Payment Status Updates
        elseif (!empty($data->reference_number) && !empty($data->payment_status) && !empty($data->email)) {
            $order->reference_number = $data->reference_number;
            $order->payment_status = $data->payment_status;
            $order->email = $data->email;

            // Log incoming PUT request data
            error_log("Received PUT request for payment: Reference Number = " . $data->reference_number . ", Payment Status = " . $data->payment_status . ", Email = " . $data->email);

            // Update the payment status in the database
            if ($order->updatePaymentStatus($data->reference_number, $data->payment_status)) {
                // Log database update success
                error_log("Payment status updated successfully in the database: Reference Number = " . $data->reference_number);

                http_response_code(200);
                echo json_encode(["message" => "Payment status updated successfully."]);
            } else {
                error_log("Failed to update payment status for reference_number: " . $data->reference_number);
                http_response_code(503);
                echo json_encode(["message" => "Unable to update payment status."]);
            }
        } else {
            error_log("Incomplete data for updating: " . json_encode($data));
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. Reference number, email, and status are required for order status updates. Reference number, payment status, and email are required for payment updates."]);
        }
        break;

    case 'GET':
        // Debug log to verify GET request handling
        error_log("Handling GET request in OrderController.php");

        if (isset($_GET['id'])) {
            $order->id = $_GET['id'];
            $stmt = $order->read();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($row);
            } else {
                error_log("Order not found for ID: " . $order->id);
                http_response_code(404);
                echo json_encode(["message" => "Order not found."]);
            }
        } elseif (isset($_GET['reference_number'])) {
            $reference_number = $_GET['reference_number'];
            $stmt = $order->searchByReferenceNumber($reference_number);
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($row);
            } else {
                error_log("Order not found for reference number: " . $reference_number);
                http_response_code(404);
                echo json_encode(["message" => "Order not found."]);
            }
        } elseif (isset($_GET['status'])) {
            $status = $_GET['status'];
            $stmt = $order->readAllByOrderStatus($status);
            $orders_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($orders_arr, $row);
            }
            if (count($orders_arr) > 0) {
                http_response_code(200);
                echo json_encode($orders_arr);
            } else {
                error_log("No orders found with status: " . $status);
                http_response_code(204); // No Content
                echo json_encode(["message" => "No orders found with that status."]);
            }
        } elseif (isset($_GET['order_type'])) {
            $order_type = $_GET['order_type'];
            $stmt = $order->readAllByOrderType($order_type);
            $orders_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($orders_arr, $row);
            }
            if (count($orders_arr) > 0) {
                http_response_code(200);
                echo json_encode($orders_arr);
            } else {
                error_log("No orders found with order type: " . $order_type);
                http_response_code(204); // No Content
                echo json_encode(["message" => "No orders found with that order type."]);
            }
        } elseif (isset($_GET['search'])) {
            $keyword = $_GET['search'];
            $stmt = $order->search($keyword);
            $orders_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($orders_arr, $row);
            }
            if (count($orders_arr) > 0) {
                http_response_code(200);
                echo json_encode($orders_arr);
            } else {
                error_log("No orders found for search keyword: " . $keyword);
                http_response_code(204); // No Content
                echo json_encode(["message" => "No orders found."]);
            }
        } elseif (isset($_GET['payment_status'])) {
            $payment_status = $_GET['payment_status'];
            $stmt = $order->readAllByPaymentStatus($payment_status);
            $orders_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($orders_arr, $row);
            }
            if (count($orders_arr) > 0) {
                http_response_code(200);
                echo json_encode($orders_arr);
            } else {
                error_log("No orders found with payment status: " . $payment_status);
                http_response_code(204); // No Content
                echo json_encode(["message" => "No orders found with that payment status."]);
            }
        } else {
            // Fetch all orders if no specific parameter is provided
            $stmt = $order->readAll();
            $orders_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($orders_arr, $row);
            }
            if (count($orders_arr) > 0) {
                http_response_code(200);
                echo json_encode($orders_arr);
            } else {
                error_log("No orders found.");
                http_response_code(204); // No Content
                echo json_encode(["message" => "No orders found."]);
            }
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $order->id = $data->id;
            if ($order->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Order deleted successfully."]);
            } else {
                error_log("Failed to delete order with ID: " . $data->id);
                http_response_code(503);
                echo json_encode(["message" => "Unable to delete order."]);
            }
        } else {
            error_log("Incomplete data for deleting order: " . json_encode($data));
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. ID is required."]);
        }
        break;

    default:
        error_log("Unsupported request method: " . $request_method);
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
        break;
}
?>
