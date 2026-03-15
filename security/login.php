<?php
require '_database.php';
require '../_base.php';
$max_attempts = 3;
$lockout_minutes = 3;

// 1. Fetch flash messages from session
$error = $_SESSION['login_error'] ?? null;
$lockout_remaining_seconds = $_SESSION['lockout_time'] ?? 0;
$saved_email = $_SESSION['saved_email'] ?? '';

// 2. Clear session variables immediately so they only show once
unset($_SESSION['login_error']);
unset($_SESSION['lockout_time']);
unset($_SESSION['saved_email']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Save email to repopulate the input field
    $_SESSION['saved_email'] = $email;

    // UPDATED: Using $_db instead of $pdo
    $stmt = $_db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $now = time();
        $is_locked_out = false;
        
        // UPDATED: Using object syntax ($user->email) instead of array syntax ($user['email'])
        $display_email = htmlspecialchars($user->email);

        // Check if user is currently locked out
        if (!empty($user->lockout_until)) {
            $lockout_time = strtotime($user->lockout_until);

            if ($now < $lockout_time) {
                $is_locked_out = true;
                $_SESSION['lockout_time'] = $lockout_time - $now;
                $_SESSION['login_error'] = "Account ($display_email) locked. Please wait <span id='countdown'></span>.";
            } else {
                $stmt = $_db->prepare("UPDATE users SET login_attempts = 0, lockout_until = NULL WHERE id = ?");
                $stmt->execute([$user->id]);
                $user->login_attempts = 0;
            }
        }

        // Process login if NOT locked out
        if (!$is_locked_out) {
            // Note: Still using plain text password comparison here. 
            if ($password === $user->password) {
                $stmt = $_db->prepare("UPDATE users SET login_attempts = 0, lockout_until = NULL WHERE id = ?");
                $stmt->execute([$user->id]);

                $_SESSION['user_id'] = $user->id;
                $_SESSION['role'] = $user->role;
                $_SESSION['name'] = $user->first_name;

                if ($user->role === 'Admin') {
                    header("Location: admin.php"); // use redirect()
                } else {
                    header("Location: ../index.php"); // use redirect()
                }
                exit;
            } else {
                $attempts = $user->login_attempts + 1;

                if ($attempts >= $max_attempts) {
                    $lockout_seconds = $lockout_minutes * 60;
                    $lockout_date = date('Y-m-d H:i:s', time() + $lockout_seconds);

                    $stmt = $_db->prepare("UPDATE users SET login_attempts = ?, lockout_until = ? WHERE id = ?");
                    $stmt->execute([$attempts, $lockout_date, $user->id]);

                    $_SESSION['lockout_time'] = $lockout_seconds;
                    $_SESSION['login_error'] = "3 failed attempts. Account ($display_email) locked for <span id='countdown'></span>.";
                } else {
                    $stmt = $_db->prepare("UPDATE users SET login_attempts = ? WHERE id = ?");
                    $stmt->execute([$attempts, $user->id]);
                    $remaining = $max_attempts - $attempts;
                    $_SESSION['login_error'] = "Invalid password. You have $remaining attempt(s) left.";
                }
            }
        }
    } else {
        $_SESSION['login_error'] = "Invalid email or password.";
    }

    // 3. Redirect back to this exact page to clear the POST request
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
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
        #countdown { font-weight: bold; }
    </style>
</head>

<body>
    <div class="login-box">
        <h2>FurniHome Login</h2>

        <?php if ($error): ?>
            <div class="error-msg" id="errorBox"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <input type="email" name="email" id="emailInput" placeholder="Email" required
                value="<?php echo htmlspecialchars($saved_email); ?>">

            <div class="password-container">
                <input type="password" name="password" id="loginPass" placeholder="Password" required>
                <i class="fa-solid fa-eye-slash" id="toggleLoginPass"></i>
            </div>

            <button type="submit" id="submitBtn">Login</button>
        </form>
        <br>
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
