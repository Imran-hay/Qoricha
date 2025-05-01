<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9; /* Light gray background */
            color: #343a40; /* Dark gray text */
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px; /* Increased width */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Gradient background */
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.15); /* Enhanced shadow */
            transition: all 0.3s ease;
            overflow-y: auto;
            padding: 20px;
            z-index: 100;
        }

        .sidebar.hidden {
            margin-left: -280px;
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
        }
        .header{
            font-size: 1.5em;
            color: #fff; /* White text for header */
            margin-top: 30px;
            text-align: center;
        }

        .sidebar ul li a, .sidebar ul li button {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            transition: background-color 0.3s, color 0.3s;
            border: none;
            background-color: transparent;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-size: 1em;
        }

        .sidebar ul li a:hover, .sidebar ul li button:hover {
            background-color: rgba(255, 255, 255, 0.1); /* Lighter background on hover */
            color: #d4e157; /* Accent color on hover */
        }

        .sidebar ul li a i, .sidebar ul li button i {
            margin-right: 15px;
            font-size: 1.2em;
            width: 20px; /* Fixed width for icons */
            text-align: center;
        }

        .submenu {
            list-style: none;
            padding-left: 0;
            margin-top: 10px;
            display: none;
            background-color: rgba(0, 0, 0, 0.1);
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

        /* Content Styles */
        .content {
        
        }

        .content.shifted {
            margin-left: 0;
        }

        /* Hamburger Button Styles */
        .hamburger-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 101;
            color: ;
            font-size: 2em;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            transition: color 0.3s;
        }

        .hamburger-button:hover {
            color: #764ba2;
        }

        /* Media Queries */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }

            .sidebar.hidden {
                margin-left: 0;
            }

            .content {
                margin-left: 0;
            }

            .hamburger-button {
                left: 10px;
                top: 10px;
            }
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
            sidebar.classList.toggle('hidden');
            content.classList.toggle('shifted');
        }
    </script>
</head>
<body>
    <button class="hamburger-button" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="sidebar">
        <h2 class="header">Admin Panel</h2>
        <ul>
            <li>
                <a href="manage_stocks.php">
                    <i class="fa-solid fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="approvals.php">
                    <i class="fa-solid fa-check-circle"></i>
                    Approvals
                </a>
            </li>
            <li>
                <a href="manage_bank.php">
                    <i class="fa-solid fa-university"></i>
                    Manage Bank
                </a>
            </li>
            <li>
                <a href="manage_finance.php">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    Manage Finance
                </a>
            </li>
            <li>
                <button onclick="toggleSubMenu('manageItemsSubMenu')">
                    <i class="fa-solid fa-box"></i>
                    Manage Items <i class="fa-solid fa-caret-down" style="margin-left: auto;"></i>
                </button>
                <ul id="manageItemsSubMenu" class="submenu">
                    <li><a href="create_item.php">Add Item</a></li>
                    <li><a href="view_items.php">View Items</a></li>
                    <li><a href="add_stock.php">Add Items To Stock</a></li>
                    <li><a href="view_stock.php">View Stock List</a></li>
                </ul>
            </li>
            <li>
                <button onclick="toggleSubMenu('expensesSubMenu')">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                    Manage Expenses <i class="fa-solid fa-caret-down" style="margin-left: auto;"></i>
                </button>
                <ul id="expensesSubMenu" class="submenu">
                    <li><a href="add_expense.php">Add Expenses</a></li>
                    <li><a href="view_expenses.php">View Expenses</a></li>
                    <li><a href="add_category.php">Add Expenses Categories</a></li>
                    <li><a href="view_categories.php">View Category List</a></li>
                </ul>
            </li>
            <li>
                <a href="manage_customers.php">
                    <i class="fa-solid fa-users"></i>
                    Manage Customer
                </a>
            </li>
            <li>
                <a href="manage_suppliers.php">
                    <i class="fa-solid fa-user-friends"></i>
                    Manage Supplier
                </a>
            </li>
            <li>
                <a href="hosted_customers.php">
                    <i class="fa-solid fa-user-tag"></i>
                    Hosted Customer
                </a>
            </li>
            <li>
                <a href="manage_bincard.php">
                    <i class="fa-solid fa-book"></i>
                    Manage Bincard
                </a>
            </li>
            <li>
                <button onclick="toggleSubMenu('reportsSubMenu')">
                    <i class="fa-solid fa-chart-line"></i>
                    Reports <i class="fa-solid fa-caret-down" style="margin-left: auto;"></i>
                </button>
                <ul id="reportsSubMenu" class="submenu">
                    <li><a href="stock_list.php">Stock List</a></li>
                    <li><a href="cash_sales.php">Cash Sales</a></li>
                    <li><a href="credit_sales.php">Credit Sales</a></li>
                    <li><a href="agent_cash_sales.php">Agent Cash Sales</a></li>
                    <li><a href="commission_calculator.php">Commission Calculator</a></li>
                    <li><a href="agent_credit_sales.php">Agent Credit Sales</a></li>
                    <li><a href="financial_reports.php">Financial Reports</a></li>
                    <li><a href="outdated_items.php">Outdated Items</a></li>
                    <li><a href="credit_customer_report.php">Credit Customer Report</a></li>
                </ul>
            </li>
            <li>
                <a href="availability_system.php">
                    <i class="fa-solid fa-check-circle"></i>
                    Check Availability System
                </a>
            </li>
            <li>
                <a href="account_settings.php">
                    <i class="fa-solid fa-cog"></i>
                    Account Setting
                </a>
            </li>
            <li>
                <a href="support_system.php">
                    <i class="fa-solid fa-headset"></i>
                    Support System
                </a>
            </li>
        </ul>
    </div>

    <div class="content">
      
    </div>
</body>
</html>