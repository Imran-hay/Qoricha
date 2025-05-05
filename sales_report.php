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
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: #f9f9f9;
            margin-left: 280px;
        }
        .content {
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            margin: 20px auto;
            margin-left: 80px;
        }
        h1 {
            text-align: center;
            color: #764ba2;
        }
        form {
            margin-bottom: 20px;
        }
        form input, form select {
            padding: 8px;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
        }
        th {
            background: #f4f4f4;
        }
    </style>
</head>
<body>
<div class="content">
    <h1>Sales Report</h1>

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
                        <td><?= htmlspecialchars($sale['agentgit_name']) ?></td>
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
