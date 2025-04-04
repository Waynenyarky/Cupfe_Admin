<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Favorite.php';

$database = new Database();
$db = $database->getConnection();

$favorite = new Favorite($db);

// Handle different requests
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->user_id) && !empty($data->item_id)) {
            $favorite->user_id = $data->user_id;
            $favorite->item_id = $data->item_id;
            if ($favorite->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Favorite was created."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create favorite."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'GET':
        if (isset($_GET['id'])) {
            $favorite->id = $_GET['id'];
            $stmt = $favorite->read();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Favorite not found."]);
            }
        } elseif (isset($_GET['search'])) {
            $keyword = $_GET['search'];
            $stmt = $favorite->search($keyword);
            $favorites_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($favorites_arr, $row);
            }
            echo json_encode($favorites_arr);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Missing parameter: id or search keyword."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && (!empty($data->user_id) || !empty($data->item_id))) {
            $favorite->id = $data->id;
            if (!empty($data->user_id)) $favorite->user_id = $data->user_id;
            if (!empty($data->item_id)) $favorite->item_id = $data->item_id;
            if ($favorite->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Favorite updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update favorite."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $favorite->id = $data->id;
            if ($favorite->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Favorite deleted successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to delete favorite."]);
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
