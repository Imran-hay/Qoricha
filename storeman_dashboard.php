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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary-color: #0a888f;
            --secondary-color: #45d9e0;
            --accent-color: #ff7e5f;
            --light-bg: #f8f9fa;
            --dark-color: #2c3e50;
            --light-text: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --white: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            color: var(--dark-color);
            min-height: 100vh;
            margin-left:280px
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 2rem;
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .search-bar {
            position: relative;
        }
        
        .search-bar input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border-radius: 50px;
            border: none;
            width: 200px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .search-bar input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .search-bar input:focus {
            width: 250px;
            outline: none;
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        .search-bar i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
        }
        
        .notification {
            position: relative;
            cursor: pointer;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger-color);
            color: white;
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
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .notification-dropdown.active {
            display: block;
        }
        
        .notification-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 500;
        }
        
        .notification-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        
        .notification-icon.warning {
            background-color: var(--warning-color);
        }
        
        .notification-icon.danger {
            background-color: var(--danger-color);
        }
        
        .notification-content {
            flex-grow: 1;
        }
        
        .notification-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .notification-message {
            font-size: 0.85rem;
            color: var(--light-text);
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .welcome-section {
            margin-bottom: 2rem;
        }
        
        .welcome-section h1 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .welcome-section p {
            color: var(--light-text);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: var(--primary-color);
        }
        
        .stat-card.warning::before {
            background-color: var(--warning-color);
        }
        
        .stat-card.danger::before {
            background-color: var(--danger-color);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .stat-label {
            color: var(--light-text);
            font-size: 0.95rem;
        }
        
        .stat-icon {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 1.8rem;
            opacity: 0.2;
            color: var(--primary-color);
        }
        
        .stat-card.warning .stat-icon {
            color: var(--warning-color);
        }
        
        .stat-card.danger .stat-icon {
            color: var(--danger-color);
        }
        
        .data-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 992px) {
            .data-section {
                grid-template-columns: 1fr;
            }
        }
        
        .data-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .view-all {
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            transition: color 0.3s ease;
        }
        
        .view-all:hover {
            color: var(--primary-color);
        }
        
        .inventory-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .inventory-table th {
            text-align: left;
            padding: 0.75rem;
            background-color: rgba(10, 136, 143, 0.05);
            color: var(--light-text);
            font-weight: 500;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        
        .inventory-table td {
            padding: 0.75rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .inventory-table tr:last-child td {
            border-bottom: none;
        }
        
        .stock-indicator {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .stock-normal {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .stock-low {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .stock-critical {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
</head>
<body>
  <!--   <div class="header">
        <h2>Storeman Dashboard</h2>
        <div class="header-actions">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search items...">
            </div>
            <div class="notification" onclick="toggleNotificationDropdown()">
                <i class="fas fa-bell"></i>
                <?php if ($low_stock_count > 0 || $near_expiry_count > 0): ?>
                    <span class="notification-badge"><?php echo $low_stock_count + $near_expiry_count; ?></span>
                <?php endif; ?>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">Notifications</div>
                    <?php if ($low_stock_count > 0): ?>
                        <div class="notification-item">
                            <div class="notification-icon warning">
                                <i class="fas fa-exclamation"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">Low Stock Alert</div>
                                <div class="notification-message"><?php echo $low_stock_count; ?> items below threshold</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($near_expiry_count > 0): ?>
                        <div class="notification-item">
                            <div class="notification-icon danger">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">Expiry Warning</div>
                                <div class="notification-message"><?php echo $near_expiry_count; ?> items near expiry</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($low_stock_count == 0 && $near_expiry_count == 0): ?>
                        <div class="notification-item">
                            <div class="notification-content">
                                <div class="notification-message">No new notifications</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div> -->

    <div class="main-content">
        <div class="welcome-section">
            <h1>Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Store Manager'; ?></h1>
            <p>Here's what's happening with your inventory today</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($total_stock); ?></div>
                <div class="stat-label">Total Items in Stock</div>
                <i class="fas fa-boxes stat-icon"></i>
            </div>
            <div class="stat-card warning">
                <div class="stat-value"><?php echo $low_stock_count; ?></div>
                <div class="stat-label">Low Stock Items</div>
                <i class="fas fa-exclamation-triangle stat-icon"></i>
            </div>
            <div class="stat-card danger">
                <div class="stat-value"><?php echo $near_expiry_count; ?></div>
                <div class="stat-label">Near Expiry Items</div>
                <i class="fas fa-clock stat-icon"></i>
            </div>
        </div>

        <div class="data-section">
            <div class="data-card">
                <div class="section-header">
                    <h3 class="section-title">Recent Inventory Items</h3>
                    <a href="view_stock.php" class="view-all">
                        View All <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                <table class="inventory-table">
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
                                    $stock_class = 'stock-normal';
                                    if ($item['stock'] < 5) {
                                        $stock_class = 'stock-critical';
                                    } elseif ($item['stock'] < $low_stock_threshold) {
                                        $stock_class = 'stock-low';
                                    }
                                    ?>
                                    <span class="stock-indicator <?php echo $stock_class; ?>">
                                        <?php echo htmlspecialchars($item['stock']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td><?php echo $item['expire_date'] ? date('M d, Y', strtotime($item['expire_date'])) : 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent_items)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No recent items found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="data-card">
                <div class="section-header">
                    <h3 class="section-title">Inventory Distribution</h3>
                </div>
                <div class="chart-container">
                    <canvas id="inventoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Toggle notification dropdown
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('active');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('notificationDropdown');
            const notificationIcon = document.querySelector('.notification');
            
            if (!notificationIcon.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
        
        // Inventory chart
        const ctx = document.getElementById('inventoryChart').getContext('2d');
        const inventoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($categories, 'category')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($categories, 'count')); ?>,
                    backgroundColor: [
                        '#0a888f',
                        '#45d9e0',
                        '#ff7e5f',
                        '#2c3e50',
                        '#6c757d',
                        '#28a745'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw} items`;
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