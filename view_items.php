<?php
session_start();

// Include database configuration
require 'config.php';
require 'sidebar.php';

// Fetch items from the database
$stmt = $pdo->prepare("SELECT * FROM items ORDER BY item_name ASC"); //Order by item name
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle item update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_item'])) {
    $item_id = $_POST['item_id'];
    $hs_code = $_POST['hs_code'];
    $item_name = $_POST['item_name'];
    $stock = $_POST['stock'];
    $unit_price = $_POST['unit_price'];
    $expire_date = $_POST['expire_date'];

    try {
        $updateStmt = $pdo->prepare("
            UPDATE items
            SET hs_code = ?, item_name = ?, stock = ?, unit_price = ?, expire_date = ?
            WHERE item_id = ?
        ");
        $updateStmt->execute([$hs_code, $item_name, $stock, $unit_price, $expire_date, $item_id]);

        echo "<script>alert('Item updated successfully!'); window.location.href='view_items.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error updating item: " . $e->getMessage() . "'); window.location.href='view_items.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Items</title>
    <link rel="stylesheet" href="view_items.css">
    <style>
        /* Basic styling for the table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .container {
            margin-left: 280px; /* Adjust for sidebar */
            padding-left: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .no-items {
            text-align: center;
            margin-top: 20px;
            font-style: italic;
            color: #888;
        }

        /* Style for the update form */
        .update-form {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .update-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .update-form input[type="text"],
        .update-form input[type="number"],
        .update-form input[type="date"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .update-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .update-form input[type="submit"]:hover {
            background-color: #3e8e41;
        }
    </style>
</head>
<body>
    <header>
        <!-- Header content here -->
    </header>
    <main>
        <div class="container">
            <h2>View Items</h2>

            <?php if (count($items) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>HS Code</th>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>Stock</th>
                            <th>Unit Price</th>
                            <th>Expire Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['hs_code']); ?></td>
                                <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['stock']); ?></td>
                                <td><?php echo htmlspecialchars($item['unit_price']); ?></td>
                                <td><?php echo htmlspecialchars($item['expire_date']); ?></td>
                                <td>
                                    <button onclick="showUpdateForm('<?php echo htmlspecialchars($item['item_id']); ?>')">Update</button>
                                </td>
                            </tr>
                            <tr id="updateFormRow_<?php echo htmlspecialchars($item['item_id']); ?>" style="display: none;">
                                <td colspan="7">
                                    <form class="update-form" method="POST" action="">
                                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['item_id']); ?>">

                                        <label for="hs_code_<?php echo htmlspecialchars($item['item_id']); ?>">HS Code:</label>
                                        <input type="text" name="hs_code" id="hs_code_<?php echo htmlspecialchars($item['item_id']); ?>" value="<?php echo htmlspecialchars($item['hs_code']); ?>" required>

                                        <label for="item_name_<?php echo htmlspecialchars($item['item_id']); ?>">Item Name:</label>
                                        <input type="text" name="item_name" id="item_name_<?php echo htmlspecialchars($item['item_id']); ?>" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>

                                        <label for="stock_<?php echo htmlspecialchars($item['item_id']); ?>">Stock:</label>
                                        <input type="number" name="stock" id="stock_<?php echo htmlspecialchars($item['item_id']); ?>" value="<?php echo htmlspecialchars($item['stock']); ?>" required>

                                        <label for="unit_price_<?php echo htmlspecialchars($item['item_id']); ?>">Unit Price:</label>
                                        <input type="number" name="unit_price" id="unit_price_<?php echo htmlspecialchars($item['item_id']); ?>" value="<?php echo htmlspecialchars($item['unit_price']); ?>" required>

                                        <label for="expire_date_<?php echo htmlspecialchars($item['item_id']); ?>">Expire Date:</label>
                                        <input type="date" name="expire_date" id="expire_date_<?php echo htmlspecialchars($item['item_id']); ?>" value="<?php echo htmlspecialchars($item['expire_date']); ?>" required>

                                        <input type="submit" name="update_item" value="Update Item">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-items">No items found in the database.</p>
            <?php endif; ?>
        </div>
    </main>
    <script src="js/script.js"></script>
    <script>
        function showUpdateForm(itemId) {
            var row = document.getElementById('updateFormRow_' + itemId);
            if (row) {
                row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
            }
        }
    </script>
</body>
</html>