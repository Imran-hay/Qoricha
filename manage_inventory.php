<?php
session_start();
require 'storeman_sidebar.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'storeman') {
    header("Location: login.php");
    exit();
}

// Include database configuration
require 'config.php';

// Handle form submissions for adding or updating items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        // Add new item
        $item_name = $_POST['item_name'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $expiry_date = $_POST['expiry_date'];

        $stmt = $pdo->prepare("INSERT INTO items (item_name, price, stock, expiry_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$item_name, $price, $stock, $expiry_date]);
    } elseif (isset($_POST['update_item'])) {
        // Update existing item
        $item_id = $_POST['item_id'];
        $item_name = $_POST['item_name'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $expiry_date = $_POST['expiry_date'];

        $stmt = $pdo->prepare("UPDATE items SET item_name = ?, price = ?, stock = ?, expiry_date = ? WHERE item_id = ?");
        $stmt->execute([$item_name, $price, $stock, $expiry_date, $item_id]);
    }
}

// Fetch inventory items
$stmt = $pdo->prepare("SELECT * FROM items");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }
        h1 {
            color: #0a888f;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #0a888f;
            color: white;
        }
        .form-container {
            margin-bottom: 20px;
        }
        .form-container input {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container button {
            padding: 10px 15px;
            background-color: #0a888f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #00887c;
        }
    </style>
</head>
<body>
    <h1>Manage Inventory</h1>

    <div class="form-container">
        <form method="POST">
            <input type="hidden" name="item_id" id="item_id">
            <input type="text" name="item_name" id="item_name" placeholder="Item Name" required>
            <input type="number" name="price" id="price" placeholder="Price" step="0.01" required>
            <input type="number" name="stock" id="stock" placeholder="Stock" required>
            <input type="date" name="expiry_date" id="expiry_date" required>
            <button type="submit" name="add_item">Add Item</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item ID</th>
                <th>Item Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Expiry Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                <td><?php echo htmlspecialchars($item['price']); ?></td>
                <td><?php echo htmlspecialchars($item['stock']); ?></td>
                <td><?php echo htmlspecialchars($item['expiry_date']); ?></td>
                <td>
                    <button onclick="editItem(<?php echo htmlspecialchars($item['item_id']); ?>, '<?php echo htmlspecialchars($item['item_name']); ?>', <?php echo htmlspecialchars($item['price']); ?>, <?php echo htmlspecialchars($item['stock']); ?>, '<?php echo htmlspecialchars($item['expiry_date']); ?>')">Edit</button>
                    <a href="delete_item.php?id=<?php echo htmlspecialchars($item['item_id']); ?>" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        function editItem(id, name, price, stock, expiry) {
            document.getElementById('item_id').value = id;
            document.getElementById('item_name').value = name;
            document.getElementById('price').value = price;
            document.getElementById('stock').value = stock;
            document.getElementById('expiry_date').value = expiry;
        }
    </script>
</body>
</html>