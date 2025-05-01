<?php
session_start();
require 'config.php'; // Include your database configuration

// Handle form submission for editing stock
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stock_id = intval($_POST['stock_id']); // Sanitize input
    $new_quantity = intval($_POST['quantity']); // Sanitize input

    if ($new_quantity > 0) {
        // Update the quantity in the stocks table
        $stmt = $pdo->prepare("UPDATE stocks SET quantity = :quantity WHERE id = :id");
        $stmt->bindParam(':quantity', $new_quantity, PDO::PARAM_INT);
        $stmt->bindParam(':id', $stock_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Stock updated successfully.";
        } else {
            $_SESSION['message'] = "Error updating stock.";
        }

        // Redirect back to the view stock page
        header("Location: view_stock.php");
        exit;
    } else {
        $_SESSION['message'] = "Quantity must be greater than zero.";
    }
}

// Fetch the stock record to edit based on the selected stock ID
if (isset($_GET['id'])) {
    $stock_id = intval($_GET['id']); // Sanitize input

    $stmt = $pdo->prepare("SELECT s.id, s.item_id, s.quantity, i.common_name, i.spec_code, i.expire_date 
                            FROM stocks s 
                            JOIN items i ON s.item_id = i.id 
                            WHERE s.id = :id");
    $stmt->bindParam(':id', $stock_id, PDO::PARAM_INT);
    $stmt->execute();
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stock) {
        $_SESSION['message'] = "Stock record not found.";
        header("Location: view_stock.php");
        exit;
    }
} else {
    $_SESSION['message'] = "No stock selected.";
    header("Location: view_stock.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stock</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
        }
        h2 {
            color: #45d9e0;
            text-align: center;
        }
        form {
            max-width: 400px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .button {
            background-color: #45d9e0;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .button:hover {
            background-color: #0a888f;
        }
        .message {
            text-align: center;
            color: green;
        }
    </style>
</head>
<body>
    <h2>Edit Stock</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="hidden" name="stock_id" value="<?php echo htmlspecialchars($stock['id']); ?>">
        <div class="form-group">
            <label for="item_id">Item ID:</label>
            <input type="text" id="item_id" value="<?php echo htmlspecialchars($stock['item_id']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="common_name">Common Name:</label>
            <input type="text" id="common_name" value="<?php echo htmlspecialchars($stock['common_name']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="spec_code">Specification Code:</label>
            <input type="text" id="spec_code" value="<?php echo htmlspecialchars($stock['spec_code']); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="expire_date">Expire Date:</label>
            <input type="text" id="expire_date" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($stock['expire_date']))); ?>" readonly>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" min="1" value="<?php echo htmlspecialchars($stock['quantity']); ?>" required>
        </div>
        <button type="submit" class="button">Update Stock</button>
    </form>
</body>
</html>