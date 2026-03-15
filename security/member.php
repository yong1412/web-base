<?php
require '../_base.php';
auth('Member');

$user = db_fetch_single("SELECT photo FROM users WHERE id = ?", [$_SESSION['user_id']]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Member Area</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="font-family: sans-serif; padding: 40px; background: #f0fdf4;">
    <div style="background: white; padding: 30px; border-radius: 8px;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <?php if ($user['photo']): ?>
                <img src="../uploads/<?= htmlspecialchars($user['photo']) ?>" alt="Profile Photo" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
            <?php else: ?>
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 24px; color: #94a3b8; border: 2px solid #ddd;">
                    <i class="fa-solid fa-user"></i>
                </div>
            <?php endif; ?>
            <div>
                <h1 style="color: #16a34a; margin: 0;">Member Dashboard</h1>
                <p style="margin: 0; color: #666;">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
            </div>
        </div>
        <hr>
        <h3>My Activity</h3>
        <ul>
            <li>Order History</li>
            <li>Wishlist</li>
            <li>Track Shipping</li>
        </ul>
        <br>
        <a href="profile.php">Edit Profile</a> | <a href="../index.php">Continue Shopping</a> | <a href="logout.php">Logout</a>
    </div>
</body>
</html>