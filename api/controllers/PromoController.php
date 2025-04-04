<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Promo.php';

$database = new Database();
$db = $database->getConnection();

$promo = new Promo($db);

// Get the request method
$request_method = $_SERVER['REQUEST_METHOD'];

// Handle different request methods
switch ($request_method) {
    case 'POST':
        // Create a new promo
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->code) && isset($data->discount) && isset($data->is_active)) {
            $promo->code = $data->code;
            $promo->discount = $data->discount;
            $promo->is_active = $data->is_active;
            if ($promo->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Promo was created."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create promo."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'GET':
        // Fetch a promo based on ID
        if (isset($_GET['id'])) {
            $promo->id = $_GET['id'];
            $stmt = $promo->read();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Promo not found."]);
            }
        } elseif (isset($_GET['search'])) {
            $keyword = $_GET['search'];
            $stmt = $promo->search($keyword);
            $promos_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($promos_arr, $row);
            }
            echo json_encode($promos_arr);
        } elseif (isset($_GET['is_active'])) {
            $is_active = $_GET['is_active'];
            $stmt = $promo->readAllByIsActive($is_active);
            $promos_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($promos_arr, $row);
            }
            echo json_encode($promos_arr);
        } else {
            // Fetch all promos if no specific parameter is provided
            $stmt = $promo->readAll();
            $promos_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($promos_arr, $row);
            }
            echo json_encode($promos_arr);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && (!empty($data->code) || isset($data->discount) || isset($data->is_active))) {
            $promo->id = $data->id;
            if (!empty($data->code)) $promo->code = $data->code;
            if (isset($data->discount)) $promo->discount = $data->discount;
            if (isset($data->is_active)) $promo->is_active = $data->is_active;
            if ($promo->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Promo updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update promo."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $promo->id = $data->id;
            if ($promo->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Promo deleted successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to delete promo."]);
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
