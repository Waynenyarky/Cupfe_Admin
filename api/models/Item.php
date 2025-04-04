<?php
class Item {
    private $conn;
    private $table_name = "items";

    public $id;
    public $name;
    public $new_name;
    public $description;
    public $price_small;
    public $price_medium;
    public $price_large;
    public $category;
    public $subcategory;
    public $is_available;
    public $image_url;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new item with image upload
    public function create($image = null, $target_dir = "ItemImages/") {
        if ($image && $target_dir) {
            $upload_result = $this->uploadImage($image, $target_dir);
            if ($upload_result["success"]) {
                $this->image_url = $upload_result["file_path"]; // Use dynamic URL from uploadImage
            } else {
                return ["success" => false, "message" => $upload_result["message"]];
            }
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  (name, description, price_small, price_medium, price_large, category, subcategory, is_available, image_url) 
                  VALUES (:name, :description, :price_small, :price_medium, :price_large, :category, :subcategory, :is_available, :image_url)";
        $stmt = $this->conn->prepare($query);

        $name = htmlspecialchars(strip_tags($this->name));
        $description = htmlspecialchars(strip_tags($this->description));
        $price_small = htmlspecialchars(strip_tags($this->price_small));
        $price_medium = htmlspecialchars(strip_tags($this->price_medium));
        $price_large = htmlspecialchars(strip_tags($this->price_large));
        $category = htmlspecialchars(strip_tags($this->category));
        $subcategory = htmlspecialchars(strip_tags($this->subcategory));
        $is_available = htmlspecialchars(strip_tags($this->is_available));
        $image_url = htmlspecialchars(strip_tags($this->image_url));

        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price_small", $price_small);
        $stmt->bindParam(":price_medium", $price_medium);
        $stmt->bindParam(":price_large", $price_large);
        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":subcategory", $subcategory);
        $stmt->bindParam(":is_available", $is_available);
        $stmt->bindParam(":image_url", $image_url);

        if ($stmt->execute()) {
            return ["success" => true, "message" => "Item created successfully."];
        } else {
            return ["success" => false, "message" => "Failed to create item."];
        }
    }

