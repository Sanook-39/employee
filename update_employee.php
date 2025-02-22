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

// รับข้อมูลพนักงานจาก POST
$data = json_decode(file_get_contents("php://input"));

if (isset($data->id, $data->first_name, $data->last_name, $data->gender, $data->position, $data->birthdate)) {
    $id = $data->id;
    $first_name = $data->first_name;
    $last_name = $data->last_name;
    $gender = $data->gender;
    $position = $data->position;
    $birthdate = $data->birthdate;

    try {
        // คำสั่ง SQL เพื่อลงข้อมูลพนักงาน
        $stmt = $pdo->prepare("UPDATE employees SET first_name = :first_name, last_name = :last_name, gender = :gender, position = :position, birth_date = :birthdate WHERE id = :id");
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':birthdate', $birthdate);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update employee']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
}
?>
