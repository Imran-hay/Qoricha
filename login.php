<?php
session_start();
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url('images/background.jpg'); /* Full background image */
            background-size: cover; /* Cover the entire background */
            background-position: center; /* Center the image */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full height */
        }

        .login-container {
            display: flex;
            flex-direction: column; /* Vertical layout */
            max-width: 400px; /* Max width for the form */
            background: rgba(255, 255, 255, 0.9); /* Slightly transparent white */
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            padding: 40px; /* Padding for the form area */
            align-items: center; /* Center the contents */
        }

        .logo {
            display: block;
            margin: 0 auto 20px auto; /* Center the logo */
            width: 150px; /* Adjust size as needed */
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px; /* Spacing below the title */
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #0a888f; /* Teal color */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px; /* Spacing above button */
        }

        button:hover {
            background-color: rgb(4, 70, 73);
        }

        p {
            text-align: center;
            margin-top: 15px;
        }

        a {
            color: #0a888f;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="images/Qoricha logo.png" alt="Logo" class="logo"> <!-- Logo -->
        <h2>Welcome Back!</h2>
        <form action="authenticate.php" method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p><a href="forgot_password.php">Forgot Password?</a></p>
    </div>
</body>
</html>