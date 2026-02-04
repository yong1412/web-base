<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];
    $new_pass = $_POST['password'];

    $sql = "UPDATE users SET full_name=?, email=?, dob=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$full_name, $email, $dob, $user_id]);

    if (!empty($new_pass)) {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->execute([$hashed, $user_id]);
        $msg = "Password updated!";
    } else {
        $msg = "Profile updated!";
    }
    $_SESSION['name'] = $full_name;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$backLink = ($user['role'] === 'Admin') ? 'admin.php' : 'index.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; background: #f3f4f6; display: flex; justify-content: center; padding-top: 50px; }
        .card { background: white; padding: 30px; border-radius: 10px; width: 400px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 5px 0 15px 0; border: 1px solid #ddd; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #f97316; color: white; border: none; cursor: pointer; }
        .back-btn { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; }
        
        .password-container { position: relative; width: 100%; }
        .password-container i {
            position: absolute;
            right: 10px;
            top: 40%; /* Adjusted slightly for alignment */
            cursor: pointer;
            color: #666;
        }
    </style>
</head>
<body>

<div class="card">
    <h2 style="text-align: center;">Edit Profile</h2>
    <?php if($msg) echo "<p style='color:green; text-align:center;'>$msg</p>"; ?>

    <form method="POST">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>Date of Birth</label>
        <input type="date" name="dob" value="<?php echo $user['dob']; ?>">

        <label>New Password (Optional)</label>
        <div class="password-container">
            <input type="password" name="password" id="profilePass" placeholder="********">
            <i class="fa-solid fa-eye" id="toggleProfilePass"></i>
        </div>

        <button type="submit">Save Changes</button>
    </form>
    
    <a href="<?php echo $backLink; ?>" class="back-btn">&larr; Back to Main Page</a>
</div>

<script>
    const toggleProfilePass = document.querySelector('#toggleProfilePass');
    const profilePass = document.querySelector('#profilePass');

    toggleProfilePass.addEventListener('click', function (e) {
        const type = profilePass.getAttribute('type') === 'password' ? 'text' : 'password';
        profilePass.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
        this.classList.toggle('fa-eye');
    });
</script>

</body>
</html>