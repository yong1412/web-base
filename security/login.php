<?php
require './db.php';
require './_base.php';
$max_attempts = 3;
$lockout_minutes = 3;

// 1. Fetch flash messages from session
$error = temp('login_error') ?? $_SESSION['login_error'] ?? null;
$success = temp('success') ?? $_SESSION['success'] ?? null;
$lockout_remaining_seconds = $_SESSION['lockout_time'] ?? 0;
$saved_login = $_SESSION['saved_login'] ?? '';

// 2. Clear session variables immediately so they only show once
unset($_SESSION['login_error']);
unset($_SESSION['success']);
unset($_SESSION['lockout_time']);
unset($_SESSION['saved_login']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'];
    
    // Save login id to repopulate the input field
    $_SESSION['saved_login'] = $login_id;

    // Validate if the input is a valid phone number or email format
    if (strpos($login_id, '@') === false) {
        if (!preg_match('/^[0-9]{3}-[0-9]{7,8}$/', $login_id)) {
            $_SESSION['login_error'] = "Phone number must be in the format XXX-XXXXXXX (e.g., 014-2461428).";
            redirect($_SERVER['PHP_SELF']);
        }
    } elseif (!filter_var($login_id, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = "Invalid email format.";
        redirect($_SERVER['PHP_SELF']);
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR contact_number = ?");
    $stmt->execute([$login_id, $login_id]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    if ($user) {
        if ($user->status === 'inactive' || $user->status === 'blocked') {
            $_SESSION['login_error'] = "Account is inactive or blocked.";
            redirect($_SERVER['PHP_SELF']);
        }

        $now = time();
        $is_locked_out = false;
        
        // Display the email or fallback to the contact number if email is empty
        $display_id = htmlspecialchars($user->email ?? $user->contact_number);

        // Check if user is currently locked out
        if (!empty($user->lockout_until)) {
            $lockout_time = strtotime($user->lockout_until);

            if ($now < $lockout_time) {
                $is_locked_out = true;
                $_SESSION['lockout_time'] = $lockout_time - $now;
                $_SESSION['login_error'] = "Account ($display_id) locked. Please wait <span id='countdown'></span>.";
            } else {
                $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, lockout_until = NULL WHERE id = ?");
                $stmt->execute([$user->id]);
                $user->login_attempts = 0;
            }
        }

        // Process login if NOT locked out
        if (!$is_locked_out) {
            if (sha1($password) === $user->password) {
                $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, lockout_until = NULL WHERE id = ?");
                $stmt->execute([$user->id]);

                if (isset($_POST['remember'])) {
                    $token = bin2hex(random_bytes(16));
                    setcookie('remember_token', $token, time() + (86400 * 30), "/");
                    $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmt->execute([$token, $user->id]);
                }

                $_SESSION['user_id'] = $user->id;
                $_SESSION['role'] = $user->role;
                $_SESSION['name'] = $user->first_name;
                $_SESSION['first_name'] = $user->first_name;
                $_SESSION['last_name'] = $user->last_name;
                $_SESSION['photo'] = $user->photo;

                if ($user->role === 'Admin' || $user->role === 'Member') {
                    redirect("/page/member/dashboard.php");
                } else {
                    redirect("/index.php");
                }
            } else {
                $attempts = $user->login_attempts + 1;

                if ($attempts >= $max_attempts) {
                    $lockout_seconds = $lockout_minutes * 60;
                    $lockout_date = date('Y-m-d H:i:s', time() + $lockout_seconds);

                    $stmt = $pdo->prepare("UPDATE users SET login_attempts = ?, lockout_until = ? WHERE id = ?");
                    $stmt->execute([$attempts, $lockout_date, $user->id]);

                    $_SESSION['lockout_time'] = $lockout_seconds;
                    $_SESSION['login_error'] = "3 failed attempts. Account ($display_id) locked for <span id='countdown'></span>.";
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET login_attempts = ? WHERE id = ?");
                    $stmt->execute([$attempts, $user->id]);
                    $remaining = $max_attempts - $attempts;
                    $_SESSION['login_error'] = "Invalid password. You have $remaining attempt(s) left.";
                }
            }
        }
    } else {
        $_SESSION['login_error'] = "Invalid email/phone or password.";
    }

    // 3. Redirect back to this exact page to clear the POST request
    redirect($_SERVER['PHP_SELF']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login | FurniHome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f3f4f6; }
        .login-box { background: white; padding: 40px; border-radius: 8px; width: 300px; text-align: center; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #f97316; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; transition: background 0.3s; }
        .password-container { position: relative; width: 100%; }
        .password-container i { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; }
        .error-msg { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 4px; font-size: 14px; margin-bottom: 15px; border: 1px solid #f87171; }
        .success-msg { background: #dcfce7; color: #16a34a; padding: 10px; border-radius: 4px; font-size: 14px; margin-bottom: 15px; border: 1px solid #86efac; }
        #countdown { font-weight: bold; }
    </style>
</head>

<body>
    <div class="login-box">
        <h2>FurniHome Login</h2>

        <?php if ($error): ?>
            <div class="error-msg" id="errorBox"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-msg"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <input type="text" name="login_id" id="loginIdInput" placeholder="Email or Phone Number" required
                value="<?php echo htmlspecialchars($saved_login); ?>">

            <div class="password-container">
                <input type="password" name="password" id="loginPass" placeholder="Password" required>
                <i class="fa-solid fa-eye-slash" id="toggleLoginPass"></i>
            </div>

            <div style="text-align: left; margin: 10px 0; font-size: 14px;">
                <label>
                    <input type="checkbox" name="remember" style="width: auto; margin: 0;"> Remember Me
                </label>
                <a href="_forgot_password.php" style="float: right; color: #f97316; text-decoration: none;">Forgot Password?</a>
            </div>

            <button type="submit" id="submitBtn">Login</button>
        </form>
        <br>
        <a href="../page/auth/register.php" style="color: #666; font-size: 13px;">Register</a><br/>
        <a href="../index.php" style="color: #666; font-size: 13px;">Back to Shop</a>
    </div>

    <script>
        const toggleLoginPass = document.querySelector('#toggleLoginPass');
        const password = document.querySelector('#loginPass');

        toggleLoginPass.addEventListener('click', function(e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });

        // Countdown Timer Logic
        let remainingSeconds = <?php echo (int)$lockout_remaining_seconds; ?>;

        if (remainingSeconds > 0) {
            const countdownElement = document.getElementById('countdown');
            const errorBox = document.getElementById('errorBox');

            const timer = setInterval(() => {
                let minutes = Math.floor(remainingSeconds / 60);
                let seconds = remainingSeconds % 60;

                seconds = seconds < 10 ? '0' + seconds : seconds;

                if (countdownElement) {
                    countdownElement.textContent = minutes + ":" + seconds;
                }

                remainingSeconds--;

                if (remainingSeconds < 0) {
                    clearInterval(timer);
                    if (errorBox) {
                        errorBox.style.display = 'none';
                    }
                }
            }, 1000);
        }
    </script>
</body>

</html>