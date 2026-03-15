<?php
require '../_base.php';
auth('Admin');

$user = db_fetch_single("SELECT photo FROM users WHERE id = ?", [$_SESSION['user_id']]);

$msg = '';
if (is_post() && post('action') === 'clear_cookies') {
    db_execute("UPDATE users SET remember_token = NULL");
    setcookie('remember_token', '', time() - 3600, "/");
    $msg = "All member and admin cookies have been successfully invalidated.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="font-family: sans-serif; padding: 40px; background: #fee2e2;">
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
                <h1 style="color: #dc2626; margin: 0;">Admin Dashboard</h1>
                <p style="margin: 0; color: #666;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>
            </div>
        </div>
        
        <?php if ($msg): ?>
            <div style="color: #15803d; background: #dcfce7; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px solid #86efac;">
                <?= $msg ?>
            </div>
        <?php endif; ?>
        
        <hr>
        <h3>Management Tools</h3>
        <ul>
            <li>Manage Users</li>
            <li>Inventory Control</li>
            <li>System Logs</li>
            <li>
                <form method="POST" onsubmit="return confirm('Are you sure you want to invalidate all cookies? Everyone will be forced to log in again.');" style="display:inline;">
                    <input type="hidden" name="action" value="clear_cookies">
                    <button type="submit" style="background: none; border: none; color: #dc2626; text-decoration: underline; cursor: pointer; padding: 0; font-size: inherit;">Clear All User Cookies (Safety Feature)</button>
                </form>
            </li>
        </ul>
        <br>
        <a href="profile.php">Edit Profile</a> | <a href="../index.php">View Shop</a> | <a href="logout.php">Logout</a>
    </div>
</body>
</html>