<?php
session_start();

// Include database configuration
require 'config.php';
require 'sidebar.php';

// Fetch expired items from the database
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM items WHERE expire_date < ? ORDER BY expire_date ASC");
$stmt->execute([$today]);
$expired_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | Expired Items</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e6f0ff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #fd7e14;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
            --white: #ffffff;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: #4a5568;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-left: 280px;
            padding: 30px;
            transition: var(--transition);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title h2 {
            font-size: 24px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .page-title p {
            color: #718096;
            margin: 5px 0 0;
            font-size: 14px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: #3a56d4;
            box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-warning {
            background-color: rgba(253, 126, 20, 0.1);
            color: var(--warning);
            border-left: 4px solid var(--warning);
        }

        .card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid #e9ecef;
        }

        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .data-table tr:hover td {
            background-color: rgba(220, 53, 69, 0.05);
        }

        .data-table .expired td {
            color: var(--danger);
            font-weight: 500;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger);
        }

        .no-items {
            text-align: center;
            padding: 30px;
            color: #6c757d;
            font-style: italic;
        }

        .days-expired {
            font-weight: 600;
        }

        @media (max-width: 1200px) {
            .container {
                margin-left: 0;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h2>Expired Inventory Items</h2>
                <p>Items that have passed their expiration date</p>
            </div>
            <div>
                <a href="view_items.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to All Items
                </a>
            </div>
        </div>

        <?php if (count($expired_items) > 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> You have <?= count($expired_items) ?> expired items in your inventory that need attention.
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Item Name</th>
                                <th>Stock</th>
                                <th>Unit Price</th>
                                <th>Expired Since</th>
                              
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expired_items as $item): 
                                $expire_date = new DateTime($item['expire_date']);
                                $today = new DateTime();
                                $interval = $today->diff($expire_date);
                                $days_expired = abs($interval->days);
                            ?>
                            <tr class="expired">
                                <td><?= htmlspecialchars($item['item_id']) ?></td>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td><?= htmlspecialchars($item['stock']) ?></td>
                                <td>₱<?= number_format($item['unit_price'], 2) ?></td>
                                <td>
                                    <span class="days-expired"><?= $days_expired ?> days</span><br>
                                    <small><?= date('M d, Y', strtotime($item['expire_date'])) ?></small>
                                </td>
                               
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="no-items">
                    <i class="fas fa-check-circle fa-3x" style="color: #28a745; margin-bottom: 15px;"></i>
                    <h3>No Expired Items</h3>
                    <p>All items in your inventory are currently within their expiration dates.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>