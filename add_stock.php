<?php
session_start();
require 'config.php'; // Include your database configuration
require 'storeman_sidebar.php'; // Include sidebar

// Fetch items for the dropdown
try {
    $stmt = $pdo->prepare("SELECT item_id, item_name FROM items"); // Selecting item_id and item_name
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching items: " . htmlspecialchars($e->getMessage());
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = intval($_POST['item_id']); // Sanitize input
    $quantity_to_add = intval($_POST['quantity']); // Sanitize input

    if ($quantity_to_add > 0) {
        // Fetch the current stock and item details
        $stmt = $pdo->prepare("SELECT stock, item_name FROM items WHERE item_id = :id");
        $stmt->bindParam(':id', $item_id, PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            $current_stock = $item['stock'];
            $item_name = $item['item_name'];

            // Update stock quantity in the items table
            $stmt = $pdo->prepare("UPDATE items SET stock = stock + :quantity WHERE item_id = :id");
            $stmt->bindParam(':quantity', $quantity_to_add, PDO::PARAM_INT);
            $stmt->bindParam(':id', $item_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['message'] = "Stock updated successfully for $item_name.";
            } else {
                $_SESSION['message'] = "Error updating stock in items table.";
            }
        } else {
            $_SESSION['message'] = "Item not found.";
        }
    } else {
        $_SESSION['message'] = "Quantity must be greater than zero.";
    }

    // Redirect back to the same page to show the message
    header("Location: add_stock.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
            display: flex;
        }
        .content {
            flex-grow: 1;
            margin-left: 270px; /* Account for sidebar width */
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        h2 {
            color: #0a888f; /* Dashboard color for heading */
            text-align: center;
        }
        form {
            max-width: 400px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        select, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #0a888f; /* Dashboard color for borders */
            border-radius: 5px;
        }
        .button {
            background-color: #0a888f; /* Dashboard color for button */
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .button:hover {
            background-color: #0a888f; /* Darker shade on hover */
        }
        .message {
            text-align: center;
            color: #0a888f; /* Success message color */
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="content">
        <h2>Add Stock</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="item_id">Select Item:</label>
                <select name="item_id" id="item_id" required>
                    <option value="">--Select an item--</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?php echo htmlspecialchars($item['item_id']); ?>">
                            <?php echo htmlspecialchars($item['item_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="quantity" min="1" required>
            </div>
            <button type="submit" class="button">Add to Stock</button>
        </form>
    </div>
</body>
</html>