<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FurniHome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: "Segoe UI", sans-serif; }
        body { background: #fff; }
        .header { display: flex; justify-content: space-between; padding: 16px 40px; border-bottom: 1px solid #eee; align-items: center; }
        .nav { display: flex; gap: 20px; align-items: center; }
        .nav a { text-decoration: none; color: #333; font-weight: 500; }
        .btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; font-size: 14px; }
        .btn-login { background: #f3f4f6; color: #333; }
        .btn-admin { background: #dc2626; color: white; }
    </style>
</head>
<body>

<div class="header">
    <h2 style="color: #333;"><i class="fa-solid fa-house" style="color: #f97316;"></i> FurniHome</h2>
    <nav class="nav">
        <a href="index.php">Shop</a>
        
        <?php if ($isLoggedIn): ?>
            <span style="font-size: 14px; color: #666;">Hi, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
            
            <?php if ($role === 'Admin'): ?>
                <a href="admin.php" class="btn btn-admin">Admin Dashboard</a>
            <?php endif; ?>

            <a href="profile.php"><i class="fa-solid fa-user-gear"></i> Edit Profile</a>
            
            <a href="logout.php" style="color: red;">Logout</a>

        <?php else: ?>
            <a href="login.php" class="btn btn-login">Login</a>
        <?php endif; ?>
    </nav>
</div>

<div style="padding: 50px; text-align: center; background: #fff7ed;">
    <h1>Welcome to Premium Living</h1>
    <p>Discover furniture that fits your style.</p>
</div>

</body>
</html>