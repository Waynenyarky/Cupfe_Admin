<?php
class Promo {
    private $conn;
    private $table_name = "promos";

    public $id;
    public $code;
    public $discount;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new promo
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (code, discount, is_active) VALUES (:code, :discount, :is_active)";
        $stmt = $this->conn->prepare($query);
        $this->code = htmlspecialchars(strip_tags($this->code));
        $this->discount = htmlspecialchars(strip_tags($this->discount));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $stmt->bindParam(":code", $this->code);
        $stmt->bindParam(":discount", $this->discount);
        $stmt->bindParam(":is_active", $this->is_active);

        return $stmt->execute();
    }

    // Retrieve promo by ID
    public function read() {
        $query = "SELECT id, code, discount, is_active, created_at, updated_at FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Retrieve all promos by is_active status
    public function readAllByIsActive($is_active) {
        $query = "SELECT id, code, discount, is_active, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE is_active = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $is_active);
        $stmt->execute();
        return $stmt;
    }

    // Retrieve all promos
    public function readAll() {
        $query = "SELECT id, code, discount, is_active, created_at, updated_at FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update promo
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET code = :code, discount = :discount, is_active = :is_active WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->code = htmlspecialchars(strip_tags($this->code));
        $this->discount = htmlspecialchars(strip_tags($this->discount));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));
        $stmt->bindParam(":code", $this->code);
        $stmt->bindParam(":discount", $this->discount);
        $stmt->bindParam(":is_active", $this->is_active);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Delete promo
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        return $stmt->execute();
    }

    // Search promos by code
    public function search($keyword) {
        $query = "SELECT id, code, discount, is_active, created_at, updated_at FROM " . $this->table_name . " WHERE code LIKE ?";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->execute();
        
        return $stmt;
    }
}
?>
