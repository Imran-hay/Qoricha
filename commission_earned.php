<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit();
}
require 'agent_sidebar.php'; // Include your sidebar for navigation
require 'config.php'; // Include your database connection settings

// Fetch transactions for the logged-in agent to calculate commissions
$stmt = $pdo->prepare("
    SELECT t.transaction_id, c.name AS customer_name, t.amount, t.created_at 
    FROM transactions t
    JOIN customers c ON t.customer_id = c.customer_id
    WHERE t.agent_id = :agent_id
");
$stmt->execute(['agent_id' => $_SESSION['user_id']]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total commissions (assuming a commission rate, e.g., 10%)
$commission_rate = 0.10; // 10%
$total_commissions = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commissions Earned</title>
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
        .total-row {
            font-weight: bold;
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Commissions Earned</h1>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Customer Name</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Commission</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $transaction): 
                        $commission = $transaction['amount'] * $commission_rate;
                        $total_commissions += $commission;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($transaction['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($transaction['amount'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($transaction['created_at']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($commission, 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="4">Total Commissions Earned:</td>
                        <td><?php echo htmlspecialchars(number_format($total_commissions, 2)); ?></td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>