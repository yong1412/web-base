<?php
// order/order_base.php
date_default_timezone_set('Asia/Kuala_Lumpur');

// Only start the session if one hasn't been started yet by another file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbname = 'furnihome';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

try {
    // Your exact requested PDO setup
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Global variable for the helpers to use
    $_db = $pdo;

} catch (PDOException $e) {
    die("Order Database Connection failed: " . $e->getMessage());
}

// ============================================================================
// Your Personal Helper Functions
// ============================================================================

function db_execute($sql, $params = []) {
    global $_db;
    $stmt = $_db->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

function db_fetch_all($sql, $params = []) {
    global $_db;
    $stmt = $_db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_OBJ); 
}

function db_fetch_single($sql, $params = []) {
    global $_db;
    $stmt = $_db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_OBJ);
}

function is_post() { 
    return $_SERVER['REQUEST_METHOD'] == 'POST'; 
}

function redirect($url) { 
    header("Location: $url"); 
    exit(); 
}

function encode($val) { 
    return htmlspecialchars($val); 
}
?>