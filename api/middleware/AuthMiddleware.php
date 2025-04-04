<?php
use \Firebase\JWT\JWT;

class AuthMiddleware {
    public static function verifyToken($headers) {
        $key = "Expresso";
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            $arr = explode(" ", $authHeader);
            $jwt = $arr[1];

            if ($jwt) {
                try {
                    $decoded = JWT::decode($jwt, $key, array('HS256'));
                    return $decoded->data;
                } catch (Exception $e) {
                    http_response_code(401);
                    echo json_encode(["message" => "Access denied. Invalid token."]);
                    exit();
                }
            }
        }
        http_response_code(401);
        echo json_encode(["message" => "Access denied. Token not provided."]);
        exit();
    }
}
?>
