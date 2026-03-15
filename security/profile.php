<?php

require '../_base.php';

auth();
$user_id = $_SESSION['user_id'];

// Fetch user early to get current photo and data
$stmt = $_db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 1. Fetch flash messages from session (PRG pattern)
$msg = temp('profile_msg') ?? "";
$error = temp('profile_error') ?? "";
$show_otp_form = $_SESSION['show_otp_form'] ?? false;

if (is_post()) {
    $action = post('action');

    if ($action === 'deactivate') {
        $deactivate_password = post('deactivate_password');
        if (sha1($deactivate_password) === $user->password) {
            $stmt = $_db->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$user_id]);
            setcookie('remember_token', '', time() - 3600, "/");
            session_destroy();
            redirect('login.php');
        } else {
            temp('profile_error', "Incorrect password. Account deactivation failed.");
            redirect('profile.php');
        }
    }

    if ($action === 'cancel_otp') {
        unset($_SESSION['profile_otp'], $_SESSION['profile_update_data'], $_SESSION['show_otp_form']);
        redirect('profile.php');
    }

    if ($action === 'update_profile') {
        $first_name = post('first_name');
        $last_name = post('last_name');
        $email = post('email');
        $dob = post('dob');
        $contact_number = post('contact_number');
        $old_pass = post('old_password');
        $new_pass = post('password');
        $confirm_pass = post('confirm_password');
        
        $email_changed = ($email !== $user->email);
        $password_changed = (!empty($new_pass) || !empty($old_pass) || !empty($confirm_pass));

        $photo = $user->photo; // Default to existing photo
        if (!empty($_POST['cropped_photo'])) {
            $imgData = $_POST['cropped_photo'];
            $imgArray = explode(',', $imgData);
            if (count($imgArray) == 2) {
                $base64 = $imgArray[1];
                $decoded = base64_decode($base64);
                if ($decoded !== false) {
                    if (!is_dir('../uploads')) mkdir('../uploads', 0777, true);
                    $filename = uniqid('profile_') . '.jpg';
                    if (file_put_contents('../uploads/' . $filename, $decoded)) {
                        // Delete the old photo from the server to save space
                        if ($user->photo && file_exists('../uploads/' . $user->photo)) {
                            unlink('../uploads/' . $user->photo);
                        }
                        $photo = $filename;
                    }
                }
            }
        }

        // Password change validation
        if ($password_changed) {
            if (empty($old_pass) || empty($new_pass) || empty($confirm_pass)) {
                temp('profile_error', "Please fill in all password fields to change your password.");
                redirect('profile.php');
            }
            if ($new_pass !== $confirm_pass) {
                temp('profile_error', "New passwords do not match.");
                redirect('profile.php');
            }
            if (sha1($old_pass) !== $user->password) {
                temp('profile_error', "Incorrect old password.");
                redirect('profile.php');
            }
        }

        // Validation for email duplication (id != ?)
        if ($email_changed) {
            $existing_email = db_fetch_single("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user_id]);
            if ($existing_email) {
                temp('profile_error', "Email is already in use by another account.");
                redirect('profile.php');
            }
        }

        if ($email_changed || $password_changed) {
            $_SESSION['profile_otp'] = rand(100000, 999999);
            $_SESSION['profile_update_data'] = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'dob' => $dob,
                'contact_number' => $contact_number,
                'password' => $new_pass ? sha1($new_pass) : null,
                'photo' => $photo
            ];
            $_SESSION['show_otp_form'] = true;
            temp('profile_msg', "OTP sent to your email! (Simulated: {$_SESSION['profile_otp']})");
            redirect('profile.php');
        } else {
            $sql = "UPDATE users SET first_name=?, last_name=?, email=?, dob=?, contact_number=?, photo=? WHERE id=?";
            $stmt = $_db->prepare($sql);
            $stmt->execute([$first_name, $last_name, $email, $dob, $contact_number, $photo, $user_id]);
            $_SESSION['name'] = $first_name;
            temp('profile_msg', "Profile updated successfully!");
            redirect('profile.php');
        }
    }

    if ($action === 'verify_otp') {
        if (post('otp') == ($_SESSION['profile_otp'] ?? null)) {
            $data = $_SESSION['profile_update_data'];
            if ($data['password']) {
                $sql = "UPDATE users SET first_name=?, last_name=?, email=?, dob=?, contact_number=?, password=?, photo=? WHERE id=?";
                $stmt = $_db->prepare($sql);
                $stmt->execute([$data['first_name'], $data['last_name'], $data['email'], $data['dob'], $data['contact_number'], $data['password'], $data['photo'], $user_id]);
            } else {
                $sql = "UPDATE users SET first_name=?, last_name=?, email=?, dob=?, contact_number=?, photo=? WHERE id=?";
                $stmt = $_db->prepare($sql);
                $stmt->execute([$data['first_name'], $data['last_name'], $data['email'], $data['dob'], $data['contact_number'], $data['photo'], $user_id]);
            }
            unset($_SESSION['profile_otp'], $_SESSION['profile_update_data'], $_SESSION['show_otp_form']);
            
            setcookie('remember_token', '', time() - 3600, "/");
            session_destroy();
            session_start();
            temp('login_error', "Account updated successfully. Please login again.");
            redirect('login.php');
        } else {
            temp('profile_error', "Invalid OTP.");
        }
        redirect('profile.php');
    }
}

