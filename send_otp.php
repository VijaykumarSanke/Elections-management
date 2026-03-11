<?php
session_start();
require_once 'otp_functions.php';

// Database connection
$conn = new mysqli('localhost', 'root', '', 'secure_elections');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$redirect = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $name = trim($_POST['name'] ?? '');
    $roll_no = trim($_POST['roll_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $eduprime_password = trim($_POST['eduprime_password'] ?? '');

    // Check for empty fields
    if (empty($name) || empty($roll_no) || empty($email) || empty($password) || empty($eduprime_password)) {
        $message = "All fields are required.";
    } else {
        
        // Validate roll number format
        $roll_no_pattern = '/^(22|23|24|25)\d{3}[A-Z]\d{4}$/';

        if (!preg_match($roll_no_pattern, $roll_no)) {
            $message = "Invalid roll number format.";
        } else {

            // Fetch student data from database
            $stmt = $conn->prepare("SELECT name, roll_no, email, eduprime_password FROM students WHERE roll_no = ? LIMIT 1");
            $stmt->bind_param("s", $roll_no);
            $stmt->execute();
            $studentResult = $stmt->get_result();

            if ($studentResult->num_rows == 0) {
                $message = "Roll number not found. You are not eligible to register.";
            } else {
                
                $studentData = $studentResult->fetch_assoc();
                
                // Extract database values
                $db_name = trim($studentData['name']);
                $db_roll_no = trim($studentData['roll_no']);
                $db_email = trim($studentData['email']);
                $db_eduprime_password = trim($studentData['eduprime_password']);

                // Validate all fields
                $errors = [];

                if ($name !== $db_name) {
                    $errors[] = "Name does not match our records.";
                }

                if ($roll_no !== $db_roll_no) {
                    $errors[] = "Roll number does not match our records.";
                }

                if ($email !== $db_email) {
                    $errors[] = "Email does not match our records.";
                }

                if ($eduprime_password !== $db_eduprime_password) {
                    $errors[] = "Incorrect EduPrime password.";
                }

                if (count($errors) > 0) {
                    $message = implode(" ", $errors);
                } else {
                    
                    // Check if already registered
                    $stmt2 = $conn->prepare("SELECT * FROM users WHERE roll_no = ? LIMIT 1");
                    $stmt2->bind_param("s", $roll_no);
                    $stmt2->execute();
                    $userResult = $stmt2->get_result();

                    if ($userResult->num_rows > 0) {
                        $message = "This roll number is already registered.";
                    } else {
                        
                        // Generate and send OTP
                        $otp = generateOTP();
                        
                        if (storeOTP($conn, $email, $otp)) {
                            if (sendOTPEmail($email, $otp, $name)) {
                                // Store registration data in session
                                $_SESSION['registration_data'] = [
                                    'name' => $db_name,
                                    'roll_no' => $db_roll_no,
                                    'email' => $db_email,
                                    'password' => password_hash($password, PASSWORD_DEFAULT),
                                    'otp_sent_time' => time()
                                ];
                                
                                $redirect = "verify_otp.html?email=" . urlencode($email);
                            } else {
                                $message = "Failed to send OTP email. Please check your email configuration.";
                                error_log("OTP Email failed for: " . $email);
                            }
                        } else {
                            $message = "Failed to generate OTP. Please try again.";
                        }
                    }
                    $stmt2->close();
                }
            }
            $stmt->close();
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
    <title>Registration Status</title>
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

        .large-text {
            font-size: 20px;
            margin-bottom: 20px;
            max-width: 600px;
            line-height: 1.6;
        }

        .error {
            color: #d32f2f;
        }

        .success {
            color: #27ae60;
        }

        .home-button {
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

        .home-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
    </style>
    <?php if ($redirect): ?>
    <script>
        setTimeout(function() {
            window.location.href = '<?php echo $redirect; ?>';
        }, 2000);
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <div class="message-box">
            <?php if ($redirect): ?>
                <div class="icon">📧</div>
                <p class="large-text success">OTP sent successfully! Redirecting to verification page...</p>
            <?php elseif (!empty($message)): ?>
                <div class="icon">❌</div>
                <p class="large-text error"><?php echo htmlspecialchars($message); ?></p>
                <button class="home-button" onclick="window.history.back()">Go Back</button>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>