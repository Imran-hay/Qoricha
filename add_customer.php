<?php
session_start();
   

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit();
}

require 'agent_sidebar.php';
require 'config.php';

// Handle form submission
$success_message = ""; // Initialize success message variable
$error_message = ""; // Initialize error message variable
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $tin = $_POST['tin'];

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO customers (customer_id, name, email, phone, address, tin) VALUES (?,?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $email, $phone, $address, $tin])) {
        $success_message = "Customer added successfully!";
    } else {
        $error_message = "Error adding customer.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Customer</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }
        .content {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 20px auto;
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
            color: #0a888f; /* Updated heading color */
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            padding: 10px;
            background-color: #0a888f; /* Button color */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        button:hover {
            background-color: #0a7b7f; /* Darker shade on hover */
        }
        .message {
            margin-bottom: 20px;
            text-align: center;
            color: #d9534f; /* Red color for error messages */
        }
        .success {
            color: #5cb85c; /* Green color for success */
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Add New Customer</h1>

        <?php if (isset($error_message)): ?>
            <div class="message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" required>

            <label for="address">Address</label>
            <textarea id="address" name="address" rows="4" required></textarea>

            <label for="tin">TIN Number</label>
            <input type="text" id="tin" name="tin" required>

            <button type="submit">Add Customer</button>
        </form>
    </div>
</body>
</html>