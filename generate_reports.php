<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'storeman') {
    /*header("Location: login.php");
    exit()*/;
}
require 'config.php'; // Include your database connection settings
require 'storeman_sidebar.php'; // Include your sidebar for navigation

// Fetch stock levels from the database
$stmt = $pdo->prepare("SELECT item_id, item_name, stock FROM items");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Level Report | Inventory System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #4361ee;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #2c3e50;
            --border-radius: 10px;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }

        .container {
            margin-left: 250px;
            padding: 30px;
            transition: var(--transition);
        }

        .dashboard-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            color: var(--secondary-color);
            font-size: 28px;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }

        .header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
        }

        .report-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .report-date {
            background: var(--light-color);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .print-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: var(--transition);
        }

        .print-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
        }

        tr:nth-child(even) {
            background-color: rgba(67, 97, 238, 0.03);
        }

        tr:hover {
            background-color: rgba(67, 97, 238, 0.08);
            transition: var(--transition);
        }

        .stock-level {
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }

        .stock-low {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .stock-medium {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning-color);
        }

        .stock-high {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
        }

        .no-items {
            text-align: center;
            padding: 40px;
            color: #888;
            font-size: 16px;
        }

        .no-items i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ddd;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: var(--transition);
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-card i {
            font-size: 30px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .summary-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .summary-card .value {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-color);
        }

        @media (max-width: 992px) {
            .container {
                margin-left: 0;
                padding: 20px;
            }
            
            .summary-cards {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            th, td {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-card">
            <div class="header">
                <h1>Stock Level Report</h1>
                <div class="report-info">
                    <span class="report-date">
                        <i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y'); ?>
                    </span>
                    <button class="print-btn" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>

            <div class="summary-cards">
                <div class="summary-card">
                    <i class="fas fa-boxes"></i>
                    <h3>Total Items</h3>
                    <div class="value"><?php echo count($items); ?></div>
                </div>
                <div class="summary-card">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Low Stock Items</h3>
                    <div class="value">
                        <?php 
                            $lowStockCount = 0;
                            foreach ($items as $item) {
                                if ($item['stock'] < 10) $lowStockCount++;
                            }
                            echo $lowStockCount;
                        ?>
                    </div>
                </div>
                <div class="summary-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>In Stock Items</h3>
                    <div class="value">
                        <?php 
                            $inStockCount = 0;
                            foreach ($items as $item) {
                                if ($item['stock'] > 0) $inStockCount++;
                            }
                            echo $inStockCount;
                        ?>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <?php if (count($items) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Item Name</th>
                                <th>Current Stock Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): 
                                // Determine stock status class
                                $stockClass = '';
                                if ($item['stock'] < 5) {
                                    $stockClass = 'stock-low';
                                } elseif ($item['stock'] < 15) {
                                    $stockClass = 'stock-medium';
                                } else {
                                    $stockClass = 'stock-high';
                                }
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                    <td>
                                        <span class="stock-level <?php echo $stockClass; ?>">
                                            <?php echo htmlspecialchars($item['stock']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-items">
                        <i class="fas fa-box-open"></i>
                        <p>No items found in the inventory</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Add animation to table rows
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                row.style.transition = `all 0.3s ease ${index * 0.05}s`;
                
                setTimeout(() => {
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, 100);
            });
        });
    </script>
</body>
</html>