<?php
// Head for member and admin panels with sidebar

require_once '../lib/base.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /security/login.php');
    exit;
}

$user_role = $_SESSION['role'] ?? 'Member';
$user_name = ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? 'User');
$user_photo = $_SESSION['photo'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'FurniHome Panel'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../css/app.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../js/app.js"></script>
    <script src="../../js/validation.js"></script>
</head>
<body>

<div class="panel-dashboard">
    <aside class="sidebar">
        <div class="logo">
            <i class="fa-solid fa-house"></i>
            <span>FurniHome <?php echo $user_role; ?></span>
        </div>
        <nav>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="/page/auth/register.php">Register</a>
                <a href="/security/login.php">Login</a>
            <?php else: ?>
                <a href="/page/member/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
                <a href="/page/member/profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">My Profile</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                    <a href="/page/admin/list.php" class="<?php echo(strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : ''); ?>">Manage Member</a>
                <?php endif; ?>
                <a href="/security/logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="panel-main-content">
        <div class="top-bar">
            <h1><?php echo isset($page_title) ? $page_title : 'Panel'; ?></h1>
            <div class="panel-user">
                <?php if ($user_photo): ?>
                    <img src="../../uploads/profiles/<?php echo htmlspecialchars($user_photo); ?>" alt="Profile" class="profile-avatar">
                <?php endif; ?>
                Welcome, <?php echo htmlspecialchars($user_name); ?>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="color: red; background: #ffe6e6; padding: 10px; margin-bottom: 20px; border: 1px solid #ff9999; border-radius: 4px;">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div style="color: green; background: #e6ffe6; padding: 10px; margin-bottom: 20px; border: 1px solid #99ff99; border-radius: 4px;">
                <?php echo htmlspecialchars($_SESSION['success']); ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>