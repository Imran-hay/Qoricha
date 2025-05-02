<?php
session_start();
require 'agent_sidebar.php'; 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';

// Fetch user information (assuming users table exists with these fields)
$stmt = $pdo->prepare("SELECT user_id, fullname, email, role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Initialize variables to prevent undefined errors
$total_sales = 0;
$daily_sales_amount = 0;
$daily_quantity = 0;
$commissions_earned = 0;
$recent_orders = [];
$pending_approvals = [];

// Check if sales table exists and fetch statistics
$sales_table_exists = $pdo->query("SHOW TABLES LIKE 'sales'")->rowCount() > 0;

if ($sales_table_exists) {
    // Total sales count
    $total_sales_stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE user_id = ?");
    $total_sales_stmt->execute([$_SESSION['user_id']]);
    $total_sales = $total_sales_stmt->fetchColumn();

    // Daily sales amount (using total_amount field)
    $daily_sales_stmt = $pdo->prepare("
        SELECT IFNULL(SUM(total_amount), 0) 
        FROM sales 
        WHERE user_id = ? AND DATE(created_at) = CURDATE()
    ");
    $daily_sales_stmt->execute([$_SESSION['user_id']]);
    $daily_sales_amount = $daily_sales_stmt->fetchColumn();

    // Daily quantity sold
    $daily_quantity_stmt = $pdo->prepare("
        SELECT IFNULL(SUM(quantity), 0) 
        FROM sales 
        WHERE user_id = ? AND DATE(created_at) = CURDATE()
    ");
    $daily_quantity_stmt->execute([$_SESSION['user_id']]);
    $daily_quantity = $daily_quantity_stmt->fetchColumn();

    // Simple commission calculation (1% of total sales)
    $commissions_earned = $daily_sales_amount * 0.01;

    // Recent orders (simplified query based on existing fields)
    $recent_orders_stmt = $pdo->prepare("
        SELECT 
            sale_id, 
            customer_name, 
            quantity, 
            total_amount, 
            payment_type,
            created_at
        FROM sales 
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $recent_orders_stmt->execute([$_SESSION['user_id']]);
    $recent_orders = $recent_orders_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check if approvals table exists (though your SQL file shows it doesn't)
$approvals_table_exists = $pdo->query("SHOW TABLES LIKE 'approvals'")->rowCount() > 0;

// Dummy approvals data since table doesn't exist
$pending_approvals = [
    ['id' => 1, 'sale_id' => 1001, 'customer_name' => 'Sample Customer', 'total_amount' => 1500],
    ['id' => 2, 'sale_id' => 1002, 'customer_name' => 'Another Customer', 'total_amount' => 2500]
];

// Weekly sales data for chart (simulated since we don't have historical data)
$weekly_sales = [
    ['day' => 'Monday', 'amount' => 1500, 'quantity' => 5],
    ['day' => 'Tuesday', 'amount' => 2300, 'quantity' => 8],
    ['day' => 'Wednesday', 'amount' => 1800, 'quantity' => 6],
    ['day' => 'Thursday', 'amount' => 2100, 'quantity' => 7],
    ['day' => 'Friday', 'amount' => 3200, 'quantity' => 10],
    ['day' => 'Saturday', 'amount' => 2800, 'quantity' => 9],
    ['day' => 'Sunday', 'amount' => 1200, 'quantity' => 4]
];

// Payment type distribution (simulated if no data exists)
$payment_types = [
    ['payment_type' => 'cash', 'amount' => 7500, 'count' => 15],
    ['payment_type' => 'credit', 'amount' => 4500, 'count' => 9]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #0a888f;
            --primary-light: #e6f0ff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #fd7e0b;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
            --white: #ffffff;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            --transition: all 0.2s ease;
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
            padding: 20px;
            transition: var(--transition);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .dashboard-title h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .dashboard-title p {
            color: #64748b;
            margin: 5px 0 0;
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-bar input {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
            transition: var(--transition);
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .notification {
            position: relative;
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .action-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 15px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            text-align: center;
            text-decoration: none;
            color: var(--dark);
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .action-icon {
            font-size: 20px;
            margin-bottom: 8px;
            color: var(--primary);
        }

        .action-title {
            font-weight: 500;
            font-size: 14px;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 15px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        .stat-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .stat-card .card-title {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .stat-card .card-icon {
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            background: rgba(10, 136, 143, 0.1);
            color: var(--primary);
        }

        .stat-card .card-value {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            margin: 5px 0;
        }

        .stat-card .card-change {
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .chart-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 15px;
            box-shadow: var(--box-shadow);
        }

        .chart-container .chart-header {
            margin-bottom: 15px;
        }

        .chart-container .chart-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        /* Data Tables */
        .data-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 15px;
            box-shadow: var(--box-shadow);
            margin-bottom: 20px;
        }

        .data-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .data-card .card-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .data-table th {
            text-align: left;
            padding: 10px;
            font-weight: 600;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-badge.cash {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .status-badge.credit {
            background: rgba(253, 126, 20, 0.1);
            color: var(--warning);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .header-actions {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Welcome, <?= htmlspecialchars($user['fullname']) ?></h1>
                <p>Agent Dashboard Overview</p>
            </div>
            <div class="header-actions">
                <div class="search-bar">
                    <input type="text" placeholder="Search...">
                </div>
                <div class="notification">
                    <i class="fas fa-bell"></i>
                    <?php if (count($pending_approvals) > 0): ?>
                        <span class="notification-badge"><?= count($pending_approvals) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="create_sale.php" class="action-card">
                <div class="action-icon"><i class="fas fa-cash-register"></i></div>
                <div class="action-title">New Sale</div>
            </a>
            <a href="customers.php" class="action-card">
                <div class="action-icon"><i class="fas fa-users"></i></div>
                <div class="action-title">Customers</div>
            </a>
            <a href="reports.php" class="action-card">
                <div class="action-icon"><i class="fas fa-chart-pie"></i></div>
                <div class="action-title">Reports</div>
            </a>
            <a href="profile.php" class="action-card">
                <div class="action-icon"><i class="fas fa-user"></i></div>
                <div class="action-title">Profile</div>
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="card-header">
                    <div class="card-title">Today's Sales</div>
                    <div class="card-icon"><i class="fas fa-shopping-cart"></i></div>
                </div>
                <div class="card-value">₱<?= number_format($daily_sales_amount, 2) ?></div>
                <div class="card-change">
                    <i class="fas fa-box"></i> <?= $daily_quantity ?> items
                </div>
            </div>
            <div class="stat-card">
                <div class="card-header">
                    <div class="card-title">Total Sales</div>
                    <div class="card-icon"><i class="fas fa-chart-line"></i></div>
                </div>
                <div class="card-value"><?= $total_sales ?></div>
                <div class="card-change">
                    <i class="fas fa-history"></i> All time
                </div>
            </div>
            <div class="stat-card">
                <div class="card-header">
                    <div class="card-title">Commissions</div>
                    <div class="card-icon"><i class="fas fa-money-bill-wave"></i></div>
                </div>
                <div class="card-value">₱<?= number_format($commissions_earned, 2) ?></div>
                <div class="card-change">
                    <i class="fas fa-percent"></i> 1% of sales
                </div>
            </div>
            <div class="stat-card">
                <div class="card-header">
                    <div class="card-title">Pending</div>
                    <div class="card-icon"><i class="fas fa-clock"></i></div>
                </div>
                <div class="card-value"><?= count($pending_approvals) ?></div>
                <div class="card-change <?= count($pending_approvals) > 0 ? 'negative' : 'positive' ?>">
                    <?php if (count($pending_approvals) > 0): ?>
                        <i class="fas fa-exclamation-circle"></i> Needs action
                    <?php else: ?>
                        <i class="fas fa-check-circle"></i> All clear
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Weekly Sales</h3>
                </div>
                <canvas id="weeklySalesChart" height="200"></canvas>
            </div>
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Payment Methods</h3>
                </div>
                <canvas id="paymentMethodChart" height="200"></canvas>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="data-card">
            <div class="card-header">
                <h3 class="card-title">Recent Orders</h3>
                <a href="sales.php" class="view-all">View All</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Qty</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['customer_name']) ?></td>
                        <td><?= $order['quantity'] ?></td>
                        <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                        <td>
                            <span class="status-badge <?= $order['payment_type'] ?>">
                                <?= ucfirst($order['payment_type']) ?>
                            </span>
                        </td>
                        <td><?= date('M d', strtotime($order['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Weekly Sales Chart
        const weeklySalesCtx = document.getElementById('weeklySalesChart').getContext('2d');
        const weeklySalesChart = new Chart(weeklySalesCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($weekly_sales, 'day')) ?>,
                datasets: [{
                    label: 'Sales Amount',
                    data: <?= json_encode(array_column($weekly_sales, 'amount')) ?>,
                    backgroundColor: 'rgba(10, 136, 143, 0.7)',
                    borderColor: 'rgba(10, 136, 143, 1)',
                    borderWidth: 1
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

        // Payment Method Chart
        const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
        const paymentMethodChart = new Chart(paymentMethodCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($payment_types, 'payment_type')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($payment_types, 'amount')) ?>,
                    backgroundColor: [
                        'rgba(10, 136, 143, 0.7)',
                        'rgba(253, 126, 20, 0.7)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%'
            }
        });
    </script>
</body>
</html>