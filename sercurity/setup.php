<?php
// setup.php
require 'db.php';

// 1. Create Users Table (Updated to match your login and profile scripts)
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    dob DATE,
    role ENUM('Admin', 'Member') DEFAULT 'Member',
    login_attempts INT DEFAULT 0,
    lockout_until DATETIME DEFAULT NULL
)";
$_db->exec($sql);

// 2. Insert or Update Admin (Using plain text '123')
$stmt = $_db->prepare("REPLACE INTO users (id, first_name, last_name, email, password, role) VALUES (1, 'Super', 'Admin', 'admin@furni.com', '123', 'Admin')");
$stmt->execute();

// 3. Insert or Update Member (Using plain text '123')
$stmt = $_db->prepare("REPLACE INTO users (id, first_name, last_name, email, password, role) VALUES (2, 'John', 'Doe', 'member@furni.com', '123', 'Member')");
$stmt->execute();

echo "<h1>Setup Complete!</h1>";
echo "<p>Database table created.</p>";
echo "<p>Users inserted with plain text password: <strong>123</strong></p>";
echo "<a href='login.php'>Go to Login Page</a>";
?>