<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit();
}
require 'agent_sidebar.php'; // Include your sidebar for navigation
require 'config.php'; // Include your database connection settings

// Fetch cash sales transactions for the logged-in agent
$stmt = $pdo->prepare("
    SELECT t.transaction_id, c.name AS customer_name, t.amount, t.created_at 
    FROM transactions t
    JOIN customers c ON t.customer_id = c.customer_id
    WHERE t.agent_id = :agent_id AND t.payment_method = 'cash'
");
$stmt->execute(['agent_id' => $_SESSION['user_id']]);
$cash_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Sales Report</title>
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
        <h1>Cash Sales Report</h1>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Customer Name</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($cash_sales) > 0): ?>
                    <?php foreach ($cash_sales as $sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sale['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($sale['amount']); ?></td>
                            <td><?php echo htmlspecialchars($sale['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No cash sales found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>