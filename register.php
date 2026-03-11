<?php
// Database connection with error handling
$conn = new mysqli('localhost', 'root', '', 'secure_elections');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variable
$message = "";

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
        
        // Validate roll number format - NEW FORMAT
        // Pattern: (22|23|24|25) + (071A|075A|etc.) + exactly 4 digits
        $roll_no_pattern = '/^(22|23|24|25)\d{3}[A-Z]\d{4}$/';

        if (!preg_match($roll_no_pattern, $roll_no)) {
            $message = "Invalid roll number format.";
        } else {

            // ✅ Fetch student data from database using prepared statement
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

                // ✅ VALIDATE ALL FIELDS - Must match EXACTLY with database
                $errors = [];

                // Check Name
                if ($name !== $db_name) {
                    $errors[] = "Name does not match our records.";
                }

                // Check Roll Number (should already match, but double-check)
                if ($roll_no !== $db_roll_no) {
                    $errors[] = "Roll number does not match our records.";
                }

                // Check Email
                if ($email !== $db_email) {
                    $errors[] = "Email does not match our records.";
                }

                // Check EduPrime Password
                if ($eduprime_password !== $db_eduprime_password) {
                    $errors[] = "Incorrect EduPrime password.";
                }

                // If ANY field doesn't match, show error
                if (count($errors) > 0) {
                    $message = implode(" ", $errors);
                } else {
                    
                    // ✅ All fields matched! Check if already registered
                    $stmt2 = $conn->prepare("SELECT * FROM users WHERE roll_no = ? LIMIT 1");
                    $stmt2->bind_param("s", $roll_no);
                    $stmt2->execute();
                    $userResult = $stmt2->get_result();

                    if ($userResult->num_rows > 0) {
                        $message = "This roll number is already registered.";
                    } else {
                        
                        // ✅ Generate unique vote ID
                        $vote_id = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
                        
                        // Insert new user with prepared statement
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                          $stmt3 = $conn->prepare("INSERT INTO users (name, roll_no, email, vote_id, password) VALUES (?, ?, ?, ?, ?)");
                         $stmt3->bind_param("sssss", $db_name, $db_roll_no, $db_email, $vote_id, $hashedPassword);


                        if ($stmt3->execute()) {
                            // ✅ Registration successful - redirect to confirmation page
                            header("Location: registration_confirmation.php?vote_id=$vote_id");
                            exit();
                        } else {
                            $message = "Registration failed. Please try again.";
                            // Log the actual error for debugging (don't show to user)
                            error_log("Database Error: " . $stmt3->error);
                        }
                        $stmt3->close();
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
            font-family: Arial, sans-serif;
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

        .large-text {
            font-size: 24px;
            margin-bottom: 20px;
            max-width: 600px;
        }

        .error {
            color: #d32f2f;
        }

        .home-button {
            font-size: 18px;
            padding: 10px 20px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            margin-top: 10px;
        }

        .home-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (!empty($message)) : ?>
            <p class="large-text error"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <button class="home-button" onclick="goToHomePage()">Go to Home Page</button>
    </div>

    <script>
        function goToHomePage() {
            window.location.href = 'index.html';
        }
    </script>
</body>
</html>