<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the required files for PHPMailer
require __DIR__ . '/../../vendor/autoload.php';

class TableReservation {
    private $conn;
    private $table_name = "table_reservations";

    public $id;
    public $reference_number;
    public $username;
    public $email;
    public $phone_number;
    public $reservation_date;
    public $reservation_time;
    public $amount;
    public $Bundle;
    public $payment_status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CRUD Operations

    // Create a new reservation
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET 
                    reference_number=:reference_number, 
                    username=:username, 
                    email=:email, 
                    phone_number=:phone_number, 
                    reservation_date=:reservation_date, 
                    reservation_time=:reservation_time,
                    amount=:amount,
                    Bundle=:Bundle,
                    payment_status=:payment_status,
                    created_at=:created_at";
        $stmt = $this->conn->prepare($query);
        $this->sanitizeProperties();
        $this->bindCreateUpdateParams($stmt);
        return $stmt->execute();
    }

    // Read reservations
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update a reservation
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET ";
        $fields = [];
        $params = [];
        $properties = [
            'reference_number', 'username', 'email', 'phone_number', 
            'reservation_date', 'reservation_time', 'amount', 
            'Bundle', 'payment_status', 'updated_at'
        ];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $fields[] = "$property = :$property";
                $params[":$property"] = htmlspecialchars(strip_tags($this->$property));
            }
        }
        $query .= implode(", ", $fields) . " WHERE id = :id";
        $params[":id"] = htmlspecialchars(strip_tags($this->id));
        $stmt = $this->conn->prepare($query);
        foreach ($params as $param => $value) {
            $stmt->bindParam($param, $value);
        }
        return $stmt->execute();
    }

    // Delete a reservation
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    // Search reservations
    public function search($keywords) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE 
                    username LIKE ? OR 
                    email LIKE ? OR 
                    phone_number LIKE ? OR 
                    reference_number LIKE ? OR
                    amount LIKE ? OR
                    Bundle LIKE ? OR
                    payment_status LIKE ?
                  ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";
        for ($i = 1; $i <= 7; $i++) {
            $stmt->bindParam($i, $keywords);
        }
        $stmt->execute();
        return $stmt;
    }

    // Fetch reservations by Bundle
    public function fetchByBundle($bundle) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Bundle = :Bundle ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $bundle = htmlspecialchars(strip_tags($bundle));
        $stmt->bindParam(":Bundle", $bundle);
        $stmt->execute();
        return $stmt;
    }

    // Verify reservation details
    public function verifyReservationDetails() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE reference_number = :reference_number 
                  AND email = :email 
                  AND username = :username 
                  LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $this->sanitizeProperties(['reference_number', 'email', 'username']);
        $stmt->bindParam(":reference_number", $this->reference_number);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->setProperties($row);
            return true;
        }
        return false;
    }

    // Update payment status
    public function updatePaymentStatus() {
        $query = "UPDATE " . $this->table_name . " 
                  SET payment_status = :payment_status 
                  WHERE reference_number = :reference_number";
        $stmt = $this->conn->prepare($query);
        $this->sanitizeProperties(['payment_status', 'reference_number']);
        $stmt->bindParam(":payment_status", $this->payment_status);
        $stmt->bindParam(":reference_number", $this->reference_number);
        if ($stmt->execute()) {
            $this->sendConfirmationEmail();
            return true;
        }
        return false;
    }

    // Email-related methods

    // Send confirmation email
    public function sendConfirmationEmail() {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'expressoapp25@gmail.com';
            $mail->Password = 'lsjg lqle cuhy xrad';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('expressoapp25@gmail.com', 'Expresso Cafe');
            $mail->addAddress($this->email, $this->username);
            $mail->isHTML(true);
            $mail->Subject = 'CupFe Expresso Confirmation Email';
            $mail->Body = '
                Dear ' . $this->username . ',

                Your reservation has been confirmed! Show this email verification at the counter when you arrive at the cafe. <br> <br>

                Reference Number: <b>' . $this->reference_number . '</b><br>
                Bundle: <b>' . $this->Bundle . '</b><br>
                Amount Paid: <b>Php ' . $this->amount . '</b><br>
                Reservation Date: <b>' . $this->reservation_date . '</b><br>
                Reservation Time: <b>' . $this->reservation_time . '</b><br><br>

                Thank you for choosing our service.<br><br>

                Best regards,<br>
                Expresso Cafe
            ';
            $mail->AltBody = 'Dear ' . $this->username . ', Your reservation has been confirmed! Reference Number: ' . $this->reference_number . ', Bundle: ' . $this->Bundle . ', Amount Paid: Php ' . $this->amount . ', Reservation Date: ' . $this->reservation_date . ', Reservation Time: ' . $this->reservation_time . '. Thank you for choosing our service. Best regards, Expresso Cafe';
            $mail->send();
            error_log('Confirmation email has been sent');
        } catch (Exception $e) {
            error_log("Confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    // Helper methods

    // Sanitize properties
    private function sanitizeProperties($properties = null) {
        if ($properties === null) {
            $properties = [
                'reference_number', 'username', 'email', 'phone_number', 
                'reservation_date', 'reservation_time', 'amount', 
                'Bundle', 'payment_status', 'created_at'
            ];
        }
        foreach ($properties as $property) {
            $this->$property = htmlspecialchars(strip_tags($this->$property));
        }
    }

    // Bind parameters for create and update
    private function bindCreateUpdateParams($stmt) {
        $stmt->bindParam(":reference_number", $this->reference_number);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":reservation_date", $this->reservation_date);
        $stmt->bindParam(":reservation_time", $this->reservation_time);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":Bundle", $this->Bundle);
        $stmt->bindParam(":payment_status", $this->payment_status);
        $stmt->bindParam(":created_at", $this->created_at);
    }

    // Set properties from a fetched row
    private function setProperties($row) {
        $this->id = $row['id'];
        $this->amount = $row['amount'];
        $this->Bundle = $row['Bundle'];
        $this->reservation_date = $row['reservation_date'];
        $this->reservation_time = $row['reservation_time'];
        $this->payment_status = $row['payment_status']; // Include payment_status
    }
}
?>
