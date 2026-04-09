<?php

require_once '../../lib/base.php';

require_login();

// Only process the form if it was submitted via POST; otherwise, redirect back to the edit page.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: edit_profile.php');
    exit;
}

// Retrieve the current user’s ID from the session and get all the form inputs, trimming spaces where needed.
$user_id = $_SESSION['user_id'];

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$dob = $_POST['dob'] ?? null;
$dob = !empty($dob) ? $dob : null;
$contact_number = trim($_POST['contact_number'] ?? '');
$email = trim($_POST['email'] ?? '') ?: null; // Set to null if empty
$photo_data = $_POST['photo_data'] ?? '';

// Validation
$errors = [];

if (empty($first_name)) {
    $errors[] = 'First name is required.';
}

if (empty($last_name)) {
    $errors[] = 'Last name is required.';
}

if (!empty($contact_number) && !preg_match('/^[0-9]{3}-[0-9]{7,8}$/', $contact_number)) {
    $errors[] = 'Phone number must be in the format XXX-XXXXXXX (e.g., 014-2461428).';
}

if ($email !== null) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already taken by another user.';
        }
    }
}

if (!empty($new_password)) {
    if (empty($old_password)) {
        $errors[] = 'Old password is required to change your password.';
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $current_pass = $stmt->fetchColumn();
        if (sha1($old_password) !== $current_pass) {
            $errors[] = 'Incorrect old password.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        }
    }
}

if (!empty($dob)) {
    $birthDate = DateTime::createFromFormat('Y-m-d', $dob);
    $today = new DateTime();

    if (!$birthDate) {
        $errors[] = 'Invalid date of birth format.';
    } elseif ($birthDate > $today) {
        $errors[] = 'Date of birth cannot be in the future.';
    } else {
        $age = $today->diff($birthDate)->y;
        if ($age < 13) {
            $errors[] = 'You must be at least 13 years old.';
        }
    }
}

// If there are any validation errors, save them in the session and redirect back to the edit page.
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: edit_profile.php');
    exit;
}

$photo_filename = null;
if (!empty($photo_data)) {
    // Decode base64, get type, save file
    $photo_data = str_replace('data:image/', '', $photo_data);
    list($type, $data) = explode(';', $photo_data);
    list(, $data) = explode(',', $data);
    $data = base64_decode($data);

    $photo_filename = 'profile_' . $user_id . '_' . time() . '.' . $type;
    $filepath = '../../uploads/profiles/' . $photo_filename;

    // Save file
    if (!file_put_contents($filepath, $data)) {
        $_SESSION['error'] = 'Failed to upload photo.';
        header('Location: edit_profile.php');
        exit;
    }
}

// Build a list of database columns to update and their corresponding values, including password and photo if provided.
$update_fields = ['first_name = ?', 'last_name = ?', 'contact_number = ?', 'dob = ?', 'email = ?'];
$update_values = [$first_name, $last_name, $contact_number, $dob, $email];

// Check if email changed to reset verification status
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user = $stmt->fetch();
if ($email !== $current_user['email']) {
    $update_fields[] = 'email_verified = 0';
}

if (!empty($new_password)) {
    $update_fields[] = 'password = ?';
    $update_values[] = sha1($new_password);
}

if ($photo_filename) {
    $update_fields[] = 'photo = ?';
    $update_values[] = $photo_filename;
}

$update_values[] = $user_id;

// Update the user’s record in the database and check if it was successful.
$query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
$stmt = $pdo->prepare($query);
if ($stmt->execute($update_values)) { // If the update succeeds, show a success message and update session info; otherwise, show an error message.
    $_SESSION['success'] = 'Profile updated successfully.';
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
    if ($photo_filename) {
        $_SESSION['photo'] = $photo_filename;
    }

    if (!empty($new_password)) {
        $notification_email = $current_user['email'] ?: $email;
        if (!empty($notification_email)) {
            require_once '../../lib/config.php';
            require_once '../../lib/mailer.php';
            $emergency_token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$emergency_token, $user_id]);
            $reset_link = BASE_URL . '/security/emergencyPassword.php?token=' . $emergency_token;
            $subject = "Security Alert: Your Password Was Changed";
            $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                <h2 style='color: #f97316;'>Password Changed</h2>
                <p>Your FurniHome account password was recently changed.</p>
                <p>If you made this change, you can safely ignore this email.</p>
                <p><strong>Not you?</strong> If you didn't change your password, please secure your account immediately.</p>
                <a href='{$reset_link}' style='display: inline-block; margin-top: 15px; padding: 10px 20px; background: #dc2626; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;'>Not you? Secure Account</a>
            </div>
            ";
            $altMessage = "Your password was changed. If this wasn't you, reset your password immediately at {$reset_link}";
            send_email($notification_email, $subject, $message, $altMessage);
        }
    }

    if (isset($_POST['verify_email']) && !empty($email)) {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare("UPDATE users SET email_token = ? WHERE id = ?");
        $stmt->execute([$token, $user_id]);
        if (send_verification_email($email, $token)) {
            $_SESSION['success'] .= ' Verification email sent to ' . htmlspecialchars($email) . '. Please check your inbox.';
        } else {
            $_SESSION['error'] = 'Profile updated, but failed to send verification email.';
        }
    }
} else {
    $_SESSION['error'] = 'Failed to update profile.';
}

header('Location: profile.php');
exit;
?>