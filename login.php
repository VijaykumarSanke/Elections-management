<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'secure_elections');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vote_id = $_POST['vote_id'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE vote_id = ?");
    $stmt->bind_param("s", $vote_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id']; // Ensure user_id is set correctly
            header('Location: election.html');
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with this voter ID.";
    }

    $stmt->close();
    $conn->close();
}
?>