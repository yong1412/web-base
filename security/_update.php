<?php
// setup.php
require 'db.php';


// 2. Alter existing table to add new features if they are missing (non-destructive)
try {
    // Check for and add contact_number column
    $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'contact_number'");
    $stmt->execute([$dbname]);
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN contact_number VARCHAR(20) DEFAULT NULL AFTER dob");
        echo "<p>✅ Migrated: 'contact_number' column added.</p>";
    }

    // Check for and add remember_token column
    $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'remember_token'");
    $stmt->execute([$dbname]);
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL AFTER status");
        echo "<p>✅ Migrated: 'remember_token' column added.</p>";
    }

    // Check for and update status column to include 'inactive'
    $stmt = $pdo->prepare("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'status'");
    $stmt->execute([$dbname]);
    $column_info = $stmt->fetch();
    if ($column_info && strpos($column_info['COLUMN_TYPE'], "'inactive'") === false) {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN status ENUM('active','blocked','inactive') DEFAULT 'active'");
        echo "<p>✅ Migrated: 'status' column updated to include 'inactive'.</p>";
    }

    // Check for and add login_attempts column
    $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'login_attempts'");
    $stmt->execute([$dbname]);
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN login_attempts INT DEFAULT 0 AFTER status");
        echo "<p>✅ Migrated: 'login_attempts' column added.</p>";
    }

    // Check for and add lockout_until column
    $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'lockout_until'");
    $stmt->execute([$dbname]);
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN lockout_until DATETIME DEFAULT NULL AFTER login_attempts");
        echo "<p>✅ Migrated: 'lockout_until' column added.</p>";
    }

    // Check for and add UNIQUE constraint to email if missing
    $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'email'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE users ADD UNIQUE(email)");
        echo "<p>✅ Migrated: 'email' column set to UNIQUE.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Error altering table: " . $e->getMessage() . "</p>";
}

// 3. Insert or Update Admin
$pass_admin = sha1('123');
$stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, contact_number, role) VALUES ('Super', 'Admin', 'admin@furni.com', ?, '012-3456789', 'Admin') ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), password = VALUES(password), contact_number = VALUES(contact_number), role = VALUES(role)");
$stmt->execute([$pass_admin]);

// 4. Insert or Update Member
$pass_member = sha1('123');
$stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, contact_number, role) VALUES ('John', 'Doe', 'member@furni.com', ?, '011-12345678', 'Member') ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), password = VALUES(password), contact_number = VALUES(contact_number), role = VALUES(role)");
$stmt->execute([$pass_member]);

// 5. Insert or Update Yong Kai Quan
$pass_yong = sha1('');
$stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, contact_number, role) VALUES ('Yong', 'Kai Quan', 'kaiquan1412@gmail.com', ?, '019-9876543', 'Admin') ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), password = VALUES(password), contact_number = VALUES(contact_number), role = VALUES(role)");
$stmt->execute([$pass_yong]);


echo "<h2>Database update Complete!</h2>";
echo "<p>Your database is now up to date.</p>";
echo "<p>Yong Kai Quan's password is set to '<strong>123</strong>'.</p>";
echo "<a href='login.php'>Go to Login Page</a>";