<?php
session_start();
require 'storeman_sidebar.php'; 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'storeman') {
    exit();
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
$stmt = $pdo->prepare("SELECT COUNT(*) FROM items WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute();
$near_expiry_count = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storeman Dashboard</title>
    <link rel="stylesheet" href="style.css">
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

        .statistics {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card {
            background: var(--light-bg);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 30%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .numbers {
            font-weight: bold;
            font-size: 1.5em;
            color: var(--black1);
        }

        .cardName {
            color: var(--black2);
            font-size: 1em;
            margin-top: 5px;
        }

        .data-available {
            margin-top: 20px;
            font-size: 14px;
            color: var(--black2);
            text-align: center; /* Center text */
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Storeman Dashboard</h2>
        <div class="search-bar">
            <input type="text" placeholder="Search items...">
        </div>
        <div class="notification" onclick="toggleNotificationDropdown()">
            <i class="fa-solid fa-bell"></i>
            <div class="notification-dropdown" id="notificationDropdown">
                <p>Low Stock: <?php echo $low_stock_count; ?> items</p>
                <p>Near Expiry: <?php echo $near_expiry_count; ?> items</p>
            </div>
        </div>
    </div>

    <div class="content">
        <h1>Welcome, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest'; ?></h1>

        <div class="statistics">
            <div class="card">
                <div class="numbers"><?php echo $total_stock; ?></div>
                <div class="cardName">Total Stock Level</div>
            </div>
            <div class="card">
                <div class="numbers"><?php echo $low_stock_count; ?></div>
                <div class="cardName">Low Stock Alerts</div>
            </div>
            <div class="card">
                <div class="numbers"><?php echo $near_expiry_count; ?></div>
                <div class="cardName">Near Expiry Alerts</div>
            </div>
        </div>
        
        <p class="data-available">Data available for your review.</p>
    </div>

    <script>
        // Toggle notification dropdown
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('active');
        }
    </script>
</body>
</html>