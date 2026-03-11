<?php
require_once 'email_config.php';
require 'vendor/autoload.php'; // PHPMailer autoload (Composer)

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set timezone
date_default_timezone_set('Asia/Kolkata');

/**
 * Generate a random OTP
 */
function generateOTP($length = OTP_LENGTH) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

/**
 * Store OTP in database
 */
function storeOTP($conn, $email, $otp) {
    // Delete any existing OTPs for this email
    $delete_stmt = $conn->prepare("DELETE FROM otp_verifications WHERE email = ?");
    $delete_stmt->bind_param("s", $email);
    $delete_stmt->execute();
    $delete_stmt->close();
    
    // Calculate expiry time
    $expires_at = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
    
    // Insert new OTP
    $stmt = $conn->prepare("INSERT INTO otp_verifications (email, otp, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $otp, $expires_at);
    $result = $stmt->execute();
    
    // Debug logging
    if ($result) {
        error_log("OTP stored successfully - Email: $email, OTP: $otp, Expires: $expires_at");
    } else {
        error_log("Failed to store OTP - Error: " . $stmt->error);
    }
    
    $stmt->close();
    return $result;
}

/**
 * Verify OTP
 */
function verifyOTP($conn, $email, $otp) {
    // First, let's check what we have in database
    $debug_stmt = $conn->prepare("SELECT email, otp, expires_at, verified, NOW() as db_current_time FROM otp_verifications WHERE email = ?");
    $debug_stmt->bind_param("s", $email);
    $debug_stmt->execute();
    $debug_result = $debug_stmt->get_result();
    
    if ($debug_result->num_rows > 0) {
        $debug_data = $debug_result->fetch_assoc();
        error_log("Debug - Email: {$debug_data['email']}, Stored OTP: {$debug_data['otp']}, Input OTP: $otp");
        error_log("Debug - Expires: {$debug_data['expires_at']}, Current: {$debug_data['db_current_time']}, Verified: {$debug_data['verified']}");
    } else {
        error_log("Debug - No OTP found for email: $email");
    }
    $debug_stmt->close();
    
    // Now verify
    $stmt = $conn->prepare("SELECT * FROM otp_verifications WHERE email = ? AND otp = ? AND expires_at > NOW() AND verified = FALSE");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Mark OTP as verified
        $update_stmt = $conn->prepare("UPDATE otp_verifications SET verified = TRUE WHERE email = ? AND otp = ?");
        $update_stmt->bind_param("ss", $email, $otp);
        $update_stmt->execute();
        $update_stmt->close();
        $stmt->close();
        error_log("OTP verified successfully for email: $email");
        return true;
    }
    
    $stmt->close();
    error_log("OTP verification failed for email: $email, OTP: $otp");
    return false;
}

/**
 * Send OTP via email
 */
function sendOTPEmail($email, $otp, $name) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification - Campus Democracy';
        $mail->Body    = getOTPEmailTemplate($otp, $name);
        $mail->AltBody = "Your OTP for registration is: $otp\n\nThis OTP is valid for " . OTP_EXPIRY_MINUTES . " minutes.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Get HTML email template for OTP
 */
function getOTPEmailTemplate($otp, $name) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 28px; }
            .content { padding: 40px 30px; }
            .otp-box { background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); border: 2px dashed #667eea; border-radius: 10px; padding: 30px; text-align: center; margin: 30px 0; }
            .otp-code { font-size: 36px; font-weight: bold; color: #667eea; letter-spacing: 8px; margin: 10px 0; }
            .expiry { color: #e74c3c; font-size: 14px; margin-top: 15px; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 12px; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; color: #856404; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🗳️ Email Verification</h1>
            </div>
            <div class="content">
                <p>Dear ' . htmlspecialchars($name) . ',</p>
                <p>Thank you for registering for Campus Democracy at VNRVJIET. To complete your registration, please verify your email address.</p>
                
                <div class="otp-box">
                    <p style="margin: 0; font-size: 16px; color: #666;">Your verification code is:</p>
                    <div class="otp-code">' . htmlspecialchars($otp) . '</div>
                    <p class="expiry">⏰ This code expires in ' . OTP_EXPIRY_MINUTES . ' minutes</p>
                </div>
                
                <div class="warning">
                    <strong>⚠️ Security Notice:</strong> Never share this code with anyone. Our staff will never ask for this code.
                </div>
                
                <p>If you did not request this verification, please ignore this email.</p>
            </div>
            <div class="footer">
                <p>© 2024 VNRVJIET Campus Democracy. All rights reserved.</p>
                <p>This is an automated email. Please do not reply.</p>
            </div>
        </div>
    </body>
    </html>
    ';
}

/**
 * Clean up expired OTPs (call this periodically)
 */
function cleanupExpiredOTPs($conn) {
    $stmt = $conn->prepare("DELETE FROM otp_verifications WHERE expires_at < NOW()");
    $stmt->execute();
    $stmt->close();
}
?>