<?php
session_start();
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session
header("Location: login.php"); // Redirect to the login page
exit();
?>
<style> .logout {
    color: #ff4d4d; /* Red color for logout */
    font-weight: bold;
}
.logout:hover {
    text-decoration: underline; /* Underline on hover */
}</style>