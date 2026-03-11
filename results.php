<?php
// Database connection
$conn = new mysqli('localhost', 'root', 'root@123', 'secure_elections');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define categories and candidates
$categories = [
    'Sports Incharge' => ['Akhil', 'Jashuva', 'Rithwik'],
    'Co-Curricular Activities Incharge' => ['Anjani', 'Roshitha', 'Keerthi Sree'],
    'General Activity Incharge' => ['Shiva Mani', 'Aksh', 'Vijay Kumar'],
];

echo "<div class='container'><h1>Results</h1>";

foreach ($categories as $category => $candidates) {
    echo "<h2>$category</h2>";

    $votes = [];
    foreach ($candidates as $candidate) {
        // Get the vote count for each candidate
        $sql = "SELECT COUNT(*) as votes FROM votes WHERE category='$category' AND candidate='$candidate'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $votes[$candidate] = $row['votes'];
    }

    // Display vote counts and find the leading candidates
    $max_votes = max($votes);
    $leading_candidates = array_keys($votes, $max_votes);

    foreach ($votes as $candidate => $vote_count) {
        echo "$candidate: $vote_count votes<br>";
    }

    if (!empty($leading_candidates)) {
        echo "<strong>Leading: " . implode(", ", $leading_candidates) . " with " . $max_votes . " votes</strong><br>";
    }
}

echo "<button onclick=\"location.href='index.html'\">Go to Home Page</button></div>";

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('images/result.png') no-repeat center center fixed; /* Add your background image */
            background-size: cover; /* Ensure the background image covers the entire page */
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center; /* Center the container horizontally */
            align-items: center; /* Center the container vertically */
        }
        .container {
            width: 100%;
            max-width: 600px; /* Adjust the width to make it more compact */
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent background */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center; /* Center-align text inside container */
        }
        h1 {
            font-size: 36px; /* Increased font size */
            margin-bottom: 20px;
        }
        h2 {
            font-size: 24px; /* Increased font size for category headings */
            margin-bottom: 15px;
        }
        .container p, .container button {
            font-size: 20px; /* Increased font size */
        }
        .container strong {
            font-weight: bold;
        }
        button {
            display: block;
            width: 100%;
            max-width: 200px;
            padding: 15px;
            margin: 20px auto;
            font-size: 18px; /* Increased font size */
            cursor: pointer;
            border: none;
            color: #fff;
            background-color: #007bff; /* Blue */
            transition: background-color 0.3s ease;
            border-radius: 5px;
        }
        button:hover {
            background-color: #0056b3; /* Darker blue */
        }
    </style>
</head>
<body>
</body>
</html>