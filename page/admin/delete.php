<?php
// Admin member delete

require_once '../../lib/base.php';

require_admin(); 

$id = $_GET['id'] ?? 0; // Retrieve the user ID from the URL or use 0 if not provided.

// Redirect with an error if the user ID is missing or invalid.
if (!$id) {
    $_SESSION['error'] = 'Invalid user ID.';
    $referer = $_SERVER['HTTP_REFERER'] ?? 'list.php';
    header('Location: ' . $referer);
    exit;
}

// Retrieve the user details from the database based on the ID.
$stmt = $pdo->prepare("SELECT id, first_name, last_name, email, role FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// Redirect with an error if the user does not exist.
if (!$user) {
    $_SESSION['error'] = 'User not found.';
    $referer = $_SERVER['HTTP_REFERER'] ?? 'list.php';
    header('Location: ' . $referer);
    exit;
}

// Prevent deleting admin users (optional security measure)
// Ensure the last admin user cannot be deleted to prevent locking out the system.
if ($user['role'] === 'Admin') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'Admin'");
    $stmt->execute();
    $admin_count = $stmt->fetchColumn();

    if ($admin_count <= 1) {
        $_SESSION['error'] = 'Cannot delete the last admin user.';
        $referer = $_SERVER['HTTP_REFERER'] ?? 'list.php';
        header('Location: ' . $referer);
        exit;
    }
}

// Attempt to delete the user from the database and set a success or error message.
try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = 'User "' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '" has been deleted successfully.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
}

// Redirect the user back to the previous page or the list page after deletion.
$referer = $_SERVER['HTTP_REFERER'] ?? 'list.php';
header('Location: ' . $referer);
exit;
?>