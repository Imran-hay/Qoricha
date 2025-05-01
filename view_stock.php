<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and has the 'storeman' role
// (Add your session checking code here)

// Include your database connection settings
require 'config.php';

// Include the storeman sidebar
require 'storeman_sidebar.php';

$stmt = $pdo->prepare("
    SELECT 
        items.item_id,
        items.item_name, 
        items.unit_price, 
        items.stock, 
        items.expire_date, 
        NOW() AS received_date, 
        sales.created_at
    FROM 
        items
    LEFT JOIN 
        sales ON items.item_id = sales.item_id
    ORDER BY 
        items.item_name ASC
");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Stock</title>
    <link rel="stylesheet" href="view_items.css"> <!-- Create this CSS file -->
    <style>
        /* Basic styling for the table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .container {
            margin-left: 210px;
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
    </style>
</head>
<body>
    <header>
        <!-- Header content here -->
    </header>
    <main>
        <div class="container">
            <h2>View Stock</h2>

            <?php if (count($items) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Expire Date</th>
                            <th>Received Date</th>
                            <th>Delivery Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['unit_price']); ?></td>
                                <td><?php echo htmlspecialchars($item['stock']); ?></td>
                                <td><?php echo htmlspecialchars($item['expire_date']); ?></td>
                                <td><?php echo htmlspecialchars($item['received_date']); ?></td>
                                <td><?php echo htmlspecialchars($item['created_at']); ?></td>
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
</body>
</html>