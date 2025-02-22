<?php
require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;
include 'config.php';

$key = "secret_key";  // คีย์ลับสำหรับการเข้ารหัส JWT

// รับข้อมูลจาก POST
$data = json_decode(file_get_contents("php://input"));

if (isset($data->username) && isset($data->password)) {
    $username = $data->username;
    $password = $data->password;

    // ตรวจสอบผู้ใช้จากฐานข้อมูล
    $stmt = $pdo->prepare("SELECT * FROM userss WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // if ($user && $password, $user['password']) {
        if ($user && $password == $user['password']) {

        // สร้าง JWT
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600;  // 1 ชั่วโมง
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'userId' => $user['id']
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');
        echo json_encode(['message' => 'Login successful', 'token' => $jwt]);
    } else {
        echo json_encode(['message' => 'Invalid credentials', $stmt]);
    }
} else {
    echo json_encode(['message' => 'Username and password are required']);
}
?>
