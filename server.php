<?php
require __DIR__ . '/vendor/autoload.php'; // Correct path for your setup

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiWebSocket implements MessageComponentInterface {
    private $emailConnections = []; // Map emails to their WebSocket connections
    private $secretKey = "Expresso"; // Your JWT secret key
    private $messageQueue = []; // Queue for incoming messages
    private $isProcessingQueue = false; // To ensure sequential message processing

    public function onOpen(ConnectionInterface $conn) {
        echo "New connection opened: {$conn->resourceId}\n";

        // Extract token from the connection URL
        $queryParams = [];
        parse_str($conn->httpRequest->getUri()->getQuery(), $queryParams);

        if (!isset($queryParams['token'])) {
            echo "Connection rejected: Missing token ({$conn->resourceId})\n";
            $conn->close();
            return;
        }

        $token = $queryParams['token'];
        echo "Token received: {$token}\n";

        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            $email = $decoded->data->email ?? null;

            if (!$email) {
                echo "Connection rejected: Token missing 'email' claim ({$conn->resourceId})\n";
                $conn->close();
                return;
            }

            $this->emailConnections[$email][] = $conn;
            echo "New connection established for email: {$email} (Connection ID: {$conn->resourceId})\n";

            echo "Active emailConnections map:\n";
            print_r(array_keys($this->emailConnections));
        } catch (\Exception $e) {
            echo "Connection rejected: Invalid token ({$e->getMessage()}) ({$conn->resourceId})\n";
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Received raw message payload: {$msg}\n";

        $data = json_decode($msg, true);

        if (empty($data)) {
            echo "Failed to decode message payload: {$msg}\n";
            return;
        }

        if (!isset($data['type'])) {
            echo "Message missing 'type' field: " . json_encode($data) . "\n";
            return;
        }

        echo "Message type detected: {$data['type']}\n";

        // Add message to the queue
        $this->messageQueue[] = $data;
        echo "Message queued: " . json_encode($data) . "\n";
        echo "Current queue size: " . count($this->messageQueue) . "\n";

        // Start processing the queue
        $this->processMessageQueue();
    }

    private function processMessageQueue() {
        if ($this->isProcessingQueue) {
            echo "Queue is already being processed. Waiting for completion.\n";
            return; // Prevent overlapping processing
        }
        $this->isProcessingQueue = true;

        echo "Queue size before processing: " . count($this->messageQueue) . "\n";

        while (!empty($this->messageQueue)) {
            $data = array_shift($this->messageQueue); // Get the next message from the queue
            echo "Processing message: " . json_encode($data) . "\n";

            // Route messages based on their type
            if ($data['type'] === "notification" && isset($data['message'])) {
                echo "Routing notification to email: {$data['email']}\n";
                $this->sendNotificationToEmail($data['email'], $data['message']);
            } elseif ($data['type'] === "orderStatus" && isset($data['reference_number'], $data['status'])) {
                echo "Routing order status to email: {$data['email']}\n";
                $this->sendOrderStatusToEmail($data['email'], $data['reference_number'], $data['status']);
            } else {
                echo "Unrecognized or malformed message type.\n";
            }
        }

        echo "Queue processing completed. Queue size after processing: " . count($this->messageQueue) . "\n";
        $this->isProcessingQueue = false;
    }

    public function onClose(ConnectionInterface $conn) {
        foreach ($this->emailConnections as $email => $connections) {
            $this->emailConnections[$email] = array_filter($connections, fn($c) => $c !== $conn);

            if (empty($this->emailConnections[$email])) {
                unset($this->emailConnections[$email]);
            }
        }

        echo "Connection {$conn->resourceId} has been closed.\n";

        echo "Updated emailConnections map after disconnection:\n";
        print_r(array_keys($this->emailConnections));
    }

    public function onError(ConnectionInterface $conn, \Throwable $e) {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function sendNotificationToEmail($email, $message) {
        echo "Attempting to send notification to {$email}\n";

        if (!isset($this->emailConnections[$email])) {
            echo "No active connections found for {$email}\n";
            return;
        }

        foreach ($this->emailConnections[$email] as $conn) {
            echo "Processing connection ID: {$conn->resourceId} for email: {$email}\n";

            try {
                $payload = [
                    "type" => "notification",
                    "message" => $message,
                    "source" => "server"
                ];

                $conn->send(json_encode($payload));
                echo "Notification sent to {$email} (Connection ID: {$conn->resourceId})\n";
            } catch (\Exception $e) {
                echo "Failed to send notification to {$email} (Connection ID: {$conn->resourceId}): {$e->getMessage()}\n";
            }
        }

        echo "Finished attempting to send notification to: {$email}\n";
    }

    private function sendOrderStatusToEmail($email, $reference_number, $status) {
        echo "Attempting to send order status to {$email}\n";

        if (!isset($this->emailConnections[$email])) {
            echo "No active connections found for {$email}\n";
            return;
        }

        foreach ($this->emailConnections[$email] as $conn) {
            echo "Processing connection ID: {$conn->resourceId} for email: {$email}\n";

            try {
                $payload = [
                    "type" => "orderStatus",
                    "reference_number" => $reference_number,
                    "status" => $status,
                    "source" => "server"
                ];

                $conn->send(json_encode($payload));
                echo "Order status sent to {$email} (Connection ID: {$conn->resourceId})\n";
            } catch (\Exception $e) {
                echo "Failed to send order status to {$email} (Connection ID: {$conn->resourceId}): {$e->getMessage()}\n";
            }
        }

        echo "Finished attempting to send order status to: {$email}\n";
    }
}

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ApiWebSocket()
        )
    ),
    8080, // WebSocket server port
    '0.0.0.0' // Bind to all interfaces
);

$server->run();
