<?php
// Database connection
$conn = new mysqli('localhost', 'root', 'root@123', 'secure_elections');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$voter_id = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roll_no = $_POST['roll_no'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE roll_no = ?");
    $stmt->bind_param("s", $roll_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $voter_id = $row['vote_id'];
        } else {
            $message = "Invalid password.";
        }
    } else {
        $message = "No user found with this roll number.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Voter ID</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            position: relative;
            margin: 0;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1;
            position: relative;
            text-align: center;
        }

        h1 {
            font-size: 36px;
            text-align: center;
            margin-bottom: 20px;
        }

        form input[type="text"],
        form input[type="password"] {
            display: block;
            width: calc(100% - 20px);
            padding: 15px;
            margin: 10px auto;
            font-size: 18px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .buttons button {
            display: inline-block;
            width: calc(45% - 10px);
            padding: 10px;
            margin: 10px 5px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            color: #fff;
            background-color: #007bff;
            transition: background-color 0.3s ease;
            border-radius: 5px;
        }

        .buttons button:hover {
            background-color: #0056b3;
        }

        body::before {
            content: 'Forgot Voter ID';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            font-size: 200px;
            color: rgba(186, 128, 128, 0.05);
            z-index: 0;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Forgot Voter ID</h1>
        <?php if ($voter_id): ?>
            <p>Your Voter ID is: <?php echo htmlspecialchars($voter_id); ?></p>
        <?php else: ?>
            <form method="POST" action="">
                <input type="text" name="roll_no" placeholder="Roll No." required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Retrieve Voter ID</button>
            </form>
            <?php if ($message): ?>
                <p><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
        <?php endif; ?>
        <div class="buttons">
            <button onclick="location.href='login.html'">Login</button>
            <button onclick="location.href='index.html'">Go to Home Page</button>
        </div>
    </div>
</body>
</html>