<?php
// Admin member detail

require_once '../../lib/base.php';

require_admin(); 

$id = $_GET['id'] ?? 0; // Get the member ID from the URL, or use 0 if none is provided.

// Redirect to the member list if the ID is missing.
if (!$id) {
    header('Location: list.php');
    exit;
}

// Retrieve the user’s details from the database based on the ID.
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Redirect to the list with an error if the user does not exist.
if (!$user) {
    $_SESSION['error'] = 'User not found.';
    header('Location: list.php');
    exit;
}

$page_title = 'Member Detail';
include '../_head_panel.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Member Detail</h2>
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
                <p><strong>ID:</strong> <?php echo $user['id']; ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Role:</strong> <span class="badge badge-<?php echo $user['role'] === 'Admin' ? 'danger' : 'primary'; ?>"><?php echo ucfirst($user['role']); ?></span></p>
                <p>
                    <strong>Date of Birth:</strong>
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
                <p><strong>Created:</strong> <?php echo $user['created_at']; ?></p>
                <p><strong>Updated:</strong> <?php echo $user['updated_at']; ?></p>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <a href="list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
        <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i></a>
        <?php if ($user['status'] === 'active'): ?>
            <button type="button" class="btn btn-danger block-btn" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"><i class="fas fa-ban"></i> Block</button>
        <?php else: ?>
            <button type="button" class="btn btn-success unblock-btn" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"><i class="fas fa-check"></i> Unblock</button>
        <?php endif; ?>
        <!-- <button type="button" class="btn btn-delete delete-btn" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"><i class="fas fa-trash"></i></button> -->
    </div>
</div>

<script>
$(document).ready(function() { 
    $('.block-btn').on('click', function() { // When the “Block” button is clicked, show a confirmation popup using SweetAlert.
        const userId = $(this).data('id');
        const userName = $(this).data('name');

        // Display a warning popup asking the admin to confirm the block action.
        Swal.fire({
            title: 'Block User?',
            text: `Are you sure you want to block "${userName}"? They will not be able to access the system.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, block user',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to block page
                window.location.href = `block.php?id=${userId}`;
            }
        });
    });

    $('.unblock-btn').on('click', function() { // When the “Unblock” button is clicked, show a confirmation popup for unblocking.
        const userId = $(this).data('id');
        const userName = $(this).data('name');

        // Show a question-style popup asking the admin to confirm the unblock action.
        Swal.fire({
            title: 'Unblock User?',
            text: `Are you sure you want to unblock "${userName}"? They will regain access to the system.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, unblock user',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to unblock page
                window.location.href = `unblock.php?id=${userId}`;
            }
        });
    });

    $('.delete-btn').on('click', function() { // When the “Delete” button is clicked, show a confirmation popup warning about permanent deletion.
        const userId = $(this).data('id');
        const userName = $(this).data('name');

        // Tell the admin that deleting this user cannot be undone and ask if they are sure.
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete user "${userName}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete user',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `delete.php?id=${userId}`;
            }
        });
    });
});
</script>

<?php
include '../_foot_panel.php';
?>