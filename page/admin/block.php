<?php
// Block member
// blocks a member account by updating the user’s status to "blocked" in the database, ensures only an admin can perform the action 
// redirects back to the previous page with a success message.

require_once '../../lib/base.php';

require_admin(); 

$id = $_GET['id'] ?? 0;

if (!$id) {
    header('Location: list.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET status = 'blocked' WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['success'] = 'Member blocked successfully.';
$referer = $_SERVER['HTTP_REFERER'] ?? 'list.php';
header('Location: ' . $referer);
exit;
?>