<?php

require_once '../../lib/base.php';

require_login();

// Retrieve the current user’s ID from the session.
$user_id = $_SESSION['user_id'];

// Get the full profile details of the logged-in user from the database.
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// If the user doesn’t exist, show an error and redirect to the home page.
if (!$user) {
    $_SESSION['error'] = 'Profile not found.';
    header('Location: ../index.php');
    exit;
}

$page_title = 'My Profile';
include '../_head_panel.php';
?>

<div class="card">
    <div class="card-header">
        <h2>My Profile</h2>
    </div>
    <div class="card-body">
        <div class="profile-photo">
            <strong>Profile Photo:</strong><br>
            <?php if ($user['photo']): ?>
                <a href="../../uploads/profiles/<?php echo $user['photo']; ?>" target="_blank">
                    <img src="../../uploads/profiles/<?php echo $user['photo']; ?>" alt="Profile Photo">
                </a>
            <?php else: ?>
                <div style="width: 150px; height: 150px; background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d; margin: 10px auto;">
                    No photo uploaded
                </div>
            <?php endif; ?>
        </div>
        <div class="profile-info two-col">
            <div>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Date of Birth:</strong>
                    <?php
                    if (!empty($user['dob'])) {
                        echo htmlspecialchars(date('d M Y', strtotime($user['dob'])));
                    } else {
                        echo '<em>Not set</em>';
                    }
                    ?>
                </p>
            </div>
            <div>
                <p><strong>Status:</strong> <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($user['status']); ?></span></p>
                <p><strong>Email Verified:</strong> <?php echo $user['email_verified'] ? '<span style="color: #28a745; font-size: 18px;">✓ Verified</span>' : '<span style="color: #dc3545; font-size: 18px;">✗ Not Verified</span>'; ?></p>
                <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($user['contact_number']); ?></p>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <a href="edit_profile.php" class="btn btn-primary"><i class="fas fa-edit"></i> Edit Profile</a>
        <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>
</div>

<?php
include '../_foot_panel.php';
?>