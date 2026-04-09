# Email Configuration Guide

This guide explains how to set up email sending functionality using PHPMailer in your FurniHome.

## 🚀 Quick Setup

### 1. Configure Email Settings

Edit `lib/config.php` and update the following constants:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'tanchiayen050607@gmail.com'); 
define('SMTP_PASSWORD', 'lasvmqqefujbshad'); 
define('SMTP_ENCRYPTION', 'tls');
define('FROM_EMAIL', 'tanchiayen050607@gmail.com');
define('FROM_NAME', 'FurniHome');
define('BASE_URL', 'http://localhost:8000'); 
```

### 2. Gmail Setup (Recommended for Development)

#### Enable 2-Factor Authentication
1. Go to your Google Account settings
2. Navigate to **Security** > **2-Step Verification**
3. Enable 2-Step Verification if not already enabled

#### Generate App Password
1. In Google Account settings, go to **Security**
2. Under "Signing in to Google", click **App passwords**
3. Select **Mail** and **Other (custom name)**
4. Enter "FurniHome" as the custom name
5. Copy the 16-character password
6. Use this password as `SMTP_PASSWORD` in your config

### 3. Test Email Functionality

Run the test script to verify everything works:

```bash
cd /path/to/your/project/app
php test_email.php
```

Update the `$testEmail` variable in `test_email.php` with your actual email address first.

## 📧 Supported Email Providers

### Gmail (Development)
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
```

### Outlook/Hotmail
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
```

### Yahoo Mail
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls');
```

### Production Services

#### SendGrid
```php
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'apikey');
define('SMTP_PASSWORD', 'your-sendgrid-api-key');
```

#### Mailgun
```php
define('SMTP_HOST', 'smtp.mailgun.org');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-mailgun-smtp-username');
define('SMTP_PASSWORD', 'your-mailgun-smtp-password');
```

## 🔧 Configuration Options

| Constant | Description | Example |
|----------|-------------|---------|
| `SMTP_HOST` | SMTP server hostname | `smtp.gmail.com` |
| `SMTP_PORT` | SMTP server port | `587` (TLS), `465` (SSL) |
| `SMTP_USERNAME` | SMTP authentication username | `your-email@gmail.com` |
| `SMTP_PASSWORD` | SMTP authentication password | `your-app-password` |
| `SMTP_ENCRYPTION` | Encryption type: `'tls'` or `'ssl'` | `'tls'` |
| `FROM_EMAIL` | Sender email address | `noreply@yourdomain.com` |
| `FROM_NAME` | Sender display name | `FurniHome` |
| `REPLY_TO_EMAIL` | Reply-to email address | `support@yourdomain.com` |
| `BASE_URL` | Application base URL | `https://yourdomain.com` |
| `EMAIL_DEBUG` | Enable debug output | `false` (production) |

## 🛠️ Troubleshooting

### Common Issues

#### 1. "SMTP Error: Could not authenticate"
- Verify your SMTP credentials
- For Gmail, ensure you're using an App Password, not your regular password
- Check if 2FA is enabled on your Google account

#### 2. "Connection failed"
- Verify SMTP host and port settings
- Check if your firewall is blocking SMTP ports
- Try different ports (587 for TLS, 465 for SSL)

#### 3. "Email not received"
- Check spam/junk folder
- Verify the recipient email address
- Test with a different email provider

#### 4. "SSL certificate problem"
- Some SMTP servers have certificate issues
- Set `SMTP_ENCRYPTION` to `'tls'` instead of `'ssl'`
- Or disable SSL verification (not recommended for production)

### Debug Mode

Enable debug mode to see detailed SMTP communication:

```php
define('EMAIL_DEBUG', true);
define('EMAIL_DEBUG_LEVEL', 2); // 1 = client, 2 = client and server
```

## 🔒 Security Best Practices

1. **Never commit credentials** to version control
2. **Use environment variables** in production:
   ```php
   define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));
   ```
3. **Use OAuth2** for Gmail in production instead of app passwords
4. **Enable SPF/DKIM/DMARC** records for your domain
5. **Monitor email sending** for abuse
6. **Use dedicated SMTP services** for high-volume sending

## 📝 Usage Examples

### Send Custom Email
```php
require_once 'lib/mailer.php';

$message = "<h1>Hello!</h1><p>This is a test email.</p>";
$altMessage = "Hello! This is a test email.";

if (send_email('user@example.com', 'Test Subject', $message, $altMessage)) {
    echo "Email sent successfully!";
} else {
    echo "Email sending failed.";
}
```

### Send Verification Email
```php
require_once 'lib/mailer.php';

$token = generate_verification_token(); // Your token generation logic

if (send_verification_email('user@example.com', $token)) {
    echo "Verification email sent!";
} else {
    echo "Failed to send verification email.";
}
```

## 📞 Support

If you encounter issues:
1. Check the PHP error logs
2. Enable debug mode and review SMTP output
3. Test with different email providers
4. Verify your server can connect to external SMTP ports

For PHPMailer-specific issues, refer to the [PHPMailer documentation](https://github.com/PHPMailer/PHPMailer).