<?php
// ============================================================================
// Universal Database Connection
// ============================================================================

try {
    $_db = new PDO('mysql:host=localhost;dbname=furnihome', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    ]);
} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// ============================================================================
// Database Helpers (Usable by ANY function/table)
// ============================================================================

// Execute a query (Insert, Update, Delete)
function db_execute($sql, $params = []) {
    global $_db;
    $stmt = $_db->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount(); 
}

// Fetch multiple rows (Select all)
function db_fetch_all($sql, $params = []) {
    global $_db;
    $stmt = $_db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch a single row (Select one)
function db_fetch_single($sql, $params = []) {
    global $_db;
    $stmt = $_db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ============================================================================
// Security & Authentication
// ============================================================================

// Auto-login via Remember Me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $u = db_fetch_single("SELECT * FROM users WHERE remember_token = ? AND status = 'active'", [$_COOKIE['remember_token']]);
    if ($u) {
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['role'] = $u['role'];
        $_SESSION['name'] = $u['first_name'];
    }
}

// Auth Function
function auth($role = null) {
    if (!isset($_SESSION['user_id'])) {
        temp('login_error', 'Please login to continue.');
        redirect('/security/login.php');
    }
    if ($role && $_SESSION['role'] !== $role) {
        redirect('/index.php');
    }
}
?>