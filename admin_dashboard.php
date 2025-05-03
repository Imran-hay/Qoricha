<?php
session_start();
require 'sidebar.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    //header("Location: login.php");
    //exit();
}

require 'config.php'; // Database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css"> <!-- Link to your CSS file -->
    <style>
        

        .content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 110px; /* Adjust based on sidebar width */
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

       </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
   
    <div class="content">
        <div class="header">
            <h1>Welcome, <?php echo htmlspecialchars('Admin'); ?>!</h1>
            <div class="user-info">
                <i class="fa-solid fa-bell"></i>
            </div>
        </div>

        <div class="quick-actions">
            <a href="approvals.php">Manage Approvals</a>
            <a href="account_settings.php">Manage Users</a>
            <a href="view_items.php">Manage Items</a>
            <a href="reports.php">View Reports</a>
        </div>

        <div class="statistics">
            <div class="statistic-card">
                <h3><?php
                    // Calculate KPIs
                    $currentYear = date('Y');
                    $currentMonth = date('m');

                    // Total Revenue for the current month
                    $sqlTotalRevenue = "SELECT SUM(total_amount) AS totalRevenue FROM sales WHERE YEAR(created_at) = $currentYear AND MONTH(created_at) = $currentMonth AND status = 'approved'";
                    $resultTotalRevenue = $pdo->query($sqlTotalRevenue);
                    $totalRevenue = $resultTotalRevenue->fetch(PDO::FETCH_ASSOC)['totalRevenue'] ?? 0;
                    echo number_format($totalRevenue, 2);
                    ?> ETB</h3>
                <p>Total Revenue (This Month)</p>
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

        <div class="charts">
            <div class="chart-container">
                <h2>Daily Sales</h2>
                <canvas id="dailySalesChart"></canvas>
            </div>
            <div class="chart-container">
                <h2>Monthly Sales</h2>
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>

        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Item ID</th>
                        <th>Total Amount</th>
                        <th>Payment Type</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch Recent Orders
                    $sqlRecentOrders = "SELECT sale_id, customer_name, item_id, total_amount, payment_type, created_at, status FROM sales ORDER BY created_at DESC LIMIT 5";
                    $resultRecentOrders = $pdo->query($sqlRecentOrders);

                    while ($row = $resultRecentOrders->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['sale_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['customer_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['item_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['total_amount']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['payment_type']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "</tr>";
                    }
                    ?>
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
                    data: [100, 200, 150], // Replace with actual data
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

        // Monthly Sales Chart
        const monthlySalesCtx = document.getElementById('monthlySalesChart').getContext('2d');
        const monthlySalesChart = new Chart(monthlySalesCtx, {
            type: 'bar',
            data: {
                labels: ['January', 'February', 'March', 'April', 'May'],
                datasets: [{
                    label: 'Monthly Sales',
                    data: [500, 600, 750, 800, 900], // Replace with actual data
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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
    </script>
</body>
</html>