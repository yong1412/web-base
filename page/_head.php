<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'FurniHome'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/app.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/app.js"></script>
    <script src="../../js/validation.js"></script>
</head>
<body style="background: #fff7ed;">

<header class="header">
    <div class="logo">
        <i class="fa-solid fa-house" style="color: #f97316;"></i>
        FurniHome
    </div>

    <nav class="nav">
        <a href="/index.php">Home</a>
        <a href="/page/about-us.php">About us</a>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="/page/auth/register.php">Register</a>
            <a href="/security/login.php">Login</a>
        <?php else: ?>
            <a href="/page/member/dashboard.php">Dashboard</a>
            <a href="/page/member/profile.php">Profile</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <a href="/page/admin/list.php">Admin</a>
            <?php endif; ?>
            <a href="/security/logout.php">Logout</a>
        <?php endif; ?>
    </nav>
</header>

<?php if (isset($_SESSION['error'])): ?>
    <div style="color: red; background: #ffe6e6; padding: 10px; margin: 10px 40px; border: 1px solid #ff9999; border-radius: 4px;">
        <?php echo htmlspecialchars($_SESSION['error']); ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div style="color: green; background: #e6ffe6; padding: 10px; margin: 10px 40px; border: 1px solid #99ff99; border-radius: 4px;">
        <?php echo htmlspecialchars($_SESSION['success']); ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($page_banner) && $page_banner): ?>
<div class="banner">
    <img src="/assets/image1.jpg" alt="FurniHome Banner" class="banner-image">
    <div class="banner-overlay">
        <h1><?php echo isset($banner_title) ? $banner_title : 'About FurniHome'; ?></h1>
        <p><?php echo isset($banner_subtitle) ? $banner_subtitle : 'Discover Our Journey in Quality Furniture'; ?></p>
    </div>
</div>
<?php endif; ?>

<div class="container<?php echo (!isset($page_banner) || !$page_banner) ? ' container-standard' : ''; ?>">