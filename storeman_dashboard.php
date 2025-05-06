<?php
session_start();
require 'storeman_sidebar.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'storeman') {
    //exit();
}

// Include database configuration
require 'config.php';

// Fetch total stock levels from the items table
$stmt = $pdo->prepare("SELECT SUM(stock) FROM items");
$stmt->execute();
$total_stock = $stmt->fetchColumn();

// Fetch low stock alerts
$low_stock_threshold = 10;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE stock < ?");
$stmt->execute([$low_stock_threshold]);
$low_stock_count = $stmt->fetchColumn();

// Fetch near expiry items
$stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE expire_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute();
$near_expiry_count = $stmt->fetchColumn();

// Fetch recent inventory items
$stmt = $pdo->prepare("SELECT item_name, stock, unit_price, expire_date FROM items ORDER BY item_id DESC LIMIT 5");
$stmt->execute();
$recent_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories distribution
$stmt = $pdo->prepare("SELECT item_id, COUNT(*) as count FROM items GROUP BY item_id");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storeman Dashboard</title>
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
    <!-- Using the sidebar from storeman_sidebar.php -->
    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Dashboard Overview</h1>
                <p>Welcome back! Here's what's happening with your inventory today.</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card inventory">
                <div class="card-header">
                    <span class="card-title">Total Items in Stock</span>
                    <span class="card-icon"><i class="fas fa-boxes"></i></span>
                </div>
                <div class="card-value"><?php echo number_format($total_stock); ?></div>
                <div class="card-change">
                    <i class="fas fa-archive"></i> Updated daily
                </div>
            </div>

            <div class="stat-card warning">
                <div class="card-header">
                    <span class="card-title">Low Stock Items</span>
                    <span class="card-icon"><i class="fas fa-exclamation-triangle"></i></span>
                </div>
                <div class="card-value"><?php echo $low_stock_count; ?></div>
                <div class="card-change negative">
                    <i class="fas fa-arrow-down"></i> Restock needed
                </div>
            </div>

            <div class="stat-card danger">
                <div class="card-header">
                    <span class="card-title">Near Expiry Items</span>
                    <span class="card-icon"><i class="fas fa-clock"></i></span>
                </div>
                <div class="card-value"><?php echo $near_expiry_count; ?></div>
                <div class="card-change warning">
                    <i class="fas fa-exclamation-circle"></i> Check dates
                </div>
            </div>

            <div class="stat-card info">
                <div class="card-header">
                    <span class="card-title">New Orders</span>
                    <span class="card-icon"><i class="fas fa-shopping-cart"></i></span>
                </div>
                <div class="card-value">0</div>
                <div class="card-change positive">
                    <i class="fas fa-check-circle"></i> No pending orders
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Inventory Distribution</h3>
                </div>
                <canvas id="inventoryChart" height="250"></canvas>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">Stock Levels Over Time</h3>
                </div>
                <canvas id="stockLevelsChart" height="250"></canvas>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="activity-section">
            <div class="activity-card">
                <div class="activity-header">
                    <h3 class="activity-title">Recent Inventory Items</h3>
                    <a href="view_stock.php" class="view-all">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Expiry</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td>
                                <?php
                                $stock_class = '';
                                if ($item['stock'] < 5) {
                                    $stock_class = 'danger';
                                } elseif ($item['stock'] < $low_stock_threshold) {
                                    $stock_class = 'warning';
                                } else {
                                    $stock_class = 'success';
                                }
                                ?>
                                <span class="badge <?php echo $stock_class; ?>">
                                    <?php echo htmlspecialchars($item['stock']); ?>
                                </span>
                            </td>
                            <td>₱<?php echo number_format($item['unit_price'], 2); ?></td>
                            <td><?php echo $item['expire_date'] ? date('M d, Y', strtotime($item['expire_date'])) : 'N/A'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Inventory Chart
        const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
        const inventoryChart = new Chart(inventoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($categories, 'item_id')); ?>,
                datasets: [{
                    label: 'Items',
                    data: <?php echo json_encode(array_column($categories, 'count')); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
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

        // Stock Levels Chart (Dummy Data)
        const stockLevelsCtx = document.getElementById('stockLevelsChart').getContext('2d');
        const stockLevelsChart = new Chart(stockLevelsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Stock Levels',
                    data: [50, 60, 70, 80, 90, 80, 70, 60, 50, 40, 50, 60],
                    fill: false,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.1
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
    </script>
</body>
</html>