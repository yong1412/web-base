<?php
require '../_base.php';

$msg = temp('msg');
$error = temp('error');
$step = $_SESSION['forgot_step'] ?? 1;
$email_for_form = $_SESSION['forgot_email'] ?? '';

if (is_post()) {
    $action = post('action');

    // Step 1: User submits email to request OTP
    if ($action === 'request_otp') {
        $email = post('email');
        $stmt = $_db->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $otp = rand(100000, 999999);
            $_SESSION['forgot_otp'] = $otp;
            $_SESSION['forgot_email'] = $email;
            $_SESSION['forgot_step'] = 2;
            // In a real app, you would email this OTP.
            temp('msg', "An OTP has been sent to your email. (Simulated: $otp)");
        } else {
            temp('error', "No active account found with that email address.");
        }
        redirect();
    }

    // Step 2: User submits OTP
    if ($action === 'verify_otp') {
        $otp = post('otp');
        if ($otp == ($_SESSION['forgot_otp'] ?? null)) {
            $_SESSION['forgot_step'] = 3;
            temp('msg', "OTP verified. Please set your new password.");
        } else {
            temp('error', "Invalid OTP. Please try again.");
        }
        redirect();
    }

    // Step 3: User submits new password
    if ($action === 'reset_password') {
        $password = post('password');
        $confirm_password = post('confirm_password');

        if (strlen($password) < 6) {
             temp('error', "Password must be at least 6 characters long.");
             redirect();
        }

        if ($password !== $confirm_password) {
            temp('error', "Passwords do not match.");
            redirect();
        }

        $hashed_password = sha1($password);
        $email = $_SESSION['forgot_email'];

        $stmt = $_db->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);

        unset($_SESSION['forgot_step'], $_SESSION['forgot_otp'], $_SESSION['forgot_email']);
        
        temp('login_error', "Your password has been reset. Please login with your new password.");
        redirect('login.php');
    }
    
    // Action to go back to step 1
    if ($action === 'cancel') {
        unset($_SESSION['forgot_step'], $_SESSION['forgot_otp'], $_SESSION['forgot_email']);
        redirect('forgot_password.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | FurniHome</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f3f4f6; }
        .card { background: white; padding: 40px; border-radius: 8px; width: 350px; text-align: center; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #f97316; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; transition: background 0.3s; }
        .msg { background: #dcfce7; color: #16a34a; padding: 10px; border-radius: 4px; font-size: 14px; margin-bottom: 15px; border: 1px solid #86efac; }
        .error-msg { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 4px; font-size: 14px; margin-bottom: 15px; border: 1px solid #f87171; }
        .back-btn { display: inline-block; margin-top: 15px; color: #666; font-size: 13px; }
        #strengthBar { height: 5px; width: 0%; background: red; transition: 0.3s; margin-top: -5px; margin-bottom: 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Forgot Password</h2>

        <?php if ($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>

        <?php if ($step == 1): ?>
            <p style="font-size: 14px; color: #666;">Enter your email to receive a password reset OTP.</p>
            <form method="POST"><input type="hidden" name="action" value="request_otp"><input type="email" name="email" placeholder="Your Email Address" required><button type="submit">Send OTP</button></form>
        <?php elseif ($step == 2): ?>
            <p style="font-size: 14px; color: #666;">Enter the OTP sent to <strong><?= htmlspecialchars($email_for_form) ?></strong>.</p>
            <form method="POST"><input type="hidden" name="action" value="verify_otp"><input type="text" name="otp" placeholder="6-digit OTP" required><button type="submit">Verify OTP</button></form>
        <?php elseif ($step == 3): ?>
            <p style="font-size: 14px; color: #666;">Create a new password for your account.</p>
            <form method="POST"><input type="hidden" name="action" value="reset_password"><input type="password" name="password" id="password" placeholder="New Password" required><div id="strengthBar"></div><input type="password" name="confirm_password" placeholder="Confirm New Password" required><button type="submit">Reset Password</button></form>
        <?php endif; ?>
        
        <?php if ($step > 1): ?>
            <form method="POST"><input type="hidden" name="action" value="cancel"><button type="submit" style="background: none; color: #666; font-size: 13px; padding: 0; margin-top: 10px; text-decoration: underline;">Start Over</button></form>
        <?php else: ?>
             <a href="login.php" class="back-btn">&larr; Back to Login</a>
        <?php endif; ?>
    </div>
<script>
    const password = document.getElementById('password');
    if (password) {
        password.addEventListener('input', function() {
            let val = this.value;
            let strength = 0;
            if (val.length >= 6) strength += 25;
            if (val.match(/[a-z]+/)) strength += 25;
            if (val.match(/[A-Z]+/)) strength += 25;
            if (val.match(/[0-9]+/)) strength += 25;
            let bar = document.getElementById('strengthBar');
            bar.style.width = strength + '%';
            if (strength <= 25) bar.style.background = 'red';
            else if (strength <= 50) bar.style.background = 'orange';
            else if (strength <= 75) bar.style.background = 'yellow';
            else bar.style.background = 'green';
        });
    }
</script>
</body>
</html>