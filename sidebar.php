<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #7c3aed;
            --accent-color: #a78bfa;
            --text-color: #f8fafc;
            --hover-color: #6366f1;
            --submenu-bg: rgba(79, 70, 229, 0.9);
            --sidebar-bg: linear-gradient(160deg, #4f46e5 0%, #7c3aed 100%);
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius: 12px;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styling */
        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            height: 100vh;
            padding: 20px 0;
            box-shadow: var(--shadow);
            position: fixed;
            left: 0;
            top: 0;
            transition: var(--transition);
            overflow-y: auto;
            z-index: 100;
            border-top-right-radius: var(--border-radius);
            border-bottom-right-radius: var(--border-radius);
        }
        
        .sidebar.hidden {
            transform: translateX(-100%);
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            margin-bottom: 10px;
            text-align: center;
            position: relative;
        }
        
        .sidebar-header h2 {
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
            padding: 15px 0;
            position: relative;
            display: inline-block;
        }
        
        .sidebar-header h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0 15px;
        }
        
        .sidebar ul li {
            margin: 8px 0;
            position: relative;
        }
        
        .sidebar ul li a, .sidebar ul li button {
            color: var(--text-color);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            transition: var(--transition);
            background-color: transparent;
            border: none;
            width: 100%;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .sidebar ul li a:hover, .sidebar ul li button:hover {
            background-color: var(--hover-color);
            transform: translateX(5px);
        }
        
        .sidebar ul li.active a {
            background-color: var(--accent-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }
        
        .icon {
            margin-right: 12px;
            font-size: 1.1rem;
            min-width: 24px;
            text-align: center;
        }
        
        .submenu {
            display: none;
            padding-left: 10px;
            margin-top: 5px;
            animation: fadeIn 0.3s ease-out;
            background-color: var(--submenu-bg);
            border-radius: 8px;
            overflow: hidden;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .submenu li a {
            padding: 10px 15px 10px 45px;
            font-size: 0.9rem;
            background-color: transparent;
            border-radius: 0;
            margin: 0;
            position: relative;
        }
        
        .submenu li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .submenu li a::before {
            content: '';
            position: absolute;
            left: 25px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background-color: var(--accent-color);
            border-radius: 50%;
            opacity: 0;
            transition: var(--transition);
        }
        
        .submenu li a:hover::before {
            opacity: 1;
            left: 30px;
        }
        
        .arrow {
            margin-left: auto;
            transition: var(--transition);
            font-size: 0.8rem;
        }
        
        .sidebar ul li button.active .arrow {
            transform: rotate(180deg);
        }
        
        .content {
      
        }
        
        .content.expanded {
            margin-left: 0;
        }
        
        /* Hamburger button styling */
        .hamburger-button {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 1000;
            color: var(--primary-color);
            font-size: 1.5rem;
            cursor: pointer;
            background: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        
        .hamburger-button:hover {
            transform: scale(1.1);
            color: var(--secondary-color);
        }
        
        /* Notification badge */
        .badge {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #ef4444;
            color: white;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
            min-width: 20px;
            text-align: center;
        }
        
        /* Highlight for important menu items */
        .highlight {
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background-color: var(--accent-color);
            border-radius: 4px 0 0 4px;
            transition: var(--transition);
            opacity: 0;
        }
        
        .sidebar ul li a:hover .highlight, 
        .sidebar ul li button:hover .highlight {
            opacity: 1;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 260px;
            }
            
            .sidebar.hidden {
                transform: translateX(0);
            }
            
            .content {
                margin-left: 0;
            }
        }
        
        /* Glow effect for active items */
        .sidebar ul li.active a {
            box-shadow: 0 0 15px rgba(167, 139, 250, 0.5);
        }
        
        /* Pulse animation for notifications */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .badge.pulse {
            animation: pulse 1.5s infinite;
        }
    </style>
</head>
<body>
    <div class="hamburger-button" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </div>
    
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
        </div>
        
        <ul>
            <li class="active">
                <a href="admin_dashboard.php">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-gauge-high"></i></span>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            
            <li>
                <a href="approvals.php">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-clipboard-check"></i></span>
                    <span class="text">Approvals</span>
                  
                </a>
            </li>
            
            <li>
                <button onclick="toggleSubMenu(this, 'manageBankSubMenu')">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-landmark"></i></span>
                    <span class="text">Manage Bank</span>
                    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <ul id="manageBankSubMenu" class="submenu">
                    <li><a href="add_bank.php">Add Bank</a></li>
                    <li><a href="view_banks.php">View Banks</a></li>
                </ul>
            </li>
            
            <li>
                <button onclick="toggleSubMenu(this, 'manageItemsSubMenu')">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                    <span class="text">Manage Items</span>
                    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <ul id="manageItemsSubMenu" class="submenu">
                    <li><a href="create_item.php">Add Item</a></li>
                    <li><a href="view_items.php">View Items</a></li>
                </ul>
            </li>
            
            <li>
                <button onclick="toggleSubMenu(this, 'expensesSubMenu')">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-receipt"></i></span>
                    <span class="text">Manage Expenses</span>
                    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
                 
                </button>
                <ul id="expensesSubMenu" class="submenu">
                    <li><a href="view_expenses.php">View Expenses</a></li>
                    <li><a href="view_categories.php">View Categories</a></li>
                </ul>
            </li>
            
            <li>
                <a href="manage_customers.php">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-users-between-lines"></i></span>
                    <span class="text">Manage Customers</span>
                </a>
            </li>
            
            <li>
                <a href="manage_suppliers.php">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-truck-field"></i></span>
                    <span class="text">Manage Suppliers</span>
                </a>
            </li>
            
         <!--    <li>
                <a href="hosted_customers.php">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-house-user"></i></span>
                    <span class="text">Hosted Customers</span>
                </a>
            </li> -->
            
            <li>
                <button onclick="toggleSubMenu(this, 'reportsSubMenu')">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-chart-pie"></i></span>
                    <span class="text">Reports</span>
                    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <ul id="reportsSubMenu" class="submenu">
                 <!--    <li><a href="stock_list.php">Stock List</a></li>
                    <li><a href="cash_sales.php">Cash Sales</a></li>
                    <li><a href="credit_sales.php">Credit Sales</a></li>
                    <li><a href="agent_cash_sales.php">Agent Cash Sales</a></li>
                    <li><a href="commission_calculator.php">Commission Calculator</a></li>
                    <li><a href="agent_credit_sales.php">Agent Credit Sales</a></li> -->
                    <li><a href="financial_reports.php">Financial Reports</a></li>
                    <li><a href="expired_items.php">Outdated Items</a></li>
               <!--      <li><a href="credit_customer_report.php">Credit Customer Report</a></li> -->
                </ul>
            </li>
            
         <!--    <li>
                <a href="account_settings.php">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-user-gear"></i></span>
                    <span class="text">Account Settings</span>
                </a>
            </li> -->

            <li>
                <a href="register.php">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-user-gear"></i></span>
                    <span class="text">Register User</span>
                </a>
            </li>

            <li class="icon-only logout">
                <a href="logout.php">
                    <span class="icon"><i class="fa-solid fa-arrow-right-from-bracket"></i></span>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="content">
        <!-- Your main content goes here -->
    </div>

    <script>
        function toggleSubMenu(button, id) {
            const submenu = document.getElementById(id);
            const isActive = button.classList.contains('active');
            
            // Close all other submenus first
            document.querySelectorAll('.submenu').forEach(menu => {
                if (menu.id !== id) {
                    menu.style.display = 'none';
                    menu.previousElementSibling.classList.remove('active');
                }
            });
            
            // Toggle current submenu
            submenu.style.display = isActive ? 'none' : 'block';
            button.classList.toggle('active');
        }
        
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');
            
            sidebar.classList.toggle('hidden');
            content.classList.toggle('expanded');
            
            // Store state in localStorage
            const isHidden = sidebar.classList.contains('hidden');
            localStorage.setItem('sidebarHidden', isHidden);
        }
        
        // Check localStorage on load
        document.addEventListener('DOMContentLoaded', () => {
            if (localStorage.getItem('sidebarHidden') === 'true') {
                toggleSidebar();
            }
            
            // Set active menu item based on current page
            const currentPage = window.location.pathname.split('/').pop();
            document.querySelectorAll('.sidebar a').forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.parentElement.classList.add('active');
                    
                    // Open parent submenu if this is a submenu item
                    const submenuItem = link.closest('.submenu');
                    if (submenuItem) {
                        submenuItem.style.display = 'block';
                        const toggleButton = submenuItem.previousElementSibling;
                        toggleButton.classList.add('active');
                    }
                }
            });
        });
    </script>
</body>
</html>