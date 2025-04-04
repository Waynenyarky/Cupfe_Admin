<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Review.php';

$database = new Database();
$db = $database->getConnection();

$review = new Review($db);

// Handle different requests
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->user_id) && !empty($data->item_id) && !empty($data->rating) && !empty($data->comment)) {
            $review->user_id = $data->user_id;
            $review->item_id = $data->item_id;
            $review->rating = $data->rating;
            $review->comment = $data->comment;
            if ($review->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Review was created."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create review."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'GET':
        if (isset($_GET['id'])) {
            $review->id = $_GET['id'];
            $stmt = $review->read();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Review not found."]);
            }
        } elseif (isset($_GET['search'])) {
            $keyword = $_GET['search'];
            $stmt = $review->search($keyword);
            $reviews_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($reviews_arr, $row);
            }
            echo json_encode($reviews_arr);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Missing parameter: id or search keyword."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id) && (!empty($data->user_id) || !empty($data->item_id) || !empty($data->rating) || !empty($data->comment))) {
            $review->id = $data->id;
            if (!empty($data->user_id)) $review->user_id = $data->user_id;
            if (!empty($data->item_id)) $review->item_id = $data->item_id;
            if (!empty($data->rating)) $review->rating = $data->rating;
            if (!empty($data->comment)) $review->comment = $data->comment;
            if ($review->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Review updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update review."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->id)) {
            $review->id = $data->id;
            if ($review->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Review deleted successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to delete review."]);
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
