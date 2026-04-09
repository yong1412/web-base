<?php
// Process member registration

require_once '../../lib/base.php';

// if the form was submitted via POST; otherwise, go back to the registration page.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$errors = [];
// Take the form inputs, clean them to remove unwanted characters, and store them in variables.
$first_name = sanitize_input($_POST['first_name'] ?? '');
$last_name = sanitize_input($_POST['last_name'] ?? '');
$email = sanitize_input($_POST['email'] ?? '') ?: null; // Set to null if empty
$contact_number = sanitize_input($_POST['contact_number'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Check that all fields are filled and the email is in the correct format
$errors[] = validate_required($first_name, 'First name');
$errors[] = validate_required($last_name, 'Last name');
$errors[] = validate_required($contact_number, 'Phone number');
if (!empty($contact_number)) {
    $errors[] = validate_phone_number($contact_number, 'Phone number');
}
if ($email !== null) {
    $errors[] = validate_email($email);
}
$errors[] = validate_required($password, 'Password');
if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match.';
}
$errors = array_filter($errors); // Remove empty

// If there are errors, save them to the session and send the user back to the registration page.
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: register.php');
    exit;
}

// See if the email is already in the database; if yes, show an error and stop registration.
if ($email !== null) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Email already registered.';
        header('Location: register.php');
        exit;
    }
}

// Generate a 6-digit OTP for WhatsApp verification
$whatsapp_otp = rand(100000, 999999);

//$password = password_hash($password, PASSWORD_DEFAULT);
$password = sha1($password);

// Save registration data temporarily in session
$_SESSION['pending_registration'] = [
    'first_name' => $first_name,
    'last_name' => $last_name,
    'contact_number' => $contact_number,
    'email' => $email,
    'password' => $password,
    'otp' => $whatsapp_otp
];

// --- WhatsApp API Integration (Example using Twilio) ---
// You will need to sign up for Twilio to get your SID, Auth Token, and a WhatsApp-enabled number.
$twilio_sid = 'AC95adf5e348d4bdb89818f500e5c785cb';
$twilio_token = 'b722b86653b81ae909a38b753af0063f';
$from_whatsapp = 'whatsapp:+14155238886'; // Replace with your Twilio WhatsApp number
$from_sms = '+14155238886'; // Replace with your Twilio SMS number

// Format the phone number (e.g., formatting '014-2461428' to '+60142461428' for Malaysia)
$formatted_number = '+60' . ltrim(str_replace('-', '', $contact_number), '0');
$message_body = "Your FurniHome verification code is: $whatsapp_otp";

// 1. Send via WhatsApp
$to_whatsapp = 'whatsapp:' . $formatted_number;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.twilio.com/2010-04-01/Accounts/$twilio_sid/Messages.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_USERPWD, "$twilio_sid:$twilio_token");
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'From' => $from_whatsapp,
    'To' => $to_whatsapp,
    'Body' => $message_body
]));
curl_exec($ch);
curl_close($ch);

// 2. Send via SMS
$to_sms = $formatted_number;

$ch_sms = curl_init();
curl_setopt($ch_sms, CURLOPT_URL, "https://api.twilio.com/2010-04-01/Accounts/$twilio_sid/Messages.json");
curl_setopt($ch_sms, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_sms, CURLOPT_POST, true);
curl_setopt($ch_sms, CURLOPT_USERPWD, "$twilio_sid:$twilio_token");
curl_setopt($ch_sms, CURLOPT_POSTFIELDS, http_build_query([
    'From' => $from_sms,
    'To' => $to_sms,
    'Body' => $message_body
]));
curl_exec($ch_sms);
curl_close($ch_sms);
// --------------------------------------------------------

$_SESSION['success'] = 'An OTP has been sent to your SMS and WhatsApp. Please verify your account.';

header('Location: verify_whatsapp.php');
exit;
?>