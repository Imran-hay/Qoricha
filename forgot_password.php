<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('images/background.jpg'); /* Background image */
            background-size: cover; /* Cover the entire background */
            background-position: center; /* Center the image */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full height */
        }

        .container {
            background: rgba(255, 255, 255, 0.9); /* Slightly transparent white */
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 400px; /* Max width */
            width: 100%; /* Full width on mobile */
            text-align: center; /* Center text */
        }

        h2 {
            margin-bottom: 20px; /* Spacing below the title */
            color: #333; /* Dark text color */
        }

        input[type="email"] {
            width: 100%; /* Full width */
            padding: 10px;
            margin: 10px 0; /* Margin between inputs */
            border: 1px solid #ccc; /* Light border */
            border-radius: 5px; /* Rounded corners */
            font-size: 16px; /* Font size */
        }

        button {
            width: 100%; /* Full width */
            padding: 10px;
            background-color: #0a888f; /* Teal color */
            color: white; /* White text */
            border: none; /* No border */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor */
            font-size: 16px; /* Font size */
            margin-top: 10px; /* Spacing above button */
        }

        button:hover {
            background-color: rgb(4, 70, 73); /* Darker teal on hover */
        }

        p {
            margin-top: 15px; /* Spacing above paragraph */
            color: #666; /* Gray text */
        }

        a {
            color: #0a888f; /* Teal color for links */
            text-decoration: none; /* No underline */
        }

        a:hover {
            text-decoration: underline; /* Underline on hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <form action="send_verification.php" method="post">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Send Verification Code</button>
        </form>
        <p><a href="login.php">Back to Login</a></p>
    </div>
</body>
</html>