<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storeman Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: rgb(194, 221, 223);
            height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            position: fixed;
            left: 0;
            top: 0;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        .sidebar.hidden {
            transform: translateX(-100%);
        }
        .sidebar h2 {
            color: white;
            text-align: center;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 15px 0;
            align-items: center;
        }
        .sidebar ul li a, .toggle-button {
            color: black;
            text-decoration: none;
            padding: 10px;
            display: flex;
            align-items: center;
            border-radius: 4px;
            transition: background 0.3s;
            background-color: transparent;
            border: none;
            width: 100%;
            cursor: pointer;
        }
        .sidebar ul li a:hover, .toggle-button:hover {
            background-color: #0a888f;
        }
        .submenu {
            display: none; /* Initially hide submenu */
            padding-left: 15px; /* Indent submenus */
            margin-top: 5px; /* Space above submenus */
            background-color: rgb(194, 221, 223); /* Match sidebar background */
        }
        .submenu li {
            margin: 5px 0; /* Space between submenu items */
        }
        .content {
            margin-left: 270px; /* Account for sidebar width */
            padding: 20px;
            flex-grow: 1;
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.hidden {
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
            }
        }
        .hamburger-button {
            position: fixed;
            left: 5px;
            top: 20px;
            z-index: 1000;
            color: black;
            font-size: 30px;
            cursor: pointer;
        }
    </style>
    <script>
        function toggleSubMenu(id) {
            const submenu = document.getElementById(id);
            submenu.style.display = submenu.style.display === "block" ? "none" : "block";
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');

            sidebar.classList.toggle('hidden'); // Toggle sidebar visibility

            // Adjust content margin based on sidebar state
            content.style.marginLeft = sidebar.classList.contains('hidden') ? '0' : '250px'; // Adjust based on sidebar width
        }
    </script>
</head>
<body>
    <i class="fa-solid fa-bars hamburger-button" onclick="toggleSidebar()"></i>
    <div class="sidebar">
        <h2>Storeman Dashboard</h2>
        <ul>
            <li>
                <a href="storeman_dashboard.php">
                    <i class="fa-solid fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <button class="toggle-button" onclick="toggleSubMenu('manageInventorySubMenu')">
                    <i class="fa-solid fa-box"></i>
                    <span>Manage Inventory ▼</span>
                </button>
                <ul id="manageInventorySubMenu" class="submenu">
                    <li><a href="add_stock.php">Add Stock</a></li>
                    <li><a href="view_stock.php">View Stock</a></li>
                    <li><a href="add_item.php">Add item</a></li> <!-- New Add Item link -->
                </ul>
            </li>
            <li>
                <a href="receive_stock.php">
                    <i class="fa-solid fa-arrow-down"></i>
                    <span>Receive Stock</span>
                </a>
            </li>
            <li>
                <button class="toggle-button" onclick="toggleSubMenu('stockAdjustmentsSubMenu')">
                    <i class="fa-solid fa-tools"></i>
                    <span>Stock Adjustments ▼</span>
                </button>
                <ul id="stockAdjustmentsSubMenu" class="submenu">
                    <li><a href="stock_adjustments.php">Adjust Stock Levels</a></li>
                </ul>
            </li>
            <li>
                <button class="toggle-button" onclick="toggleSubMenu('reportsSubMenu')">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Generate Reports ▼</span>
                </button>
                <ul id="reportsSubMenu" class="submenu">
                    <li><a href="generate_reports.php">Stock Levels</a></li>
                    <li><a href="sales_history.php">Sales History</a></li>
                </ul>
            </li>
            <li>
                <a href="user_management.php">
                    <i class="fa-solid fa-users-cog"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
    
</body>
</html>