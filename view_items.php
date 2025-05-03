<?php
session_start();

// Include database configuration
require 'config.php';
require 'sidebar.php';

// Fetch items from the database
$stmt = $pdo->prepare("SELECT * FROM items ORDER BY item_name ASC");
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle item update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_item'])) {
    $item_id = $_POST['item_id'];
    $hs_code = $_POST['hs_code'];
    $item_name = $_POST['item_name'];
    $stock = $_POST['stock'];
    $unit_price = $_POST['unit_price'];
    $expire_date = $_POST['expire_date'];

    try {
        $updateStmt = $pdo->prepare("
            UPDATE items
            SET hs_code = ?, item_name = ?, stock = ?, unit_price = ?, expire_date = ?
            WHERE item_id = ?
        ");
        $updateStmt->execute([$hs_code, $item_name, $stock, $unit_price, $expire_date, $item_id]);

        $_SESSION['success_message'] = 'Item updated successfully!';
        header("Location: view_items.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error updating item: ' . $e->getMessage();
        header("Location: view_items.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | View Items</title>
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

        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #c82333;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-warning {
            background-color: var(--warning);
            color: var(--white);
        }

        .btn-warning:hover {
            background-color: #e06b0e;
            box-shadow: 0 2px 8px rgba(253, 126, 20, 0.3);
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
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
    table-layout: fixed; /* Add this to control column widths */
}

.data-table th, 
.data-table td {
    padding: 12px 8px; /* Reduce padding slightly */
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
    word-wrap: break-word; /* Ensure long text wraps */
}

/* Set specific widths for columns */
.data-table th:nth-child(1),
.data-table td:nth-child(1) {
    width: 10%; /* HS Code */
}

.data-table th:nth-child(2),
.data-table td:nth-child(2) {
    width: 10%; /* Item ID */
}

.data-table th:nth-child(3),
.data-table td:nth-child(3) {
    width: 20%; /* Item Name */
}

.data-table th:nth-child(4),
.data-table td:nth-child(4) {
    width: 8%; /* Stock */
    text-align: center;
}

.data-table th:nth-child(5),
.data-table td:nth-child(5) {
    width: 12%; /* Unit Price */
    text-align: right;
}

.data-table th:nth-child(6),
.data-table td:nth-child(6) {
    width: 12%; /* Expire Date */
}

.data-table th:nth-child(7),
.data-table td:nth-child(7) {
    width: 10%; /* Status */
    text-align: center;
}

.data-table th:nth-child(8),
.data-table td:nth-child(8) {
    width: 18%; /* Actions */
    text-align: center;
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
            background-color: var(--primary-light);
        }

        .data-table .expired {
            background-color: rgba(220, 53, 69, 0.05);
        }

        .data-table .expired td {
            color: var(--danger);
            font-weight: 500;
        }

        .data-table .expiring-soon {
            background-color: rgba(253, 126, 20, 0.05);
        }

        .data-table .expiring-soon td {
            color: var(--warning);
            font-weight: 500;
        }
        .badge {
    display: inline-block;
    padding: 4px 6px; /* Reduced padding */
    border-radius: 12px;
    font-size: 11px; /* Smaller font */
    font-weight: 500;
    white-space: nowrap; /* Prevent text wrapping */
    max-width: 100px; /* Maximum width */
    overflow: hidden;
    text-overflow: ellipsis;
}
.table-responsive {
    overflow-x: auto;
    width: 100%;
    -webkit-overflow-scrolling: touch;
}

        .badge-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background-color: rgba(253, 126, 20, 0.1);
            color: var(--warning);
        }

        .badge-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger);
        }

        .update-form {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-top: 10px;
            border: 1px solid #e9ecef;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #495057;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
            outline: none;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 15px;
        }

        .no-items {
            text-align: center;
            padding: 30px;
            color: #6c757d;
            font-style: italic;
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
                <h2>Inventory Management</h2>
                <p>View and manage all items in your inventory</p>
            </div>
            <div>
                <a href="expired_items.php" class="btn btn-danger">
                    <i class="fas fa-exclamation-circle"></i> View Expired Items
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="table-responsive">
                <?php if (count($items) > 0): ?>
                    <table class="data-table">
                        <thead>
                        <tr>
            <th>HS Code</th>
            <th>Item ID</th>
            <th>Item Name</th>
            <th>Stock</th>
            <th>Unit Price</th>
            <th>Expire Date</th>
            <th>Actions</th>
        </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): 
                                $expire_date = new DateTime($item['expire_date']);
                                $today = new DateTime();
                                $interval = $today->diff($expire_date);
                                $days_left = $interval->format('%r%a');
                                
                                $row_class = '';
                                $status = '';
                                
                                if ($days_left < 0) {
                                    $row_class = 'expired';
                                    $status = '<span class="badge badge-danger">Expired</span>';
                                } elseif ($days_left <= 30) {
                                    $row_class = 'expiring-soon';
                                    $status = '<span class="badge badge-warning">Expiring soon</span>';
                                } else {
                                    $status = '<span class="badge badge-success">Active</span>';
                                }
                            ?>
                            <tr class="<?= $row_class ?>">
                                <td><?= htmlspecialchars($item['hs_code']) ?></td>
                                <td><?= htmlspecialchars($item['item_id']) ?></td>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td><?= htmlspecialchars($item['stock']) ?></td>
                                <td>₱<?= number_format($item['unit_price'], 2) ?></td>
                                <td><?= date('M d, Y', strtotime($item['expire_date'])) ?></td>
                           
                                <td>
                                    <button class="btn btn-warning" onclick="showUpdateForm('<?= htmlspecialchars($item['item_id']) ?>')">
                                        <i class="fas fa-edit"></i> Update
                                    </button>
                                </td>
                            </tr>
                            <tr id="updateFormRow_<?= htmlspecialchars($item['item_id']) ?>" style="display: none;">
                                <td colspan="8">
                                    <form class="update-form" method="POST" action="">
                                        <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['item_id']) ?>">

                                        <div class="form-group">
                                            <label for="hs_code_<?= htmlspecialchars($item['item_id']) ?>">HS Code</label>
                                            <input type="text" class="form-control" name="hs_code" id="hs_code_<?= htmlspecialchars($item['item_id']) ?>" value="<?= htmlspecialchars($item['hs_code']) ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="item_name_<?= htmlspecialchars($item['item_id']) ?>">Item Name</label>
                                            <input type="text" class="form-control" name="item_name" id="item_name_<?= htmlspecialchars($item['item_id']) ?>" value="<?= htmlspecialchars($item['item_name']) ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="stock_<?= htmlspecialchars($item['item_id']) ?>">Stock</label>
                                            <input type="number" class="form-control" name="stock" id="stock_<?= htmlspecialchars($item['item_id']) ?>" value="<?= htmlspecialchars($item['stock']) ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="unit_price_<?= htmlspecialchars($item['item_id']) ?>">Unit Price</label>
                                            <input type="number" step="0.01" class="form-control" name="unit_price" id="unit_price_<?= htmlspecialchars($item['item_id']) ?>" value="<?= htmlspecialchars($item['unit_price']) ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="expire_date_<?= htmlspecialchars($item['item_id']) ?>">Expire Date</label>
                                            <input type="date" class="form-control" name="expire_date" id="expire_date_<?= htmlspecialchars($item['item_id']) ?>" value="<?= htmlspecialchars($item['expire_date']) ?>" required>
                                        </div>

                                        <div class="form-actions">
                                            <button type="button" class="btn btn-danger" onclick="hideUpdateForm('<?= htmlspecialchars($item['item_id']) ?>')">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                            <button type="submit" name="update_item" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-items">
                        <i class="fas fa-box-open fa-3x" style="color: #dee2e6; margin-bottom: 15px;"></i>
                        <p>No items found in the inventory.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showUpdateForm(itemId) {
            var row = document.getElementById('updateFormRow_' + itemId);
            if (row) {
                row.style.display = 'table-row';
                // Scroll to the form
                row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function hideUpdateForm(itemId) {
            var row = document.getElementById('updateFormRow_' + itemId);
            if (row) {
                row.style.display = 'none';
            }
        }
    </script>
</body>
</html>