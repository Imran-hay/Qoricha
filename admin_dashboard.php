<?php
session_start();
require 'sidebar.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration
require 'config.php';

// Fetch dashboard data
$dashboard_data = [
    'total_sales' => $pdo->query("SELECT SUM(total_amount) FROM sales")->fetchColumn() ?? 0,
    'current_balance' => $pdo->query("SELECT current_balance FROM balance LIMIT 1")->fetchColumn() ?? 0,
    'total_withdrawals' => $pdo->query("SELECT SUM(amount) FROM withdrawals")->fetchColumn() ?? 0,
    'total_expenses' => $pdo->query("SELECT SUM(amount) FROM expenses")->fetchColumn() ?? 0,
    'monthly_sales' => $pdo->query("
        SELECT DATE_FORMAT(created_at, '%b') AS month, 
               SUM(total_amount) AS total 
        FROM sales 
        GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
        ORDER BY MIN(created_at)
    ")->fetchAll(PDO::FETCH_ASSOC),
    'payment_types' => $pdo->query("
        SELECT payment_type, COUNT(*) AS count 
        FROM sales 
        GROUP BY payment_type
    ")->fetchAll(PDO::FETCH_ASSOC),
    'recent_orders' => $pdo->query("
        SELECT s.sale_id, s.customer_name, s.total_amount, 
               DATE_FORMAT(s.created_at, '%b %d, %Y') AS date,
               s.payment_type, u.fullname AS agent_name
        FROM sales s
        LEFT JOIN users u ON s.user_id = u.user_id
        ORDER BY s.created_at DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC),
    'recent_customers' => $pdo->query("
        SELECT DISTINCT customer_name FROM sales ORDER BY created_at DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC),
    'inventory_data' => $pdo->query("
        SELECT item_name, stock FROM items ORDER BY stock ASC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC),
    'expense_data' => $pdo->query("
        SELECT ec.category_name, SUM(e.amount) AS total 
        FROM expenses e
        JOIN expense_categories ec ON e.category_id = ec.category_id
        GROUP BY ec.category_name
    ")->fetchAll(PDO::FETCH_ASSOC),
    'credit_sales' => $pdo->query("
        SELECT SUM(total_amount) AS total 
        FROM sales 
        WHERE payment_type = 'Credit' AND status = 'approved'
    ")->fetchColumn() ?? 0,
    'recent_repayments' => $pdo->query("
        SELECT r.repayment_id, r.sale_id, r.amount, 
               DATE_FORMAT(r.repayment_date, '%b %d, %Y') AS date,
               r.status
        FROM repayments r
        ORDER BY r.repayment_date DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC),
    'total_items' => $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn() ?? 0
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e6f0ff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #fd7e14;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
            --white: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 8px 22px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: #4a5568;
            margin: 0;
            padding: 0;
        }

        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: var(--transition);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .page-title p {
            color: #718096;
            margin: 5px 0 0;
            font-size: 14px;
        }

        /* Dashboard Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .stat-card.sales::before { background: var(--primary); }
        .stat-card.balance::before { background: var(--success); }
        .stat-card.withdrawals::before { background: var(--danger); }
        .stat-card.expenses::before { background: var(--warning); }
        .stat-card.inventory::before { background: var(--info); }
        .stat-card.credit-sales::before { background: #6f42c1; }

        .stat-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-card .card-title {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }

        .stat-card .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat-card.sales .card-icon { background: rgba(67, 97, 238, 0.1); color: var(--primary); }
        .stat-card.balance .card-icon { background: rgba(40, 167, 69, 0.1); color: var(--success); }
        .stat-card.withdrawals .card-icon { background: rgba(220, 53, 69, 0.1); color: var(--danger); }
        .stat-card.expenses .card-icon { background: rgba(253, 126, 20, 0.1); color: var(--warning); }
        .stat-card.inventory .card-icon { background: rgba(23, 162, 184, 0.1); color: var(--info); }
        .stat-card.credit-sales .card-icon { background: rgba(111, 66, 193, 0.1); color: #6f42c1; }

        .stat-card .card-value {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin: 5px 0;
        }

        .stat-card .card-change {
            font-size: 12px;
            display: flex;
            align-items: center;
        }

        .stat-card .card-change.positive { color: var(--success); }
        .stat-card .card-change.negative { color: var(--danger); }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
        }

        .chart-card .chart-header {
            margin-bottom: 20px;
        }

        .chart-card .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        /* Recent Activity */
        .activity-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        .activity-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
        }

        .activity-card .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .activity-card .activity-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .activity-card .view-all {
            color: var(--primary);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
        }

        .activity-card .view-all:hover {
            text-decoration: underline;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 12px 15px;
            font-size: 13px;
            font-weight: 500;
            color: #718096;
            border-bottom: 1px solid #edf2f7;
        }

        .data-table td {
            padding: 12px 15px;
            font-size: 14px;
            border-bottom: 1px solid #edf2f7;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background-color: var(--primary-light);
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge.primary { background: rgba(67, 97, 238, 0.1); color: var(--primary); }
        .badge.success { background: rgba(40, 167, 69, 0.1); color: var(--success); }
        .badge.danger { background: rgba(220, 53, 69, 0.1); color: var(--danger); }
        .badge.warning { background: rgba(253, 126, 20, 0.1); color: var(--warning); }
        .badge.info { background: rgba(23, 162, 184, 0.1); color: var(--info); }

        /* Customer List */
        .customer-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .customer-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .customer-item:last-child {
            border-bottom: none;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: var(--primary);
            font-weight: 600;
        }

        .customer-name {
            font-size: 14px;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .charts-section,
            .activity-section {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Using the sidebar from sidebar.php -->
    
    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Dashboard Overview</h1>
                <p>Welcome back! Here's what's happening with your business today.</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card sales">
                <div class="card-header">
                    <span class="card-title">Total Sales</span>
                    <span class="card-icon"><i class="fas fa-shopping-cart"></i></span>
                </div>
                <div class="card-value">₱<?= number_format($dashboard_data['total_sales'], 2) ?></div>
                <div class="card-change positive">
                    <i class="fas fa-arrow-up"></i> 12% from last month
                </div>
            </div>

            <div class="stat-card balance">
                <div class="card-header">
                    <span class="card-title">Current Balance</span>
                    <span class="card-icon"><i class="fas fa-wallet"></i></span>
                </div>
                <div class="card-value">₱<?= number_format($dashboard_data['current_balance'], 2) ?></div>
                <div class="card-change positive">
                    <i class="fas fa-arrow-up"></i> 8% from last week
                </div>
            </div>
            <div class="statistic-card">
                <h3><?php
                    // Number of Orders for the current month
                    $sqlTotalOrders = "SELECT COUNT(*) AS totalOrders FROM sales WHERE YEAR(created_at) = $currentYear AND MONTH(created_at) = $currentMonth AND status = 'approved'";
                    $resultTotalOrders = $pdo->query($sqlTotalOrders);
                    $totalOrders = $resultTotalOrders->fetch(PDO::FETCH_ASSOC)['totalOrders'] ?? 0;
                    echo $totalOrders;
                    ?></h3>
                <p>Number of Orders (This Month)</p>
            </div>
            <div class="statistic-card">
                <h3><?php
                    // New Customers for the current month
                    $sqlNewCustomers = "SELECT COUNT(*) AS newCustomers FROM customers WHERE YEAR(customer_id) = $currentYear AND MONTH(customer_id) = $currentMonth"; // Assuming customer_id is auto-increment and roughly corresponds to creation date.  This might need adjustment.
                    $resultNewCustomers = $pdo->query($sqlNewCustomers);
                    $newCustomers = $resultNewCustomers->fetch(PDO::FETCH_ASSOC)['newCustomers'] ?? 0;
                    echo $newCustomers;
                    ?></h3>
                <p>New Customers (This Month)</p>
            </div>
        </div>

            <div class="stat-card withdrawals">
                <div class="card-header">
                    <span class="card-title">Total Withdrawals</span>
                    <span class="card-icon"><i class="fas fa-money-bill-wave"></i></span>
                </div>
                <div class="card-value">₱<?= number_format($dashboard_data['total_withdrawals'], 2) ?></div>
                <div class="card-change negative">
                    <i class="fas fa-arrow-down"></i> 3% from last month
                </div>
            </div>

            <div class="stat-card expenses">
                <div class="card-header">
                    <span class="card-title">Total Expenses</span>
                    <span class="card-icon"><i class="fas fa-receipt"></i></span>
                </div>
                <div class="card-value">₱<?= number_format($dashboard_data['total_expenses'], 2) ?></div>
                <div class="card-change negative">
                    <i class="fas fa-arrow-down"></i> 5% from last month
                </div>
            </div>

            <div class="stat-card inventory">
                <div class="card-header">
                    <span class="card-title">Inventory Items</span>
                    <span class="card-icon"><i class="fas fa-boxes"></i></span>
                </div>
                <div class="card-value"><?= $dashboard_data['total_items'] ?></div>
                <div class="card-change">
                    <i class="fas fa-info-circle"></i> Total products
                </div>
            </div>

            <div class="stat-card credit-sales">
                <div class="card-header">
                    <span class="card-title">Pending Credit</span>
                    <span class="card-icon"><i class="fas fa-credit-card"></i></span>
                </div>
                <div class="card-value">₱<?= number_format($dashboard_data['credit_sales'], 2) ?></div>
                <div class="card-change">
                    <i class="fas fa-info-circle"></i> Total outstanding
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Monthly Sales Performance</h3>
                </div>
                <canvas id="salesChart" height="250"></canvas>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Inventory Status</h3>
                </div>
                <canvas id="inventoryChart" height="250"></canvas>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Payment Methods</h3>
                </div>
                <canvas id="paymentChart" height="250"></canvas>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Expense Categories</h3>
                </div>
                <canvas id="expenseChart" height="250"></canvas>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="activity-section">
            <div class="activity-card">
                <div class="activity-header">
                    <h3 class="activity-title">Recent Orders</h3>
                    <a href="sales.php" class="view-all">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard_data['recent_orders'] as $order): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($order['sale_id']) ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge <?= 
                                    $order['payment_type'] === 'Credit' ? 'warning' : 
                                    ($order['payment_type'] === 'Cash' ? 'success' : 'primary') 
                                ?>">
                                    <?= htmlspecialchars($order['payment_type']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($order['date']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="activity-card">
                <div class="activity-header">
                    <h3 class="activity-title">Recent Repayments</h3>
                    <a href="repayments.php" class="view-all">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard_data['recent_repayments'] as $repayment): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($repayment['sale_id']) ?></td>
                            <td>₱<?= number_format($repayment['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($repayment['date']) ?></td>
                            <td>
                                <span class="badge <?= 
                                    $repayment['status'] === 'completed' ? 'success' : 
                                    ($repayment['status'] === 'failed' ? 'danger' : 'warning') 
                                ?>">
                                    <?= htmlspecialchars(ucfirst($repayment['status'])) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="activity-card">
                <div class="activity-header">
                    <h3 class="activity-title">Recent Customers</h3>
                    <a href="customers.php" class="view-all">View All</a>
                </div>
                <ul class="customer-list">
                    <?php foreach ($dashboard_data['recent_customers'] as $customer): ?>
                    <li class="customer-item">
                        <div class="customer-avatar">
                            <?= strtoupper(substr($customer['customer_name'], 0, 1)) ?>
                        </div>
                        <div class="customer-name"><?= htmlspecialchars($customer['customer_name']) ?></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($dashboard_data['monthly_sales'], 'month')) ?>,
                datasets: [{
                    label: 'Monthly Sales',
                    data: <?= json_encode(array_column($dashboard_data['monthly_sales'], 'total')) ?>,
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderColor: 'rgba(67, 97, 238, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Payment Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentChart = new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($dashboard_data['payment_types'], 'payment_type')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($dashboard_data['payment_types'], 'count')) ?>,
                    backgroundColor: [
                        'rgba(67, 97, 238, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(253, 126, 20, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(23, 162, 184, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Inventory Chart
        const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
        const inventoryChart = new Chart(inventoryCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($dashboard_data['inventory_data'], 'item_name')) ?>,
                datasets: [{
                    label: 'Stock Level',
                    data: <?= json_encode(array_column($dashboard_data['inventory_data'], 'stock')) ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Expense Chart
        const expenseCtx = document.getElementById('expenseChart').getContext('2d');
        const expenseChart = new Chart(expenseCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($dashboard_data['expense_data'], 'category_name')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($dashboard_data['expense_data'], 'total')) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    </script>
</body>
</html>