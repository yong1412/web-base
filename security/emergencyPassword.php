<?php
require './db.php';
require './_base.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    temp('login_error', 'Invalid or missing reset token.');
    redirect('login.php');
}

// Find the user with this token
$stmt = $pdo->prepare("SELECT id, email, contact_number FROM users WHERE remember_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    temp('login_error', 'Invalid or expired reset token.');
    redirect('login.php');
}

if (is_post()) {
    $password = post('password');
    $confirm_password = post('confirm_password');

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = sha1($password);
        
        // Update password and invalidate the emergency token so it can't be reused
        $stmt = $pdo->prepare("UPDATE users SET password = ?, remember_token = NULL WHERE id = ?");
        $stmt->execute([$hashed_password, $user['id']]);

        temp('success', 'Your password has been secured and changed successfully. Please login.');
        redirect('login.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emergency Password Reset | FurniHome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f3f4f6; }
        .card { background: white; padding: 40px; border-radius: 8px; width: 350px; text-align: center; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; transition: background 0.3s; }
        button:hover { background: #b91c1c; }
        .error-msg { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 4px; font-size: 14px; margin-bottom: 15px; border: 1px solid #f87171; }
        #strengthBar { height: 5px; width: 0%; background: red; transition: 0.3s; margin-top: -5px; margin-bottom: 10px; border-radius: 3px; }
        .password-container { position: relative; width: 100%; }
        .password-container i { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="color: #dc2626;">Secure Your Account</h2>
        <p style="font-size: 14px; color: #666;">Set a new password immediately for <strong><?= htmlspecialchars($user['email'] ?? $user['contact_number']) ?></strong> to lock out unauthorized users.</p>

        <?php if (isset($error)): ?><div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <div class="password-container">
                <input type="password" name="password" id="password" placeholder="New Password" required>
                <i class="fa-solid fa-eye-slash toggle-password" data-target="password"></i>
            </div>
            <div id="strengthBar"></div>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" required>
                <i class="fa-solid fa-eye-slash toggle-password" data-target="confirm_password"></i>
            </div>
            <button type="submit">Change Password</button>
        </form>
    </div>
    <script>
        $(document).ready(function() {
            $('.toggle-password').on('click', function() {
                const targetId = $(this).data('target');
                const input = $('#' + targetId);
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                } else {
                    input.attr('type', 'password');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                }
            });
        });
    </script>
</body>
</html>