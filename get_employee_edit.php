<?php
require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;
include 'config_1.php';
include 'config.php';

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

// ตรวจสอบว่าเรามี ID ของพนักงานหรือไม่
if (isset($_GET['id'])) {
    $employee_id = $_GET['id'];

    try {
        // ค้นหาข้อมูลพนักงาน
        $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = :id");
        $stmt->bindParam(':id', $employee_id, PDO::PARAM_INT);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($employee) {
            echo json_encode(['success' => true, 'employee' => $employee]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Employee not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Employee ID is required']);
}
?>
