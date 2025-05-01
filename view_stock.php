<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'storeman') {
    header("Location: login.php");
    exit();
}
require 'config.php'; // Include your database connection settings
require 'storeman_sidebar.php';

// Fetch item records from the database
try {
    $stmt = $pdo->prepare("
        SELECT item_id, item_name, price, stock, expiry_date, created_at 
        FROM items
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching items: " . htmlspecialchars($e->getMessage());
    exit;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit_item'])) {
        if (!empty($_POST['selected_items'])) {
            $item_id = $_POST['selected_items'][0]; // Edit only the first selected item
            header("Location: edit_item.php?id=" . $item_id);
            exit;
        } else {
            $_SESSION['message'] = "No item selected for editing.";
        }
    }

    if (isset($_POST['delete_item'])) {
        if (!empty($_POST['selected_items'])) {
            $ids = implode(',', array_map('intval', $_POST['selected_items'])); // Sanitize IDs
            $stmt = $pdo->prepare("DELETE FROM items WHERE item_id IN ($ids)");

            if ($stmt->execute()) {
                $_SESSION['message'] = "Selected item records deleted successfully.";
            } else {
                $_SESSION['message'] = "Error deleting item records.";
            }

            // Redirect to avoid form resubmission issues
            header("Location: view_items.php");
            exit;
        } else {
            $_SESSION['message'] = "No item records selected for deletion.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Items</title>
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
            color: #0a888f; /* Updated heading color */
            text-align: center;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .search-bar {
            display: flex;
            align-items: center;
        }
        .search-bar input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
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
        }
        button:hover {
            background-color: #0a7b7f; /* Darker shade on hover */
        }
        .notification {
            text-align: center;
            color: green;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #0a888f;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Item List</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="notification"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="header">
            <div class="search-bar">
                <input type="text" placeholder="Search items..." />
                <button type="button">Search</button>
            </div>
            <div class="button-container">
                <form action="" method="POST" style="display: inline;">
                    <button type="submit" name="edit_item" <?php echo empty($items) ? 'disabled' : ''; ?>>Edit Item</button>
                </form>
                <form action="" method="POST" style="display: inline;">
                    <button type="submit" name="delete_item" <?php echo empty($items) ? 'disabled' : ''; ?>>Delete Item</button>
                </form>
            </div>
        </div>

        <form action="" method="POST">
            <table>
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Expiry Date</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><input type="checkbox" name="selected_items[]" value="<?php echo htmlspecialchars($item['item_id']); ?>"></td>
                                <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($item['stock']); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($item['expiry_date']))); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($item['created_at']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No item records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>
</body>
</html>