<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code</title>
</head>
<body>
    <h2>Verify Code</h2>
    <form action="reset_password.php" method="post">
        <input type="text" name="verification_code" placeholder="Enter your verification code" required>
        <button type="submit">Verify Code</button>
    </form>
</body>
</html>