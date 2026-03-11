<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'secure_elections');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user_id is set in session
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $category1 = isset($_POST['category1']) ? $_POST['category1'] : '';
        $category2 = isset($_POST['category2']) ? $_POST['category2'] : '';
        $category3 = isset($_POST['category3']) ? $_POST['category3'] : '';

        // Ensure all categories are selected
        if (!empty($category1) && !empty($category2) && !empty($category3)) {
            // Check if the user has already voted using prepared statement
            $check_stmt = $conn->prepare("SELECT * FROM votes WHERE user_id = ?");
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                // User has already voted
                $message = "You have already voted. Multiple voting is not allowed.";
            } else {
                // Insert the votes using prepared statement
                $stmt = $conn->prepare("INSERT INTO votes (user_id, category, candidate) VALUES (?, ?, ?)");
                
                // Insert first vote
                $cat1 = 'Sports Incharge';
                $stmt->bind_param("iss", $user_id, $cat1, $category1);
                $stmt->execute();
                
                // Insert second vote
                $cat2 = 'Co-Curricular Activities Incharge';
                $stmt->bind_param("iss", $user_id, $cat2, $category2);
                $stmt->execute();
                
                // Insert third vote
                $cat3 = 'General Activity Incharge';
                $stmt->bind_param("iss", $user_id, $cat3, $category3);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    // Update the users table to set has_voted flag using prepared statement
                    $update_stmt = $conn->prepare("UPDATE users SET has_voted = TRUE WHERE id = ?");
                    $update_stmt->bind_param("i", $user_id);
                    $update_stmt->execute();
                    $update_stmt->close();

                    $message = "Thank you for voting!";
                } else {
                    $message = "Error: Unable to record your vote.";
                }
                
                $stmt->close();
            }
            $check_stmt->close();
        } else {
            $message = "Error: Please select a candidate for all categories.";
        }
    } else {
        $message = "Error: User ID is not set in the session.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote</title>
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
        .message {
            font-size: 20px;
            margin-top: 20px;
            color: #333;
        }
        .home-button {
            display: block;
            margin: 20px 0;
            padding: 15px 30px;
            font-size: 18px;
            cursor: pointer;
            border: none;
            color: #fff;
            background-color: #007bff; /* Blue */
            transition: background-color 0.3s ease;
        }
        .home-button:hover {
            background-color: #0056b3; /* Darker blue */
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($message != ''): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <button class="home-button" onclick="location.href='index.html'">Go to Home Page</button>
        <?php else: ?>
            <form id="voteForm" action="vote.php" method="post">
                <div class="category" id="category1">
                    <h2>Sports Incharge</h2>
                    <label><input type="radio" name="category1" value="Akhil" required> Akhil</label>
                    <label><input type="radio" name="category1" value="Jashuva"> Jashuva</label>
                    <label><input type="radio" name="category1" value="Rithwik"> Rithwik</label>
                    <button type="button" onclick="nextCategory(1)">Next</button>
                </div>

                <div class="category" id="category2" style="display: none;">
                    <h2>Co-Curricular Activities Incharge</h2>
                    <label><input type="radio" name="category2" value="Anjani" required> Anjani</label>
                    <label><input type="radio" name="category2" value="Roshitha"> Roshitha</label>
                    <label><input type="radio" name="category2" value="Keerthi Sree"> Keerthi Sree</label>
                    <button type="button" onclick="nextCategory(2)">Next</button>
                </div>

                <div class="category" id="category3" style="display: none;">
                    <h2>General Activity Incharge</h2>
                    <label><input type="radio" name="category3" value="Shiva Mani" required> Shiva Mani</label>
                    <label><input type="radio" name="category3" value="Aksh"> Aksh</label>
                    <label><input type="radio" name="category3" value="Vijay Kumar"> Vijay Kumar</label>
                    <button type="submit" class="submit-button">Submit Vote</button>
                </div>
            </form>
            <button class="home-button" onclick="location.href='index.html'">Go to Home Page</button>
        <?php endif; ?>
    </div>

    <script>
        function nextCategory(currentCategory) {
            document.getElementById('category' + currentCategory).style.display = 'none';
            document.getElementById('category' + (currentCategory + 1)).style.display = 'block';
        }
    </script>
</body>
</html>