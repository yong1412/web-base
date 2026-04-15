<?php
// lib/base.php

// 1. Database Configuration
$host    = 'localhost';
$dbname  = 'furnihome';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

try {
    // EXACTLY as requested
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Global variable for the helpers to use
    $_db = $pdo;

} catch (PDOException $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// 2. Updated Helpers (Modified to return objects manually)
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
    // Manually fetching as objects since global default was removed
    return $stmt->fetchAll(PDO::FETCH_OBJ); 
}

function db_fetch_single($sql, $params = []) {
    global $_db;
    $stmt = $_db->prepare($sql);
    $stmt->execute($params);
    // Manually fetching as an object
    return $stmt->fetch(PDO::FETCH_OBJ);
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