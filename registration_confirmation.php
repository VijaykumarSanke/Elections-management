<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmation</title>
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

        .voter-id {
            font-size: 20px;
            color: green;
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
        <p class="large-text">Registration Successful!</p>
        <p class="voter-id">Your Voter ID is: <?php echo htmlspecialchars($_GET['vote_id']); ?></p>
        <button class="home-button" onclick="goToHomePage()">Go to Home Page</button>
    </div>

    <script>
        function goToHomePage() {
            window.location.href = 'index.html';
        }
    </script>
</body>
</html>