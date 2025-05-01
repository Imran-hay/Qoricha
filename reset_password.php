<?php
session_start();
require 'config.php'; // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST['verification_code'];

    // Check if the entered code matches the stored code
    if ($entered_code === $_SESSION['verification_code']) {
        // Proceed to reset password
        echo '<form action="update_password.php" method="post">
                <input type="password" name="new_password" placeholder="Enter new password" required>
                <button type="submit">Reset Password</button>
              </form>';
    } else {
        echo "Invalid verification code.";
    }
}
?>