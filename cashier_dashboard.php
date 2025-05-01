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
SELECT SUM(s.quantity * i.price) AS total_sales
FROM sales s
JOIN items i ON s.item_id = i.item_id
WHERE s.status = 'approved' AND DATE(s.sale_date) = CURDATE()
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
$recentTransactionsQuery = $pdo->prepare("SELECT * FROM sales s JOIN items i ON s.item_id = i.item_id ORDER BY s.sale_date DESC LIMIT 5");
$recentTransactionsQuery->execute();
$recentTransactions = $recentTransactionsQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending credit sales for notification
$pendingCreditSalesQuery = $pdo->prepare("SELECT * FROM sales WHERE status = 'pending' AND payment_type = 'credit'");
$pendingCreditSalesQuery->execute();
$pendingCreditSales = $pendingCreditSalesQuery->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the sales chart
$salesData = []; // Fetch actual sales data for the chart
$salesLabels = []; // Corresponding labels (e.g., dates)
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cashier Dashboard</title>

xml

Copy
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
        --dark-grey: #333; /* Added dark grey for hover */
        --white: #fff;
        --grey: rgb(245, 245, 245);
        --black1: #222;
        --black2: #999;
        --primary-color: #45d9e0;
        --border-color: #ccc;
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
        flex-grow: 1; /* Allows the title to grow */
        text-align: center; /* Centers the title */
        margin: 0; /* Removes margin */
    }

    .search-bar {
        display: flex;
        align-items: center;
        margin-left: auto; /* Aligns search bar to the right */
    }

    .search-bar input[type="text"] {
        width: 150px;
        padding: 8px;
        border-radius: 5px;
        border: none;
    }

    .notification-icon {
        position: relative;
        display: flex;
        align-items: center;
        margin-left: 20px; /* Space between search bar and notification */
    }

    .notification-dropdown {
        display: none;
        position: absolute;
        top: 30px;
        right: 0;
        background: white;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .notification-dropdown.active {
        display: block;
    }

    .notification-dropdown ul {
        list-style: none;
        padding: 10px;
        margin: 0;
    }

    .notification-dropdown li {
        padding: 5px 0;
    }

    main {
        flex: 1; /* Takes up remaining space */
        padding: 20px;
    }

    .metrics {
        display: flex;
        justify-content: space-around;
        margin: 20px 0;
    }

    .metric-card {
        background: white;
        border-radius: 5px;
        padding: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        flex: 1;
        margin: 0 10px;
        text-align: center;
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

    /* Hover effects */
    .search-bar input[type="text"]:hover {
        border: 1px solid var(--dark-grey);
    }

    .notification-icon:hover {
        color: var(--dark-grey);
    }

    @media (max-width: 600px) {
        .metrics {
            flex-direction: column;
        }
        .metric-card {
            margin: 10px 0;
        }
    }
</style>
</head>

<body>

<header>

<h1>Cashier Dashboard</h1>

<div class="search-bar">

<input type="text" placeholder="Search...">

<i class="fa-solid fa-bell notification-icon" onclick="toggleNotificationDropdown()"></i>

<div class="notification-dropdown" id="notificationDropdown">

<ul>

<li><strong>Pending Credit Sales:</strong></li>

<?php foreach ($pendingCreditSales as $sale): ?>

<li><?php echo htmlspecialchars($sale['customer_name']); ?> - Awaiting Approval</li>

<?php endforeach; ?>

</ul>

</div>

</div>

</header>

<main>

<h2>Welcome to the Cashier Dashboard</h2>

<div class="metrics">

<div class="metric-card">Total Sales Today: $<span id="totalSales"><?php echo htmlspecialchars($totalSales); ?></span></div>

<div class="metric-card">Pending Approvals: <span id="pendingApprovals"><?php echo htmlspecialchars($pendingApprovals); ?></span></div>

<div class="metric-card">Total Collected Repayments: $<span id="totalRepayments"><?php echo htmlspecialchars($totalRepayments); ?></span></div>

</div>

php-template

Copy
<div class="charts">
    <h2>Sales Over the Week</h2>
    <canvas id="salesChart" width="400" height="200"></canvas>
</div>

<div class="recent-transactions">
    <h2>Recent Transactions</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
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
                    <td><?php echo htmlspecialchars($transaction['sale_date']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['quantity']); ?></td>
                    <td>$<?php echo htmlspecialchars($transaction['quantity'] * $transaction['price']); ?></td>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

function toggleNotificationDropdown() {

const dropdown = document.getElementById('notificationDropdown');

dropdown.classList.toggle('active');

}

// Fetch actual sales data from your database

const salesData = <?php echo json_encode($salesData); ?>; // Fetch from PHP

const salesLabels = <?php echo json_encode($salesLabels); ?>; // Corresponding labels (e.g., dates)

const ctx = document.getElementById('salesChart').getContext('2d');

const salesChart = new Chart(ctx, {

type: 'line',

data: {

labels: salesLabels, // Replace with actual labels

datasets: [{

label: 'Sales Over the Week',

data: salesData, // Replace with actual data

backgroundColor: 'rgba(10, 136, 143, 0.2)',

borderColor: 'rgba(10, 136, 143, 1)',

borderWidth: 2

}]

},

options: {

responsive: true,

scales: {

y: {

beginAtZero: true

}

}

}

});

</script>

</body>

</html>