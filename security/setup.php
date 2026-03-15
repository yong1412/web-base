<?php
require '_database.php';

echo "<h1>Database Setup & Migration</h1>";

// 1. Create a complete Users Table if it doesn't exist
$create_sql = "CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `role` enum('Admin','Member') DEFAULT 'Member',
  `status` enum('active','blocked','inactive') DEFAULT 'active',
  `remember_token` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_token` varchar(64) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `login_attempts` int(11) DEFAULT 0,
  `lockout_until` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
$_db->exec($create_sql);
echo "<p>✅ Initial schema check complete. 'users' table is present.</p>";

// 2. Alter existing table to add new features if they are missing (non-destructive)
try {
    $dbname = 'furnihome';

    // Check for and add remember_token column
    $stmt = $_db->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'remember_token'");
    $stmt->execute([$dbname]);
    if (!$stmt->fetch()) {
        $_db->exec("ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL AFTER status");
        echo "<p>✅ Migrated: 'remember_token' column added.</p>";
    }

    // Check for and update status column to include 'inactive'
    $stmt = $_db->prepare("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'users' AND COLUMN_NAME = 'status'");
    $stmt->execute([$dbname]);
    $column_info = $stmt->fetch();
    if ($column_info && strpos($column_info->COLUMN_TYPE, "'inactive'") === false) {
        $_db->exec("ALTER TABLE users MODIFY COLUMN status ENUM('active','blocked','inactive') DEFAULT 'active'");
        echo "<p>✅ Migrated: 'status' column updated to include 'inactive'.</p>";
    }

    // Check for and add UNIQUE constraint to email if missing
    $stmt = $_db->query("SHOW INDEX FROM users WHERE Key_name = 'email'");
    if (!$stmt->fetch()) {
        $_db->exec("ALTER TABLE users ADD UNIQUE(email)");
        echo "<p>✅ Migrated: 'email' column set to UNIQUE.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Error altering table: " . $e->getMessage() . "</p>";
}

// 3. Insert or Update Admin
$pass_admin = sha1('123');
$stmt = $_db->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES ('Super', 'Admin', 'admin@furni.com', ?, 'Admin') ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), password = VALUES(password), role = VALUES(role)");
$stmt->execute([$pass_admin]);

// 4. Insert or Update Member
$pass_member = sha1('123');
$stmt = $_db->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES ('John', 'Doe', 'member@furni.com', ?, 'Member') ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), password = VALUES(password), role = VALUES(role)");
$stmt->execute([$pass_member]);

// 5. Insert or Update Yong Kai Quan
$pass_yong = sha1('yong1412');
$stmt = $_db->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES ('Yong', 'Kai Quan', 'kaiquan1412@gmail.com', ?, 'Admin') ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), password = VALUES(password), role = VALUES(role)");
$stmt->execute([$pass_yong]);

echo "<h2>Setup Complete!</h2>";
echo "<p>Your database is now up to date.</p>";
echo "<p>Default users are present with the SHA1 password for '<strong>123</strong>'.</p>";
echo "<a href='login.php'>Go to Login Page</a>";
?>