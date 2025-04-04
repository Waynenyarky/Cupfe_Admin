<?php
class OrderItem {
    private $conn;
    private $table_name = "order_items";

    public $id;
    public $reference_number;
    public $item_id;
    public $item_name;
    public $username;
    public $email;
    public $quantity;
    public $price;
    public $size;
    public $special_instructions; // New field added

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new order item
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (reference_number, item_id, item_name, username, email, quantity, price, size, special_instructions) 
                  VALUES 
                  (:reference_number, :item_id, :item_name, :username, :email, :quantity, :price, :size, :special_instructions)";
        $stmt = $this->conn->prepare($query);
        $this->reference_number = htmlspecialchars(strip_tags($this->reference_number));
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        $this->item_name = htmlspecialchars(strip_tags($this->item_name));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->size = htmlspecialchars(strip_tags($this->size));
        $this->special_instructions = htmlspecialchars(strip_tags($this->special_instructions)); // Sanitize

        // Bind parameters
        $stmt->bindParam(":reference_number", $this->reference_number);
        $stmt->bindParam(":item_id", $this->item_id);
        $stmt->bindParam(":item_name", $this->item_name);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":size", $this->size);
        $stmt->bindParam(":special_instructions", $this->special_instructions);

        try {
            if ($stmt->execute()) {
                return true;
            } else {
                error_log("OrderItem creation failed: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
        } catch (Exception $e) {
            error_log("OrderItem creation exception: " . $e->getMessage());
            return false;
        }
    }

    // Create multiple order items
    public function createMultiple($order_items) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (reference_number, item_id, item_name, quantity, price, size, special_instructions, username, email) 
                  VALUES 
                  (:reference_number, :item_id, :item_name, :quantity, :price, :size, :special_instructions, :username, :email)";
        $stmt = $this->conn->prepare($query);

        foreach ($order_items as $item) {
            $this->reference_number = htmlspecialchars(strip_tags($item->reference_number ?? ''));
            $this->item_id = htmlspecialchars(strip_tags($item->item_id ?? ''));
            $this->item_name = htmlspecialchars(strip_tags($item->item_name ?? ''));
            $this->quantity = htmlspecialchars(strip_tags($item->quantity ?? ''));
            $this->price = htmlspecialchars(strip_tags($item->price ?? ''));
            $this->size = htmlspecialchars(strip_tags($item->size ?? ''));
            $this->special_instructions = htmlspecialchars(strip_tags($item->special_instructions ?? '')); // Sanitize
            $this->username = htmlspecialchars(strip_tags($item->username ?? ''));
            $this->email = htmlspecialchars(strip_tags($item->email ?? ''));

            $stmt->bindParam(":reference_number", $this->reference_number);
            $stmt->bindParam(":item_id", $this->item_id);
            $stmt->bindParam(":item_name", $this->item_name);
            $stmt->bindParam(":quantity", $this->quantity);
            $stmt->bindParam(":price", $this->price);
            $stmt->bindParam(":size", $this->size);
            $stmt->bindParam(":special_instructions", $this->special_instructions); // Bind new field
            $stmt->bindParam(":username", $this->username);
            $stmt->bindParam(":email", $this->email);

            try {
                if (!$stmt->execute()) {
                    error_log("OrderItem creation failed for item_id: " . $this->item_id . " reference_number: " . $this->reference_number);
                    return false;
                }
            } catch (Exception $e) {
                error_log("OrderItem creation exception for item_id: " . $this->item_id . " - " . $e->getMessage());
                return false;
            }
        }

        return true; // Return true if all insertions succeed
    }

    // Retrieve order item by ID
    public function read() {
        $query = "SELECT id, reference_number, item_id, item_name, quantity, price, size, special_instructions, username, created_at 
                  FROM " . $this->table_name . " 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        try {
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {
            error_log("OrderItem read exception: " . $e->getMessage());
            return false;
        }
    }

    // Update order item
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET ";
        $fields = [];

        if (!empty($this->item_id)) $fields[] = "item_id = :item_id";
        if (!empty($this->item_name)) $fields[] = "item_name = :item_name";
        if (!empty($this->quantity)) $fields[] = "quantity = :quantity";
        if (!empty($this->price)) $fields[] = "price = :price";
        if (!empty($this->size)) $fields[] = "size = :size";
        if (!empty($this->special_instructions)) $fields[] = "special_instructions = :special_instructions";
        if (!empty($this->username)) $fields[] = "username = :username";

        $query .= implode(", ", $fields);
        $query .= " WHERE reference_number = :reference_number AND item_id = :item_id";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        if (!empty($this->item_id)) $stmt->bindParam(":item_id", $this->item_id);
        if (!empty($this->item_name)) $stmt->bindParam(":item_name", $this->item_name);
        if (!empty($this->quantity)) $stmt->bindParam(":quantity", $this->quantity);
        if (!empty($this->price)) $stmt->bindParam(":price", $this->price);
        if (!empty($this->size)) $stmt->bindParam(":size", $this->size);
        if (!empty($this->special_instructions)) $stmt->bindParam(":special_instructions", $this->special_instructions);
        if (!empty($this->username)) $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":reference_number", $this->reference_number);
        $stmt->bindParam(":item_id", $this->item_id);

        try {
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("OrderItem update exception: " . $e->getMessage());
            return false;
        }
    }

    // Delete order item
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        try {
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("OrderItem delete exception: " . $e->getMessage());
            return false;
        }
    }

    // Search order items by reference_number
    public function search($keyword) {
        $query = "SELECT id, reference_number, item_id, item_name, quantity, price, size, special_instructions, username, created_at 
                  FROM " . $this->table_name . " 
                  WHERE reference_number LIKE ?";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);

        try {
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {
            error_log("OrderItem search exception: " . $e->getMessage());
            return false;
        }
    }

    // Fetch all order items
    public function fetchAll() {
        $query = "SELECT id, reference_number, item_id, item_name, quantity, price, size, special_instructions, username, email, created_at 
                  FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);

        try {
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {
            error_log("OrderItem fetchAll exception: " . $e->getMessage());
            return false;
        }
    }
}
