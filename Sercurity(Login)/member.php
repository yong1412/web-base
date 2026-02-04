<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Member') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Member Area</title></head>
<body style="font-family: sans-serif; padding: 40px; background: #f0fdf4;">
    <div style="background: white; padding: 30px; border-radius: 8px;">
        <h1 style="color: #16a34a;">Member Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        <hr>
        <h3>My Activity</h3>
        <ul>
            <li>Order History</li>
            <li>Wishlist</li>
            <li>Track Shipping</li>
        </ul>
        <br>
        <a href="profile.php">Edit Profile</a> | <a href="index.php">Continue Shopping</a> | <a href="logout.php">Logout</a>
    </div>
</body>
</html>