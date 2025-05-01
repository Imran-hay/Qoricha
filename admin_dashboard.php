<?php
session_start();
require 'sidebar.php'; 

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css"> <!-- Link to your CSS file -->
</head>
<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="icon"><ion-icon name="logo-apple"></ion-icon></span>
                        <span class="title" style="font-size: 1.5em; font-weight: 500">Qoricha</span>
                    </a>
                </li>
                <li class="hovered">
                    <a href="admin_dashboard.php"> <!-- Link to the current dashboard -->
                        <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                <!-- Add other navigation items as needed -->
                <li>
                    <a href="logout.php">
                        <span class="icon"><ion-icon name="log-out-outline"></ion-icon></span>
                        <span class="title">Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                <div class="search">
                    <label>
                        <input type="text" placeholder="Search here" />
                        <ion-icon name="search-outline"></ion-icon>
                    </label>
                </div>
                <div class="user">
                    <img src="user.jpg" alt="User Image" />
                </div>
            </div>

            <h1>Admin Dashboard</h1>
            <div class="cardBox">
                <!-- Add your cards here -->
            </div>

            <div class="graphBox">
                <div class="box">
                    <canvas id="myChart"></canvas>
                </div>
                <div class="box">
                    <canvas id="earning"></canvas>
                </div>
            </div>

            <div class="details">
                <div class="recentOrders">
                    <h2>Recent Orders</h2>
                    <!-- Add order details here -->
                </div>
                <div class="recentCustomers">
                    <h2>Recent Customers</h2>
                    <!-- Add customer details here -->
                </div>
            </div>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
    <script src="my_chart.js"></script>
    <script>
        // Chart.js initialization
        var ctx = document.getElementById("myChart").getContext("2d");
        var earning = document.getElementById("earning").getContext("2d");

        // Polar Area Chart
        var trafficChart = new Chart(ctx, {
            type: "polarArea",
            data: {
                labels: ["Facebook", "Youtube", "Shopee"],
                datasets: [{
                    label: "Traffic Source",
                    data: [1100, 1500, 2205],
                    backgroundColor: [
                        "rgba(255, 99, 132, 1)",
                        "rgba(54, 162, 235, 1)",
                        "rgba(255, 206, 86, 1)",
                    ],
                }],
            },
            options: {
                responsive: true,
            },
        });

        // Bar Chart
        var earningChart = new Chart(earning, {
            type: "bar",
            data: {
                labels: [
                    "January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                ],
                datasets: [{
                    label: "Earning",
                    data: [
                        4500, 4106, 7005, 6754, 5154, 4554,
                        7815, 3152, 12204, 4457, 8740, 11000
                    ],
                    backgroundColor: [
                        "rgba(255, 99, 132, 1)",
                        "rgba(54, 162, 235, 1)",
                        "rgba(255, 206, 86, 1)",
                        "rgba(75, 192, 192, 1)",
                        "rgba(153, 102, 255, 1)",
                        "rgba(255, 159, 64, 1)",
                        "rgba(255, 99, 132, 1)",
                        "rgba(54, 162, 235, 1)",
                        "rgba(255, 206, 86, 1)",
                        "rgba(75, 192, 192, 1)",
                        "rgba(153, 102, 255, 1)",
                        "rgba(255, 159, 64, 1)",
                    ],
                }],
            },
            options: {
                responsive: true,
            },
        });
    </script>
</body>
</html>