<?php
class Receipt {
    private $conn;
    private $table_name = "receipts";

    public $id;
    public $email;
    public $reference_number;
    public $receipt_text;
    public $created_at;
    public $receipt_for;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new receipt
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (email, reference_number, receipt_text, created_at, receipt_for) 
                  VALUES (:email, :reference_number, :receipt_text, :created_at, :receipt_for)";
        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email ?? '')); // Sanitize email
        $this->reference_number = htmlspecialchars(strip_tags($this->reference_number ?? ''));
        $this->receipt_text = htmlspecialchars(strip_tags($this->receipt_text ?? ''));
        $this->created_at = htmlspecialchars(strip_tags($this->created_at ?? ''));
        $this->receipt_for = htmlspecialchars(strip_tags($this->receipt_for ?? ''));
        $stmt->bindParam(":email", $this->email); // Bind email
        $stmt->bindParam(":reference_number", $this->reference_number);
        $stmt->bindParam(":receipt_text", $this->receipt_text);
        $stmt->bindParam(":created_at", $this->created_at);
        $stmt->bindParam(":receipt_for", $this->receipt_for);

        return $stmt->execute();
    }

    // Retrieve receipt by ID
    public function read() {
        $query = "SELECT id, email, reference_number, receipt_text, created_at, receipt_for 
                  FROM " . $this->table_name . " 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Fetch all receipts
    public function fetchAll() {
        $query = "SELECT id, email, reference_number, receipt_text, created_at, receipt_for 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Fetch all receipts by email
    public function fetchAllByEmail($email) {
        $query = "SELECT id, email, reference_number, receipt_text, created_at, receipt_for 
                  FROM " . $this->table_name . " 
                  WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $email = htmlspecialchars(strip_tags($email)); // Sanitize email
        $stmt->bindParam(1, $email);
        $stmt->execute();
        return $stmt;
    }

    // Fetch all receipts by receipt_for
    public function fetchAllByReceiptFor($receipt_for) {
        $query = "SELECT id, email, reference_number, receipt_text, created_at, receipt_for 
                  FROM " . $this->table_name . " 
                  WHERE receipt_for = ?";
        $stmt = $this->conn->prepare($query);
        $receipt_for = htmlspecialchars(strip_tags($receipt_for)); // Sanitize receipt_for
        $stmt->bindParam(1, $receipt_for);
        $stmt->execute();
        return $stmt;
    }

    // Update receipt
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET receipt_text = :receipt_text, receipt_for = :receipt_for 
                  WHERE reference_number = :reference_number";
        $stmt = $this->conn->prepare($query);
        $this->receipt_text = htmlspecialchars(strip_tags($this->receipt_text ?? ''));
        $this->receipt_for = htmlspecialchars(strip_tags($this->receipt_for ?? ''));
        $stmt->bindParam(":receipt_text", $this->receipt_text);
        $stmt->bindParam(":receipt_for", $this->receipt_for);
        $stmt->bindParam(":reference_number", $this->reference_number);

        return $stmt->execute();
    }

    // Delete receipt
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        return $stmt->execute();
    }

    // Search receipts by reference number
    public function search($keyword) {
        $query = "SELECT id, email, reference_number, receipt_text, created_at, receipt_for 
                  FROM " . $this->table_name . " 
                  WHERE reference_number LIKE ?";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->execute();

        return $stmt;
    }

    // Search receipts by email
    public function searchByEmail($keyword) {
        $query = "SELECT id, email, reference_number, receipt_text, created_at, receipt_for 
                  FROM " . $this->table_name . " 
                  WHERE email LIKE ?";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->execute();

        return $stmt;
    }

    // Generate receipt text based on order data
    public function generateOrderReceipt($reference_number) {
        $query = "SELECT o.reference_number, o.username, o.email, o.total_amount, o.created_at, o.promo_code, o.order_type, o.payment_method, o.payment_status,
                         oi.item_id, oi.quantity, oi.price, oi.special_instructions, oi.size, i.name, i.category 
                  FROM orders o
                  JOIN order_items oi ON o.reference_number = oi.reference_number
                  JOIN items i ON oi.item_id = i.id
                  WHERE o.reference_number = :reference_number";
        $stmt = $this->conn->prepare($query);

        $this->reference_number = htmlspecialchars(strip_tags($reference_number ?? ''));
        $stmt->bindParam(":reference_number", $this->reference_number);
        $stmt->execute();

        error_log("Query executed: " . $query);
        error_log("Reference number: " . $this->reference_number);

        $receipt_text = "Order Receipt\n";
        $receipt_text .= "Reference Number: " . $reference_number . "\n";

        $first_row = true;
        $total_amount = 0;
        $email = '';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            error_log("Row fetched: " . json_encode($row));
            if ($first_row) {
                $receipt_text .= "Username: " . $row['username'] . "\n";
                $receipt_text .= "Email: " . $row['email'] . "\n";
                $receipt_text .= "Order Created At: " . $row['created_at'] . "\n";
                $receipt_text .= "Order Type: " . $row['order_type'] . "\n";
                $receipt_text .= "Payment Method: " . $row['payment_method'] . "\n";
                $receipt_text .= "Payment Status: " . $row['payment_status'] . "\n";
                if (!empty($row['promo_code'])) {
                    $receipt_text .= "Promo Code: " . $row['promo_code'] . "\n";
                }
                $receipt_text .= "Items:\n";
                $first_row = false;
                $email = $row['email'];
            }
            $receipt_text .= "- " . $row['name'] . " (" . $row['category'] . ") x" . $row['quantity'] . ": Php" . $row['price'] . "\n";
            $receipt_text .= "  - Size: " . $row['size'] . "\n";
            if (!empty($row['special_instructions'])) {
                $receipt_text .= "  - Special Instructions: " . $row['special_instructions'] . "\n";
            }
            $total_amount = $row['total_amount'];
        }

        if ($first_row) {
            // No rows found
            error_log("No rows found for reference number: " . $reference_number);
            return false;
        }

        if (isset($total_amount)) {
            $receipt_text .= "Total Amount: Php" . $total_amount . "\n";
        }
        $this->receipt_text = $receipt_text;

        $this->email = $email; // Include email in receipt
        $this->created_at = date('Y-m-d H:i:s');
        $this->receipt_for = 'order';
        return $receipt_text;
    }

    // Generate receipt text based on table reservation data
    public function generateTableReceipt($reference_number) {
        $query = "SELECT tr.reference_number, tr.username, tr.email, tr.phone_number, tr.bundle, tr.amount, 
                         tr.reservation_date, tr.reservation_time, tr.created_at
                  FROM table_reservations tr
                  WHERE tr.reference_number = :reference_number";
        $stmt = $this->conn->prepare($query);

        $this->reference_number = htmlspecialchars(strip_tags($reference_number ?? ''));
        $stmt->bindParam(":reference_number", $this->reference_number);
        $stmt->execute();

        $receipt_text = "Table Reservation Receipt\n";
        $receipt_text .= "Reference Number: " . $reference_number . "\n";

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $receipt_text .= "Username: " . $row['username'] . "\n";
            $receipt_text .= "Email: " . $row['email'] . "\n";
            $receipt_text .= "Phone Number: " . $row['phone_number'] . "\n";
            $receipt_text .= "Bundle: " . $row['bundle'] . "\n";
            $receipt_text .= "Reservation Date: " . $row['reservation_date'] . "\n";
            $receipt_text .= "Reservation Time: " . $row['reservation_time'] . "\n";
            $receipt_text .= "Amount: Php" . $row['amount'] . "\n";
            $receipt_text .= "Created At: " . $row['created_at'] . "\n";

            $this->email = $row['email'];
            $this->created_at = date('Y-m-d H:i:s');
            $this->receipt_for = 'table';
            $this->receipt_text = $receipt_text;

            // Call the generateNotifTableReservation function
            include_once __DIR__ . '/../models/Notification.php';
            $notification = new Notification($this->conn);
            $notification->generateNotifTableReservation(
                $row['email'],
                $row['reference_number'],
                $row['bundle'],
                $row['reservation_date'],
                $row['reservation_time'],
                $row['created_at']
            );

            return $receipt_text;
        } else {
            // No rows found
            error_log("No rows found for reference number: " . $reference_number);
            return false;
        }
    }
}
?>
