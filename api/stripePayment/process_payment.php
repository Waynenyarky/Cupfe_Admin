<?php
require __DIR__ . '/../../vendor/autoload.php';
\Stripe\Stripe::setApiKey('sk_test_51QzHASBZDDPGc55vXnCpb6JfewHCv9LhmTf3rUVHEU9g8h8mCbdVslmMGQ9bnc53ZrorIqtZ7BCCpyxJEqTtiZDk00ZekGSW3T'); // Replace with your Stripe secret key

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/TableReservation.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePostRequest();
} else {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Endpoint not found."]);
    exit();
}

function handlePostRequest() {
    try {
        $database = new Database();
        $db = $database->getConnection();

        $table_reservation = new TableReservation($db);

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        error_log("Received Data: " . print_r($data, true));

        if (!$data) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid JSON received."]);
            exit();
        }

        $table_reservation->reference_number = $data['reference_number'] ?? null;
        $table_reservation->email = $data['email'] ?? null;
        $table_reservation->username = $data['username'] ?? null;

        if (!$table_reservation->reference_number || !$table_reservation->email || !$table_reservation->username || empty($data['token'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing required fields: reference_number, email, username, or token."]);
            exit();
        }

        if (!$table_reservation->verifyReservationDetails()) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Reservation details not found or do not match."]);
            exit();
        }

        // Validate amount field
        if (empty($table_reservation->amount) || !is_numeric($table_reservation->amount) || $table_reservation->amount <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid payment amount retrieved from database."]);
            exit();
        }

        error_log("Table Reservation Amount: " . $table_reservation->amount);

        // Check if the reservation is already paid
        if ($table_reservation->payment_status === 'Paid') {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Reservation is already marked as paid."
            ]);
            exit();
        }

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => (int)($table_reservation->amount * 100), // Convert to cents
            'currency' => 'Php',
            'payment_method_data' => [
                'type' => 'card',
                'card' => [
                    'token' => $data['token']
                ],
            ],
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never', // Disable redirect-based payment methods
            ],
        ]);

        $confirmedPaymentIntent = $paymentIntent->confirm([]);

        error_log("Stripe Status: " . $confirmedPaymentIntent->status);

        if ($confirmedPaymentIntent->status === 'succeeded') {
            $table_reservation->payment_status = 'Paid';
            if ($table_reservation->updatePaymentStatus()) {
                // Automatically generate a receipt for the table reservation
                include_once __DIR__ . '/../models/Receipt.php';
                $receipt = new Receipt($db);

                $receipt->reference_number = $table_reservation->reference_number;
                $receipt->email = $table_reservation->email;
                $receipt->receipt_for = 'Table';

                $receipt_text = $receipt->generateTableReceipt($table_reservation->reference_number);
                if ($receipt_text) {
                    $receipt->receipt_text = $receipt_text;
                    $receipt->created_at = date('Y-m-d H:i:s');

                    if ($receipt->create()) {
                        http_response_code(200);
                        echo json_encode([
                            "success" => true,
                            "message" => "Payment successful, receipt created, and email confirmation sent! You can now exit this page."
                        ]);
                    } else {
                        http_response_code(503);
                        echo json_encode([
                            "success" => false,
                            "message" => "Payment successful, but failed to create receipt."
                        ]);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode([
                        "success" => false,
                        "message" => "Payment successful, but failed to generate receipt text."
                    ]);
                }
            } else {
                http_response_code(503);
                echo json_encode([
                    "success" => false,
                    "message" => "Payment successful, but failed to update payment status."
                ]);
            }
            exit();
        } else {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Payment failed. Please try again."
            ]);
            exit();
        }
    } catch (\Stripe\Exception\ApiErrorException $e) {
        error_log("Stripe API Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Stripe API Error: " . $e->getMessage()]);
        exit();
    } catch (Exception $e) {
        error_log("General Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error processing payment: " . $e->getMessage()]);
        exit();
    }
}
?>