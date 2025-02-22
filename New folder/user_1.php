<?php
require_once 'config.php'; // ใช้ไฟล์นี้ในการตั้งค่าการเชื่อมต่อฐานข้อมูล
require_once 'vendor/autoload.php'; // ไฟล์ Autoload ของ Firebase JWT

use Firebase\JWT\JWT;

// คีย์ลับที่ใช้ในการเข้ารหัส JWT
$key = "secret_key";

// ฟังก์ชันสำหรับตรวจสอบ JWT
// function authenticate() {
//     global $key;

//     // ตรวจสอบว่า Authorization header มีค่า
//     if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
//         // เอาค่าของ JWT ออกมาจาก header
//         $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
//         $token = str_replace('Bearer ', '', $authHeader); // ลบคำว่า Bearer ออก

//         try {
//             $decoded = JWT::decode($token, $key, array('HS256'));
//             return $decoded;
//         } catch (Exception $e) {
//             echo json_encode(['message' => 'Unauthorized: ' . $e->getMessage()]);
//             exit();
//         }
//     } else {
//         // ถ้าไม่มี Authorization header
//         echo json_encode(['message' => 'Authorization header missing']);
//         exit();
//     }
// }

// เชื่อมต่อกับฐานข้อมูล
function getDBConnection() {
    try {
        // แก้ไขการตั้งค่าเชื่อมต่อกับฐานข้อมูล
        $pdo = new PDO('mysql:host=localhost;dbname=my_database', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo json_encode(['message' => 'Database connection failed: ' . $e->getMessage()]);
        exit();
    }
}

// รับข้อมูลจากฐานข้อมูล
function getAllEmployees() {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM employees";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// เพิ่มพนักงาน
function addEmployee($data) {
    if (empty($data['birth_date'])) {
        return ['message' => 'Error: Birthdate cannot be empty'];
    }

    $pdo = getDBConnection();
    $sql = "INSERT INTO employees (first_name, last_name, gender, birth_date, position) 
            VALUES (:first_name, :last_name, :gender, :birth_date, :position)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':first_name', $data['first_name']);
    $stmt->bindParam(':last_name', $data['last_name']);
    $stmt->bindParam(':gender', $data['gender']);
    $stmt->bindParam(':birth_date', $data['birth_date']);
    $stmt->bindParam(':position', $data['position']);

    $stmt->execute();
    return ['message' => 'Employee added successfully'];
}

// แก้ไขข้อมูลพนักงาน
function updateEmployee($id, $data) {
    $pdo = getDBConnection();
    $sql = "UPDATE employees SET first_name = :first_name, last_name = :last_name, 
            gender = :gender, birth_date = :birth_date, position = :position WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':first_name', $data['first_name']);
    $stmt->bindParam(':last_name', $data['last_name']);
    $stmt->bindParam(':gender', $data['gender']);
    $stmt->bindParam(':birth_date', $data['birth_date']);
    $stmt->bindParam(':position', $data['position']);
    $stmt->execute();
    return ['message' => 'Employee updated successfully'];
}

// ลบข้อมูลพนักงาน
function deleteEmployee($id) {
    $pdo = getDBConnection();
    $sql = "DELETE FROM employees WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return ['message' => 'Employee deleted successfully'];
}

// ตรวจสอบประเภทคำขอและดำเนินการ
// $decoded = authenticate(); // ตรวจสอบ JWT

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT * FROM employees WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($employee) {
                echo json_encode($employee);
            } else {
                echo json_encode(['message' => 'Employee not found']);
            }
        } else {
            echo json_encode(getAllEmployees()); // ถ้าไม่มี ID ให้คืนค่าพนักงานทั้งหมด
        }
        break;

    case 'POST':
        // เพิ่มพนักงานใหม่
        $data = json_decode(file_get_contents("php://input"), true);
        echo json_encode(addEmployee($data));
        break;

    case 'PUT':
        // รับข้อมูล JSON จาก request body
        $putData = json_decode(file_get_contents("php://input"), true);

        if (!isset($_GET['id'])) {
            echo json_encode(['message' => 'Missing employee ID']);
            exit;
        }

        $id = $_GET['id'];
        $first_name = $putData['first_name'] ?? null;
        $last_name = $putData['last_name'] ?? null;
        $gender = $putData['gender'] ?? null;
        $birth_date = $putData['birth_date'] ?? null;
        $position = $putData['position'] ?? null;

        if (!$first_name || !$last_name || !$gender || !$birth_date || !$position) {
            echo json_encode(['message' => 'Missing required fields']);
            exit;
        }

        echo json_encode(updateEmployee($id, $putData)); // แก้ไขข้อมูลพนักงาน
        break;

    case 'DELETE':
        // ลบพนักงาน
        if (isset($_GET['id'])) {
            $id = $_GET['id']; // รับ ID จาก URL
            echo json_encode(deleteEmployee($id));
        } else {
            echo json_encode(['message' => 'Employee ID is required']);
        }
        break;

    default:
        echo json_encode(['message' => 'Method not allowed']);
        break;
}
?>
