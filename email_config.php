<?php
// Email configuration for OTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'vnr.contacts7@gmail.com');
define('SMTP_PASSWORD', 'aobbatsmhjoiilyi');  // Remove spaces from app password
define('SMTP_FROM_EMAIL', 'vnr.contacts7@gmail.com');
define('SMTP_FROM_NAME', 'Campus Democracy - VNRVJIET');

// OTP settings
define('OTP_EXPIRY_MINUTES', 10); // OTP validity in minutes
define('OTP_LENGTH', 6);          // OTP length
?>