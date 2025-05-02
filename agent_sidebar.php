<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --text-color: #f8fafc;
            --hover-color: #3b82f6;
            --submenu-bg: rgba(30, 64, 175, 0.8);
            --sidebar-bg: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
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
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }
        
        .sidebar.hidden {
            transform: translateX(-100%);
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .sidebar-header img {
            max-width: 80%;
            height: auto;
            filter: brightness(0) invert(1);
            transition: var(--transition);
        }
        
        .sidebar:hover .sidebar-header img {
            transform: scale(1.05);
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0 15px;
        }
        
        .sidebar ul li {
            margin: 8px 0;
            position: relative;
        }
        
        .sidebar ul li a, .toggle-button {
            color: var(--text-color);
            text-decoration: none;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            transition: var(--transition);
            background-color: transparent;
            border: none;
            width: 100%;
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
        }
        
        .sidebar ul li a:hover, .toggle-button:hover {
            background-color: var(--hover-color);
            transform: translateX(5px);
        }
        
        .sidebar ul li.active a {
            background-color: var(--accent-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .icon {
            margin-right: 12px;
            font-size: 18px;
            min-width: 24px;
            text-align: center;
        }
        
        .submenu {
            display: none;
            padding-left: 10px;
            margin-top: 5px;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .submenu li a {
            padding: 10px 15px 10px 40px;
            font-size: 14px;
            background-color: var(--submenu-bg);
            border-radius: 6px;
            margin: 4px 0;
        }
        
        .submenu li a:hover {
            background-color: var(--hover-color);
        }
        
        .submenu li a::before {
            content: "→";
            position: absolute;
            left: 20px;
            opacity: 0;
            transition: var(--transition);
        }
        
        .submenu li a:hover::before {
            opacity: 1;
            left: 25px;
        }
        
        .toggle-button .arrow {
            margin-left: auto;
            transition: var(--transition);
        }
        
        .toggle-button.active .arrow {
            transform: rotate(180deg);
        }
        
        .content {
            margin-left: 280px;
            padding: 30px;
            flex-grow: 1;
            transition: var(--transition); 
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
            font-size: 24px;
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
        }
        
        /* Floating notification badge */
        .badge {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
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
        
        /* Glow effect on hover */
        .sidebar ul li a:hover, .toggle-button:hover {
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
        }
        
        /* Subtle pulse animation for active items */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
        
        .sidebar ul li.active a {
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
            <!-- <img src="images/Qorichalogo.png" alt="Qoricha Logo"> -->
        </div>
        
        <ul>
            <li class="icon-only active">
                <a href="agent_dashboard.php">
                    <span class="icon"><i class="fa-solid fa-gauge-high"></i></span>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            
            <li class="icon-only">
                <button class="toggle-button" onclick="toggleSubMenu(this, 'manageSalesSubMenu')">
                    <span class="icon"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                    <span class="text">Manage Sales</span>
                    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
                  
                </button>
                <ul id="manageSalesSubMenu" class="submenu">
                    <li><a href="create_sale.php">Create New Sale</a></li>
                    <li><a href="pending_approvals.php">Pending Approvals</a></li>
                    <li><a href="add_repayment.php">Add Repayment</a></li>
                </ul>
            </li>
            
            <li class="icon-only">
                <button class="toggle-button" onclick="toggleSubMenu(this, 'manageCustomersSubMenu')">
                    <span class="icon"><i class="fa-solid fa-users-gear"></i></span>
                    <span class="text">Manage Customers</span>
                    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <ul id="manageCustomersSubMenu" class="submenu">
                    <li><a href="add_customer.php">Add New Customer</a></li>
                    <li><a href="view_customers.php">View Customer List</a></li>
                </ul>
            </li>
            
      <!--       <li class="icon-only">
                <button class="toggle-button" onclick="toggleSubMenu(this, 'hostedCustomersSubMenu')">
                    <span class="icon"><i class="fa-solid fa-house-chimney-user"></i></span>
                    <span class="text">Hosted Customers</span>
                    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
               
                </button>
                <ul id="hostedCustomersSubMenu" class="submenu">
                    <li><a href="todays_hosted.php">Today's Hosted</a></li>
                    <li><a href="this_month_hosted.php">This Month's Hosted</a></li>
                    <li><a href="all_hosted.php">View All Hosted</a></li>
                </ul>
            </li> -->
            
       <!--      <li class="icon-only">
                <button class="toggle-button" onclick="toggleSubMenu(this, 'reportsSubMenu')">
                    <span class="icon"><i class="fa-solid fa-chart-simple"></i></span>
                    <span class="text">Reports & Analytics</span>
                    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <ul id="reportsSubMenu" class="submenu">
                    <li><a href="item_list.php">Item List</a></li>
                    <li><a href="cash_sales.php">Cash Sales</a></li>
                    <li><a href="credit_sales.php">Credit Sales</a></li>
                    <li><a href="commission_earned.php">Commission Earned</a></li>
                </ul>
            </li> -->
            
            <li class="icon-only">
                <a href="view_bank.php">
                    <span class="icon"><i class="fa-solid fa-building-columns"></i></span>
                    <span class="text">Bank Accounts</span>
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