<?php
// Configure session cookie to expire when the browser closes
session_set_cookie_params([
    'lifetime' => 0, // Session lasts until the browser is closed
    'path' => '/',
    'domain' => '', // Adjust for subdomains if necessary
    'secure' => true, // Requires HTTPS for security
    'httponly' => true, // Prevents access via JavaScript
    'samesite' => 'Strict' // Prevents cross-site requests
]);

session_start(); // Start the session after setting cookie parameters

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"), true);

// Check if a token is provided in the request
if (isset($data['token'])) {
    // Store the token in the session
    $_SESSION['user_token'] = $data['token'];

    // Check if email is provided and store it in the session
    if (isset($data['email'])) {
        $_SESSION['user_email'] = $data['email'];
    }

    // Respond with a success message
    echo json_encode(["message" => "Session token and email stored successfully."]);
} else {
    // If the token is missing, respond with an error
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Token is missing."]);
}
?>
