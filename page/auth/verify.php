<?php
// Email verification

require_once '../../lib/base.php';

// Get the verification token from the link; if none is provided, set it as empty.
$token = $_GET['token'] ?? '';

// If there is no token, show an error and redirect the user to the homepage.
if (empty($token)) {
    $_SESSION['error'] = 'Invalid verification link.';
    header('Location: /index.php');
    exit;
}

// Find the user with this token and mark their email as verified, then remove the token so it can’t be used again.
$stmt = $pdo->prepare("
    UPDATE users
    SET email_verified = 1, email_token = NULL
    WHERE email_token = ? AND email_verified = 0
");
$stmt->execute([$token]);

// If a user was updated, show a success message; otherwise, show an error saying the link is invalid or expired.
if ($stmt->rowCount() > 0) {
    $_SESSION['success'] = 'Email verified successfully. You can now log in.';
} else {
    $_SESSION['error'] = 'Invalid or expired verification link.';
}

header('Location: /index.php');
exit;
?>