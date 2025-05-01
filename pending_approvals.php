<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit();
}
require 'agent_sidebar.php';
require 'config.php';

// Fetch sales approvals for the logged-in agent
$stmt = $pdo->prepare("SELECT s.sale_id, c.name AS customer_name, s.quantity, i.item_name, (s.quantity * i.price) AS amount, s.sale_date, s.payment_type, s.status 
                        FROM sales s 
                        JOIN customers c ON s.customer_id = c.customer_id 
                        JOIN items i ON s.item_id = i.item_id 
                        WHERE s.agent_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Approvals</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .content {
            margin-left: 220px; /* Adjust for sidebar width */
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #f2f2f2;
        }
        tr:hover {
            background: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Sales Approval Status</h1>
        <table>
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Customer Name</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Sale Date</th>
                    <th>Payment Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($sales) > 0): ?>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                            <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($sale['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($sale['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($sale['amount']); ?></td>
                            <td><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                            <td><?php echo htmlspecialchars($sale['payment_type']); ?></td>
                            <td><?php echo htmlspecialchars($sale['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No sales found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>