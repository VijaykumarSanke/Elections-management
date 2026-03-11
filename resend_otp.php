<?php
session_start();
require_once 'otp_functions.php';

// Database connection
$conn = new mysqli('localhost', 'root', '', 'secure_elections');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    // Check if registration data exists in session
    if (!isset($_SESSION['registration_data'])) {
        $message = "Session expired. Please register again.";
    } else {
        $registrationData = $_SESSION['registration_data'];
        
        // Check if enough time has passed since last OTP (prevent spam)
        $timeSinceLastOTP = time() - ($registrationData['otp_sent_time'] ?? 0);
        
        if ($timeSinceLastOTP < 60) {
            $waitTime = 60 - $timeSinceLastOTP;
            $message = "Please wait $waitTime seconds before requesting a new OTP.";
        } else {
            $name = $registrationData['name'];
            
            // Generate and send new OTP
            $otp = generateOTP();
            
            if (storeOTP($conn, $email, $otp)) {
                if (sendOTPEmail($email, $otp, $name)) {
                    // Update OTP sent time
                    $_SESSION['registration_data']['otp_sent_time'] = time();
                    $success = true;
                    $message = "New OTP sent successfully!";
                } else {
                    $message = "Failed to send OTP email. Please try again.";
                    error_log("OTP Resend failed for: " . $email);
                }
            } else {
                $message = "Failed to generate OTP. Please try again.";
            }
        }
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend OTP</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            text-align: center;
            padding: 20px;
        }

        .message-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
        }

        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .message {
            font-size: 18px;
            margin: 20px 0;
            line-height: 1.6;
        }

        .success {
            color: #27ae60;
        }

        .error {
            color: #d32f2f;
        }

        .button {
            font-size: 16px;
            padding: 12px 30px;
            cursor: pointer;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }
    </style>
    <?php if ($success): ?>
    <script>
        setTimeout(function() {
            window.location.href = 'verify_otp.html?email=<?php echo urlencode($_POST['email']); ?>';
        }, 2000);
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <div class="message-box">
            <?php if ($success): ?>
                <div class="icon">📧</div>
                <p class="message success"><?php echo htmlspecialchars($message); ?></p>
                <p>Redirecting back...</p>
            <?php else: ?>
                <div class="icon">❌</div>
                <p class="message error"><?php echo htmlspecialchars($message); ?></p>
                <button class="button" onclick="window.history.back()">Go Back</button>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>