<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/TableTracker.php';

$database = new Database();
$db = $database->getConnection();

$table_tracker = new TableTracker($db);

// Handle different requests
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->total_tables) && !empty($data->available_tables)) {
            $table_tracker->total_tables = $data->total_tables;
            $table_tracker->available_tables = $data->available_tables;
            if ($table_tracker->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Table tracker entry was created."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create table tracker entry."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'GET':
        $stmt = $table_tracker->read();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($row);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && !empty($data->total_tables) && !empty($data->available_tables)) {
            $table_tracker->id = $data->id;
            $table_tracker->total_tables = $data->total_tables;
            $table_tracker->available_tables = $data->available_tables;
            $table_tracker->updated_at = date('Y-m-d H:i:s');
            if ($table_tracker->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Table tracker entry updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update table tracker entry."]);
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
