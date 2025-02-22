<?php
require_once 'vendor/autoload.php';

use \Firebase\JWT\JWT;

include 'config_1.php';
include 'config.php';

// ฟังก์ชันตรวจสอบ JWT
function isValidJWT($jwt)
{
    global $pdo;

    if (!$jwt) {
        return false;
    }

    try {
        // ตรวจสอบ JWT โดยไม่ต้องส่ง array ของอัลกอริธึม
        $decoded = JWT::decode($jwt, "your_secret_key", ['HS256']);  // ใช้ array() หรือ [] แล้วแต่เวอร์ชัน
        return $decoded;
    } catch (Exception $e) {
        return false;
    }
}

// รับ JWT จาก headers
$jwt = null;
$headers = apache_request_headers();
if (isset($headers['Authorization'])) {
    $matches = [];
    if (preg_match('/Bearer (.*)/', $headers['Authorization'], $matches)) {
        $jwt = $matches[1];
    }
}

// ตรวจสอบว่า JWT ถูกต้องหรือไม่
$decoded = isValidJWT($jwt);
if (!$decoded) {
    http_response_code(401);
    echo json_encode(['message' => 'Invalid or expired token']);
    exit;
}

// ค้นหาข้อมูลพนักงานจากฐานข้อมูล
try {
    $stmt = $pdo->prepare("SELECT e.id, e.first_name, e.last_name, p.position_name 
            FROM employees e
            JOIN position p ON e.position = p.id");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['employees' => $employees]);
} catch (PDOException $e) {
    echo json_encode(['message' => 'Error fetching employees']);
}
