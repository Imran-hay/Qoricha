<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    //header("Location: login.php");
    //exit();
}

// Include database configuration
require 'config.php';

// Fetch user information
$stmt = $pdo->prepare("SELECT  fullname, email, role, address, phone, tin, joining_date, region, user_id FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch statistics using agent_id
$total_sales_stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE user_id = ?");
$total_sales_stmt->execute([$_SESSION['user_id']]);
$total_sales = $total_sales_stmt->fetchColumn();

$daily_sales_stmt = $pdo->prepare("SELECT SUM(quantity) FROM sales WHERE user_id = ? AND DATE(due_date) = CURDATE()");
$daily_sales_stmt->execute([$_SESSION['user_id']]);
$daily_sales = $daily_sales_stmt->fetchColumn();

// Calculate commissions based on sales
$commissions_earned = $daily_sales * 0.0001; // 0.01% of daily sales

$recent_orders_stmt = $pdo->prepare("SELECT * FROM sales WHERE user_id = ? ORDER BY due_date DESC LIMIT 5");
$recent_orders_stmt->execute([$_SESSION['user_id']]);
$recent_orders = $recent_orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch newly accepted approvals (dummy data for demonstration)
$new_approvals = ["Approval #1", "Approval #2", "Approval #3"]; // Replace with actual fetch logic

// Ensure all values are strings (handle NULLs)
$new_approvals = array_map(function($value) {
    return (string) $value; // Cast to string, NULL will become an empty string
}, $new_approvals);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9; /* Light gray background */
            color: #343a40; /* Dark gray text */
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 240px; /* Adjust based on sidebar width */
            transition: margin-left 0.3s ease;
        }

        .content.shifted {
            margin-left: 0;
        }

        /* Header Styles */
        .header {
            background: #fff;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .header h1 {
            font-size: 1.75em;
            color: #333;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info i {
            margin-left: 10px;
            font-size: 1.5em;
            color: #764ba2;
            cursor: pointer;
        }

        /* Quick Actions Styles */
        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .quick-actions a {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s;
            flex: 1 1 200px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .quick-actions a:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        /* Statistics Styles */
        .statistics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .statistic-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .statistic-card h3 {
            font-size: 1.5em;
            margin: 0 0 10px;
            color: #555;
        }

        .statistic-card p {
            font-size: 1.1em;
            color: #777;
            margin: 0;
        }
        .charts {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .chart-container {
            flex: 1;
            margin: 0 10px;
            background: #fff;
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        canvas {
            height: 200px; /* Adjusted height for better appearance */
        }

        /* Recent Orders Styles */
        .recent-orders {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .recent-orders h2 {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #555;
        }

        .recent-orders table {
            width: 100%;
            border-collapse: collapse;
        }

        .recent-orders th, .recent-orders td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .recent-orders th {
            background-color: #f9f9f9;
            font-weight: 600;
        }

        .recent-orders tr:hover {
            background-color: #f5f5f5;
        }

        .status {
            padding: 5px 8px;
            border-radius: 5px;
            font-size: 0.9em;
            color: #fff;
        }

        .status.delivered {
            background-color: #28a745; /* Green */
        }

        .status.pending {
            background-color: #ffc107; /* Yellow */
            color: #333;
        }

        .status.return {
            background-color: #dc3545; /* Red */
        }

        .status.inprogress {
            background-color: #007bff; /* Blue */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
            }
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            .header h1 {
                margin-bottom: 10px;
            }
            .quick-actions {
                flex-direction: column;
            }
        }

        /* Sidebar Styles (moved from sidebar.php) */
        .sidebar {
            width: 240px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
            overflow-y: auto;
            z-index: 100;
        }

        .sidebar.hidden {
            margin-left: -240px;
        }

        .sidebar h2 {
            text-align: left;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 600;
            font-size: 1.75em;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
        }

        .sidebar ul li a, .toggle-button {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3s;
            background-color: transparent;
            border: none;
            width: 100%;
            cursor: pointer;
            text-align: left;
            font-size: 1em;
        }

        .sidebar ul li a:hover, .toggle-button:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #d4e157;
        }

        .sidebar ul li a i, .sidebar ul li button i {
            margin-right: 15px;
            font-size: 1.2em;
            width: 20px;
            text-align: center;
        }

        .submenu {
            display: none;
            padding-left: 0px;
            margin-top: 10px;
            background-color: rgba(0, 0, 0, 0.1);
            list-style: none;
            border-radius: 5px;
            overflow: hidden;
        }

        .submenu li {
            margin: 0;
        }

        .submenu li a {
            padding: 10px 20px;
            display: block;
            color: #ddd;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
            border-radius: 0;
        }

        .submenu li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #d4e157;
        }

        /* Hamburger Button */
        .hamburger-button {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 101;
            color: black;
            font-size: 2em;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            transition: color 0.3s;
        }

        .hamburger-button:hover {
            color: rgb(8, 5, 11);
        }
    </style>
    <script>
        let showIconsOnly = false; // State to toggle between icons and text

        function toggleSubMenu(id) {
            const submenu = document.getElementById(id);
            submenu.style.display = submenu.style.display === "block" ? "none" : "block";
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');

            sidebar.classList.toggle('hidden');

            if (sidebar.classList.contains('hidden')) {
                content.classList.add('shifted');
            } else {
                content.classList.remove('shifted');
            }
        }
    </script>
</head>
<body>
    <?php include 'agent_sidebar.php'; ?>
    <div class="content">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars($user['fullname'] ?? 'Agent'); ?>!</h1>
            <div class="user-info">
                <i class="fa-solid fa-bell"></i>
            </div>
        </div>

        <div class="quick-actions">
            <a href="create_sale.php">Create New Sale</a>
            <a href="pending_approvals.php">View Pending Approvals</a>
            <a href="manage_customers.php">Manage Customers</a>
            <a href="reports.php">View Reports</a>
        </div>

        <div class="statistics">
            <div class="statistic-card">
                <h3><?php echo htmlspecialchars((string)$daily_sales ?? '0'); ?></h3>
                <p>Daily Sales</p>
            </div>
            <div class="statistic-card">
                <h3><?php echo htmlspecialchars((string)$total_sales ?? '0'); ?></h3>
                <p>Total Sales</p>
            </div>
            <div class="statistic-card">
                <h3><?php echo htmlspecialchars(number_format($commissions_earned, 2) ?? '0.00'); ?></h3>
                <p>Commissions Earned</p>
            </div>
        </div>

        <div class="charts">
            <div class="chart-container">
                <h2>Daily Sales</h2>
                <canvas id="dailySalesChart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Payment Types</h2>
                <canvas id="paymentTypeChart"></canvas>
            </div>
        </div>

        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Payment Type</th>
                        <th>Payment Status</th>
                        <th>Delivery Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['customer_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($order['payment_type'] ?? ''); ?></td>
                        <td class="status <?php echo strtolower($order['payment_status'] ?? 'pending'); ?>">
                            <?php echo htmlspecialchars($order['payment_status'] ?? 'Pending'); ?>
                        </td>
                        <td class="status <?php echo strtolower($order['delivery_status'] ?? 'pending'); ?>">
                            <?php echo htmlspecialchars($order['delivery_status'] ?? 'Pending'); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <i class="fa-solid fa-bars hamburger-button" onclick="toggleSidebar()"></i>

    <script>
        // Daily Sales Chart
        const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
        const dailySalesChart = new Chart(dailySalesCtx, {
            type: 'line',
            data: {
                labels: ['Today', 'Yesterday', '2 Days Ago'],
                datasets: [{
                    label: 'Daily Sales',
                    data: [<?php echo htmlspecialchars((string)$daily_sales ?? '0'); ?>, 200, 150],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Sales Amount'
                        }
                    }
                }
            }
        });

        // Payment Type Chart
        const paymentTypeCtx = document.getElementById('paymentTypeChart').getContext('2d');
        const paymentTypeChart = new Chart(paymentTypeCtx, {
            type: 'pie',
            data: {
                labels: ['Cash', 'Credit'],
                datasets: [{
                    label: 'Payment Types',
                    data: [300, 150], // Replace with actual data
                    backgroundColor: [
                        'rgb(23, 64, 91)',
                        'rgb(174, 69, 97)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Payment Types Breakdown'
                    }
                }
            }
        });
    </script>
</body>
</html>