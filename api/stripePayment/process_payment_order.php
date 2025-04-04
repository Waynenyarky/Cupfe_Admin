<?php
require __DIR__ . '/../../vendor/autoload.php';
\Stripe\Stripe::setApiKey('sk_test_51QzHASBZDDPGc55vXnCpb6JfewHCv9LhmTf3rUVHEU9g8h8mCbdVslmMGQ9bnc53ZrorIqtZ7BCCpyxJEqTtiZDk00ZekGSW3T'); // Replace with your Stripe secret key

include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/Order.php';

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

        $order = new Order($db);

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        error_log("Received Data: " . print_r($data, true));

        if (!$data) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid JSON received."]);
            exit();
        }

        $order->reference_number = $data['reference_number'] ?? null;
        $order->email = $data['email'] ?? null;
        $order->username = $data['username'] ?? null;

        if (!$order->reference_number || !$order->email || !$order->username || empty($data['token'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Missing required fields: reference_number, email, username, or token."]);
            exit();
        }

        if (!$order->verifyOrderDetails()) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Order details not found or do not match."]);
            exit();
        }

        // Validate total_amount field
        if (empty($order->total_amount) || !is_numeric($order->total_amount) || $order->total_amount <= 0) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid payment amount retrieved from database."]);
            exit();
        }

        error_log("Order Total Amount: " . $order->total_amount);

        // Check if the order is already paid
        if ($order->payment_status === 'Paid') {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "message" => "Order is already marked as paid."
            ]);
            exit();
        }

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => (int)($order->total_amount * 100), // Convert to cents
            'currency' => 'Php',
            'payment_method_data' => [
                'type' => 'card',
                'card' => [
                    'token' => $data['token']
                ],
            ],
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ]);

        $confirmedPaymentIntent = $paymentIntent->confirm([]);

        error_log("Stripe Status: " . $confirmedPaymentIntent->status);

        if ($confirmedPaymentIntent->status === 'succeeded') {
            $order->payment_status = 'Paid';
            if ($order->updatePaymentStatus($order->reference_number, $order->payment_status)) {
                // Automatically generate a receipt for the order
                include_once __DIR__ . '/../models/Receipt.php';
                $receipt = new Receipt($db);

                $receipt->reference_number = $order->reference_number;
                $receipt->email = $order->email;
                $receipt->receipt_for = 'Order';

                $receipt_text = $receipt->generateOrderReceipt($order->reference_number);
                if ($receipt_text) {
                    $receipt->receipt_text = $receipt_text;
                    $receipt->created_at = date('Y-m-d H:i:s');

                    if ($receipt->create()) {
                        http_response_code(200);
                        echo json_encode([
                            "success" => true,
                            "message" => "Payment successful, receipt created, and email confirmation sent! You can now exit this Page"
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