<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'storeman') {
    /*header("Location: login.php");
    exit()*/;
}
require 'config.php'; // Include your database connection settings
require 'storeman_sidebar.php'; // Include your sidebar for navigation

// Fetch stock levels from the database
$stmt = $pdo->prepare("SELECT item_id, item_name, stock FROM items");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Level Report</title>
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
    </style>
</head>
<body>
    <div class="content">
        <h1>Stock Level Report</h1>
        <table>
            <tr>
                <th>Item ID</th>
                <th>Item Name</th>
                <th>Current Stock Level</th>
            </tr>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['stock']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>