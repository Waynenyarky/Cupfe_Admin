<?php
use \Firebase\JWT\JWT;

require_once __DIR__ . '/../../vendor/autoload.php';
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(200);
    exit();
}

// Start session at the beginning of the script
session_start();

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$key = "Expresso"; //jwt key

// Handle different requests
switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        ob_start(); // Start the output buffer
        header("Content-Type: application/json; charset=UTF-8");

        // Admin creates a new user without OTP verification
        if (strpos($_SERVER['REQUEST_URI'], '/create-for-admin') !== false) {
            $data = json_decode(file_get_contents("php://input"));
            if (!empty($data->username) && !empty($data->password) && !empty($data->email) && !empty($data->role)) {
                $user->username = $data->username;
                $user->password = $data->password;
                $user->email = $data->email;
                $user->role = $data->role;

                if ($user->createForAdmin()) {
                    http_response_code(201);
                    echo json_encode(["message" => "User created successfully by Admin."]);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Failed to create user by Admin."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incomplete data."]);
            }
        } 
        // Regular user creation with OTP verification
        elseif (strpos($_SERVER['REQUEST_URI'], '/create') !== false) {
            $data = json_decode(file_get_contents("php://input"));
            if (!empty($data->username) && !empty($data->password) && !empty($data->email) && !empty($data->role)) {
                $user->username = $data->username;
                $user->password = $data->password;
                $user->email = $data->email;
                $user->role = $data->role;

                if ($user->create()) {
                    if ($user->sendOTP()) {
                        http_response_code(200);
                        echo json_encode(["message" => "OTP sent. Please verify the OTP."]);
                    } else {
                        http_response_code(500);
                        echo json_encode(["message" => "Failed to send OTP."]);
                    }
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Failed to create user."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incomplete data."]);
            }
        } elseif (strpos($_SERVER['REQUEST_URI'], '/verify-otp') !== false) {
            $data = json_decode(file_get_contents("php://input"));
            if (!empty($data->email) && !empty($data->otp)) {
                $user->email = $data->email;
                if ($user->verifyOTP($data->otp)) {
                    http_response_code(200);
                    echo json_encode(["message" => "User verified successfully."]);
                } else {
                    http_response_code(400);
                    echo json_encode(["message" => "Invalid OTP."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incomplete data."]);
            }
        } elseif (strpos($_SERVER['REQUEST_URI'], '/login') !== false) {
            // Decode the JSON payload from the request body
            $data = json_decode(file_get_contents("php://input"));
        
            if (!empty($data->email) && !empty($data->password)) {
                $user->email = $data->email;
                if ($user->login($data->password)) {
                    $token = [
                        "iss" => "http://yourdomain.com",
                        "aud" => "http://yourdomain.com",
                        "iat" => time(),
                        "nbf" => time(),
                        "data" => [
                            "id" => $user->id,
                            "username" => $user->username,
                            "role" => $user->role,
                            "email" => $user->email // Include email in the token payload
                        ]
                    ];
                    $jwt = JWT::encode($token, $key, 'HS256');
                    http_response_code(200);
                    echo json_encode([
                        "message" => "Login successful.",
                        "token" => $jwt,
                        "role" => $user->role,
                        "email" => $user->email,
                        "username" => $user->username
            
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(["message" => "Invalid credentials."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incomplete data."]);
            }
        }
        ob_end_flush(); // Flush the output buffer
        break;

    case 'GET':
        if (isset($_GET['id'])) {
            $user->id = $_GET['id']; 
            $stmt = $user->readOne();
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "User not found."]);
            }
        } elseif (isset($_GET['username'])) { // Handle username filtering
            $username = $_GET['username'];
            $stmt = $user->search($username); // Call the search method for usernames
            $users_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($users_arr, $row);
            }
            echo json_encode($users_arr);
        } elseif (isset($_GET['role'])) {
            $role = $_GET['role'];
            $stmt = $user->readByRole($role);
            $users_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($users_arr, $row);
            }
            echo json_encode($users_arr);
        } else {
            $stmt = $user->readAll();
            $users_arr = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($users_arr, $row);
            }
            echo json_encode($users_arr);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));

        // CAPTCHA Secret Key
        $captcha_secret = "6LcNav8qAAAAAEXekW15pBcb_YmsFZahjTulI-IX";

        if (strpos($_SERVER['REQUEST_URI'], '/change-password-admin') !== false) {
            if (!empty($data->email) && !empty($data->new_password) && !empty($data->captcha)) {
                // Verify CAPTCHA
                $captcha_response = $data->captcha;
                $verify_url = "https://www.google.com/recaptcha/api/siteverify?secret=$captcha_secret&response=$captcha_response";

                $captcha_verification = file_get_contents($verify_url);
                $captcha_result = json_decode($captcha_verification, true);

                if (!$captcha_result['success']) {
                    http_response_code(400);
                    echo json_encode(["message" => "CAPTCHA verification failed."]);
                    break;
                }

                // Proceed with password change logic
                $user->email = $data->email;
                if ($user->changePasswordAdmin($data->new_password)) {
                    http_response_code(200);
                    echo json_encode(["message" => "Password changed successfully by Admin."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to change password."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incomplete data."]);
            }
        } elseif (strpos($_SERVER['REQUEST_URI'], '/change-password') !== false) {
            if (!empty($data->email) && !empty($data->new_password) && !empty($data->otp)) {
                $user->email = $data->email;
                if ($user->changePassword($data->new_password, $data->otp)) {
                    http_response_code(200);
                    echo json_encode(["message" => "Password changed successfully."]);
                } else {
                    http_response_code(400);
                    echo json_encode(["message" => "Invalid OTP or unable to change password."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incomplete data."]);
            }
        } elseif (strpos($_SERVER['REQUEST_URI'], '/generate-password-change-otp') !== false) {
            if (!empty($data->email)) {
                $user->email = $data->email;
                if ($user->generatePasswordChangeOTP()) {
                    http_response_code(200);
                    echo json_encode(["message" => "OTP sent for password change. Please verify OTP"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Failed to generate or send OTP."]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Incomplete data."]);
            }
        } elseif (!empty($data->id) && (!empty($data->username) || !empty($data->email) || !empty($data->role) || isset($data->active))) {
            $user->id = $data->id;
            if (!empty($data->username)) $user->username = $data->username;
            if (!empty($data->email)) $user->email = $data->email;
            if (!empty($data->role)) $user->role = $data->role;
            if (isset($data->active)) $user->active = $data->active; // Handle the active field

            if ($user->update()) {
                http_response_code(200);
                echo json_encode(["message" => "User updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update user."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->email)) {
            $user->email = htmlspecialchars(strip_tags($data->email)); 
            if ($user->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "User deleted successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to delete user."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
}
?>
