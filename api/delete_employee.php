<?php
// require_once 'vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
// include 'config_1.php';
// include 'config.php';
include __DIR__ . '/../config_1.php';
include __DIR__ . '/../config.php';

// ฟังก์ชันตรวจสอบ JWT
function isValidJWT($jwt) {
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

// ตรวจสอบว่ามีการส่ง ID ของพนักงานมาหรือไม่
if (isset($_GET['id'])) {
    $employee_id = $_GET['id'];

    try {
        // คำสั่ง SQL เพื่อลบพนักงาน
        $stmt = $pdo->prepare("DELETE FROM employees WHERE id = :id");
        $stmt->bindParam(':id', $employee_id, PDO::PARAM_INT);

        // Execute การลบ
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete employee']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
}
?>
