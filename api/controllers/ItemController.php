<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../config/configURL.php'; // Include dynamic BASE_URL
include_once __DIR__ . '/../models/Item.php';

$database = new Database();
$db = $database->getConnection();

$item = new Item($db);

// Handle different requests
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $target_dir = __DIR__ . '/../ItemImages/';

        // Log form fields and uploaded files
        error_log(print_r($_POST, true)); // Log all form fields
        error_log(print_r($_FILES, true)); // Log uploaded files

        if (!empty($_POST['id'])) {
            // **Update Item Logic**
            $item->id = $_POST['id'];
            $item->name = $_POST['name'];
            $item->description = $_POST['description'] ?? null; // Optional field
            $item->price_small = $_POST['price_small'];
            $item->price_medium = $_POST['price_medium'];
            $item->price_large = $_POST['price_large'];
            $item->category = $_POST['category'];
            $item->subcategory = $_POST['subcategory'] ?? null; // Optional field
            $item->is_available = $_POST['is_available'] ?? 1; // Defaults to available

            // Handle image upload if provided
            if (!empty($_FILES['image']['name'])) {
                $image_name = basename($_FILES['image']['name']);
                $target_file = $target_dir . $image_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $item->image_url = BASE_URL . '/expresso-cafe/api/ItemImages/' . $image_name; // Use BASE_URL
                } else {
                    http_response_code(400);
                    echo json_encode(["message" => "Failed to upload image."]);
                    exit;
                }
            } else {
                $item->image_url = $_POST['image_url'] ?? null;
            }

            $result = $item->update(null, $target_dir, $item->image_url);

            if ($result) {
                http_response_code(200);
                echo json_encode(["message" => "Item updated successfully."]);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Failed to update item."]);
            }
        } else {
            // **Create Item Logic**
            if (!empty($_POST['name']) && !empty($_POST['price_small']) && !empty($_POST['price_medium']) && !empty($_POST['price_large']) && !empty($_POST['category'])) {
                $item->name = $_POST['name'];
                $item->description = $_POST['description'] ?? null; // Optional field
                $item->price_small = $_POST['price_small'];
                $item->price_medium = $_POST['price_medium'];
                $item->price_large = $_POST['price_large'];
                $item->category = $_POST['category'];
                $item->subcategory = $_POST['subcategory'] ?? null; // Optional field
                $item->is_available = $_POST['is_available'] ?? 1; // Defaults to available
                $image = isset($_FILES['image']) ? $_FILES['image'] : null;

                $result = $item->create($image, $target_dir);
                if ($result['success']) {
                    http_response_code(201);
                    echo json_encode(["message" => "Item created successfully."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => $result['message']]);
                }
            } else {
                http_response_code(400);
                $missing_fields = [];
                if (empty($_POST['name'])) $missing_fields[] = 'name';
                if (empty($_POST['price_small'])) $missing_fields[] = 'price_small';
                if (empty($_POST['price_medium'])) $missing_fields[] = 'price_medium';
                if (empty($_POST['price_large'])) $missing_fields[] = 'price_large';
                if (empty($_POST['category'])) $missing_fields[] = 'category';
                error_log("Incomplete data: " . implode(', ', $missing_fields));
                echo json_encode(["message" => "Incomplete data: " . implode(', ', $missing_fields)]);
            }
        }
        break;

    case 'GET':
        // Handle item retrieval
        if (isset($_GET['id'])) {
            $item->id = $_GET['id'];
            $stmt = $item->read();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Item not found."]);
            }
        } elseif (isset($_GET['search'])) {
            $keyword = $_GET['search'];
            $stmt = $item->search($keyword);
            $items_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($items_arr, $row);
            }
            echo json_encode($items_arr);
        } elseif (isset($_GET['category'])) {
            $category = $_GET['category'];
            $stmt = $item->fetchByCategory($category);
            $items_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($items_arr, $row);
            }
            echo json_encode($items_arr);
        } elseif (isset($_GET['subcategory'])) {
            $subcategory = $_GET['subcategory'];
            $stmt = $item->fetchBySubCategory($subcategory);
            $items_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($items_arr, $row);
            }
            echo json_encode($items_arr);
        } else {
            $stmt = $item->fetchAll();
            $items_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($items_arr, $row);
            }
            echo json_encode($items_arr);
        }
        break;

    case 'DELETE':
        // Decode JSON payload
        $input_data = json_decode(file_get_contents("php://input"), true);

        if (!empty($input_data['id'])) {
            $item->id = $input_data['id'];
            if ($item->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Item deleted successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Failed to delete item."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "ID is required to delete the item."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
}
?>