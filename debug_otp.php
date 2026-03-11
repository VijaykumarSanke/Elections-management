<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

$conn = new mysqli('localhost', 'root', '', 'secure_elections');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set MySQL timezone
$conn->query("SET time_zone = '+05:30'");

echo "<h2>OTP Debug Information</h2>";
echo "<p><strong>Current PHP Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Get current database time
$time_result = $conn->query("SELECT NOW() as db_time");
$time_row = $time_result->fetch_assoc();
echo "<p><strong>Current Database Time:</strong> " . $time_row['db_time'] . "</p>";

echo "<hr>";

// Show all OTPs
echo "<h3>All OTPs in Database:</h3>";
$result = $conn->query("SELECT * FROM otp_verifications ORDER BY created_at DESC LIMIT 10");

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Email</th><th>OTP</th><th>Created At</th><th>Expires At</th><th>Verified</th><th>Status</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        $is_expired = (strtotime($row['expires_at']) < time()) ? 'EXPIRED' : 'VALID';
        $is_verified = $row['verified'] ? 'YES' : 'NO';
        
        $status_color = ($is_expired == 'EXPIRED') ? 'red' : 'green';
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td><strong>{$row['otp']}</strong></td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>{$row['expires_at']}</td>";
        echo "<td>{$is_verified}</td>";
        echo "<td style='color: $status_color;'><strong>{$is_expired}</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No OTPs found in database.</p>";
}

echo "<hr>";

// Show session data
echo "<h3>Session Data:</h3>";
if (isset($_SESSION['registration_data'])) {
    echo "<pre>";
    print_r($_SESSION['registration_data']);
    echo "</pre>";
} else {
    echo "<p>No registration data in session.</p>";
}

$conn->close();
?>