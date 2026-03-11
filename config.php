<?php
// Set timezone (adjust to your timezone)
date_default_timezone_set('Asia/Kolkata'); // For India

// Database connection
function getDatabaseConnection() {
    $conn = new mysqli('localhost', 'root', '', 'secure_elections');
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set MySQL timezone
    $conn->query("SET time_zone = '+05:30'"); // India timezone
    
    return $conn;
}
?>