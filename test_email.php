<?php

require_once 'lib/base.php';
require_once 'lib/mailer.php';

// Test email sending
$testEmail = 'your-test-email@example.com'; 
$testToken = 'test-token-123';

echo "Testing email functionality...\n<br/>";

if (send_verification_email($testEmail, $testToken)) {
    echo "✅ Email sent successfully!\n<br/>";
    echo "Check your inbox at: $testEmail\n<br/><br/>";
} else {
    echo "❌ Email sending failed. Check the error logs.\n<br/><br/>";
}

echo "\nEmail configuration:\n<br/>";
echo "SMTP Host: " . SMTP_HOST . "\n<br/>";
echo "SMTP Port: " . SMTP_PORT . "\n<br/>";
echo "From Email: " . FROM_EMAIL . "\n<br/>";
echo "From Name: " . FROM_NAME . "\n<br/>";
echo "Base URL: " . BASE_URL . "\n<br/>";
?>