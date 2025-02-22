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

// ตรวจสอบข้อมูลที่รับมาจาก POST
$data = json_decode(file_get_contents("php://input"));

if (isset($data->first_name) && isset($data->last_name) && isset($data->position)) {
    try {
        // คำสั่ง SQL เพื่อเพิ่มพนักงาน
        $stmt = $pdo->prepare("INSERT INTO employees (first_name, last_name, position, birth_date) VALUES (:first_name, :last_name, :position, :birthdate)");
        $stmt->bindParam(':first_name', $data->first_name);
        $stmt->bindParam(':last_name', $data->last_name);
        $stmt->bindParam(':position', $data->position);
        $stmt->bindParam(':birthdate', $data->birthdate);
        
        // Execute SQL
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Employee added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add employee']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
}
?>
