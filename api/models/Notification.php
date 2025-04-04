<?php
class Notification {
    private $conn;
    private $table_name = "notifications";

    public $id;
    public $email; // Changed from user_id to email
    public $message;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new notification
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (email, message) VALUES (:email, :message)";
        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email)); // Changed from user_id to email
        $this->message = htmlspecialchars(strip_tags($this->message));
        $stmt->bindParam(":email", $this->email); // Changed from :user_id to :email
        $stmt->bindParam(":message", $this->message);

        return $stmt->execute();
    }

    // Generate a notification for an order
    public function generateNotifOrder($email, $reference_number, $status, $created_at) {
        $this->email = $email;
        $this->message = "Your order for reference number: {$reference_number} is now {$status}. Created at: {$created_at}.";
        $this->created_at = $created_at;

        // Call the create function to save the notification
        return $this->create();
    }

    // Generate a notification for a receipt
    public function generateNotifReceipt($email, $reference_number, $created_at) {
        $this->email = $email;
        $this->message = "Your receipt for reference number: {$reference_number} has been saved to your transaction history. Created at: {$created_at}.";
        $this->created_at = $created_at;

        // Call the create function to save the notification
        return $this->create();
    }

    // Generate a notification for a table reservation
    public function generateNotifTableReservation($email, $reference_number, $bundle, $reservation_date, $reservation_time, $created_at) {
        $this->email = $email;
        $this->message = "Your package with reference number: {$reference_number}, package type {$bundle}, for {$reservation_date} at {$reservation_time} is confirmed And receipt saved in transaction history Created at: {$created_at}.";
        $this->created_at = $created_at;

        // Call the create function to save the notification
        return $this->create();
    }

    // Retrieve notification by ID
    public function read() {
        $query = "SELECT id, email, message, created_at FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Retrieve all notifications for a specific email
    public function readAll($email) {
        $query = "SELECT id, email, message, created_at FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        return $stmt;
    }

    // Update notification
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET email = :email, message = :message WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email)); // Changed from user_id to email
        $this->message = htmlspecialchars(strip_tags($this->message));
        $stmt->bindParam(":email", $this->email); // Changed from :user_id to :email
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Delete notification
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        return $stmt->execute();
    }

    // Search notifications by email // Changed from user_id to email
    public function search($keyword) {
        $query = "SELECT id, email, message, created_at FROM " . $this->table_name . " WHERE email LIKE ?";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->execute();
        
        return $stmt;
    }
}
?>
