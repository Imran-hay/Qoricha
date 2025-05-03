<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and has the 'storeman' role
// (Add your session checking code here)

// Include your database connection settings
require 'config.php';

// Include the storeman sidebar
require 'storeman_sidebar.php';

$stmt = $pdo->prepare("
    SELECT 
        items.item_id,
        items.item_name, 
        items.unit_price, 
        items.stock, 
        items.expire_date, 
        NOW() AS received_date, 
        sales.created_at
    FROM 
        items
    LEFT JOIN 
        sales ON items.item_id = sales.item_id
    ORDER BY 
        items.item_name ASC
");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard | View Stock</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4bb543;
            --warning-color: #fca311;
            --danger-color: #ef233c;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }

        .container {
            margin-left: 210px;
            padding: 30px;
            transition: all 0.3s;
        }

        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: var(--dark-color);
            position: relative;
            display: inline-block;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.05);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        tr:nth-child(even) {
            background-color: rgba(67, 97, 238, 0.03);
        }

        tr:hover {
            background-color: rgba(67, 97, 238, 0.08);
            transition: all 0.2s;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-active {
            background-color: rgba(75, 181, 67, 0.1);
            color: var(--success-color);
        }

        .status-expired {
            background-color: rgba(239, 35, 60, 0.1);
            color: var(--danger-color);
        }

        .status-warning {
            background-color: rgba(252, 163, 17, 0.1);
            color: var(--warning-color);
        }

        .no-items {
            text-align: center;
            padding: 40px;
            color: #888;
            font-size: 16px;
        }

        .no-items i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ddd;
        }

        .search-filter {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 15px;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            outline: none;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
        }

        .filter-btn {
            background: white;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            padding: 0 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .filter-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: rgba(67, 97, 238, 0.1);
        }

        .stock-low {
            color: var(--danger-color);
            font-weight: 500;
        }

        .stock-ok {
            color: var(--success-color);
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .pagination-btn:hover {
            background-color: #f0f0f0;
        }

        .pagination-btn.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
            }
            
            .search-filter {
                flex-direction: column;
            }
            
            th, td {
                padding: 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <main>
        <div class="container">
            <header>
                <div>
                    <h1>Inventory Dashboard</h1>
                    <p>View and manage your stock items</p>
                </div>
                <div>
                    <span class="status-badge status-active">
                        <i class="fas fa-circle"></i> Active
                    </span>
                </div>
            </header>

            <div class="card">
                <div class="search-filter">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search items...">
                    </div>
                    <button class="filter-btn">
                        <i class="fas fa-filter"></i>
                        Filters
                    </button>
                </div>

                <h2>Current Stock</h2>
                
                <div class="table-responsive">
                    <?php if (count($items) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Item Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Expiry Date</th>
                                    <th>Received</th>
                                    <th>Last Delivery</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): 
                                    // Determine stock status
                                    $stockClass = ($item['stock'] < 10) ? 'stock-low' : 'stock-ok';
                                    $statusBadge = '';
                                    
                                    if ($item['expire_date'] && strtotime($item['expire_date']) < time()) {
                                        $statusBadge = '<span class="status-badge status-expired"><i class="fas fa-exclamation-circle"></i> Expired</span>';
                                    } elseif ($item['stock'] < 5) {
                                        $statusBadge = '<span class="status-badge status-warning"><i class="fas fa-exclamation-triangle"></i> Low Stock</span>';
                                    } else {
                                        $statusBadge = '<span class="status-badge status-active"><i class="fas fa-check-circle"></i> Active</span>';
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['item_id']); ?></td>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td>$<?php echo number_format(htmlspecialchars($item['unit_price']), 2); ?></td>
                                        <td class="<?php echo $stockClass; ?>"><?php echo htmlspecialchars($item['stock']); ?></td>
                                        <td><?php echo $item['expire_date'] ? date('M d, Y', strtotime($item['expire_date'])) : 'N/A'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($item['received_date'])); ?></td>
                                        <td><?php echo $item['created_at'] ? date('M d, Y', strtotime($item['created_at'])) : 'N/A'; ?></td>
                                        <td><?php echo $statusBadge; ?></td>
                                        <td>
                                            <div class="action-btns">
                                                <button class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="pagination">
                            <button class="pagination-btn"><i class="fas fa-chevron-left"></i></button>
                            <button class="pagination-btn active">1</button>
                            <button class="pagination-btn">2</button>
                            <button class="pagination-btn">3</button>
                            <button class="pagination-btn"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    <?php else: ?>
                        <div class="no-items">
                            <i class="fas fa-box-open"></i>
                            <p>No items found in the inventory</p>
                            <button class="btn btn-primary" style="margin-top: 15px;">
                                <i class="fas fa-plus"></i> Add New Item
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Search functionality
            $('.search-box input').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('table tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Add hover effects
            $('tr').hover(
                function() {
                    $(this).css('transform', 'translateY(-2px)');
                    $(this).css('box-shadow', '0 4px 8px rgba(0,0,0,0.1)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                    $(this).css('box-shadow', 'none');
                }
            );
        });
    </script>
</body>
</html>