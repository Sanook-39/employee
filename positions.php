<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

require 'config.php'; // ไฟล์เชื่อมต่อ Database
require 'auth.php'; // ตรวจสอบ JWT

// ตรวจสอบ Token
$user = verifyJWT();
if (!$user) {
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

$sql = "SELECT id, name FROM position";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["positions" => $positions]);
?>
