<?php
// verify_whatsapp.php
require_once '../../lib/base.php';

if (!isset($_SESSION['pending_registration'])) {
    header('Location: register.php');
    exit;
}

$pending = $_SESSION['pending_registration'];
$contact_number = $pending['contact_number'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle the Resend OTP request
    if (isset($_POST['action']) && $_POST['action'] === 'resend') {
        $new_otp = rand(100000, 999999);
        $_SESSION['pending_registration']['otp'] = $new_otp;

        // Twilio Credentials (make sure these match your register_process.php)
        $twilio_sid = TWILIO_SID;
        $twilio_token = TWILIO_TOKEN; // always change this token
        $from_whatsapp = TWILIO_WHATSAPP_FROM; 
        $from_sms = TWILIO_SMS_FROM; 
        $formatted_number = '+60' . ltrim(str_replace('-', '', $contact_number), '0');
        $message_body = "Your FurniHome verification code is: $new_otp";

        // 1. Send via WhatsApp
        $to_whatsapp = 'whatsapp:' . $formatted_number;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.twilio.com/2010-04-01/Accounts/$twilio_sid/Messages.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$twilio_sid:$twilio_token");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['From' => $from_whatsapp, 'To' => $to_whatsapp, 'Body' => $message_body]));
        $wa_response = curl_exec($ch);
        // Log the exact response from Twilio so you can inspect it in your XAMPP error logs
        error_log("Twilio WA Response: " . $wa_response);
        $debug_wa = $wa_response;
        curl_close($ch);

        // 2. Send via SMS
        $to_sms = $formatted_number;
        $ch_sms = curl_init();
        curl_setopt($ch_sms, CURLOPT_URL, "https://api.twilio.com/2010-04-01/Accounts/$twilio_sid/Messages.json");
        curl_setopt($ch_sms, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_sms, CURLOPT_POST, true);
        curl_setopt($ch_sms, CURLOPT_USERPWD, "$twilio_sid:$twilio_token");
        curl_setopt($ch_sms, CURLOPT_POSTFIELDS, http_build_query(['From' => $from_sms, 'To' => $to_sms, 'Body' => $message_body]));
        $sms_response = curl_exec($ch_sms);
        // Log the exact response from Twilio so you can inspect it in your XAMPP error logs
        error_log("Twilio SMS Response: " . $sms_response);
        $debug_sms = $sms_response;
        curl_close($ch_sms);

        // Temporarily output the exact Twilio response to the screen for debugging
        $_SESSION['error'] = "<strong>Twilio WA:</strong> $debug_wa <br><br><strong>Twilio SMS:</strong> $debug_sms";
        $_SESSION['success'] = 'A new OTP has been sent to your SMS and WhatsApp.';
        header('Location: verify_whatsapp.php');
        exit;
    }

    $otp = $_POST['otp'] ?? '';
    
    if ((string)$otp === (string)$pending['otp']) {
        
        // Final check to make sure someone else didn't take the email while they were waiting
        if ($pending['email'] !== null) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$pending['email']]);
            if ($stmt->fetch()) {
                unset($_SESSION['pending_registration']);
                $_SESSION['error'] = 'Email was registered by another user during verification. Please register again.';
                header('Location: register.php');
                exit;
            }
        }

        // Insert the verified user directly into the database
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, contact_number, email, password, status)
            VALUES (?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([$pending['first_name'], $pending['last_name'], $pending['contact_number'], $pending['email'], $pending['password']]);
        
        unset($_SESSION['pending_registration']);
        
        $_SESSION['success'] = 'Account verified successfully. You may now log in.';
        
        // Redirect to login page upon success
        header('Location: /security/login.php');
        exit;
    } else {
        $error = 'Invalid OTP. Please try again.';
    }
}

$page_title = 'Verify OTP';
include '../_head.php';
?>

<main>
    <h2>Verify Your Phone Number</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <?php echo success_message($_SESSION['success']);
        unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <?php echo error_message($error); ?>
    <?php endif; ?>

    <p style="margin-bottom: 20px;">We have sent a 6-digit OTP to your SMS and WhatsApp number: <strong><?php echo htmlspecialchars($contact_number); ?></strong>.</p>

    <form action="verify_whatsapp.php" method="post">
        <div style="margin-bottom: 24px;">
            <label for="otp">Enter OTP:</label>
            <?php echo input_field('text', 'otp', '', ['required' => 'required', 'pattern' => '[0-9]{6}', 'title' => '6-digit OTP', 'placeholder' => '123456']); ?>
        </div>

        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Verify Account</button>
    </form>

    <form action="verify_whatsapp.php" method="post" style="margin-top: 20px;">
        <input type="hidden" name="action" value="resend">
        <p>Didn't receive the code? 
            <button type="submit" id="resend-btn" style="background:none; border:none; color:#007bff; text-decoration:underline; cursor:pointer;" disabled>
                Resend OTP (<span id="timer">60</span>s)
            </button>
        </p>
    </form>
</main>

<script>
    let timeLeft = 60;
    const timerSpan = document.getElementById('timer');
    const resendBtn = document.getElementById('resend-btn');
    const countdown = setInterval(() => {
        timeLeft--;
        if (timeLeft <= 0) {
            clearInterval(countdown);
            resendBtn.innerHTML = 'Resend OTP';
            resendBtn.removeAttribute('disabled');
        } else {
            timerSpan.innerText = timeLeft;
        }
    }, 1000);
</script>

<?php
include '../_foot.php';
?>