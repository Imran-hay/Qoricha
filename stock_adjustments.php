<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'storeman') {
    header("Location: login.php");
    exit();
}
require 'config.php'; // Include your database connection settings
require 'storeman_sidebar.php'; // Include your sidebar for navigation

// Fetch item records for the dropdown
$stmt = $pdo->prepare("SELECT item_id, item_name FROM items");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = trim($_POST['item_id']);
    $adjustment = trim($_POST['adjustment']);
    $reason = trim($_POST['reason']);
    $adjustment_type = trim($_POST['adjustment_type']); // New field

    if ($item_id === '' || $adjustment === '' || $adjustment_type === '') {
        $_SESSION['message'] = "Item, adjustment amount, and type are required.";
    } else {
        // Determine the adjustment sign
        $adjustment_value = ($adjustment_type === 'sale') ? -abs((int)$adjustment) : abs((int)$adjustment);

        // Fetch current stock level
        $stmt = $pdo->prepare("SELECT stock FROM items WHERE item_id = :item_id");
        $stmt->execute([':item_id' => $item_id]);
        $current_stock = $stmt->fetchColumn();

        // Calculate new stock level
        $new_stock = $current_stock + $adjustment_value;

        // Update the stock level in the database
        $stmt = $pdo->prepare("UPDATE items SET stock = :new_stock WHERE item_id = :item_id");
        $stmt->execute([':new_stock' => $new_stock, ':item_id' => $item_id]);

        $_SESSION['message'] = "Stock level adjusted successfully.";
        header("Location: view_items.php"); // Redirect after successful adjustment
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adjust Stock Level</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.min.css" />
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: #f0f0f0;
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
            color: #0a888f; /* Darker heading color */
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #d3d3d3; /* Light gray for the header */
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        select, input[type="number"], input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #0a888f; /* Green button color */
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
            background-color: #0a7b7f; /* Darker green on hover */
        }
        .message {
            text-align: center;
            color: green;
            margin-bottom: 20px;
        }
    </style>
    <script>
        function fetchStockLevel(itemId) {
            if (itemId) {
                fetch('get_stock.php?item_id=' + itemId)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('current_stock').value = data.stock || 0;
                    })
                    .catch(error => console.error('Error fetching stock level:', error));
            } else {
                document.getElementById('current_stock').value = 0;
            }
        }
    </script>
</head>
<body>
    <div class="content">
        <h1>Adjust Stock Level</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <table>
                <tr>
                    <th>Select Item</th>
                    <td>
                        <select id="item_id" name="item_id" required onchange="fetchStockLevel(this.value)">
                            <option value="">Select an item</option>
                            <?php foreach ($items as $item): ?>
                                <option value="<?php echo htmlspecialchars($item['item_id']); ?>"><?php echo htmlspecialchars($item['item_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Current Stock Level</th>
                    <td>
                        <input type="number" id="current_stock" name="current_stock" value="0" readonly>
                    </td>
                </tr>
                <tr>
                    <th>Adjustment Amount</th>
                    <td>
                        <input type="number" id="adjustment" name="adjustment" required>
                    </td>
                </tr>
                <tr>
                    <th>Adjustment Type</th>
                    <td>
                        <select id="adjustment_type" name="adjustment_type" required>
                            <option value="">Select Type</option>
                            <option value="sale">Sale (Decrease Stock)</option>
                            <option value="return">Return (Increase Stock)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Reason for Adjustment (optional)</th>
                    <td>
                        <input type="text" id="reason" name="reason">
                    </td>
                </tr>
            </table>
            <button type="submit">Adjust Stock</button>
        </form>
    </div>
</body>
</html>