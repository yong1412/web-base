<?php
// setup.php
require 'db.php';

// 1. Create Users Table
// New fields inserted after role column
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    dob DATE,
    role ENUM('Admin', 'Member') DEFAULT 'Member',
    status ENUM('active', 'blocked') DEFAULT 'active', 
    email_verified TINYINT(1) DEFAULT 0,
    email_token VARCHAR(64),
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)
    ";
$pdo->exec($sql);

// 2. Generate Real Hashes for '123'
//$password_123 = password_hash("123", PASSWORD_DEFAULT);
$password_123 = sha1("123");

// 3. Insert or Update Admin
$stmt = $pdo->prepare("REPLACE INTO users (id, first_name, last_name, email, password, role, status, email_verified) VALUES (1, 'Super', 'Admin', 'admin@furni.com', ?, 'Admin', 'active', 1)");
$stmt->execute([$password_123]);

// 4. Insert or Update Member
$stmt = $pdo->prepare("REPLACE INTO users (id, first_name, last_name, email, password, role, status, email_verified) VALUES (2, 'John', 'Doe', 'member@furni.com', ?, 'Member', 'active', 1)");
$stmt->execute([$password_123]);



echo "<h1>Setup Complete!</h1>";
echo "<p>Database created.</p>";
echo "<p>Users inserted with password: <strong>123</strong></p>";
echo "<a href='/security/login.php'>Go to Login Page</a>";
?>