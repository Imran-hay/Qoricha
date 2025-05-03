<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    // exit();
}

require 'agent_sidebar.php';
require 'config.php';

// Initialize message variables outside the conditional block
$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $tin = $_POST['tin'];

    // Insert into database
    try {
        $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone, address, tin) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $phone, $address, $tin])) {
            $success_message = "Customer added successfully!";
        } else {
            $error_message = "Error adding customer.";
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Customer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* General body and content styles (consistent with dashboard) */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            color: #343a40;
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 240px; /* Sidebar width */
            transition: margin-left 0.3s ease;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 600px; /* Reduced max-width */
            margin: 20px auto;
        }

        .content.shifted {
            margin-left: 0;
        }

        h1 {
            margin-bottom: 20px;
            text-align: center;
            color: #764ba2; /* Consistent color */
        }

        /* Form Styles */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        textarea {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Button Styles */
        button {
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Sidebar gradient */
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin-top: 10px;
        }

        button:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        /* Message Styles */
        .message {
            margin-bottom: 20px;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            display: <?php echo ($success_message != "" || $error_message != "") ? "block" : "none"; ?>; /* Conditionally show/hide */
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Add New Customer</h1>

        <?php if (isset($error_message) && $error_message != ""): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (isset($success_message) &&  $success_message != ""): ?>
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