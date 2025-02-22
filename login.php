<?php
require_once 'vendor/autoload.php';
use \Firebase\JWT\JWT;
include 'config_1.php';

$host = 'localhost';
$dbname = 'my_database';
$username = 'root';  // ชื่อผู้ใช้งาน MySQL
$password = ''; 
try {
    $pdo = new PDO("mysql:host=" .$host . ";dbname=" . $dbname, $username , $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Internal server error']);
    exit;
}

// ฟังก์ชันสำหรับการ login
function login($username, $password) {
    global $pdo;

    // ค้นหาผู้ใช้จากฐานข้อมูล
    $stmt = $pdo->prepare("SELECT * FROM userss WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบรหัสผ่านตรงกัน (ไม่ใช้ password_verify)
    if ($user && $password == $user['password']) {  // ใช้การเปรียบเทียบรหัสผ่านตรงๆ
        // สร้าง JWT
        $key = "your_secret_key";
        $payload = array(
            "id" => $user['id'],
            "username" => $user['username'],
            "exp" => time() + 100000  // JWT หมดอายุใน 1 ชั่วโมง
        );

        $jwt = JWT::encode($payload, $key, 'HS256');
        return $jwt;
    }

    return null;  // รหัสผ่านไม่ตรง
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่ามีข้อมูลที่จำเป็นหรือไม่
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        http_response_code(400);
        echo json_encode(['message' => 'Username and password are required']);
        exit;
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $jwt = login($username, $password);

    if ($jwt) {
        echo json_encode(['jwt' => $jwt, 'redirect' => 'add_user.html']);
    } else {
        http_response_code(401);
        echo json_encode(['message' => 'Invalid credentials'.$password]);
    }
}
?>
