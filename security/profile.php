<?php
session_start();
require '_database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Fetch flash messages from session (PRG pattern)
$msg = $_SESSION['profile_msg'] ?? "";
$error = $_SESSION['profile_error'] ?? "";
unset($_SESSION['profile_msg'], $_SESSION['profile_error']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $dob = $_POST['dob'];
    $new_pass = $_POST['password'];

    try {
        // 2. UPDATED: Using $_db and combining queries for efficiency
        if (!empty($new_pass)) {
            $sql = "UPDATE users SET first_name=?, last_name=?, email=?, dob=?, password=? WHERE id=?";
            $stmt = $_db->prepare($sql);
            $stmt->execute([$first_name, $last_name, $email, $dob, $new_pass, $user_id]);
            $_SESSION['profile_msg'] = "Profile and password updated successfully!";
        } else {
            $sql = "UPDATE users SET first_name=?, last_name=?, email=?, dob=? WHERE id=?";
            $stmt = $_db->prepare($sql);
            $stmt->execute([$first_name, $last_name, $email, $dob, $user_id]);
            $_SESSION['profile_msg'] = "Profile updated successfully!";
        }
        
        // Update session name so the dashboard greets them correctly
        $_SESSION['name'] = $first_name; 
        
    } catch (PDOException $e) {
        $_SESSION['profile_error'] = "Error updating profile. Email might already be in use.";
    }

    // Redirect to clear the POST request
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 3. UPDATED: Using $_db
$stmt = $_db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 4. UPDATED: Using object syntax ->
$backLink = ($user->role === 'Admin') ? 'admin.php' : 'member.php'; // I assumed member.php based on your previous code instead of index.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | FurniHome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: sans-serif; background: #f3f4f6; display: flex; justify-content: center; padding-top: 50px; }
        .card { background: white; padding: 30px; border-radius: 10px; width: 400px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        label { font-size: 14px; font-weight: bold; color: #333; display: block; margin-top: 10px; }
        input { width: 100%; padding: 10px; margin: 5px 0 15px 0; border: 1px solid #ddd; box-sizing: border-box; border-radius: 4px;}
        button { width: 100%; padding: 12px; background: #f97316; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold; margin-top: 10px;}
        .back-btn { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; font-size: 14px;}
        
        .success-msg { color: #15803d; background: #dcfce7; padding: 10px; border-radius: 4px; font-size: 14px; margin-bottom: 15px; text-align: center;}
        .error-msg { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 4px; font-size: 14px; margin-bottom: 15px; text-align: center;}
        
        .password-container { position: relative; width: 100%; }
        .password-container i {
            position: absolute;
            right: 10px;
            top: 50%; 
            transform: translateY(-80%); 
            cursor: pointer;
            color: #666;
        }
        
        .name-row { display: flex; gap: 10px; }
        .name-row div { width: 100%; }
    </style>
</head>
<body>

<div class="card">
    <h2 style="text-align: center; margin-top: 0;">Edit Profile</h2>
    
    <?php if($msg): ?>
        <div class="success-msg"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="name-row">
            <div>
                <label>First Name</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user->first_name); ?>" required>
            </div>
            <div>
                <label>Last Name</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user->last_name); ?>" required>
            </div>
        </div>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user->email); ?>" required>

        <label>Date of Birth</label>
        <input type="date" name="dob" value="<?php echo htmlspecialchars($user->dob); ?>">

        <label>New Password (Optional)</label>
        <div class="password-container">
            <input type="password" name="password" id="profilePass" placeholder="Leave blank to keep current">
            <i class="fa-solid fa-eye-slash" id="toggleProfilePass"></i>
        </div>

        <button type="submit">Save Changes</button>
    </form>
    
    <a href="<?php echo $backLink; ?>" class="back-btn">&larr; Back to Dashboard</a>
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