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
$vote_id = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    
    // Check if registration data exists in session
    if (!isset($_SESSION['registration_data'])) {
        $message = "Session expired. Please register again.";
    } elseif (empty($otp) || strlen($otp) != 6) {
        $message = "Invalid OTP format.";
    } else {
        $registrationData = $_SESSION['registration_data'];
        $email = $registrationData['email'];
        
        // Verify OTP
        if (verifyOTP($conn, $email, $otp)) {
            // OTP is valid, proceed with registration
            $name = $registrationData['name'];
            $roll_no = $registrationData['roll_no'];
            $hashedPassword = $registrationData['password'];
            
            // Generate unique vote ID
            $vote_id = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            
            // Insert user into database
            $stmt = $conn->prepare("INSERT INTO users (name, roll_no, email, vote_id, password, email_verified) VALUES (?, ?, ?, ?, ?, TRUE)");
            $stmt->bind_param("sssss", $name, $roll_no, $email, $vote_id, $hashedPassword);
            
            if ($stmt->execute()) {
                $success = true;
                
                // Clear session data
                unset($_SESSION['registration_data']);
                
                // Clean up verified OTPs
                cleanupExpiredOTPs($conn);
            } else {
                $message = "Registration failed. Please try again.";
                error_log("Database Error: " . $stmt->error);
            }
            
            $stmt->close();
        } else {
            $message = "Invalid or expired OTP. Please check and try again.";
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
    <title>OTP Verification Result</title>
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
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon {
            font-size: 70px;
            margin-bottom: 20px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        h1 {
            font-size: 28px;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .message {
            font-size: 18px;
            margin: 20px 0;
            line-height: 1.6;
            color: #666;
        }

        .voter-id-box {
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.1) 0%, rgba(34, 153, 84, 0.1) 100%);
            border: 2px solid #27ae60;
            padding: 20px;
            border-radius: 15px;
            margin: 25px 0;
        }

        .voter-id-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .voter-id {
            font-size: 32px;
            font-weight: bold;
            color: #27ae60;
            letter-spacing: 3px;
            font-family: 'Courier New', monospace;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: left;
        }

        .warning-box p {
            margin: 0;
            color: #856404;
            font-size: 14px;
            line-height: 1.6;
        }

        .button {
            font-size: 16px;
            padding: 14px 30px;
            cursor: pointer;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            margin: 10px 5px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        .button-secondary {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .button-secondary:hover {
            background: #667eea;
            color: white;
        }

        .error {
            color: #d32f2f;
        }

        .success {
            color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="message-box">
            <?php if ($success): ?>
                <div class="icon">✅</div>
                <h1>Registration Successful!</h1>
                <p class="message">Your email has been verified and your account has been created.</p>
                
                <div class="voter-id-box">
                    <div class="voter-id-label">Your Voter ID</div>
                    <div class="voter-id"><?php echo htmlspecialchars($vote_id); ?></div>
                </div>
                
                <div class="warning-box">
                    <p><strong>⚠️ Important:</strong> Please save your Voter ID. You will need it to login and cast your vote.</p>
                </div>
                
                <button class="button" onclick="location.href='login.html'">Proceed to Login</button>
                <button class="button button-secondary" onclick="location.href='index.html'">Go to Home</button>
                
            <?php else: ?>
                <div class="icon">❌</div>
                <h1>Verification Failed</h1>
                <p class="message error"><?php echo htmlspecialchars($message); ?></p>
                
                <button class="button" onclick="window.history.back()">Try Again</button>
                <button class="button button-secondary" onclick="location.href='register.html'">Start Over</button>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>