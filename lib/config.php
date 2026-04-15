<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'furniture');
define('DB_USER', 'root');
define('DB_PASS', '');

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'tanchiayen050607@gmail.com');
define('SMTP_PASSWORD', 'lasvmqqefujbshad');
define('SMTP_ENCRYPTION', 'tls'); 

// Email Settings
define('FROM_EMAIL', 'tanchiayen050607@gmail.com');
define('FROM_NAME', 'FurniHome');
define('REPLY_TO_EMAIL', 'support@yourdomain.com'); 


define('BASE_URL', 'http://localhost:8000');


define('EMAIL_DEBUG', false); // Set to true for debugging
define('EMAIL_DEBUG_LEVEL', 2); // 0 = off, 1 = client, 2 = client and server

// Twilio API Credentials
define('TWILIO_SID', 'AC95adf5e348d4bdb89818f500e5c785cb');
define('TWILIO_TOKEN', '79b41c1806d967f9cb3dfd276e12ed74');
define('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886');
define('TWILIO_SMS_FROM', '+14155238886');


?>