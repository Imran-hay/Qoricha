<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    // header("Location: login.php");
}

require 'sidebar.php';
require 'config.php';

// Initialize filter values
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$product_id = $_GET['item_id'] ?? '';
$agent_id = $_GET['user_id'] ?? '';
$payment_type = $_GET['payment_type'] ?? '';

// Build WHERE clause dynamically
$where = [];
$params = [];

if (!empty($from) && !empty($to)) {
    $where[] = "s.due_date BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
}
if (!empty($product_id)) {
    $where[] = "s.item_id = ?";
    $params[] = $product_id;
}
if (!empty($agent_id)) {
    $where[] = "s.user_id = ?";
    $params[] = $agent_id;
}
if (!empty($payment_type)) {
    $where[] = "s.payment_type = ?";
    $params[] = $payment_type;
}

$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

// Fetch sales with user and item info
$query = $pdo->prepare("
    SELECT s.*, u.fullname AS agent_name, i.item_name AS item_name 
    FROM sales s 
    LEFT JOIN users u ON s.user_id = u.user_id 
    LEFT JOIN items i ON s.item_id = i.item_id 
    $whereClause 
    ORDER BY s.due_date DESC
");
$query->execute($params);
$sales = $query->fetchAll(PDO::FETCH_ASSOC);

// Get items and agents for filter dropdowns
$products = $pdo->query("SELECT item_id, item_name FROM items")->fetchAll(PDO::FETCH_ASSOC);
$agents = $pdo->query("SELECT user_id, fullname FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* General body and content styles (consistent with dashboard) */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            margin: 0;
            padding: 0px;
            color: ##4a5568;
        }

        .content {
            margin-left: 120px; /* Adjust for sidebar width */
            padding: 30px;
            transition: var(--transition);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .page-title h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .page-title p {
            color: #718096;
            margin: 5px 0 0;
            font-size: 14px;
        }



        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background: #007bff;
            color: white;
        }

        tr:hover {
            background: #f1f1f1;
        }

        /* Message Styles */
        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Credit Sale Form Styles (Initially Hidden) */
        .credit-sale-form {
            display: none;
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .credit-sale-form h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            text-align: left;
        }

        .credit-sale-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .credit-sale-form input[type="text"],
        .credit-sale-form input[type="number"],
        .credit-sale-form input[type="date"],
        .credit-sale-form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .credit-sale-form button {
            background-color: #28a745; /* Green */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .credit-sale-form button:hover {
            background-color: #218838;
        }

        /* Add Credit Sale Button */
        .add-credit-sale-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 15px;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .add-credit-sale-button:hover {
            background-color: #0056b3;
        }

        /* Export Button Style */
        .export-button {
            background-color: #6c757d; /* Gray */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            float: right;
            margin-top: 10px;
        }

        .export-button:hover {
            background-color: #5a6268;
        }

        /* Pagination Styles */
        .pagination {
            margin-top: 20px;
            text-align: center;
            clear: both;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            text-decoration: none;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            color: #333;
            border-radius: 4px;
            margin: 0 4px;
        }

        .pagination a:hover {
            background-color: #eee;
        }

        .pagination .current {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination .page-info {
            display: inline-block;
            margin: 0 10px;
            font-size: 16px;
            color: #555;
        }

        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                width: 100%;
            }

            table {
                font-size: 14px;
            }
        }
        form {
            margin-bottom: 20px;
        }
        form input, form select {
            padding: 8px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h1>Sales Report</h1>
            <p>Generate detailed sales reports based on various criteria.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="get">
        <label>From: <input type="date" name="from" value="<?= htmlspecialchars($from) ?>"></label>
        <label>To: <input type="date" name="to" value="<?= htmlspecialchars($to) ?>"></label>

        <label>Product:
            <select name="item_id">
                <option value="">All</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['item_id'] ?>" <?= $product_id == $product['item_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($product['item_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Agent:
            <select name="user_id">
                <option value="">All</option>
                <?php foreach ($agents as $agent): ?>
                    <option value="<?= $agent['user_id'] ?>" <?= $agent_id == $agent['user_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($agent['fullname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>Payment:
            <select name="payment_type">
                <option value="">All</option>
                <option value="cash" <?= $payment_type == 'cash' ? 'selected' : '' ?>>Cash</option>
                <option value="credit" <?= $payment_type == 'credit' ? 'selected' : '' ?>>Credit</option>
            </select>
        </label>

        <button type="submit">Filter</button>
    </form>

    <!-- Sales Table -->
    <table>
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Agent</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Sale Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($sales)): ?>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td><?= htmlspecialchars($sale['sale_id']) ?></td>
                        <td><?= htmlspecialchars($sale['agent_name']) ?></td>
                        <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                        <td><?= htmlspecialchars($sale['item_name']) ?></td>
                        <td><?= htmlspecialchars($sale['quantity']) ?></td>
                        <td><?= htmlspecialchars($sale['total_amount']) ?></td>
                        <td><?= htmlspecialchars($sale['payment_type']) ?></td>
                        <td><?= htmlspecialchars($sale['status']) ?></td>
                        <td><?= htmlspecialchars($sale['due_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" style="text-align:center;">No sales found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>