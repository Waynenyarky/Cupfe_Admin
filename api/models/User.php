<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the required files for PHPMailer
require __DIR__ . '/../../vendor/autoload.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $email;
    public $role;
    public $created_at;
    public $updated_at;
    public $otp;
    public $active;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (username, password, email, role, otp, active) VALUES (:username, :password, :email, :role, :otp, :active)";
        $stmt = $this->conn->prepare($query);
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT); // Hash the password
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->otp = rand(100000, 999999); // Generate a random 6-digit OTP
        $this->active = 0; // Set active to 0 (not verified)
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":otp", $this->otp);
        $stmt->bindParam(":active", $this->active);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation
                return false;
            } else {
                throw $e;
            }
        }
    }

    // Create a new user for admin without OTP verification
    public function createForAdmin() {
        $query = "INSERT INTO " . $this->table_name . " (username, password, email, role, active) VALUES (:username, :password, :email, :role, :active)";
        $stmt = $this->conn->prepare($query);
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT); // Hash the password
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->active = 1; // Set active to 1 (verified by admin)
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":active", $this->active);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation
                return false;
            } else {
                throw $e;
            }
        }
    }
    // Read all users
    public function readAll() {
        $query = "SELECT id, username, email, role, active, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC"; // Order by newest first
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Retrieve user by ID
    public function readOne() {
        $query = "SELECT id, username, email, role, active, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  ORDER BY created_at DESC 
                  LIMIT 0,1"; // Order by newest first
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Search users by username
    public function search($username) {
        $query = "SELECT * 
                  FROM users 
                  WHERE username LIKE :username 
                  ORDER BY created_at DESC"; // Order by newest first
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $username = "%$username%"; // Add wildcards for partial matching
        $stmt->execute();
        return $stmt;
    }

    // Read users by role
    public function readByRole($role) {
        $query = "SELECT id, username, email, role, active, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE role = :role 
                  ORDER BY created_at DESC"; // Order by newest first
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role", $role);
        $stmt->execute();
        return $stmt;
    }

    // Update user
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, email = :email, role = :role, active = :active 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->active = htmlspecialchars(strip_tags($this->active));

        // Bind parameters
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":active", $this->active);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Delete user
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        return $stmt->execute();
    }

    // Login user
    public function login($password) {
        $query = "SELECT id, username, password, role, email FROM " . $this->table_name . " WHERE email = ? AND active = 1"; // Added email to the SELECT statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->role = $row['role'];
                $this->email = $row['email']; // Added to populate the email property
                return true;
            }
        }
        return false;
    }


    // Generate OTP
    public function generateOTP() {
        $this->otp = rand(100000, 999999); // Generate a random 6-digit OTP
        $query = "UPDATE " . $this->table_name . " SET otp = :otp WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":otp", $this->otp);
        $stmt->bindParam(":email", $this->email);
        return $stmt->execute();
    }

    // Send OTP to user email
    public function sendOTP() {
        // Initialize PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'expressoapp25@gmail.com'; // SMTP username
            $mail->Password = 'lsjg lqle cuhy xrad'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Add this configuration to resolve DNS issues
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Enable verbose debug output
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'html';

            // Recipients
            $mail->setFrom('expressoapp25@gmail.com', 'Expresso Cafe');
            $mail->addAddress($this->email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your email verification OTP Code';
            $mail->Body = 'Your email verification code is ' . $this->otp;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Print PHPMailer error
            error_log($mail->ErrorInfo);
            return false;
        }
    }

    // Send OTP for password change to user email
    public function sendPasswordOtp() {
        // Initialize PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Username = 'expressoapp25@gmail.com'; // SMTP username
            $mail->Password = 'lsjg lqle cuhy xrad'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Add this configuration to resolve DNS issues
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Enable verbose debug output
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = 'html';

            // Recipients
            $mail->setFrom('expressoapp25@gmail.com', 'Expresso Cafe');
            $mail->addAddress($this->email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Password Change OTP Code';
            $mail->Body = 'Your password change OTP code is ' . $this->otp;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Print PHPMailer error
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    // Verify OTP
    public function verifyOTP($otp) {
        $query = "SELECT otp FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($otp == $row['otp']) {
                // Clear OTP and set active to 1 after successful verification
                $clearOtpQuery = "UPDATE " . $this->table_name . " SET otp = NULL, active = 1 WHERE email = :email";
                $clearOtpStmt = $this->conn->prepare($clearOtpQuery);
                $clearOtpStmt->bindParam(":email", $this->email);
                $clearOtpStmt->execute();

                return true;
            }
        }

        return false;
    }

    // Change user password
    public function changePassword($new_password, $otp) {
        // Verify OTP before changing password
        $query = "SELECT otp FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($otp == $row['otp']) {
                // Clear OTP and update password after successful verification
                $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT); // Hash the new password
                $updateQuery = "UPDATE " . $this->table_name . " SET password = :password, otp = NULL WHERE email = :email";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bindParam(":password", $new_password_hashed);
                $updateStmt->bindParam(":email", $this->email);
                return $updateStmt->execute();
            }
        }
        return false;
    }

    // Change user password by admin
    public function changePasswordAdmin($new_password) {
        // Hash the new password
        $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);

        // Update the password in the database
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE email = :email";
        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":password", $new_password_hashed);
        $stmt->bindParam(":email", $this->email);

        // Execute the query
        return $stmt->execute();
    }

    // Generate OTP for password change
    public function generatePasswordChangeOTP() {
        $this->otp = rand(100000, 999999); // Generate a random 6-digit OTP
        $query = "UPDATE " . $this->table_name . " SET otp = :otp WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":otp", $this->otp);
        $stmt->bindParam(":email", $this->email);

        if ($stmt->execute()) {
            if ($this->sendPasswordOtp()) {
                return true;
            }
        }
        return false;
    }
}
?>
