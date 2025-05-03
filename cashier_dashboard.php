<?php
session_start();
require 'config.php';
require 'cashier_sidebar.php';

// Check if user is logged in and is a cashier
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cashier') {
    exit();
}

// Fetch total sales for today
$totalSalesQuery = $pdo->prepare("
SELECT SUM(s.quantity * i.unit_price) AS total_sales
FROM sales s
JOIN items i ON s.item_id = i.item_id
WHERE s.status = 'approved' AND DATE(s.due_date) = CURDATE()
");
$totalSalesQuery->execute();
$totalSales = $totalSalesQuery->fetchColumn() ?: 0;

// Fetch pending approvals
$pendingApprovalsQuery = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE status = 'pending'");
$pendingApprovalsQuery->execute();
$pendingApprovals = $pendingApprovalsQuery->fetchColumn() ?: 0;

// Fetch total repayments
$totalRepaymentsQuery = $pdo->prepare("SELECT SUM(amount) AS total_repayments FROM repayments WHERE status = 'collected'");
$totalRepaymentsQuery->execute();
$totalRepayments = $totalRepaymentsQuery->fetchColumn() ?: 0;

// Fetch recent transactions
$recentTransactionsQuery = $pdo->prepare("SELECT * FROM sales s JOIN items i ON s.item_id = i.item_id ORDER BY s.due_date DESC LIMIT 5");
$recentTransactionsQuery->execute();
$recentTransactions = $recentTransactionsQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending credit sales for notification
$pendingCreditSalesQuery = $pdo->prepare("SELECT * FROM sales WHERE status = 'pending' AND payment_type = 'credit'");
$pendingCreditSalesQuery->execute();
$pendingCreditSales = $pendingCreditSalesQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Ubuntu', sans-serif;
        }

        :root {
            --light-bg: rgb(194, 221, 223);
            --dark-teal: #0a888f;
            --dark-grey: #333;
            --white: #fff;
            --grey: rgb(245, 245, 245);
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            background-color: var(--grey);
            display: flex;
            flex-direction: column;
        }

        .header {
            background-color: var(--dark-teal);
            padding: 10px 20px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header h1 {
            flex-grow: 1;
            text-align: center;
            margin: 0;
        }

        .search-bar {
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        .search-bar input[type="text"] {
            width: 150px;
            padding: 8px;
            border-radius: 5px;
            border: none;
        }

        main {
            flex: 1;
            padding: 20px;
        }

        .metrics {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }

        .metric-button {
            background: var(--dark-teal);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 15px;
            cursor: pointer;
            text-align: center;
            flex: 1;
            margin: 0 10px;
            transition: background 0.3s;
        }

        .metric-button:hover {
            background: #0a6f70; /* Darker shade */
        }

        .recent-transactions, .credit-sales {
            margin: 20px;
            background: white;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        footer {
            text-align: center;
            padding: 10px;
            background-color: var(--dark-teal);
            color: white;
            width: 100%;
        }
    </style>
</head>

<body>

<header>
    <h1>Cashier Dashboard</h1>
    <div class="search-bar">
        <input type="text" placeholder="Search...">
    </div>
</header>

<main>
    <h2>Welcome to the Cashier Dashboard</h2>

    <div class="metrics">
        <button class="metric-button">Total Sales Today: $<span><?php echo htmlspecialchars($totalSales); ?></span></button>
        <button class="metric-button">Pending Approvals: <span><?php echo htmlspecialchars($pendingApprovals); ?></span></button>
        <button class="metric-button">Total Collected Repayments: $<span><?php echo htmlspecialchars($totalRepayments); ?></span></button>
    </div>

    <div class="recent-transactions">
        <h2>Recent Transactions</h2>
        <table>
            <thead>
                <tr>
                    <th>Due Date</th>
                    <th>Customer</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="transactionList">
                <?php foreach ($recentTransactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['item_name']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['quantity']); ?></td>
                        <td>$<?php echo htmlspecialchars($transaction['quantity'] * $transaction['unit_price']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="credit-sales">
        <h2>Pending Credit Sales</h2>
        <ul id="creditSalesList">
            <?php foreach ($pendingCreditSales as $sale): ?>
                <li><?php echo htmlspecialchars($sale['customer_name']); ?> - Awaiting Approval</li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>

<footer>
    <p>&copy; 2025 Your Company Name. All rights reserved.</p>
    <p><a href="support.php" style="color: white;">Need Help?</a></p>
</footer>

</body>
</html>