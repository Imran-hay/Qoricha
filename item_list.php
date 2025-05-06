<?php
session_start();

// Include database configuration
require 'config.php';
require 'agent_sidebar.php'; // Use the agent sidebar

// Enable error reporting for debugging
ini_set('display_errors', 0); // Turn off displaying errors to the browser
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('America/Los_Angeles'); // Replace with your timezone

try {
    // Fetch items from the database
    $stmt = $pdo->prepare("SELECT * FROM items ORDER BY item_name ASC");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($items === false) {
        throw new Exception("Error fetching items: " . print_r($pdo->errorInfo(), true));
    }

} catch (Exception $e) {
    error_log("An error occurred: " . $e->getMessage()); // Log the error
    echo "An error occurred.  Check the error log.";
    die(); // Stop execution
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent View Items</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Copy the CSS from view_items.php here */
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
            width: 15%; /* HS Code */
        }

        .data-table th:nth-child(2),
        .data-table td:nth-child(2) {
            width: 15%; /* Item ID */
        }

        .data-table th:nth-child(3),
        .data-table td:nth-child(3) {
            width: 30%; /* Item Name */
        }

        .data-table th:nth-child(4),
        .data-table td:nth-child(4) {
            width: 15%; /* Stock */
            text-align: center;
        }

        .data-table th:nth-child(5),
        .data-table td:nth-child(5) {
            width: 25%; /* Status */
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

         .badge-secondary {
            background-color: rgba(108, 117, 125, 0.1); /* Greyish */
            color: var(--dark); /* Dark text for contrast */
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
                <h2>Item Availability</h2>
                <p>View available items</p>
            </div>
        </div>


        <div class="card">
            <div class="table-responsive">
                <?php if (empty($items)): ?>
                    <div class="no-items">
                        <i class="fas fa-box-open fa-3x" style="color: #dee2e6; margin-bottom: 15px;"></i>
                        <p>No items found in the inventory.</p>
                    </div>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>HS Code</th>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <?php
                                    $status = ''; // Initialize status
                                    $row_class = '';

                                    $item_id = htmlspecialchars($item['item_id']);
                                    $expire_date_db = htmlspecialchars($item['expire_date']);

                                    if ($item['expire_date'] !== null && $item['expire_date'] !== '') {
                                        try {
                                            $expire_date = new DateTime($item['expire_date']);

                                            $today = new DateTime();
                                            $interval = $today->diff($expire_date);
                                            $days_left = $interval->format('%r%a');

                                            if ($days_left < 0) {
                                                $row_class = 'expired';
                                                $status = '<span class="badge badge-danger">Expired</span>';
                                            } elseif ($days_left <= 30) {
                                                $row_class = 'expiring-soon';
                                                $status = '<span class="badge badge-warning">Expiring soon</span>';
                                            } else {
                                                $status = '<span class="badge badge-success">Active</span>';
                                            }
                                        } catch (Exception $e) {
                                            $status = '<span class="badge badge-danger">Invalid Date</span>';
                                            error_log("Invalid date format for item " . $item_id . ": " . $expire_date_db); // Log the error
                                        }
                                    } else {
                                        $status = '<span class="badge badge-secondary">No Expiry</span>'; // Or some other indicator
                                    }
                                ?>
                                <tr class="<?= $row_class ?>">
                                    <td><?= htmlspecialchars($item['hs_code']) ?></td>
                                    <td><?= htmlspecialchars($item['item_id']) ?></td>
                                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                                    <td><?= htmlspecialchars($item['stock']) ?></td>
                                    <td><?= $status ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>