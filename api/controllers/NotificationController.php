<?php
require __DIR__ . '/../../vendor/autoload.php'; // Autoload for Pawl WebSocket client
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Notification.php';
include_once __DIR__ . '/../config/configURL.php'; // Include the configURL.php file

use Ratchet\Client\Connector;
use React\EventLoop\Factory;

$database = new Database();
$db = $database->getConnection();

$notification = new Notification($db);

// Use the WebSocket base URL from configURL.php
$webSocketBaseUrl = WS_BASE_URL;

// Function to send WebSocket messages
function sendWebSocketMessage($email, $message, $token) {
    global $webSocketBaseUrl; // Use the global WebSocket base URL
    $webSocketUrl = $webSocketBaseUrl . "?token=" . urlencode($token);

    $loop = Factory::create();
    $connector = new Connector($loop);

    $connector($webSocketUrl)->then(
        function ($conn) use ($email, $message, $loop) {
            $wsData = [
                'type' => 'notification',
                'email' => $email,
                'message' => $message
            ];

            // Send the message to the WebSocket server
            $conn->send(json_encode($wsData));

            // Keep the connection open for 1 second to ensure the message is processed
            $loop->addTimer(1, function () use ($conn, $loop) {
                $conn->close();
                $loop->stop();
            });
        },
        function ($e) use ($loop) {
            $loop->stop();
        }
    );

    $loop->run();
}

// Handle different requests
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST': // Create a new notification
        $headers = getallheaders();
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

        if (!$token) {
            http_response_code(401);
            echo json_encode(["message" => "Access denied. Token not provided."]);
            exit();
        }

        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->type)) {
            switch ($data->type) {
                case 'order':
                    if (!empty($data->email) && !empty($data->reference_number) && !empty($data->status) && !empty($data->created_at)) {
                        if ($notification->generateNotifOrder($data->email, $data->reference_number, $data->status, $data->created_at)) {
                            sendWebSocketMessage($data->email, $notification->message, $token);
                            http_response_code(201);
                            echo json_encode(["message" => "Order notification created and sent."]);
                        } else {
                            http_response_code(503);
                            echo json_encode(["message" => "Unable to create order notification."]);
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(["message" => "Incomplete data for order notification."]);
                    }
                    break;

                case 'receipt':
                    if (!empty($data->email) && !empty($data->reference_number) && !empty($data->created_at)) {
                        if ($notification->generateNotifReceipt($data->email, $data->reference_number, $data->created_at)) {
                            sendWebSocketMessage($data->email, $notification->message, $token);
                            http_response_code(201);
                            echo json_encode(["message" => "Receipt notification created and sent."]);
                        } else {
                            http_response_code(503);
                            echo json_encode(["message" => "Unable to create receipt notification."]);
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(["message" => "Incomplete data for receipt notification."]);
                    }
                    break;

                case 'table_reservation':
                    if (!empty($data->email) && !empty($data->reference_number) && !empty($data->bundle) && !empty($data->reservation_date) && !empty($data->reservation_time) && !empty($data->created_at)) {
                        if ($notification->generateNotifTableReservation($data->email, $data->reference_number, $data->bundle, $data->reservation_date, $data->reservation_time, $data->created_at)) {
                            sendWebSocketMessage($data->email, $notification->message, $token);
                            http_response_code(201);
                            echo json_encode(["message" => "Table reservation notification created and sent."]);
                        } else {
                            http_response_code(503);
                            echo json_encode(["message" => "Unable to create table reservation notification."]);
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(["message" => "Incomplete data for table reservation notification."]);
                    }
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(["message" => "Invalid notification type."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Notification type is required."]);
        }
        break;

    case 'GET': // Retrieve notifications
        if (isset($_GET['email'])) {
            $email = $_GET['email'];
            $stmt = $notification->readAll($email);
            $notifications_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($notifications_arr, $row);
            }
            if (count($notifications_arr) > 0) {
                http_response_code(200);
                echo json_encode($notifications_arr);
            } else {
                http_response_code(204); // No Content
                echo json_encode(["message" => "No notifications found for this email."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Missing parameter: email."]);
        }
        break;

    case 'PUT': // Update a notification
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && !empty($data->message)) {
            $notification->id = $data->id;
            $notification->message = $data->message;
            if ($notification->update()) {
                sendWebSocketMessage($notification->email, $data->message);
                http_response_code(200);
                echo json_encode(["message" => "Notification updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update notification."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'DELETE': // Delete a notification
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $notification->id = $data->id;
            if ($notification->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Notification deleted successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to delete notification."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
}
?>
