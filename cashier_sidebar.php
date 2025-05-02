<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #2563eb;
            --accent-color: #60a5fa;
            --text-color: #ffffff;
            --hover-color: #93c5fd;
            --submenu-bg: rgba(37, 99, 235, 0.9);
            --sidebar-bg: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius: 10px;
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
            width: 240px;
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
            font-weight: 600;
            font-size: 1.4rem;
            margin: 0;
            padding: 15px 0;
            position: relative;
        }
        
        .sidebar-header h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
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
            padding: 12px 15px;
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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
            border-radius: 6px;
            overflow: hidden;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .submenu li a {
            padding: 10px 15px 10px 40px;
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
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            background-color: var(--accent-color);
            border-radius: 50%;
            opacity: 0;
            transition: var(--transition);
        }
        
        .submenu li a:hover::before {
            opacity: 1;
            left: 25px;
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
            margin-left: 240px;
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
                width: 220px;
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
            box-shadow: 0 0 15px rgba(96, 165, 250, 0.5);
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
            <h2>Cashier Dashboard</h2>
        </div>
        
        <ul>
            <li class="active">
                <a href="cashier_dashboard.php">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-gauge-high"></i></span>
                    <span class="text">Dashboard</span>
                </a>
            </li>
            
            <li>
                <button onclick="toggleSubMenu(this, 'manageSalesSubMenu')">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-cash-register"></i></span>
                    <span class="text">Manage Sales</span>
                    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
               
                </button>
                <ul id="manageSalesSubMenu" class="submenu">
                    <li><a href="confirm_cash_sale.php">Confirm Cash Sale</a></li>
                    <li><a href="view_credit_sales.php">View Credit Sales</a></li>
                </ul>
            </li>
            
            <li>
                <button onclick="toggleSubMenu(this, 'invoicesSubMenu')">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-receipt"></i></span>
                    <span class="text">Invoices</span>
                    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <ul id="invoicesSubMenu" class="submenu">
                    <li><a href="cash_invoices.php">Cash Invoices</a></li>
                    <li><a href="credit_invoices.php">Credit Invoices</a></li>
                    <li><a href="hosted_invoices.php">Hosted Invoices</a></li>
                </ul>
            </li>
            
            <li>
                <button onclick="toggleSubMenu(this, 'creditRepaySubMenu')">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-hand-holding-usd"></i></span>
                    <span class="text">Collect Credit Repay</span>
                    <span class="arrow"><i class="fa-solid fa-chevron-down"></i></span>
                  
                </button>
                <ul id="creditRepaySubMenu" class="submenu">
                    <li><a href="verify_repayment.php">Verify Collected Repayment</a></li>
                    <li><a href="withdraw_balance.php">Withdraw From Balance</a></li>
                </ul>
            </li>
            
            <li>
                <a href="check_availability.php">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-box-open"></i></span>
                    <span class="text">Check Availability</span>
                </a>
            </li>
            
            <li>
                <a href="account_settings.php">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-user-cog"></i></span>
                    <span class="text">Account Settings</span>
                </a>
            </li>
            
            <li class="logout">
                <a href="logout.php">
                    <span class="highlight"></span>
                    <span class="icon"><i class="fa-solid fa-right-from-bracket"></i></span>
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