<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            background-color: #45d9e0;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 15px 0;
            display: flex;
            flex-direction: column; /* This ensures that items are displayed vertically */
            align-items: flex-start;
        }
        .sidebar ul li a, .toggle-button {
            color: white;
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
            display: none;
            padding-left: 15px;
            margin-top: 10px; /* Add some space above submenus */
        }
        .submenu li {
            margin: 5px 0;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            flex-grow: 1;
            transition: margin-left 0.3s ease;
        }
        .content.shifted {
            margin-left: 0;
        }
        .icon-only span {
            display: inline;
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

        /* Add styles for hamburger button positioning outside the sidebar */
        .hamburger-button {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 1000;
            color: black;
            font-size: 30px;
            cursor: pointer;
        }

        /* Sidebar toggle animation */
        .sidebar.open {
            transform: translateX(0);
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
            sidebar.classList.toggle('hidden'); // This hides the sidebar when clicked

            // Toggle between showing only icons and showing icons with text
            showIconsOnly = !showIconsOnly;
            const items = document.querySelectorAll('.sidebar ul li');
            items.forEach(item => {
                const text = item.querySelector('span');
                if (text) {
                    if (showIconsOnly) {
                        text.style.display = 'none'; // Hide text
                    } else {
                        text.style.display = 'inline'; // Show text
                    }
                }
            });
        }
    </script>
</head>
<body>
    <i class="fa-solid fa-bars hamburger-button" onclick="toggleSidebar()"></i>
    <div class="sidebar">
        <h2>
            Admin Dashboard
        </h2>
        <ul>
            <li class="icon-only">
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="icon-only">
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Approvals</span>
                </a>
            </li>
            <li class="icon-only">
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-university"></i>
                    <span>Manage Bank</span>
                </a>
            </li>
            <li class="icon-only">
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>Manage Finance</span>
                </a>
            </li>
            <li class="icon-only">
                <button class="toggle-button" onclick="toggleSubMenu('manageItemsSubMenu')">
                    <i class="fa-solid fa-box"></i>
                    <span>Manage Items ▼</span>
                </button>
                <ul id="manageItemsSubMenu" class="submenu">
                    <li><a href="create_item.php">Add Item</a></li>
                    <li><a href="view_items.php">View Items</a></li>
                    <li><a href="view_items.php">Add Items To Stock</a></li>
                    <li><a href="view_items.php">View Stock List</a></li>
                </ul>
            </li>
            <li class="icon-only">
                <button class="toggle-button" onclick="toggleSubMenu('expensesSubMenu')">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    <span>Manage Expenses ▼</span>
                </button>
                <ul id="expensesSubMenu" class="submenu">
                    <li><a href="add_item.php">Add Expenses</a></li>
                    <li><a href="view_items.php">View Expenses</a></li>
                    <li><a href="view_items.php">Add Expenses Categories</a></li>
                    <li><a href="view_items.php">View Category List</a></li>
                </ul>
            </li>
            <li class="icon-only">
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Manage Customer</span>
                </a>
            </li>
            <li class="icon-only">
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-user-friends"></i>
                    <span>Manage Supplier</span>
                </a>
            </li>
            <li class="icon-only">
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-user-tag"></i>
                    <span>Hosted Customer</span>
                </a>
            </li>
            <li class="icon-only">
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-book"></i>
                    <span>Manage Bincard</span>
                </a>
            </li>
            <li class="icon-only">
                <button class="toggle-button" onclick="toggleSubMenu('reportsSubMenu')">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Reports ▼</span>
                </button>
                <ul id="reportsSubMenu" class="submenu">
                    <li><a href="add_item.php">Stock List</a></li>
                    <li><a href="view_items.php">Cash Sales</a></li>
                    <li><a href="view_items.php">Credit Sales</a></li>
                    <li><a href="view_items.php">Agent Cash Sales</a></li>
                    <li><a href="view_items.php">Commission Calculator</a></li>
                    <li><a href="view_items.php">Agent Credit Sales</a></li>
                    <li><a href="view_items.php">Financial Reports</a></li>
                    <li><a href="view_items.php">Outdated Items</a></li>
                    <li><a href="view_items.php">Credit Customer Report</a></li>
                </ul>
            </li>
            <li class="icon-only">
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Check Availability System</span>
                </a>
            </li>
            <li class="icon-only">
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-cog"></i>
                    <span>Account Setting</span>
                </a>
            </li>
            <li class="icon-only">
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-headset"></i>
                    <span>Support System</span>
                </a>
            </li>
        </ul>
    </div>
  
</body>
</html>
