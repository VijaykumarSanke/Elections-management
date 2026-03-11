<?php
require_once 'email_config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress('your-test-email@gmail.com'); // ← Change this to YOUR email
    
    $mail->isHTML(true);
    $mail->Subject = 'PHPMailer Test - Campus Democracy';
    $mail->Body    = '<h1>Success! ✅</h1><p>Your email configuration is working correctly.</p>';
    
    $mail->send();
    echo '<h2 style="color: green;">✅ Test email sent successfully!</h2>';
    echo '<p>Check your inbox (and spam folder) for the test email.</p>';
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Email failed</h2>";
    echo "<p>Error: {$mail->ErrorInfo}</p>";
}
?>