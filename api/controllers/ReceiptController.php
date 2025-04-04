<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Receipt.php';

$database = new Database();
$db = $database->getConnection();

$receipt = new Receipt($db);

// Get the request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($request_method) {
    case 'POST':
        // Create a new receipt
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->reference_number) && !empty($data->receipt_for) && !empty($data->email)) {
            $receipt->email = htmlspecialchars(strip_tags($data->email));
            $receipt->reference_number = htmlspecialchars(strip_tags($data->reference_number));
            $receipt->receipt_for = htmlspecialchars(strip_tags($data->receipt_for));
            
            // Generate receipt text based on the receipt_for type
            if ($data->receipt_for === 'Order') {
                $receipt_text = $receipt->generateOrderReceipt($data->reference_number);
            } elseif ($data->receipt_for === 'Table') { // Changed from table_reservation to table
                $receipt_text = $receipt->generateTableReceipt($data->reference_number);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Invalid receipt_for value."]);
                break;
            }

            if ($receipt_text === false) {
                http_response_code(404);
                echo json_encode(["message" => ucfirst($data->receipt_for) . " not found."]);
                break;
            }

            $receipt->receipt_text = $receipt_text;
            $receipt->created_at = date('Y-m-d H:i:s');

            if ($receipt->create()) {
                http_response_code(201);
                echo json_encode(["message" => ucfirst($data->receipt_for) . " receipt created successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create receipt. Database error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. Missing required fields."]);
        }
        break;

    case 'GET':
        if (isset($_GET['email'])) {
            $email = $_GET['email'];
            $stmt = $receipt->fetchAllByEmail($email);
            $receipts_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($receipts_arr, $row);
            }
            echo json_encode($receipts_arr);
        } elseif (isset($_GET['reference_number'])) {
            $reference_number = $_GET['reference_number'];
            $stmt = $receipt->search($reference_number);
            $receipts_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($receipts_arr, $row);
            }
            echo json_encode($receipts_arr);
        } elseif (isset($_GET['receipt_for'])) {
            $receipt_for = $_GET['receipt_for'];
            $stmt = $receipt->fetchAllByReceiptFor($receipt_for);
            $receipts_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($receipts_arr, $row);
            }
            echo json_encode($receipts_arr);
        } elseif (isset($_GET['search_email'])) {
            $keyword = $_GET['search_email'];
            $stmt = $receipt->searchByEmail($keyword);
            $receipts_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($receipts_arr, $row);
            }
            echo json_encode($receipts_arr);
        } else {
            $stmt = $receipt->fetchAll();
            $receipts_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($receipts_arr, $row);
            }
            echo json_encode($receipts_arr);
        }
        break;

    case 'PUT':
        // Update an existing receipt
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->reference_number) && !empty($data->receipt_for) && !empty($data->email)) {
            $receipt->email = htmlspecialchars(strip_tags($data->email));
            $receipt->reference_number = htmlspecialchars(strip_tags($data->reference_number));
            $receipt->receipt_text = htmlspecialchars(strip_tags($data->receipt_text ?? ''));
            $receipt->receipt_for = htmlspecialchars(strip_tags($data->receipt_for));
            if ($receipt->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Receipt updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update receipt. Database error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. Missing required fields."]);
        }
        break;

    case 'DELETE':
        // Delete a receipt
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $receipt->id = $data->id;
            if ($receipt->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Receipt deleted successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to delete receipt. Database error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. Missing required fields."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
        break;
}
?>


