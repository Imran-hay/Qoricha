<?php
session_start();
require 'config.php'; // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT); // Hash the new password
    $email = $_SESSION['email'];

    // Update the password in the database
    $stmt = $pdo->prepare("UPDATE employees SET password = ? WHERE email = ?");
    if ($stmt->execute([$new_password, $email])) {
        echo "Password has been reset successfully.";
        // Clear the session variables
        unset($_SESSION['verification_code']);
        unset($_SESSION['email']);
    } else {
        echo "Failed to reset password. Please try again.";
    }
}
?>