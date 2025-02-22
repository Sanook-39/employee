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
        // ตรวจสอบ JWT
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

// ตรวจสอบว่าเรามีคำค้นหาหรือไม่
if (isset($_GET['search'])) {
    $searchTerm = '%' . $_GET['search'] . '%';
    try {
        $stmt = $pdo->prepare("
            SELECT e.id, e.first_name, e.last_name, p.position_name 
            FROM employees e
            JOIN position p ON e.position = p.id
            WHERE e.first_name LIKE :search OR e.last_name LIKE :search OR p.position_name LIKE :search
        ");
        $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['employees' => $employees]);
    } catch (PDOException $e) {
        echo json_encode(['message' => 'Error searching employees']);
    }
} else {
    echo json_encode(['message' => 'No search term provided']);
}
