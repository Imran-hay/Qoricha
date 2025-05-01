<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
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
            background-color:rgb(194, 221, 223);
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
            align-items: center; /* Align icons and text */
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
        .icon-only i {
            margin-right: 10px; /* Add space between icon and text */
        }
        .submenu {
            display: none; /* Initially hide submenu */
            padding-left: 15px; /* Indent submenus */
            margin-top: 5px; /* Space above submenus */
            background-color: ; /* Match sidebar background */
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
        .icon-only span {
            display: inline; /* Show text */
        }
        .sidebar.hidden .icon-only span {
            display: none; /* Hide text when sidebar is collapsed */
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
            .sidebar ul li span {
                display: none; /* Hide text on small screens */
            }
        }
        /* Hamburger button styling */
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
        let showIconsOnly = false; // State to toggle between icons and text

        function toggleSubMenu(id) {
            const submenu = document.getElementById(id);
            // Toggle display style between block and none
            submenu.style.display = submenu.style.display === "block" ? "none" : "block";
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');

            sidebar.classList.toggle('hidden'); // Toggle sidebar visibility

            // Adjust content margin based on sidebar state
            if (sidebar.classList.contains('hidden')) {
                content.style.marginLeft = '0'; // No margin when sidebar is hidden
            } else {
                content.style.marginLeft = '250px'; // Adjust based on sidebar width
            }

            // Toggle between showing only icons and showing icons with text
            showIconsOnly = !showIconsOnly;
            const items = document.querySelectorAll('.sidebar ul li');
            items.forEach(item => {
                const text = item.querySelector('span');
                if (text) {
                    text.style.display = showIconsOnly ? 'none' : 'inline'; // Toggle text visibility
                }
            });
        }
    </script>
</head>
<body>
    <i class="fa-solid fa-bars hamburger-button" onclick="toggleSidebar()"></i>
    <div class="sidebar">
    <h2><img src="images/Qoricha logo.png" alt="Company Logo" style="max-width: 100%; height: auto;"></h2>
        <ul>
            <li class="icon-only">
                <a href="agent_dashboard.php">
                    <i class="fa-solid fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="icon-only">
                <button class="toggle-button" onclick="toggleSubMenu('manageSalesSubMenu')">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Manage Sales ▼</span>
                </button>
                <ul id="manageSalesSubMenu" class="submenu">
                    <li><a href="create_sale.php">Create New Sale</a></li>
                    <li><a href="pending_approvals.php">View Pending Approvals</a></li>
                </ul>
            </li>
            <li class="icon-only">
                <button class="toggle-button" onclick="toggleSubMenu('manageCustomersSubMenu')">
                    <i class="fa-solid fa-users"></i>
                    <span>Manage Customers ▼</span>
                </button>
                <ul id="manageCustomersSubMenu" class="submenu">
                    <li><a href="add_customer.php">Add New Customer</a></li>
                    <li><a href="view_customers.php">View Customer List</a></li>
                </ul>
            </li>
            <li class="icon-only">
                <button class="toggle-button" onclick="toggleSubMenu('hostedCustomersSubMenu')">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Hosted Customers ▼</span>
                </button>
                <ul id="hostedCustomersSubMenu" class="submenu">
                    <li><a href="todays_hosted.php">Today’s Hosted</a></li>
                    <li><a href="this_month_hosted.php">This Month’s Hosted</a></li>
                    <li><a href="all_hosted.php">View All Hosted</a></li>
                </ul>
            </li>
            <li class="icon-only">
                <button class="toggle-button" onclick="toggleSubMenu('reportsSubMenu')">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Reports ▼</span>
                </button>
                <ul id="reportsSubMenu" class="submenu">
                    <li><a href="item_list.php">Item List</a></li>
                    <li><a href="cash_sales.php">Cash Sales</a></li>
                    <li><a href="credit_sales.php">Credit Sales</a></li>
                    <li><a href="commission_earned.php">Commission Earned</a></li>
                </ul>
            </li>
            <li class="icon-only">
                <a href="view_bank.php">
                    <i class="fa-solid fa-building"></i>
                    <span>View Bank Account List</span>
                </a>
            </li>
            <li class="icon-only">
                <a href="logout.php">
                    <i class="fa-solid fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
   
</body>
</html>