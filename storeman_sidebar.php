<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storeman Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --sidebar-width: 280px;
            --transition-speed: 0.3s;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--accent-color);
            display: flex;
            min-height: 100vh;
            color: var(--dark-color);
        }
        
        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            height: 100vh;
            padding: 0;
            position: fixed;
            left: 0;
            top: 0;
            transition: transform var(--transition-speed) ease;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
        }
        
        .sidebar.hidden {
            transform: translateX(-100%);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            background-color: rgba(255,255,255,0.1);
            text-align: center;
            position: relative;
            margin-bottom: 20px;
        }
        
        .sidebar-header h2 {
            color: white;
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        .sidebar-header::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background-color: var(--accent-color);
            margin: 15px auto 0;
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
        
        .sidebar ul li a, .toggle-button {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            border-radius: 6px;
            transition: all 0.3s ease;
            background-color: transparent;
            border: none;
            width: 100%;
            cursor: pointer;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
        }
        
        .sidebar ul li a:hover, .toggle-button:hover {
            background-color: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }
        
        .sidebar ul li a.active {
            background-color: rgba(255,255,255,0.2);
            font-weight: 500;
        }
        
        .sidebar ul li a::before, .toggle-button::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background-color: var(--accent-color);
            transform: scaleY(0);
            transition: transform 0.2s ease;
        }
        
        .sidebar ul li a:hover::before, .toggle-button:hover::before {
            transform: scaleY(1);
        }
        
        .sidebar ul li a i, .toggle-button i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .submenu {
            display: none;
            padding-left: 10px;
            margin-top: 5px;
            background-color: rgba(0,0,0,0.1);
            border-radius: 6px;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .submenu li {
            margin: 5px 0;
        }
        
        .submenu li a {
            padding: 10px 15px 10px 35px;
            font-size: 0.9rem;
            position: relative;
        }
        
        .submenu li a::before {
            content: '•';
            position: absolute;
            left: 20px;
            color: var(--accent-color);
        }
        
        .content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            flex-grow: 1;
            transition: margin-left var(--transition-speed) ease;
        }
        
        .hamburger-button {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 1100;
            color: var(--primary-color);
            font-size: 1.8rem;
            cursor: pointer;
            background-color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .hamburger-button:hover {
            transform: scale(1.1);
            color: var(--accent-color);
        }
        
        /* Responsive Design */
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
            
            .hamburger-button {
                left: 10px;
                top: 10px;
            }
        }
        
        /* Unique Elements */
        .sidebar-footer {
            padding: 20px;
            text-align: center;
            margin-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            padding: 15px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 8px;
            margin: 20px 15px;
            transition: all 0.3s ease;
        }
        
        .user-profile:hover {
            background-color: rgba(255,255,255,0.2);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .user-info {
            flex-grow: 1;
        }
        
        .user-name {
            color: white;
            font-weight: 500;
            margin-bottom: 3px;
        }
        
        .user-role {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="hamburger-button" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </div>
    
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Storeman Pro</h2>
        </div>
        
        <div class="user-profile">
            <div class="user-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="user-info">
                <div class="user-name">John Doe</div>
                <div class="user-role">Store Manager</div>
            </div>
        </div>
        
        <ul>
            <li>
                <a href="storeman_dashboard.php" class="active">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <button class="toggle-button" onclick="toggleSubMenu('manageInventorySubMenu')">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>Manage Inventory</span>
                    <i class="fa-solid fa-chevron-down" style="margin-left: auto; font-size: 0.8rem;"></i>
                </button>
                <ul id="manageInventorySubMenu" class="submenu">
                    <li><a href="add_stock.php">Add Stock</a></li>
                    <li><a href="view_stock.php">View Stock</a></li>
                    <li><a href="add_item.php">Add New Item</a></li>
                </ul>
            </li>
            <li>
                <a href="receive_stock.php">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                    <span>Receive Stock</span>
                </a>
            </li>
            <li>
                <button class="toggle-button" onclick="toggleSubMenu('stockAdjustmentsSubMenu')">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Stock Adjustments</span>
                    <i class="fa-solid fa-chevron-down" style="margin-left: auto; font-size: 0.8rem;"></i>
                </button>
                <ul id="stockAdjustmentsSubMenu" class="submenu">
                    <li><a href="stock_adjustments.php">Adjust Stock Levels</a></li>
                    <li><a href="damaged_items.php">Damaged Items</a></li>
                </ul>
            </li>
            <li>
                <button class="toggle-button" onclick="toggleSubMenu('reportsSubMenu')">
                    <i class="fa-solid fa-chart-pie"></i>
                    <span>Reports</span>
                    <i class="fa-solid fa-chevron-down" style="margin-left: auto; font-size: 0.8rem;"></i>
                </button>
                <ul id="reportsSubMenu" class="submenu">
                    <li><a href="generate_reports.php">Stock Levels</a></li>
                    <li><a href="sales_history.php">Sales History</a></li>
                    <li><a href="inventory_audit.php">Inventory Audit</a></li>
                </ul>
            </li>
            <li>
                <a href="user_management.php">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li>
                <a href="settings.php">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="logout.php" style="color: rgba(255,255,255,0.7); font-size: 0.9rem; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-arrow-right-from-bracket" style="margin-right: 8px;"></i>
                Logout
            </a>
        </div>
    </div>
    


    <script>
        function toggleSubMenu(id) {
            const submenu = document.getElementById(id);
            const chevron = document.querySelector(`button[onclick="toggleSubMenu('${id}')"] .fa-chevron-down`);
            
            if (submenu.style.display === "block") {
                submenu.style.display = "none";
                chevron.classList.remove('fa-chevron-up');
                chevron.classList.add('fa-chevron-down');
            } else {
                submenu.style.display = "block";
                chevron.classList.remove('fa-chevron-down');
                chevron.classList.add('fa-chevron-up');
            }
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');
            const hamburger = document.querySelector('.hamburger-button');

            sidebar.classList.toggle('hidden');
            content.style.marginLeft = sidebar.classList.contains('hidden') ? '0' : '280px';
            
            // Rotate hamburger icon
            if (sidebar.classList.contains('hidden')) {
                hamburger.innerHTML = '<i class="fa-solid fa-bars"></i>';
            } else {
                hamburger.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            }
        }
        
        // Highlight current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const links = document.querySelectorAll('.sidebar a');
            
            links.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                    
                    // Expand parent menu if this is a submenu item
                    const submenu = link.closest('.submenu');
                    if (submenu) {
                        submenu.style.display = 'block';
                        const parentButton = document.querySelector(`button[onclick="toggleSubMenu('${submenu.id}')"]`);
                        if (parentButton) {
                            const chevron = parentButton.querySelector('.fa-chevron-down');
                            if (chevron) {
                                chevron.classList.remove('fa-chevron-down');
                                chevron.classList.add('fa-chevron-up');
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>