<?php
// db.php
$host = 'localhost';
$dbname = 'furnihome';
$user = 'root';
$pass = ''; // Default XAMPP password is empty

try {
    // Create connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If database doesn't exist yet, try to connect without dbname to create it
    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
        $pdo->exec("USE $dbname");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e2) {
        die("Connection failed: " . $e2->getMessage());
    }
}
?>