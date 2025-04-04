<?php
class Review {
    private $conn;
    private $table_name = "reviews";

    public $id;
    public $user_id;
    public $item_id;
    public $rating;
    public $comment;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new review
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (user_id, item_id, rating, comment) VALUES (:user_id, :item_id, :rating, :comment)";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        $this->rating = htmlspecialchars(strip_tags($this->rating));
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":item_id", $this->item_id);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":comment", $this->comment);

        return $stmt->execute();
    }

    // Retrieve review by ID
    public function read() {
        $query = "SELECT id, user_id, item_id, rating, comment, created_at FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Update review
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET user_id = :user_id, item_id = :item_id, rating = :rating, comment = :comment WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->item_id = htmlspecialchars(strip_tags($this->item_id));
        $this->rating = htmlspecialchars(strip_tags($this->rating));
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":item_id", $this->item_id);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":comment", $this->comment);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Delete review
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        return $stmt->execute();
    }

    // Search reviews by item_id
    public function search($keyword) {
        $query = "SELECT id, user_id, item_id, rating, comment, created_at FROM " . $this->table_name . " WHERE item_id LIKE ?";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->execute();
        
        return $stmt;
    }
}
?>
