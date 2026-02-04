<?php
// setup.php
require 'db.php';

// 1. Create Users Table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    dob DATE,
    role ENUM('Admin', 'Member') DEFAULT 'Member'
)";
$pdo->exec($sql);

// 2. Generate Real Hashes for '123'
$password_123 = password_hash("123", PASSWORD_DEFAULT);

// 3. Insert or Update Admin
$stmt = $pdo->prepare("REPLACE INTO users (id, full_name, email, password, role) VALUES (1, 'Super Admin', 'admin@furni.com', ?, 'Admin')");
$stmt->execute([$password_123]);

// 4. Insert or Update Member
$stmt = $pdo->prepare("REPLACE INTO users (id, full_name, email, password, role) VALUES (2, 'John Doe', 'member@furni.com', ?, 'Member')");
$stmt->execute([$password_123]);

echo "<h1>Setup Complete!</h1>";
echo "<p>Database created.</p>";
echo "<p>Users inserted with password: <strong>123</strong></p>";
echo "<a href='login.php'>Go to Login Page</a>";
?>