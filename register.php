<?php
// Database connection
$conn = new mysqli('localhost', 'root', 'root@123', 'secure_elections');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $roll_no = $_POST['roll_no'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $vote_id = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT); // Generate a random 5-digit number

    // Validate roll number format
    $roll_no_pattern = '/^(22|23|24)004-(cs|ee|ec|me|ce)-[0-1][0-9]{2}$/';
    if (!preg_match($roll_no_pattern, $roll_no)) {
        $message = "Invalid roll number format.";
    } else {
        // Check if the roll number is in the eligible students table
        $checkStudentSql = "SELECT * FROM students WHERE roll_no = '$roll_no'";
        $result = $conn->query($checkStudentSql);

        if ($result->num_rows > 0) {
            // Check if the roll number is already registered
            $checkUserSql = "SELECT * FROM users WHERE roll_no = '$roll_no'";
            $result = $conn->query($checkUserSql);

            if ($result->num_rows > 0) {
                $message = "This roll number already exists.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (name, roll_no, email, vote_id, password) VALUES ('$name', '$roll_no', '$email', '$vote_id', '$hashedPassword')";

                if ($conn->query($sql) === TRUE) {
                    header("Location: registration_confirmation.php?vote_id=$vote_id");
                    exit();
                } else {
                    $message = "Error: " . $sql . "<br>" . $conn->error;
                }
            }
        } else {
            $message = "You are not eligible to register.";
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
        }

        .large-text {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .home-button {
            font-size: 18px;
            padding: 10px 20px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
        }

        .home-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($message)) : ?>
            <p class="large-text"><?php echo $message; ?></p>
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