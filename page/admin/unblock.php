<?php
// Unblock member

require_once '../../lib/base.php';

require_admin(); 

$id = $_GET['id'] ?? 0; // Get the user ID from the URL; if none is provided, set it to 0.

// If no ID is given, go back to the user list page.
if (!$id) {
    header('Location: list.php');
    exit;
}

// Set the user's status to "active" in the database to unblock them.
$stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
$stmt->execute([$id]);

// Save a message in the session to show that the member was unblocked.
$_SESSION['success'] = 'Member unblocked successfully.';
$referer = $_SERVER['HTTP_REFERER'] ?? 'list.php';
header('Location: ' . $referer);
exit;
?>