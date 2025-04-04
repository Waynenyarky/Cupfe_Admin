<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/TableReservation.php';

$database = new Database();
$db = $database->getConnection();

$table_reservation = new TableReservation($db);

// Handle different requests
switch ($_SERVER['REQUEST_METHOD']) {
    // POST request to create reservation
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->reference_number) && !empty($data->username) && !empty($data->email) && !empty($data->phone_number) && !empty($data->reservation_date) && !empty($data->reservation_time) && !empty($data->amount) && !empty($data->Bundle)) {
            $table_reservation->reference_number = $data->reference_number;
            $table_reservation->username = $data->username;
            $table_reservation->email = $data->email;
            $table_reservation->phone_number = $data->phone_number;
            $table_reservation->reservation_date = $data->reservation_date;
            $table_reservation->reservation_time = $data->reservation_time;
            $table_reservation->amount = $data->amount;
            $table_reservation->Bundle = $data->Bundle;
            $table_reservation->payment_status = 'Unpaid'; 
            $table_reservation->created_at = date('Y-m-d H:i:s');
            if ($table_reservation->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Reservation created. Proceed to payment.", "reference_number" => $table_reservation->reference_number]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create reservation. Database error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. Missing required fields."]);
        }
        break;

    case 'GET':
        if (isset($_GET['id'])) {
            $table_reservation->id = $_GET['id'];
            $stmt = $table_reservation->read();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Table reservation not found."]);
            }
        } elseif (isset($_GET['search'])) {
            $keyword = $_GET['search'];
            $stmt = $table_reservation->search($keyword);
            $table_reservations_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($table_reservations_arr, $row);
            }
            echo json_encode($table_reservations_arr);
        } elseif (isset($_GET['bundle'])) {
            $bundle = $_GET['bundle'];
            $stmt = $table_reservation->fetchByBundle($bundle);
            $table_reservations_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($table_reservations_arr, $row);
            }
            echo json_encode($table_reservations_arr);
        } else {
            $stmt = $table_reservation->read();
            $table_reservations_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($table_reservations_arr, $row);
            }
            echo json_encode($table_reservations_arr);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && (!empty($data->reference_number) || !empty($data->username) || !empty($data->email) || !empty($data->phone_number) || !empty($data->reservation_date) || !empty($data->reservation_time) || !empty($data->amount) || !empty($data->bundle) || !empty($data->payment_status))) {
            $table_reservation->id = $data->id;
            if (!empty($data->reference_number)) $table_reservation->reference_number = $data->reference_number;
            if (!empty($data->username)) $table_reservation->username = $data->username;
            if (!empty($data->email)) $table_reservation->email = $data->email;
            if (!empty($data->phone_number)) $table_reservation->phone_number = $data->phone_number;
            if (!empty($data->reservation_date)) $table_reservation->reservation_date = $data->reservation_date;
            if (!empty($data->reservation_time)) $table_reservation->reservation_time = $data->reservation_time;
            if (!empty($data->amount)) $table_reservation->amount = $data->amount;
            if (!empty($data->bundle)) $table_reservation->bundle = $data->bundle;
            if (!empty($data->payment_status)) $table_reservation->payment_status = $data->payment_status; // New field
            $table_reservation->updated_at = date('Y-m-d H:i:s');
            if ($table_reservation->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Table reservation updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update table reservation. Database error."]);
            }
        } elseif (!empty($data->reference_number) && !empty($data->payment_status)) {
            $table_reservation->reference_number = $data->reference_number;
            $table_reservation->payment_status = $data->payment_status;
            if ($table_reservation->updatePaymentStatus()) {
                http_response_code(200);
                echo json_encode(["message" => "Payment status updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update payment status. Database error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. Missing required fields."]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $table_reservation->id = $data->id;
            if ($table_reservation->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Table reservation deleted successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to delete table reservation. Database error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data. Missing required fields."]);
        }
        break;

    case 'PATCH':
        if (isset($_GET['action']) && $_GET['action'] == 'verify') {
            $data = json_decode(file_get_contents("php://input"));
            if (!empty($data->reference_number) && !empty($data->email) && !empty($data->username)) {
                $table_reservation->reference_number = $data->reference_number;
                $table_reservation->email = $data->email;
                $table_reservation->username = $data->username;
                if ($table_reservation->verifyReservationDetails()) {
                    http_response_code(200);
                    echo json_encode(["message" => "Reservation details verified successfully."]);
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "Reservation details not found."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incomplete data. Missing required fields."]);
            }
        } elseif (isset($_GET['action']) && $_GET['action'] == 'update-payment') {
            $data = json_decode(file_get_contents("php://input"));
            if (!empty($data->reference_number) && !empty($data->payment_status)) {
                $table_reservation->reference_number = $data->reference_number;
                $table_reservation->payment_status = $data->payment_status;
                if ($table_reservation->updatePaymentStatus()) {
                    http_response_code(200);
                    echo json_encode(["message" => "Payment status updated successfully."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to update payment status. Database error."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incomplete data. Missing required fields."]);
            }
        } else {
            http_response_code(405);
            echo json_encode(["message" => "Method not allowed."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
}
?>
