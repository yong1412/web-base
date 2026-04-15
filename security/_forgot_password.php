<?php
require './db.php';
require './_base.php';
require_once '../lib/config.php';
require_once '../lib/mailer.php';

$msg = temp('msg');
$error = temp('error');
$step = $_SESSION['forgot_step'] ?? 1;
$login_id_for_form = $_SESSION['forgot_login_id'] ?? '';

if (is_post()) {
    $action = post('action');

    // Step 1: User submits email to request OTP
    if ($action === 'request_otp') {
        $login_id = post('login_id');
        
        // Validate if the input is a valid phone number or email format
        if (strpos($login_id, '@') === false) {
            if (!preg_match('/^[0-9]{3}-[0-9]{7,8}$/', $login_id)) {
                temp('error', "Phone number must be in the format XXX-XXXXXXX (e.g., 014-2461428).");
                redirect();
            }
        } elseif (!filter_var($login_id, FILTER_VALIDATE_EMAIL)) {
            temp('error', "Invalid email format.");
            redirect();
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR contact_number = ?) AND status = 'active'");
        $stmt->execute([$login_id, $login_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $otp = rand(100000, 999999);
            $_SESSION['forgot_otp'] = $otp;
            $_SESSION['forgot_login_id'] = $login_id;
            $_SESSION['forgot_user_id'] = $user['id'];
            $_SESSION['forgot_step'] = 2;
            
            // If they entered a phone number, send via Twilio
            if (strpos($login_id, '@') === false) {
                $twilio_sid = TWILIO_SID;
                $twilio_token = TWILIO_TOKEN;
                $from_whatsapp = TWILIO_WHATSAPP_FROM; 
                $from_sms = TWILIO_SMS_FROM; 
                $formatted_number = '+60' . ltrim(str_replace('-', '', $login_id), '0');
                $message_body = "Your FurniHome password reset code is: $otp";

                // 1. Send via WhatsApp
                $to_whatsapp = 'whatsapp:' . $formatted_number;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.twilio.com/2010-04-01/Accounts/$twilio_sid/Messages.json");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_USERPWD, "$twilio_sid:$twilio_token");
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['From' => $from_whatsapp, 'To' => $to_whatsapp, 'Body' => $message_body]));
                curl_exec($ch);
                curl_close($ch);

                // 2. Send via SMS
                $to_sms = $formatted_number;
                $ch_sms = curl_init();
                curl_setopt($ch_sms, CURLOPT_URL, "https://api.twilio.com/2010-04-01/Accounts/$twilio_sid/Messages.json");
                curl_setopt($ch_sms, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_sms, CURLOPT_POST, true);
                curl_setopt($ch_sms, CURLOPT_USERPWD, "$twilio_sid:$twilio_token");
                curl_setopt($ch_sms, CURLOPT_POSTFIELDS, http_build_query(['From' => $from_sms, 'To' => $to_sms, 'Body' => $message_body]));
                curl_exec($ch_sms);
                curl_close($ch_sms);

                temp('msg', "An OTP has been sent to your SMS and WhatsApp.");
            } else {
                $subject = "Password Reset OTP - FurniHome";
                $message = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h2 style='color: #f97316;'>FurniHome Password Reset</h2>
                    <p>We received a request to reset your password. Your One-Time Password (OTP) is:</p>
                    <div style='background: #f3f4f6; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; margin: 20px 0; border-radius: 5px;'>
                        $otp
                    </div>
                    <p>Please enter this code on the password reset page.</p>
                    <p style='color: #666; font-size: 12px; margin-top: 30px;'>If you did not request this password reset, please safely ignore this email.</p>
                </div>
                ";
                $altMessage = "Your FurniHome password reset OTP is: $otp";
                
                if (send_email($login_id, $subject, $message, $altMessage)) {
                    temp('msg', "An OTP has been sent to your email.");
                } else {
                    temp('error', "Failed to send OTP to your email. Please try again later.");
                    unset($_SESSION['forgot_step'], $_SESSION['forgot_otp'], $_SESSION['forgot_login_id'], $_SESSION['forgot_user_id']);
                }
            }
        } else {
            temp('error', "No active account found with that email or phone number.");
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
        $user_id = $_SESSION['forgot_user_id'];

        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);

        unset($_SESSION['forgot_step'], $_SESSION['forgot_otp'], $_SESSION['forgot_login_id'], $_SESSION['forgot_user_id']);
        
        temp('success', "Your password has been reset. Please login with your new password.");
        redirect('login.php');
    }

    // Action to go back to step 1
    if ($action === 'cancel') {
        unset($_SESSION['forgot_step'], $_SESSION['forgot_otp'], $_SESSION['forgot_login_id'], $_SESSION['forgot_user_id']);
        redirect('_forgot_password.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password | FurniHome</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
            <p style="font-size: 14px; color: #666;">Enter your email or phone number to receive a password reset OTP.</p>
            <form method="POST"><input type="hidden" name="action" value="request_otp"><input type="text" name="login_id" placeholder="Email or Phone Number" required><button type="submit">Send OTP</button></form>
        <?php elseif ($step == 2): ?>
            <p style="font-size: 14px; color: #666;">Enter the OTP sent to <strong><?= htmlspecialchars($login_id_for_form) ?></strong>.</p>
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
    $(document).ready(function() {
        $('#password').on('input', function() {
            let val = $(this).val();
            let strength = 0;
            if (val.length >= 6) strength += 25;
            if (val.match(/[a-z]+/)) strength += 25;
            if (val.match(/[A-Z]+/)) strength += 25;
            if (val.match(/[0-9]+/)) strength += 25;
            
            let bar = $('#strengthBar');
            bar.css('width', strength + '%');
            if (strength <= 25) bar.css('background', 'red');
            else if (strength <= 50) bar.css('background', 'orange');
            else if (strength <= 75) bar.css('background', 'yellow');
            else bar.css('background', 'green');
        });
    });
</script>
</body>
</html>