    // Retrieve an item by ID
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Fetch all items
    public function fetchAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update($image = null, $target_dir = "ItemImages/", $existing_image_url = null) {
        if ($image && $target_dir) {
            $upload_result = $this->uploadImage($image, $target_dir);
            if ($upload_result["success"]) {
                $this->image_url = $upload_result["file_path"]; // Use dynamic URL from uploadImage
            } else {
                return ["success" => false, "message" => $upload_result["message"]];
            }
        } elseif ($existing_image_url) {
            $this->image_url = $existing_image_url; // Retain existing image_url if no new image is uploaded
        }
    
        $query = "UPDATE " . $this->table_name . " SET 
                    name = :name,
                    description = :description,
                    price_small = :price_small,
                    price_medium = :price_medium,
                    price_large = :price_large,
                    category = :category,
                    subcategory = :subcategory,
                    is_available = :is_available,
                    image_url = :image_url
                  WHERE id = :id";
    
        $stmt = $this->conn->prepare($query);
    
        $name = htmlspecialchars(strip_tags($this->name ?? ''));
        $description = htmlspecialchars(strip_tags($this->description ?? ''));
        $price_small = htmlspecialchars(strip_tags($this->price_small ?? ''));
        $price_medium = htmlspecialchars(strip_tags($this->price_medium ?? ''));
        $price_large = htmlspecialchars(strip_tags($this->price_large ?? ''));
        $category = htmlspecialchars(strip_tags($this->category ?? ''));
        $subcategory = htmlspecialchars(strip_tags($this->subcategory ?? ''));
        $is_available = htmlspecialchars(strip_tags($this->is_available ?? ''));
        $image_url = htmlspecialchars(strip_tags($this->image_url ?? ''));
        $id = htmlspecialchars(strip_tags($this->id ?? ''));
    
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":price_small", $price_small);
        $stmt->bindParam(":price_medium", $price_medium);
        $stmt->bindParam(":price_large", $price_large);
        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":subcategory", $subcategory);
        $stmt->bindParam(":is_available", $is_available);
        $stmt->bindParam(":image_url", $image_url);
        $stmt->bindParam(":id", $id);
    
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Item updated successfully.", "image_url" => $this->image_url];
        } else {
            return ["success" => false, "message" => "Failed to update item."];
        }
    }

    // Upload image method
    private function uploadImage($image, $target_dir) {
        if (isset($image) && $image['error'] === UPLOAD_ERR_OK) {
            $file_name = basename($image['name']);
            $file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "", $file_name);
            $target_file = $target_dir . $file_name;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_type, $allowed_types)) {
                return ["success" => false, "message" => "Invalid file type. Only JPG, PNG, and GIF are allowed."];
            }

            // Compress and resize image
            $source_image = null;
            switch ($file_type) {
                case 'jpg':
                case 'jpeg':
                    $source_image = imagecreatefromjpeg($image['tmp_name']);
                    break;
                case 'png':
                    $source_image = imagecreatefrompng($image['tmp_name']);
                    break;
                case 'gif':
                    $source_image = imagecreatefromgif($image['tmp_name']);
                    break;
            }

            if ($source_image) {
                // Resize image
                $max_width = 800; // Set the maximum width
                $max_height = 800; // Set the maximum height
                list($width, $height) = getimagesize($image['tmp_name']);
                $ratio = $width / $height;

                if ($width > $max_width || $height > $max_height) {
                    if ($ratio > 1) {
                        $new_width = $max_width;
                        $new_height = (int)($max_width / $ratio);
                    } else {
                        $new_height = $max_height;
                        $new_width = (int)($max_height * $ratio);
                    }
                    $resized_image = imagecreatetruecolor($new_width, $new_height);
                    imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    imagedestroy($source_image);
                    $source_image = $resized_image;
                }

                switch ($file_type) {
                    case 'jpg':
                    case 'jpeg':
                        imagejpeg($source_image, $target_file, 50); // 50 is the quality percentage for more aggressive compression
                        break;
                    case 'png':
                        imagepng($source_image, $target_file, 9); // 9 is the highest compression level
                        break;
                    case 'gif':
                        imagegif($source_image, $target_file);
                        break;
                }
                imagedestroy($source_image);

                // Use BASE_URL for dynamic URL generation
                $web_url = BASE_URL . '/expresso-cafe/api/ItemImages/' . $file_name;
                return ["success" => true, "file_path" => $web_url];
            } else {
                return ["success" => false, "message" => "Failed to compress the image."];
            }
        } else {
            return ["success" => false, "message" => "No image file was uploaded."];
        }
    }

    // Delete item by ID
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        return $stmt->execute();
    } 

    // Delete item by name
    public function deleteByName($name) {
        $query = "DELETE FROM " . $this->table_name . " WHERE name = :name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $name);

        return $stmt->execute();
    }

    // Search items by name
    public function search($keyword) {
        $query = "SELECT id, name, description, price_small, price_medium, price_large, category, subcategory, is_available, image_url, created_at, updated_at FROM " . $this->table_name . " WHERE name LIKE ?";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->execute();
        
        return $stmt;
    } 

    // Fetch items by category
    public function fetchByCategory($category) {
        $query = "SELECT id, name, description, price_small, price_medium, price_large, category, subcategory, is_available, image_url, created_at, updated_at FROM " . $this->table_name . " WHERE category = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category);
        $stmt->execute();
        return $stmt;
    }

    // Fetch items by subcategory
    public function fetchBySubCategory($subcategory) {
        $query = "SELECT id, name, description, price_small, price_medium, price_large, category, subcategory, is_available, image_url, created_at, updated_at FROM " . $this->table_name . " WHERE subcategory = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $subcategory);
        $stmt->execute();
        return $stmt;
    }
}
?>
