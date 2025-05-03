<?php
ob_start(); // Start output buffering
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'storeman') {
    
}
require 'config.php'; // Include your database connection settings
require 'storeman_sidebar.php'; // Include your sidebar for navigation

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $item_name = trim($_POST['item_name']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $expiry_date = $_POST['expiry_date'];

    // Validate form data
    if ($item_name === '' || $price === '' || $stock === '' || $expiry_date === '') {
        $_SESSION['message'] = "All fields are required.";
    } else {
        // Prepare SQL statement to insert new item
        $stmt = $pdo->prepare("
            INSERT INTO items (item_name, price, stock, expiry_date, created_at) 
            VALUES (:item_name, :price, :stock, :expiry_date, NOW())
        ");
        $stmt->execute([
            ':item_name' => $item_name,
            ':price' => $price,
            ':stock' => $stock,
            ':expiry_date' => $expiry_date
        ]);

        $_SESSION['message'] = "Item added successfully.";
        header("Location: view_items.php"); // Redirect after successful insertion
        exit;
    }
}
ob_end_flush(); // Flush the output buffer
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.min.css" />
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 20px auto;
        }
        h1 {
            color: #0a888f; /* Heading color */
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        input[type="text"], input[type="number"], input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #0a888f; /* Button color */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            display: block;
            margin: 20px auto; /* Center the button */
        }
        button:hover {
            background-color: #0a7b7f; /* Darker shade on hover */
        }
        .message {
            text-align: center;
            color: green;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Add New Item</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="item_name">Item Name:</label>
                <input type="text" id="item_name" name="item_name" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="stock">Stock:</label>
                <input type="number" id="stock" name="stock" required>
            </div>
            <div class="form-group">
                <label for="expiry_date">Expiry Date:</label>
                <input type="date" id="expiry_date" name="expiry_date" required>
            </div>
            <button type="submit">Add Item</button>
        </form>
    </div>
</body>
</html>