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
            padding: 20px;
            z-index: 100;
        }
        .sidebar.hidden {
           margin-left: -200px;
        }
        .sidebar h2 {
            text-align: left;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2); /* Light border */
            font-weight: 600;
            font-size: 1.75em;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 15px 0;
             /* Align icons and text */
        }
        .sidebar ul li a, .toggle-button {
            color: white;
            text-decoration: none;
            padding: 12px,15px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3;
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
        .icon-only i {
            margin-right: 10px; /* Add space between icon and text */
        }
        .sidebar ul li a i, .sidebar ul li button i {
            margin-right: 15px;
            font-size: 1.2em;
            width: 20px; /* Fixed width for icons */
            text-align: center;
        }
        .submenu {
            display: none; /* Initially hide submenu */
            padding-left: 0px; /* Indent submenus */
            margin-top: 10px; /* Space above submenus */
            background-color: rgba(0, 0, 0, 0.1) ;
            list-style: none;
            border-radius: 5px;
            overflow: hidden; /* Match sidebar background */
        }
        .submenu li {
            margin: 0; /* Space between submenu items */
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
        /*.content {
            margin-left: 270px; /* Account for sidebar width 
           // padding: 20px;
            flex-grow: 1;
            transition: margin-left 0.3s ease; 
        } */
        .content.shifted {
            margin-left: 0;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                margin-left: -280px;
            }
            .sidebar.hidden {
                transform: translateX(0);
                margin-left: 0;
            }
            .content {
                margin-left: 0;
            }
            .sidebar ul li span {
                display: none; /* Hide text on small screens */
            }
            .hamburger-button {
                left: 10px;
                top: 10px;
            }
        }
        /* Hamburger button styling */
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
            padding: 0%;
            transition: color 0.3s;
        }
        .hamburger-button:hover {
            color:rgb(8, 5, 11);
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
        <h2>Agent Panel</h2>
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
                <a href="view_banks.php">
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