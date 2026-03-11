<?php
// Define the predefined password
$predefined_password = 'admin123';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_password = $_POST['admin_password'];

    if ($admin_password == $predefined_password) {
        header('Location: results.php');
    } else {
        echo "Invalid password.";
    }
}
?>