<?php
// Email configuration for OTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your mail');
define('SMTP_PASSWORD', 'your app password');  // Remove spaces from app password
define('SMTP_FROM_EMAIL', 'your mail');
define('SMTP_FROM_NAME', 'Campus Democracy - VNRVJIET');

// OTP settings
define('OTP_EXPIRY_MINUTES', 10); // OTP validity in minutes
define('OTP_LENGTH', 6);          // OTP length
?>
