<?php
session_start();
require 'sidebar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include database configuration
require 'config.php';

// Fetch financial data based on the actual database schema
$financial_data = [
    // Summary stats
    'total_sales' => $pdo->query("SELECT SUM(total_amount) FROM sales")->fetchColumn() ?? 0,
    'total_expenses' => $pdo->query("SELECT SUM(amount) FROM expenses")->fetchColumn() ?? 0,
    'total_balance' => $pdo->query("SELECT current_balance FROM balance LIMIT 1")->fetchColumn() ?? 0,
    'total_repayments' => $pdo->query("SELECT SUM(amount) FROM repayments WHERE status = 'completed'")->fetchColumn() ?? 0,
    
    // Sales breakdown
    'sales_breakdown' => $pdo->query("
        SELECT 
            payment_type,
            COUNT(*) as count,
            SUM(total_amount) as total
        FROM sales
        GROUP BY payment_type
    ")->fetchAll(PDO::FETCH_ASSOC),
    
    // Recent sales
    'recent_sales' => $pdo->query("
        SELECT 
            s.sale_id,
            s.customer_name,
            s.total_amount,
            s.payment_type,
            s.status,
            s.created_at,
            i.item_name
        FROM sales s
        LEFT JOIN items i ON s.item_id = i.item_id
        ORDER BY s.created_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC),
    
    // Recent repayments
    'recent_repayments' => $pdo->query("
        SELECT 
            r.repayment_id,
            r.amount,
            r.repayment_date,
            r.status,
            s.customer_name,
            s.sale_id
        FROM repayments r
        JOIN sales s ON r.sale_id = s.sale_id
        ORDER BY r.repayment_date DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC),
    
    // Expense categories
    'expense_categories' => $pdo->query("
        SELECT 
            ec.category_name,
            COUNT(e.expense_id) as count,
            SUM(e.amount) as total
        FROM expense_categories ec
        LEFT JOIN expenses e ON ec.category_id = e.category_id
        GROUP BY ec.category_id
    ")->fetchAll(PDO::FETCH_ASSOC),
    
    // Monthly sales
    'monthly_sales' => $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%b') AS month,
            SUM(total_amount) AS total
        FROM sales
        WHERE YEAR(created_at) = YEAR(CURRENT_DATE)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY MIN(created_at)
    ")->fetchAll(PDO::FETCH_ASSOC),
    
    // Top items
    'top_items' => $pdo->query("
        SELECT 
            i.item_name,
            SUM(s.quantity) as total_quantity,
            SUM(s.total_amount) as total_revenue
        FROM sales s
        JOIN items i ON s.item_id = i.item_id
        GROUP BY i.item_id
        ORDER BY total_revenue DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC)
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #e0e7ff;
            --success: #10b981;
            --success-light: #d1fae5;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --info: #0ea5e9;
            --info-light: #e0f2fe;
            --dark: #1f2937;
            --light: #f9fafb;
            --white: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 0;
        }

        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: var(--transition);
        }

        /* Dashboard Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .dashboard-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            background: linear-gradient(90deg, var(--primary), #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dashboard-title p {
            color: #64748b;
            margin: 5px 0 0;
            font-size: 14px;
        }

        /* Summary Cards */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .summary-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .summary-card .card-title {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-card .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--white);
        }

        .summary-card.sales .card-icon {
            background: var(--primary);
        }

        .summary-card.expenses .card-icon {
            background: var(--danger);
        }

        .summary-card.balance .card-icon {
            background: var(--success);
        }

        .summary-card.repayments .card-icon {
            background: var(--info);
        }

        .summary-card .card-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin: 5px 0;
        }

        .summary-card .card-footer {
            font-size: 12px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
        }

        .chart-container .chart-header {
            margin-bottom: 15px;
        }

        .chart-container .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        /* Tables Section */
        .tables-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .table-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
        }

        .table-container .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .table-container .table-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .table-container .view-all {
            color: var(--primary);
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
        }

        .table-container .view-all:hover {
            text-decoration: underline;
        }

        /* Custom Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table th {
            text-align: left;
            padding: 10px;
            font-weight: 600;
            color: #64748b;
            border-bottom: 2px solid #f1f5f9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background-color: var(--primary-light);
        }

        .amount-cell {
            font-weight: 600;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.pending {
            background: var(--warning-light);
            color: var(--warning);
        }

        .status-badge.approved {
            background: var(--success-light);
            color: var(--success);
        }

        .status-badge.completed {
            background: var(--info-light);
            color: var(--info);
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .charts-section,
            .tables-section {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Financial Dashboard</h1>
                <p>Overview of your business finances</p>
            </div>
            <div>
                <button class="btn-export">
                    <i class="fas fa-file-export"></i> Export Report
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="summary-grid">
            <div class="summary-card sales">
                <div class="card-header">
                    <div>
                        <div class="card-title">Total Sales</div>
                        <div class="card-value">ETB <?= number_format($financial_data['total_sales'], 2) ?></div>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <i class="fas fa-info-circle"></i> Includes all sales records
                </div>
            </div>

            <div class="summary-card expenses">
                <div class="card-header">
                    <div>
                        <div class="card-title">Total Expenses</div>
                        <div class="card-value">ETB <?= number_format($financial_data['total_expenses'], 2) ?></div>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <i class="fas fa-info-circle"></i> Across all categories
                </div>
            </div>

            <div class="summary-card balance">
                <div class="card-header">
                    <div>
                        <div class="card-title">Current Balance</div>
                        <div class="card-value">ETB <?= number_format($financial_data['total_balance'], 2) ?></div>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <i class="fas fa-info-circle"></i> Available funds
                </div>
            </div>

            <div class="summary-card repayments">
                <div class="card-header">
                    <div>
                        <div class="card-title">Total Repayments</div>
                        <div class="card-value">ETB <?= number_format($financial_data['total_repayments'], 2) ?></div>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                </div>
                <div class="card-footer">
                    <i class="fas fa-info-circle"></i> Completed repayments
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Monthly Sales</h3>
                </div>
                <canvas id="salesChart" height="250"></canvas>
            </div>

            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Expense Categories</h3>
                </div>
                <canvas id="expensesChart" height="250"></canvas>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="tables-section">
            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Recent Sales</h3>
                    <a href="sales.php" class="view-all">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Item</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($financial_data['recent_sales'] as $sale): ?>
                        <tr>
                            <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                            <td><?= htmlspecialchars($sale['item_name']) ?></td>
                            <td class="amount-cell">ETB <?= number_format($sale['total_amount'], 2) ?></td>
                            <td>
                                <span class="status-badge <?= strtolower($sale['status']) ?>">
                                    <?= htmlspecialchars($sale['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3 class="table-title">Recent Repayments</h3>
                    <a href="repayments.php" class="view-all">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($financial_data['recent_repayments'] as $repayment): ?>
                        <tr>
                            <td><?= htmlspecialchars($repayment['customer_name']) ?></td>
                            <td class="amount-cell">ETB <?= number_format($repayment['amount'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($repayment['repayment_date'])) ?></td>
                            <td>
                                <span class="status-badge <?= strtolower($repayment['status']) ?>">
                                    <?= htmlspecialchars($repayment['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                labels: <?= json_encode(array_column($financial_data['monthly_sales'], 'month')) ?>,
                datasets: [{
                    label: 'Monthly Sales',
                    data: <?= json_encode(array_column($financial_data['monthly_sales'], 'total')) ?>,
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 2,
                    tension: 0.3,
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
                        callbacks: {
                            label: function(context) {
                                return 'ETB ' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return 'ETB ' + value.toLocaleString();
                            }
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

        // Expenses Chart
        const expensesCtx = document.getElementById('expensesChart').getContext('2d');
        const expensesChart = new Chart(expensesCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($financial_data['expense_categories'], 'category_name')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($financial_data['expense_categories'], 'total')) ?>,
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(6, 182, 212, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'ETB ' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    </script>
</body>
</html>