// 4. UPDATED: Using object syntax ->
$backLink = ($user->role === 'Admin') ? 'admin.php' : 'member.php'; // I assumed member.php based on your previous code instead of index.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | FurniHome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
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

    <?php if ($show_otp_form): ?>
        <form method="POST">
            <input type="hidden" name="action" value="verify_otp">
            <label>Enter OTP sent to your email</label>
            <input type="text" name="otp" required placeholder="6-digit OTP">
            <button type="submit">Verify & Save</button>
        </form>
        <form method="POST" style="margin-top:10px;">
            <input type="hidden" name="action" value="cancel_otp">
            <button type="submit" style="background: #94a3b8;">Cancel Editing</button>
        </form>
    <?php else: ?>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_profile">
            
            <div style="text-align: center; margin-bottom: 15px;">
                <?php if ($user->photo): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($user->photo); ?>" id="profilePreview" alt="Profile Photo" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #ddd;">
                <?php else: ?>
                    <img src="" id="profilePreview" alt="Profile Photo" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; border: 2px solid #ddd; display: none;">
                    <div id="profilePlaceholder" style="width: 100px; height: 100px; border-radius: 50%; background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; font-size: 40px; color: #94a3b8; margin-bottom: 10px; border: 2px solid #ddd;">
                        <i class="fa-solid fa-user"></i>
                    </div>
                <?php endif; ?>
                <input type="file" id="photoInput" accept="image/*" style="display: block; margin: 0 auto; width: 100%; font-size: 13px;">
                <input type="hidden" name="cropped_photo" id="cropped_photo">
            </div>

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

            <label>Contact Number</label>
            <input type="tel" name="contact_number" value="<?php echo htmlspecialchars($user->contact_number ?? ''); ?>" pattern="^01[0-9]-[0-9]{7,8}$" placeholder="01X-XXXXXXX" title="Format: 01X-XXXXXXX (Malaysia)">

            <details style="margin: 15px 0; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #fafafa;">
                <summary style="cursor: pointer; font-weight: bold; color: #666;">Reset Password</summary>
                <div style="margin-top: 10px;">
                    <label>Old Password</label>
                    <input type="password" name="old_password" placeholder="Enter current password">
                    <div style="text-align: right; margin-top: -10px; margin-bottom: 10px;">
                        <a href="forgot_password.php" style="font-size: 13px; color: #f97316; text-decoration: none;">Forgot Password?</a>
                    </div>

                    <label>New Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="profilePass" placeholder="New password">
                        <i class="fa-solid fa-eye-slash" id="toggleProfilePass"></i>
                    </div>
                    <div id="strengthBar" style="height: 5px; width: 0%; background: red; transition: 0.3s; margin-top: 5px; margin-bottom: 10px; border-radius: 3px;"></div>

                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password">
                </div>
            </details>

            <button type="submit">Save Changes</button>
        </form>

        <form method="POST" onsubmit="return confirm('Are you sure you want to deactivate your account?');" style="margin-top: 30px; padding: 15px; border: 1px solid #fee2e2; border-radius: 4px; background: #fff5f5;">
            <input type="hidden" name="action" value="deactivate">
            <h4 style="color: #dc2626; margin-top: 0; margin-bottom: 5px;">Danger Zone: Deactivate Account</h4>
            <p style="font-size: 13px; color: #666; margin-bottom: 10px;">To confirm deactivation, please enter your password.</p>
            <input type="password" name="deactivate_password" placeholder="Enter your password to confirm" required style="margin-top: 0; margin-bottom: 10px;">
            <button type="submit" style="background: #dc2626;">Deactivate Account</button>
        </form>
    <?php endif; ?>
    
    <a href="<?php echo $backLink; ?>" class="back-btn">&larr; Back to Dashboard</a>
