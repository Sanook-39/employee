<?php
// config.php
$host = 'localhost';
$dbname = 'my_database';
$username = 'root';  // ชื่อผู้ใช้งาน MySQL
$password = '';      // รหัสผ่าน MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
