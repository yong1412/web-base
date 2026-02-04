<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['full_name'];

        if ($user['role'] === 'Admin') {
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
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
        .login-box { background: white; padding: 40px; border-radius: 8px; width: 300px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #f97316; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        
        /* Wrapper for Password Input to position the eye icon */
        .password-container { position: relative; width: 100%; }
        .password-container i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>FurniHome Login</h2>
        <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            
            <div class="password-container">
                <input type="password" name="password" id="loginPass" placeholder="Password" required>
                <i class="fa-solid fa-eye" id="toggleLoginPass"></i>
            </div>

            <button type="submit">Login</button>
        </form>
        <br>
        <a href="index.php" style="color: #666; font-size: 13px;">Back to Shop</a>
    </div>

    <script>
        const toggleLoginPass = document.querySelector('#toggleLoginPass');
        const password = document.querySelector('#loginPass');

        toggleLoginPass.addEventListener('click', function (e) {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle the eye / eye-slash icon
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });
    </script>
</body>
</html>