<?php
session_start();

// Include database configuration
require 'config.php';
require 'sidebar.php';

// Fetch items from the database
$stmt = $pdo->prepare("SELECT * FROM items ORDER BY item_name ASC"); //Order by item name
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Items</title>
    <link rel="stylesheet" href="view_items.css"> <!-- Create this CSS file -->
    <style>
        /* Basic styling for the table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        },
        container {
           margin-left: "210px";
           padding-left: "20px";
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
                            <!-- Add more columns as needed -->
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
                                <!-- Add more columns as needed -->
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