</div>

<!-- Cropper Modal -->
<div id="cropperModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:white; padding:20px; border-radius:8px; width:90%; max-width:500px; text-align:center;">
        <h3 style="margin-top:0;">Edit Photo</h3>
        <div style="max-height:400px; overflow:hidden; margin-bottom:10px;">
            <img id="imageToCrop" style="max-width: 100%; display:block;">
        </div>
        <div style="display:flex; justify-content:center; gap:10px; margin-bottom:15px;">
            <button type="button" id="btnRotateLeft" style="width:40px; padding:8px;"><i class="fa-solid fa-rotate-left"></i></button>
            <button type="button" id="btnRotateRight" style="width:40px; padding:8px;"><i class="fa-solid fa-rotate-right"></i></button>
            <button type="button" id="btnFlipX" style="width:40px; padding:8px;"><i class="fa-solid fa-arrows-left-right"></i></button>
            <button type="button" id="btnFlipY" style="width:40px; padding:8px;"><i class="fa-solid fa-arrows-up-down"></i></button>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" id="btnCancelCrop" style="background:#94a3b8; width:auto; padding:10px 20px;">Cancel</button>
            <button type="button" id="btnSaveCrop" style="background:#15803d; width:auto; padding:10px 20px;">Apply</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<script>
    const profilePass = document.querySelector('#profilePass');
    const toggleProfilePass = document.querySelector('#toggleProfilePass');

    if (toggleProfilePass) {
        toggleProfilePass.addEventListener('click', function (e) {
            const type = profilePass.getAttribute('type') === 'password' ? 'text' : 'password';
            profilePass.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
            this.classList.toggle('fa-eye');
        });
    }

    if (profilePass) {
        profilePass.addEventListener('input', function() {
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

    const photoInput = document.getElementById('photoInput');
    const cropperModal = document.getElementById('cropperModal');
    const imageToCrop = document.getElementById('imageToCrop');
    const profilePreview = document.getElementById('profilePreview');
    const profilePlaceholder = document.getElementById('profilePlaceholder');
    const croppedPhotoInput = document.getElementById('cropped_photo');
    let cropper;

    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const files = e.target.files;
            if (files && files.length > 0) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    imageToCrop.src = event.target.result;
                    cropperModal.style.display = 'flex';
                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(imageToCrop, {
                        aspectRatio: 1, // Enforces a square crop
                        viewMode: 1,
                    });
                };
                reader.readAsDataURL(files[0]);
            }
        });

        document.getElementById('btnCancelCrop').addEventListener('click', function() {
            cropperModal.style.display = 'none';
            photoInput.value = ''; 
        });

        document.getElementById('btnSaveCrop').addEventListener('click', function() {
            const canvas = cropper.getCroppedCanvas({ width: 200, height: 200 });
            const base64Image = canvas.toDataURL('image/jpeg', 0.9);
            
            if (profilePreview) { profilePreview.src = base64Image; profilePreview.style.display = 'inline-block'; }
            if (profilePlaceholder) { profilePlaceholder.style.display = 'none'; }
            
            croppedPhotoInput.value = base64Image;
            cropperModal.style.display = 'none';
        });

        let flipX = 1, flipY = 1;

        document.getElementById('btnRotateLeft').addEventListener('click', () => cropper.rotate(-90));
        document.getElementById('btnRotateRight').addEventListener('click', () => cropper.rotate(90));
        document.getElementById('btnFlipX').addEventListener('click', () => { flipX = -flipX; cropper.scaleX(flipX); });
        document.getElementById('btnFlipY').addEventListener('click', () => { flipY = -flipY; cropper.scaleY(flipY); });
    }
</script>

</body>
</html>