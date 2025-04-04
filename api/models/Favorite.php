<?php
class Favorite {
    private $conn;
    private $table_name = "favorites";

    public $id;
    public $user_id;
    public $item_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new favorite
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (user_id, item_id) VALUES (:user_id, :item_id)";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":item_id", $this->item_id);

        return $stmt->execute();
    }

    // Retrieve favorite by ID
    public function read() {
        $query = "SELECT id, user_id, item_id, created_at FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Update favorite
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET user_id = :user_id, item_id = :item_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":item_id", $this->item_id);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Delete favorite
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        return $stmt->execute();
    }

    // Search favorites by user_id
    public function search($keyword) {
        $query = "SELECT id, user_id, item_id, created_at FROM " . $this->table_name . " WHERE user_id LIKE ?";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->execute();
        
        return $stmt;
    }
}
?>
