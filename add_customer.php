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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* General body and content styles (consistent with dashboard) */
        :root {
            --primary: #4361ee;
            --primary-light: #e6f0ff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #fd7e14;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
            --white: #ffffff;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: #4a5568;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-left: 280px;
            padding: 30px;
            transition: var(--transition);
             /* Added to center the content */
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align items to the top */
            min-height: calc(100vh - 60px); /* Adjust for header/footer if any */
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title h2 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .page-title p {
            color: #718096;
            margin: 5px 0 0;
            font-size: 14px;
        }

        .card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            width: 100%; /* Make the card wider */
             /* Limit the maximum width */
        }

        /* Form Styles */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: 500;
            color: #495057;
            display: block;
        }

        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
            margin-bottom: 15px;
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box; /* Important for width consistency */
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
            outline: none;
        }

        /* Button Styles */
        button {
            padding: 10px 15px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            margin-top: 10px;
        }

        button:hover {
            background-color: #3a56d4;
            box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
        }

        /* Message Styles */
        .alert {
            padding: 12px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 20px;
                 /* Center content on smaller screens */
                justify-content: center;
            }

            .card {
                width: 95%; /* Take up more space on smaller screens */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="page-header">
                <div class="page-title">
                    <h2>Add New Customer</h2>
                    <p>Add a new customer to the system</p>
                </div>
            </div>

            <?php if (isset($error_message) && $error_message != ""): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (isset($success_message) &&  $success_message != ""): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
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
    </div>
</body>
</html>