<?php

require_once '../../lib/base.php';

require_login(); 

// Get the logged-in user’s information from the database using their session ID.
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$page_title = 'Dashboard';
include '../_head_panel.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Member Dashboard</h2>
    </div>
    <div class="card-body">
        <p class="dashboard-welcome">Welcome back, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>! We're glad to have you here.</p>
    </div>
    <div class="card-footer">
        <a href="profile.php" class="btn btn-primary"><i class="fas fa-user"></i> View Profile</a>
        <a href="/security/logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<?php
include '../_foot_panel.php';
?>