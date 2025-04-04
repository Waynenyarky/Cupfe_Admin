<?php
// Include Composer's autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once __DIR__ . '/OrderItem.php';
include_once __DIR__ . '/Receipt.php';

class Order {
    private $conn;
    private $table_name = "orders";

    public $id;
    public $reference_number;
    public $username;
    public $email;
    public $total_amount;
    public $status;
    public $promo_code;
    public $created_at;
    public $updated_at;
    public $order_type;
    public $payment_method;
    public $payment_status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new order
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (reference_number, username, email, total_amount, status, promo_code, order_type, payment_method, payment_status) 
                  VALUES (:reference_number, :username, :email, :total_amount, :status, :promo_code, :order_type, :payment_method, :payment_status)";
        $stmt = $this->conn->prepare($query);

        $this->sanitizeProperties();

        // Bind parameters
        $stmt->bindParam(":reference_number", $this->reference_number);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":status", $this->status);
        if ($this->promo_code === null) {
            $stmt->bindValue(":promo_code", null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(":promo_code", $this->promo_code);
        }
        $stmt->bindParam(":order_type", $this->order_type);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":payment_status", $this->payment_status);

        try {
            if ($stmt->execute()) {
                return true;
            } else {
                error_log("Order creation failed: " . implode(", ", $stmt->errorInfo()));
                return false;
            }
        } catch (Exception $e) {
            error_log("Order creation exception: " . $e->getMessage());
            return false;
        }
    }

    // Create a new order with items
    public function createWithItems($order_items) {
        $this->conn->beginTransaction();

        try {
            // Create the order
            if (!$this->create()) {
                error_log("Order creation failed for reference_number: " . $this->reference_number);
                $this->conn->rollBack();
                return false;
            }

            // Create the order items
            $orderItem = new OrderItem($this->conn);
            foreach ($order_items as $item) {
                $orderItem->reference_number = $this->reference_number;
                $orderItem->item_id = htmlspecialchars(strip_tags($item->item_id ?? ''));
                $orderItem->item_name = htmlspecialchars(strip_tags($item->item_name ?? ''));
                $orderItem->quantity = htmlspecialchars(strip_tags($item->quantity ?? ''));
                $orderItem->price = htmlspecialchars(strip_tags($item->price ?? ''));
                $orderItem->size = htmlspecialchars(strip_tags($item->size ?? ''));
                $orderItem->special_instructions = htmlspecialchars(strip_tags($item->special_instructions ?? ''));
                $orderItem->username = htmlspecialchars(strip_tags($item->username ?? ''));
                $orderItem->email = htmlspecialchars(strip_tags($item->email ?? ''));

                if (!$orderItem->create()) {
                    error_log("OrderItem creation failed for item_id: " . $orderItem->item_id . " in reference_number: " . $this->reference_number);
                    $this->conn->rollBack();
                    return false;
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            error_log("Transaction failed: " . $e->getMessage());
            $this->conn->rollBack();
            return false;
        }
    }

    // Retrieve order by ID
    public function read() {
        $query = "SELECT id, reference_number, username, email, total_amount, status, promo_code, created_at, updated_at, order_type, payment_method, payment_status 
                  FROM " . $this->table_name . " 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Retrieve all orders by payment status
    public function readAllByPaymentStatus($payment_status) {
        $query = "SELECT id, reference_number, username, email, total_amount, status, promo_code, created_at, updated_at, order_type, payment_method, payment_status 
                  FROM " . $this->table_name . " 
                  WHERE payment_status = ? 
                  ORDER BY updated_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $payment_status);
        $stmt->execute();
        return $stmt;
    }

    // Retrieve all orders
    public function readAll() {
        $query = "SELECT id, reference_number, username, email, total_amount, status, promo_code, created_at, updated_at, order_type, payment_method, payment_status 
                  FROM " . $this->table_name . " 
                  ORDER BY updated_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Retrieve all orders by order status
    public function readAllByOrderStatus($status) {
        $query = "SELECT id, reference_number, username, email, total_amount, status, promo_code, created_at, updated_at, order_type, payment_method, payment_status 
                  FROM " . $this->table_name . " 
                  WHERE status = ? 
                  ORDER BY updated_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->execute();
        return $stmt;
    }

    // Retrieve all orders by order type
    public function readAllByOrderType($order_type) {
        $query = "SELECT id, reference_number, username, email, total_amount, status, promo_code, created_at, updated_at, order_type, payment_method, payment_status 
                  FROM " . $this->table_name . " 
                  WHERE order_type = ? 
                  ORDER BY updated_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $order_type);
        $stmt->execute();
        return $stmt;
    }

    // Search order by reference number
    public function searchByReferenceNumber($reference_number) {
        $query = "SELECT id, reference_number, username, email, total_amount, status, promo_code, created_at, updated_at, order_type, payment_method, payment_status 
                  FROM " . $this->table_name . " 
                  WHERE reference_number = ? 
                  ORDER BY updated_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $reference_number);
        $stmt->execute();
        return $stmt;
    }

    // Update order
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET ";
        $fields = [];

        if (!empty($this->username)) $fields[] = "username = :username";
        if (!empty($this->email)) $fields[] = "email = :email";
        if (!empty($this->total_amount)) $fields[] = "total_amount = :total_amount";
        if (!empty($this->status)) $fields[] = "status = :status";
        if (!empty($this->promo_code)) $fields[] = "promo_code = :promo_code";
        if (!empty($this->order_type)) $fields[] = "order_type = :order_type";
        if (!empty($this->payment_method)) $fields[] = "payment_method = :payment_method";
        if (!empty($this->payment_status)) $fields[] = "payment_status = :payment_status";

        $query .= implode(", ", $fields);
        $query .= ", updated_at = NOW() WHERE reference_number = :reference_number";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        if (!empty($this->username)) $stmt->bindParam(":username", $this->username);
        if (!empty($this->email)) $stmt->bindParam(":email", $this->email);
        if (!empty($this->total_amount)) $stmt->bindParam(":total_amount", $this->total_amount);
        if (!empty($this->status)) $stmt->bindParam(":status", $this->status);
        if (!empty($this->promo_code)) $stmt->bindParam(":promo_code", $this->promo_code);
        if (!empty($this->order_type)) $stmt->bindParam(":order_type", $this->order_type);
        if (!empty($this->payment_method)) $stmt->bindParam(":payment_method", $this->payment_method);
        if (!empty($this->payment_status)) $stmt->bindParam(":payment_status", $this->payment_status);
        $stmt->bindParam(":reference_number", $this->reference_number);

        try {
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Order update exception: " . $e->getMessage());
            return false;
        }
    }

    // Update payment status by reference number
    public function updatePaymentStatus($reference_number, $payment_status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET payment_status = :payment_status, updated_at = NOW() 
                  WHERE reference_number = :reference_number";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $this->reference_number = htmlspecialchars(strip_tags($reference_number ?? ''));
        $this->payment_status = htmlspecialchars(strip_tags($payment_status ?? ''));

        $stmt->bindParam(":reference_number", $this->reference_number);
        $stmt->bindParam(":payment_status", $this->payment_status);

        try {
            if ($stmt->execute()) {
                // Send confirmation email if payment status is updated successfully
                $this->sendConfirmationEmail();
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Payment status update exception: " . $e->getMessage());
            return false;
        }
    }
    
    // Set properties dynamically based on fetched data
    public function setProperties($row) {
        foreach ($row as $key => $value) {
            // Populate only existing properties in the class
            if (property_exists($this, $key)) {
                $this->$key = htmlspecialchars(strip_tags($value));
            }
        }
    }

    // Verify order details
    public function verifyOrderDetails() {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE reference_number = :reference_number 
                AND email = :email 
                AND username = :username 
                LIMIT 0,1";
        $stmt = $this->conn->prepare($query);

        // Sanitize the necessary properties
        $this->sanitizeProperties(['reference_number', 'email', 'username']);
        
        $stmt->bindParam(":reference_number", $this->reference_number);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":username", $this->username);

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->setProperties($row); // Populate object properties with the fetched data
            return true;
        }

        return false;
    }

    // Send confirmation email
    public function sendConfirmationEmail() {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'expressoapp25@gmail.com';
            $mail->Password = 'lsjg lqle cuhy xrad'; // Replace with your actual app password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('expressoapp25@gmail.com', 'Expresso Cafe');
            $mail->addAddress($this->email, $this->username);

            $mail->isHTML(true);
            $mail->Subject = 'Payment Confirmation - Expresso Cafe';
            $mail->Body = '
                Dear ' . $this->username . ',<br><br>

                Your payment has been confirmed successfully!<br><br>

                Reference Number: <b>' . $this->reference_number . '</b><br>
                Payment Status: <b>' . $this->payment_status . '</b><br><br>

                Thank you for choosing Expresso Cafe.<br><br>

                Best regards,<br>
                Expresso Cafe
            ';
            $mail->AltBody = 'Dear ' . $this->username . ', Your payment status has been updated successfully! Reference Number: ' . $this->reference_number . ', Payment Status: ' . $this->payment_status . '. Thank you for choosing Expresso Cafe.';

            $mail->send();
            error_log('Payment confirmation email has been sent.');
        } catch (Exception $e) {
            error_log("Payment confirmation email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }

    // Update order status by reference number
    public function updateOrderStatus($reference_number, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, updated_at = NOW() 
                  WHERE reference_number = :reference_number";
        $stmt = $this->conn->prepare($query);

        // Sanitize and bind parameters
        $this->reference_number = htmlspecialchars(strip_tags($reference_number ?? ''));
        $this->status = htmlspecialchars(strip_tags($status ?? ''));

        $stmt->bindParam(":reference_number", $this->reference_number);
        $stmt->bindParam(":status", $this->status);

        try {
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Order status update exception: " . $e->getMessage());
            return false;
        }
    }

    // Delete order
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        return $stmt->execute();
    }

    // Sanitize and typecast properties
    private function sanitizeProperties($properties = null) {
        if ($properties === null) {
            $properties = [
                'reference_number', 'username', 'email', 
                'total_amount', 'status', 'promo_code', 
                'order_type', 'payment_method', 'payment_status', 'created_at', 'updated_at'
            ];
        }

        foreach ($properties as $property) {
            // Apply HTML sanitization
            $this->$property = htmlspecialchars(strip_tags($this->$property ?? ''));

            // Typecast numeric fields explicitly
            if (in_array($property, ['total_amount'], true)) {
                $this->$property = is_numeric($this->$property) ? (float)$this->$property : 0.0;
            }
        }
    }


}
?>
