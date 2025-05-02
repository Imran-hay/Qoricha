<?php
session_start();
require 'config.php';
require 'cashier_sidebar.php';

// Check if user is logged in and is a cashier
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Since tables don't exist, we'll initialize empty arrays to prevent errors
$totalSales = 0;
$customerCount = 0;
$pendingApprovals = 0;
$totalRepayments = 0;
$recentTransactions = [];
$pendingCreditSales = [];

// If tables existed, we would use these queries:

// Fetch total sales for today
$totalSalesQuery = $pdo->prepare("
    SELECT SUM(s.total_amount) AS total_sales
    FROM sales s
    JOIN items i ON s.item_id = i.item_id
    WHERE s.status = 'approved' AND DATE(s.created_at) = CURDATE()
");
$totalSalesQuery->execute();
$totalSales = $totalSalesQuery->fetchColumn() ?: 0;

// Fetch pending approvals
$pendingApprovalsQuery = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE status = 'pending'");
$pendingApprovalsQuery->execute();
$pendingApprovals = $pendingApprovalsQuery->fetchColumn() ?: 0;


$customersQuery = $pdo->prepare("SELECT COUNT(*) FROM customers");
$customersQuery->execute();
$customerCount = $customersQuery->fetchColumn() ?: 0;




// Fetch total repayments
$totalRepaymentsQuery = $pdo->prepare("SELECT SUM(amount) AS total_repayments FROM repayments WHERE status = 'completed'");
$totalRepaymentsQuery->execute();
$totalRepayments = $totalRepaymentsQuery->fetchColumn() ?: 0;

// Fetch recent transactions
$recentTransactionsQuery = $pdo->prepare("
    SELECT s.*, i.item_name, i.unit_price
    FROM sales s 
    JOIN items i ON s.item_id = i.item_id
    ORDER BY s.created_at DESC LIMIT 5
");
$recentTransactionsQuery->execute();
$recentTransactions = $recentTransactionsQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending credit sales for notification
$pendingCreditSalesQuery = $pdo->prepare("
    SELECT s.*
    FROM sales s 
    WHERE status = 'pending' AND payment_type = 'Credit'
");
$pendingCreditSalesQuery->execute();
$pendingCreditSales = $pendingCreditSalesQuery->fetchAll(PDO::FETCH_ASSOC);


// Sample data for demonstration (remove when tables exist)



$recentTransactionsStmt = $pdo->prepare("
    SELECT created_at, customer_name, item_name, quantity, total_amount AS price, status
    FROM sales s
    JOIN items i ON s.item_id = i.item_id
    WHERE status = 'approved'
    ORDER BY created_at DESC
    LIMIT 5
");
$recentTransactionsStmt->execute();
$recentTransactions = $recentTransactionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending credit sales
$pendingCreditStmt = $pdo->prepare("
    SELECT customer_name, (total_amount) AS amount
    FROM sales
    WHERE status = 'pending' AND payment_type = 'Credit'
");
$pendingCreditStmt->execute();
$pendingCreditSales = $pendingCreditStmt->fetchAll(PDO::FETCH_ASSOC);



// Prepare data for the sales chart (sample data)
$salesData = [120, 190, 170, 220, 180, 250, 210]; // Sample sales data
$salesLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']; // Sample labels
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary-color: #0a888f;
            --secondary-color: #45d9e0;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --light-text: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --white: #ffffff;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--dark-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background-color: var(--primary-color);
            padding: 1rem 2rem;
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--box-shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        .search-notification-container {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .search-bar {
            position: relative;
        }
        
        .search-bar input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border-radius: var(--border-radius);
            border: none;
            width: 200px;
            transition: all 0.3s ease;
        }
        
        .search-bar input:focus {
            width: 250px;
            outline: none;
            box-shadow: 0 0 0 2px rgba(69, 217, 224, 0.3);
        }
        
        .search-bar i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-text);
        }
        
        .notification-icon {
            position: relative;
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--white);
        }
        
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--danger-color);
            color: var(--white);
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .notification-dropdown {
            display: none;
            position: absolute;
            top: 40px;
            right: 0;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 300px;
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .notification-dropdown.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .notification-header {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-header h3 {
            margin: 0;
            font-size: 1rem;
        }
        
        .notification-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s ease;
        }
        
        .notification-item:hover {
            background-color: rgba(10, 136, 143, 0.05);
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-title {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark-text);
        }
        
        .notification-message {
            font-size: 0.9rem;
            color: var(--light-text);
            margin-bottom: 0.5rem;
        }
        
        .notification-time {
            font-size: 0.8rem;
            color: var(--light-text);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        main {
            flex: 1;
            padding: 2rem;
        }
        
        .welcome-section {
            margin-bottom: 2rem;
        }
        
        .welcome-section h2 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .welcome-section p {
            color: var(--light-text);
        }
        
        .metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .metric-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .metric-title {
            font-size: 1rem;
            color: var(--light-text);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .metric-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .metric-change {
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .metric-change.positive {
            color: var(--success-color);
        }
        
        .metric-change.negative {
            color: var(--danger-color);
        }
        
        .charts-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }
        
        .charts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .charts-header h2 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--primary-color);
        }
        
        .chart-period-selector {
            display: flex;
            gap: 0.5rem;
        }
        
        .chart-period-btn {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            background: transparent;
            border: 1px solid var(--secondary-color);
            color: var(--secondary-color);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.8rem;
        }
        
        .chart-period-btn.active {
            background: var(--secondary-color);
            color: var(--white);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .data-tables {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .data-table {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .table-header h2 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--primary-color);
        }
        
        .table-header a {
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 0.75rem 1rem;
            background-color: rgba(10, 136, 143, 0.05);
            color: var(--light-text);
            font-weight: 500;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-size: 0.9rem;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-approved {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .status-pending {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .status-rejected {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .credit-sales-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .credit-sales-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .credit-sales-item:last-child {
            border-bottom: none;
        }
        
        .credit-sales-customer {
            font-weight: 500;
        }
        
        .credit-sales-amount {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .action-btn {
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            border: none;
            background: var(--secondary-color);
            color: var(--white);
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background: var(--primary-color);
        }
        
        footer {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 1rem;
            text-align: center;
            margin-top: auto;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .footer-links a {
            color: var(--white);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .copyright {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }
            
            .search-notification-container {
                width: 100%;
                justify-content: space-between;
            }
            
            .search-bar input {
                width: 150px;
            }
            
            .search-bar input:focus {
                width: 180px;
            }
            
            .metrics {
                grid-template-columns: 1fr;
            }
            
            .data-tables {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
 

    <main>
        <section class="welcome-section">
            <h2>Welcome back, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Cashier'; ?>!</h2>
            <p>Here's what's happening with your store today.</p>
        </section>

        <section class="metrics">
            <div class="metric-card">
                <div class="metric-title">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Today's Sales</span>
                </div>
                <div class="metric-value">$<?php echo number_format($totalSales, 2); ?></div>
             
            </div>
            
            <div class="metric-card">
                <div class="metric-title">
                    <i class="fas fa-clock"></i>
                    <span>Pending Approvals</span>
                </div>
                <div class="metric-value"><?php echo $pendingApprovals; ?></div>
             
            </div>
            
            <div class="metric-card">
                <div class="metric-title">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Collected Repayments</span>
                </div>
                <div class="metric-value">$<?php echo number_format($totalRepayments, 2); ?></div>
           
            </div>
            
            <div class="metric-card">
                <div class="metric-title">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </div>
                <div class="metric-value"><?php echo $customerCount; ?></div>
               
            </div>
        </section>

     <!--    <section class="charts-container">
            <div class="charts-header">
                <h2>Sales Performance</h2>
                <div class="chart-period-selector">
                    <button class="chart-period-btn active">Week</button>
                    <button class="chart-period-btn">Month</button>
                    <button class="chart-period-btn">Year</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </section> -->

        <section class="data-tables">
            <div class="data-table">
                <div class="table-header">
                    <h2>Recent Transactions</h2>
                    <a href="transactions.php">View All <i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Item</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $transaction): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($transaction['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['item_name']); ?></td>
                                    <td>$<?php echo number_format($transaction['quantity'] * $transaction['price'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo htmlspecialchars($transaction['status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($transaction['status'])); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentTransactions)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">No recent transactions found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="data-table">
                <div class="table-header">
                    <h2>Pending Credit Sales</h2>
                    <a href="credit_sales.php">View All <i class="fas fa-chevron-right"></i></a>
                </div>
                <ul class="credit-sales-list">
                    <?php foreach ($pendingCreditSales as $sale): ?>
                        <li class="credit-sales-item">
                            <div>
                                <div class="credit-sales-customer"><?php echo htmlspecialchars($sale['customer_name']); ?></div>
                                <small>Pending since <?php echo isset($sale['created_at']) ? date('M j', strtotime($sale['created_at'])) : 'today'; ?></small>
                            </div>
                            <div class="credit-sales-amount">$<?php echo isset($sale['amount']) ? number_format($sale['amount'], 2) : '0.00'; ?></div>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($pendingCreditSales)): ?>
                        <li class="credit-sales-item">
                            <div>No pending credit sales</div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </section>
    </main>

 

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('active');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const notificationIcon = document.querySelector('.notification-icon');
            
            if (!notificationIcon.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
        
        // Chart.js implementation
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($salesLabels); ?>,
                datasets: [{
                    label: 'Sales Amount ($)',
                    data: <?php echo json_encode($salesData); ?>,
                    backgroundColor: 'rgba(10, 136, 143, 0.1)',
                    borderColor: 'rgba(10, 136, 143, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'rgba(10, 136, 143, 1)',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 12
                        },
                        padding: 12,
                        cornerRadius: 6
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
        
        // Period selector functionality
        document.querySelectorAll('.chart-period-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.chart-period-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Here you would typically fetch new data based on the selected period
                // For demonstration, we'll just update the chart with sample data
                if (this.textContent === 'Month') {
                    // Sample month data
                    const monthLabels = [];
                    const monthData = [];
                    for (let i = 1; i <= 30; i++) {
                        monthLabels.push('Day ' + i);
                        monthData.push(Math.floor(Math.random() * 300) + 100);
                    }
                    salesChart.data.labels = monthLabels;
                    salesChart.data.datasets[0].data = monthData;
                } else if (this.textContent === 'Year') {
                    // Sample year data
                    const yearLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    const yearData = yearLabels.map(() => Math.floor(Math.random() * 1000) + 500);
                    salesChart.data.labels = yearLabels;
                    salesChart.data.datasets[0].data = yearData;
                } else {
                    // Week data (default)
                    salesChart.data.labels = <?php echo json_encode($salesLabels); ?>;
                    salesChart.data.datasets[0].data = <?php echo json_encode($salesData); ?>;
                }
                salesChart.update();
            });
        });
    </script>
</body>
</html>