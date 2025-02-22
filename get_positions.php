<?php
require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;
include 'config_1.php';
include 'config.php';

// ฟังก์ชันตรวจสอบ JWT
// ฟังก์ชันตรวจสอบ JWT
function isValidJWT($jwt) {
    global $pdo;

    if (!$jwt) {
        return false;
    }

    try {
        // ตรวจสอบ JWT โดยไม่ต้องส่ง array ของอัลกอริธึม
        $decoded = JWT::decode($jwt, "your_secret_key", ['HS256']);
        
        // ตรวจสอบว่า token หมดอายุหรือไม่
        if (isset($decoded->exp) && $decoded->exp < time()) {
            return false;  // หมดอายุแล้ว
        }

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

// ค้นหาข้อมูลตำแหน่งจากฐานข้อมูล
try {
    $stmt = $pdo->prepare("SELECT * FROM position WHERE status = 1");
    $stmt->execute();
    $position = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['position' => $position]);
} catch (PDOException $e) {
    echo json_encode(['message' => 'Error fetching position']);
}
?>
