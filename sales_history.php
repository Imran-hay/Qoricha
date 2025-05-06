<?php
session_start();




if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'storeman') {
    header("Location: login.php");
    //exit();
}
require 'config.php'; // Include your database connection settings
require 'storeman_sidebar.php'; // Include your sidebar for navigation





// Fetch approved sales history from the database
$stmt = $pdo->prepare("
    SELECT sale_id, item_name, quantity, created_at AS sale_date 
    FROM sales 
    JOIN items ON sales.item_id = items.item_id 
    WHERE sales.status IN ('approved', 'completed') 
    ORDER BY sale_date DESC
");
$stmt->execute();
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate summary statistics
$totalSales = count($sales);
$totalItemsSold = array_sum(array_column($sales, 'quantity'));
$uniqueItems = count(array_unique(array_column($sales, 'item_name')));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales History Dashboard | Inventory System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --success-color: #4361ee;
            --info-color: #0984e3;
            --warning-color: #fdcb6e;
            --danger-color: #d63031;
            --light-color: #f8f9fa;
            --dark-color: #2d3436;
            --border-radius: 12px;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f6fa;
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

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .dashboard-header h1 {
            color: var(--secondary-color);
            font-size: 28px;
            font-weight: 600;
            position: relative;
        }

        .dashboard-header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .export-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
        }

        .export-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3);
        }

        .date-filter {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--light-color);
            padding: 8px 15px;
            border-radius: 6px;
        }

        .date-filter i {
            color: var(--primary-color);
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
            padding: 25px;
            box-shadow: var(--box-shadow);
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            border-top: 4px solid var(--primary-color);
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .summary-card.total-sales {
            border-top-color: var(--primary-color);
        }

        .summary-card.items-sold {
            border-top-color: var(--success-color);
        }

        .summary-card.unique-items {
            border-top-color: var(--accent-color);
        }

        .summary-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .summary-card .value {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .summary-card .trend {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            color: var(--success-color);
        }

        .trend.up {
            color: var(--success-color);
        }

        .trend.down {
            color: var(--danger-color);
        }

        .table-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        .export-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .export-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 6px;
            z-index: 1;
            overflow: hidden;
        }
        
        .export-menu a {
            color: var(--dark-color);
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: var(--transition);
        }
        
        .export-menu a:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }
        
        .export-menu a i {
            margin-right: 8px;
            width: 18px;
            text-align: center;
        }
        
        .export-dropdown:hover .export-menu {
            display: block;
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
            background-color: rgba(108, 92, 231, 0.03);
        }

        tr:hover {
            background-color: rgba(108, 92, 231, 0.08);
            transition: var(--transition);
        }

        .sale-id {
            font-weight: 600;
            color: var(--primary-color);
        }

        .quantity {
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-block;
            min-width: 40px;
            text-align: center;
            background: rgba(0, 206, 201, 0.1);
            color: var(--accent-color);
        }

        .sale-date {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sale-date i {
            color: var(--primary-color);
            opacity: 0.7;
        }

        .no-sales {
            text-align: center;
            padding: 40px;
            color: #888;
            font-size: 16px;
        }

        .no-sales i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ddd;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 8px;
        }

        .pagination-btn {
            padding: 8px 15px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .pagination-btn:hover {
            background-color: #f0f0f0;
        }

        .pagination-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
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
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-actions {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
            }
            
            th, td {
                padding: 12px 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<div class="container">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <h1>Sales History Dashboard</h1>
                <div class="header-actions">
                    <div class="date-filter">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Last 30 Days</span>
                    </div>
                    <div class="export-dropdown">
                        <button class="export-btn">
                            <i class="fas fa-file-export"></i> Export Report
                        </button>
                        <div class="export-menu">
                            <a href="?export=csv"><i class="fas fa-file-csv"></i> CSV</a>
                            <a href="?export=excel"><i class="fas fa-file-excel"></i> Excel</a>
                            <a href="?export=pdf"><i class="fas fa-file-pdf"></i> PDF</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="summary-cards">
                <div class="summary-card total-sales">
                    <h3>Total Sales</h3>
                    <div class="value"><?php echo $totalSales; ?></div>
                    <div class="trend up">
                        <i class="fas fa-arrow-up"></i>
                      
                    </div>
                </div>
                <div class="summary-card items-sold">
                    <h3>Items Sold</h3>
                    <div class="value"><?php echo $totalItemsSold; ?></div>
                    <div class="trend up">
                        <i class="fas fa-arrow-up"></i>
                     
                    </div>
                </div>
                <div class="summary-card unique-items">
                    <h3>Unique Items</h3>
                    <div class="value"><?php echo $uniqueItems; ?></div>
                    <div class="trend down">
                        <i class="fas fa-arrow-down"></i>
                   
                    </div>
                </div>
            </div>

            <div class="table-container">
                <?php if (count($sales) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Sale Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td class="sale-id">#<?php echo htmlspecialchars($sale['sale_id']); ?></td>
                                    <td><?php echo htmlspecialchars($sale['item_name']); ?></td>
                                    <td><span class="quantity"><?php echo htmlspecialchars($sale['quantity']); ?></span></td>
                                    <td class="sale-date">
                                        <i class="far fa-calendar"></i>
                                        <?php echo date('M j, Y', strtotime($sale['sale_date'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="pagination">
                        <button class="pagination-btn">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="pagination-btn active">1</button>
                        <button class="pagination-btn">2</button>
                        <button class="pagination-btn">3</button>
                        <button class="pagination-btn">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                <?php else: ?>
                    <div class="no-sales">
                        <i class="fas fa-chart-line"></i>
                        <p>No sales records found</p>
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
                row.style.transition = `all 0.4s ease ${index * 0.05}s`;
                
                setTimeout(() => {
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, 100);
            });

            // Export button functionality
            document.querySelectorAll('.export-menu a').forEach(link => {
            link.addEventListener('click', function(e) {
                const exportType = this.textContent.trim();
                const exportBtn = document.querySelector('.export-btn');
                const originalHtml = exportBtn.innerHTML;
                
                exportBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Preparing ${exportType}...`;
                exportBtn.disabled = true;
                
                setTimeout(() => {
                    exportBtn.innerHTML = originalHtml;
                    exportBtn.disabled = false;
                }, 3000);
            });
        });
        });
    </script>
</body>
</html>