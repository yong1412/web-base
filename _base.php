<?php
// ============================================================================
// PHP Setups & Database Connection
// ============================================================================
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// Universal Database Connection for FurniHome
$host = 'localhost';
$dbname = 'furnihome';
$username = 'root';
$password = '';

try {
    $dbo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// ============================================================================
// Database Helpers (Usable by ANY function/table)
// ============================================================================

// Execute a query (Insert, Update, Delete)
function db_execute($sql, $params = []) {
    global $dbo;
    $stmt = $dbo->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount(); 
}

// Fetch multiple rows (Select all)
function db_fetch_all($sql, $params = []) {
    global $dbo;
    $stmt = $dbo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch a single row (Select one)
function db_fetch_single($sql, $params = []) {
    global $dbo;
    $stmt = $dbo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ============================================================================
// General Page Functions
// ============================================================================
function is_get() { return $_SERVER['REQUEST_METHOD'] == 'GET'; }
function is_post() { return $_SERVER['REQUEST_METHOD'] == 'POST'; }

function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function req($key, $value = null) {
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    } else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

// ============================================================================
// HTML Helpers
// ============================================================================
function encode($value) { return htmlentities($value); }

function html_text($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

function html_radios($key, $items, $br = false) {
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) echo '<br>';
    }
    echo '</div>';
}

function html_select($key, $items, $default = '- Select One -', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) echo "<option value=''>$default</option>";
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

// ============================================================================
// Error Handlings
// ============================================================================
$_err = [];

function err($key) {
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    } else {
        echo '<span></span>';
    }
}

// ============================================================================
// Global Constants (Only things that apply site-wide)
// ============================================================================
$_roles = [
    'Admin' => 'Administrator',
    'Member' => 'Member'
];
?>