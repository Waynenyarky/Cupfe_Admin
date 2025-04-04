<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/OrderItem.php';

$database = new Database();
$db = $database->getConnection();

$orderItem = new OrderItem($db);

// Get the request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($request_method) {
    case 'POST':
        // Create new order items
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->order_items) && is_array($data->order_items)) {
            $all_created = true;
            foreach ($data->order_items as $item) {
                if (
                    !empty($item->reference_number) &&
                    !empty($item->item_id) &&
                    !empty($item->item_name) &&
                    !empty($item->username) &&
                    !empty($item->email) &&
                    !empty($item->quantity) &&
                    !empty($item->price) &&
                    !empty($item->size) &&
                    isset($item->special_instructions) // Special instructions can be empty
                ) {
                    $orderItem->reference_number = $item->reference_number;
                    $orderItem->item_id = $item->item_id;
                    $orderItem->item_name = $item->item_name;
                    $orderItem->username = $item->username;
                    $orderItem->email = $item->email;
                    $orderItem->quantity = $item->quantity;
                    $orderItem->price = $item->price;
                    $orderItem->size = $item->size;
                    $orderItem->special_instructions = $item->special_instructions;

                    if (!$orderItem->create()) {
                        error_log("Failed to create order item with item_id: " . $orderItem->item_id);
                        $all_created = false;
                        break;
                    }
                } else {
                    error_log("Incomplete order item data: " . json_encode($item));
                    $all_created = false;
                    break;
                }
            }

            if ($all_created) {
                http_response_code(201);
                echo json_encode(["message" => "Order items were created."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create order items."]);
            }
        } else {
            error_log("Invalid data for order items: " . json_encode($data));
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. Order items are required."]);
        }
        break;

    case 'GET':
        // Fetch all order items if no specific parameters are provided
        if (!isset($_GET['id']) && !isset($_GET['search'])) {
            $stmt = $orderItem->fetchAll();
            $orderItems_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($orderItems_arr, $row); // Includes created_at
            }
            if (count($orderItems_arr) > 0) {
                http_response_code(200);
                echo json_encode($orderItems_arr);
            } else {
                error_log("No order items found.");
                http_response_code(204); // No Content
                echo json_encode(["message" => "No order items found."]);
            }
        } elseif (isset($_GET['id'])) {
            $orderItem->id = $_GET['id'];
            $stmt = $orderItem->read();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC); // Includes created_at
                echo json_encode($row);
            } else {
                error_log("Order item not found for ID: " . $orderItem->id);
                http_response_code(404);
                echo json_encode(["message" => "Order item not found."]);
            }
        } elseif (isset($_GET['search'])) {
            $keyword = $_GET['search'];
            $stmt = $orderItem->search($keyword);
            $orderItems_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($orderItems_arr, $row); // Includes created_at
            }
            if (count($orderItems_arr) > 0) {
                http_response_code(200);
                echo json_encode($orderItems_arr);
            } else {
                error_log("No order items found for search keyword: " . $keyword);
                http_response_code(204); // No Content
                echo json_encode(["message" => "No order items found."]);
            }
        } else {
            error_log("GET request missing parameters: id or search keyword.");
            http_response_code(400);
            echo json_encode(["message" => "Missing parameter: id or search keyword."]);
        }
        break;

    case 'PUT':
        // Update existing order items
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->order_items) && is_array($data->order_items)) {
            $all_updated = true;
            foreach ($data->order_items as $item) {
                if (!empty($item->reference_number) && !empty($item->item_id)) {
                    $orderItem->reference_number = $item->reference_number;
                    $orderItem->item_id = $item->item_id;
                    if (isset($item->item_name)) $orderItem->item_name = $item->item_name;
                    if (isset($item->username)) $orderItem->username = $item->username;
                    if (isset($item->email)) $orderItem->email = $item->email;
                    if (isset($item->quantity)) $orderItem->quantity = $item->quantity;
                    if (isset($item->price)) $orderItem->price = $item->price;
                    if (isset($item->size)) $orderItem->size = $item->size;
                    if (isset($item->special_instructions)) $orderItem->special_instructions = $item->special_instructions;

                    if (!$orderItem->update()) {
                        error_log("Failed to update order item with item_id: " . $orderItem->item_id);
                        $all_updated = false;
                        break;
                    }
                } else {
                    error_log("Incomplete order item update data: " . json_encode($item));
                    $all_updated = false;
                    break;
                }
            }

            if ($all_updated) {
                http_response_code(200);
                echo json_encode(["message" => "Order items updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update order items."]);
            }
        } else {
            error_log("Invalid data for updating order items: " . json_encode($data));
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. Order items are required."]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $orderItem->id = $data->id;
            if ($orderItem->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Order item deleted successfully."]);
            } else {
                error_log("Failed to delete order item with ID: " . $data->id);
                http_response_code(503);
                echo json_encode(["message" => "Unable to delete order item."]);
            }
        } else {
            error_log("Incomplete data for deleting order item: " . json_encode($data));
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
