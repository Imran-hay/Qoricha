<?php
session_start();
require 'agent_sidebar.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    //header("Location: login.php");
    exit();
}

// Include database configuration
require 'config.php';

// Fetch user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch statistics using agent_id
$total_sales_stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE agent_id = ?");
$total_sales_stmt->execute([$_SESSION['user_id']]);
$total_sales = $total_sales_stmt->fetchColumn();

$daily_sales_stmt = $pdo->prepare("SELECT SUM(quantity) FROM sales WHERE agent_id = ? AND DATE(sale_date) = CURDATE()");
$daily_sales_stmt->execute([$_SESSION['user_id']]);
$daily_sales = $daily_sales_stmt->fetchColumn();

// Calculate commissions based on sales
$commissions_earned = $daily_sales * 0.0001; // 0.01% of daily sales

$recent_orders_stmt = $pdo->prepare("SELECT * FROM sales WHERE agent_id = ? ORDER BY sale_date DESC LIMIT 5");
$recent_orders_stmt->execute([$_SESSION['user_id']]);
$recent_orders = $recent_orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch newly accepted approvals (dummy data for demonstration)
$new_approvals = ["Approval #1", "Approval #2", "Approval #3"]; // Replace with actual fetch logic
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .header h2 {
            flex-grow: 1; /* Allows the title to grow */
            text-align: center; /* Centers the title */
            margin: 0; /* Removes margin */
        }

        .search-bar {
            margin-right: 20px;
        }

        .search-bar input {
            width: 150px; /* Shortened width */
            padding: 8px;
            border-radius: 5px;
            border: none;
        }

        .notification {
            position: relative;
            display: flex;
            align-items: center;
        }

        .notification i {
            font-size: 24px;
            cursor: pointer;
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
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

        .notification-dropdown p {
            padding: 10px;
            margin: 0;
            border-bottom: 1px solid var(--border-color);
        }

        .content {
            padding: 20px;
            background: var(--white);
            box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
            border-radius: 20px;
            flex-grow: 1;
            margin-top: 20px; /* Space below header */
        }

        .quick-actions {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .quick-actions a {
            padding: 12px;
            border-radius: 10px;
            background-color: var(--light-bg);
            color: var(--black1);
            text-align: center;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s, transform 0.3s;
            flex: 1 1 calc(25% - 10px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .quick-actions a:hover {
            background-color: var(--dark-teal);
            transform: translateY(-2px);
            color: var(--white);
        }

        .statistics {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 20px;
            background-color: rgb(249, 249, 249);
            box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }

        .statistics h2 {
            flex: 1;
        }

        .statistics .icon {
            font-size: 24px;
            margin-right: 10px;
            color: var(--dark-teal);
        }

        .cardBox {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .card {
            background: var(--white);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 30%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
        }

        .numbers {
            font-weight: bold;
            font-size: 1.5em;
            color: var(--black1);
            display: flex;
            align-items: center;
        }

        .numbers ion-icon {
            margin-left: 10px;
            font-size: 1.5em;
        } 

        .cardName {
            color: var(--black2);
            font-size: 1em;
            margin-top: 5px;
            text-align: left;
        }

        .charts {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .chart-container {
            flex: 1;
            margin: 0 10px;
            background: var(--light-bg);
            border-radius: 10px;
            padding: 10px;
        }

        canvas {
            height: 150px; /* Adjusted height for better appearance */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            border: 1px solid var(--border-color);
            text-align: left;
        }

        th {
            background-color: #e9ecef;
            font-weight: 600;
        }

        .recent-orders table tr:hover {
            background: var(--blue);
            color: var(--white);
        }

        .recent-orders table tr td {
            padding: 12px 10px;
        }

        .status {
            padding: 2px 4px;
            color: var(--white);
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }

        .status.delivered {
            background: #8de02c;
        }

        .status.pending {
            background: #f9ca3f;
        }

        .status.return {
            background: #f00;
        }

        .status.inprogress {
            background: #1795ce;
        }

        .data-available {
            margin-top: 20px;
            font-size: 14px;
            color: var(--black2);
            text-align: center; /* Center text */
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                width: 100%;
            }

            .charts {
                flex-direction: column;
            }

            .chart-container {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Agent Dashboard</h2>
        <div class="search-bar">
            <input type="text" placeholder="Search...">
        </div>
        <div class="notification" onclick="toggleNotificationDropdown()">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-count"><?php echo count($new_approvals); ?></span>
            <div class="notification-dropdown" id="notificationDropdown">
                <?php foreach ($new_approvals as $approval): ?>
                    <p><?php echo htmlspecialchars($approval); ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="content">
        <h1>Welcome, <?php echo htmlspecialchars($user['fullname']); ?>!</h1>
        <p>Your role: <?php echo htmlspecialchars($user['role']); ?></p>

        <div class="quick-actions">
            <a href="create_sale.php">Create New Sale</a>
            <a href="pending_approvals.php">View Pending Approvals</a>
            <a href="manage_customers.php">Manage Customers</a>
            <a href="reports.php">View Reports</a>
        </div>

        <div class="statistics">
            <h2>Statistics Overview</h2>
            <div class="icon"><i class="fa-solid fa-chart-line"></i></div>
            <div class="cardBox">
                <div class="card">
                    <div class="numbers">
                        <i class="fa-solid fa-cash-register"></i>
                        <?php echo htmlspecialchars($daily_sales); ?>
                    </div>
                    <div class="cardName">Daily Sales</div>
                </div>
                <div class="card">
                    <div class="numbers">
                        <i class="fa-solid fa-shopping-cart"></i>
                        <?php echo htmlspecialchars($total_sales); ?>
                    </div>
                    <div class="cardName">Total Sales</div>
                </div>
                <div class="card">
                    <div class="numbers">
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <?php echo htmlspecialchars(number_format($commissions_earned, 2)); ?>
                    </div>
                    <div class="cardName">Commissions Earned</div>
                </div>
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
                <tr>
                    <th>Name</th>
                    <th>Payment Type</th>
                    <th>Payment Status</th>
                    <th>Delivery Status</th>
                </tr>
                <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['payment_type']); ?></td>
                    <td class="status <?php echo strtolower($order['payment_status']); ?>">
                        <?php echo htmlspecialchars($order['payment_status']); ?>
                    </td>
                    <td class="status <?php echo strtolower($order['delivery_status']); ?>">
                        <?php echo htmlspecialchars($order['delivery_status']); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <p class="data-available">Data available: <?php echo count($recent_orders); ?> orders found.</p>
        </div>
    </div>

    <script>
        // Toggle notification dropdown
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('active');
        }

        // Daily Sales Chart
        const dailySalesCtx = document.getElementById('dailySalesChart').getContext('2d');
        const dailySalesChart = new Chart(dailySalesCtx, {
            type: 'line',
            data: {
                labels: ['Today', 'Yesterday', '2 Days Ago'],
                datasets: [{
                    label: 'Daily Sales',
                    data: [<?php echo htmlspecialchars($daily_sales); ?>, 200, 150],
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