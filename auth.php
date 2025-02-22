<?php
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "your_secret_key"; // ตั้งค่า Secret Key สำหรับ JWT

function verifyJWT() {
    global $secret_key;

    $headers = apache_request_headers();
    if (!isset($headers['Authorization'])) {
        return false;
    }

    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);

    try {
        $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
        return (array) $decoded;
    } catch (Exception $e) {
        return false;
    }
}
?>
