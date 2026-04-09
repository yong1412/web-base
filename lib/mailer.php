<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/PHPMailer/src/SMTP.php';


function send_email($to, $subject, $message, $altMessage = '') { // Sends an email using SMTP with PHPMailer, including subject, message, and optional plain text version.
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = EMAIL_DEBUG ? EMAIL_DEBUG_LEVEL : SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;

        // Set encryption based on config
        if (SMTP_ENCRYPTION === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif (SMTP_ENCRYPTION === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        }

        $mail->Port       = SMTP_PORT;

        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);

        if (defined('REPLY_TO_EMAIL') && REPLY_TO_EMAIL) {
            $mail->addReplyTo(REPLY_TO_EMAIL, FROM_NAME);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        if (!empty($altMessage)) {
            $mail->AltBody = $altMessage;
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}


function send_verification_email($email, $token) { // Sends a verification email with a link for users to confirm their email address.
    $subject = 'Email Verification - FurniHome';

    $message = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Email Verification</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
            .button:hover { background: #218838; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Welcome to FurniHome!</h1>
        </div>
        <div class='content'>
            <h2>Email Verification Required</h2>
            <p>Thank you for registering with FurniHome! To complete your registration and activate your account, please verify your email address by clicking the button below:</p>

            <div style='text-align: center;'>
                <a href='" . BASE_URL . "/page/auth/verify.php?token=$token' class='button'>Verify My Email Address</a>
            </div>

            <p><strong>Verification Link:</strong><br>
            <a href='" . BASE_URL . "/page/auth/verify.php?token=$token'>" . BASE_URL . "/page/auth/verify.php?token=$token</a></p>

            <p><strong>Important:</strong> This link will expire in 24 hours for security reasons.</p>

            <p>If you did not create an account with FurniHome, please ignore this email. Your email address will not be used.</p>

            <div class='footer'>
                <p>This is an automated message from FurniHome. Please do not reply to this email.</p>
                <p>&copy; 2026 FurniHome. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $altMessage = "Welcome to FurniHome!\n\nPlease verify your email address by clicking this link:\n" . BASE_URL . "/page/auth/verify.php?token=$token\n\nIf you did not register, please ignore this email.";

    return send_email($email, $subject, $message, $altMessage);
}
?>