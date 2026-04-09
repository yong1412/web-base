<?php
// Request Email Verification

require_once '../../lib/base.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("SELECT email, email_verified FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && $user['email_verified'] == 0 && !empty($user['email'])) {
        $token = bin2hex(random_bytes(32));
        
        $stmt = $pdo->prepare("UPDATE users SET email_token = ? WHERE id = ?");
        $stmt->execute([$token, $user_id]);

        if (send_verification_email($user['email'], $token)) {
            $_SESSION['success'] = 'Verification email sent successfully! Please check your inbox.';
        } else {
            $_SESSION['error'] = 'Failed to send verification email. Please try again later.';
        }
    } else {
        $_SESSION['error'] = 'Invalid request. Email may already be verified or not set.';
    }
}

header('Location: profile.php');
exit;
?>