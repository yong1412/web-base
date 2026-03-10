<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin Panel</title></head>
<body style="font-family: sans-serif; padding: 40px; background: #fee2e2;">
    <div style="background: white; padding: 30px; border-radius: 8px;">
        <h1 style="color: #dc2626;">Admin Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>
        <hr>
        <h3>Management Tools</h3>
        <ul>
            <li>Manage Users</li>
            <li>Inventory Control</li>
            <li>System Logs</li>
        </ul>
        <br>
        <a href="profile.php">Edit Profile</a> | <a href="index.php">View Shop</a> | <a href="logout.php">Logout</a>
    </div>
</body>
</html>