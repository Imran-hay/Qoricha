<?php
session_start();

// 1. Database Connection
require 'config.php';

// 2. Authentication Check (Simplified)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    //header("Location: login.php");
    //exit();
}

// 3. Sidebar Integration
require 'sidebar.php';

// 4. Page Logic

// Initialize variables
$agent_id = $_GET['agent_id'] ?? '';
$sale_id = $_GET['sale_id'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$withholding_status_filter = $_GET['withholding_status'] ?? '';
$error_message = '';
$success_message = '';
$withholdings = [];

// Handle form submission (e.g., marking as ticketed/paid)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Example: Mark as Paid
    if (isset($_POST['mark_paid'])) {
        $withholding_id = $_POST['withholding_id'];
        try {
            $sql = "UPDATE withholdings SET withholding_status = 'paid', date_paid = NOW() WHERE withholding_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$withholding_id]);
            $success_message = "Withholding marked as paid!";
        } catch (PDOException $e) {
            $error_message = "Error updating withholding: " . $e->getMessage();
        }
    }
}

// Fetch withholdings data
$sql = "SELECT w.*, u.fullname AS agent_name
        FROM withholdings w
        JOIN users u ON w.agent_id = u.user_id
        WHERE 1=1"; // Start with a "true" condition

$params = [];

if ($agent_id) {
    $sql .= " AND w.agent_id = ?";
    $params[] = $agent_id;
}
if ($sale_id) {
    $sql .= " AND w.sale_id = ?";
    $params[] = $sale_id;
}
if ($from && $to) {
    $sql .= " AND w.date_created BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
}
if ($withholding_status_filter) {
    $sql .= " AND w.withholding_status = ?";
    $params[] = $withholding_status_filter;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$withholdings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch agents for the dropdown
$agents = $pdo->query("SELECT user_id, fullname FROM users WHERE role = 'agent'")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withholding Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <style>
        /* General body and content styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .content {
            margin-left: 120px; /* Adjust for sidebar width */
            padding: 20px;
        }

        .page-header {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .page-title h1 {
            font-size: 24px;
            margin: 0;
        }

        .page-title p {
            color: #777;
            margin: 5px 0 0;
            font-size: 14px;
        }

        /* Form Styling */
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            width: 100%; /* Full width labels */
        }

        form input,
        form select {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            width: 200px; /* Adjust input width */
            margin-right: 10px;
        }

        form button {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #0056b3;
        }

        /* Table Styling */
        .withholding-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
            background-color: #fff;
        }

        .withholding-table th,
        .withholding-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .withholding-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .withholding-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Message Styling */
        .message {
            margin-bottom: 15px;
            padding: 12px;
            border-radius: 4px;
            text-align: center;
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

        /* Actions Styling */
        .actions-form {
            display: inline;
        }

        .actions-form button {
            background-color: #28a745; /* Green */
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .actions-form button:hover {
            background-color: #218838;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 10px;
            }

            form {
                flex-direction: column;
                align-items: stretch;
            }

            form input,
            form select {
                width: 100%;
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Withholding Management</h1>
                <p>Manage withholding amounts for sales and commissions.</p>
            </div>
        </div>

        <?php if ($error_message): ?>
            <div class="message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="message success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <form method="get">
            <label for="agent_id">Agent:</label>
            <select name="agent_id" id="agent_id">
                <option value="">All Agents</option>
                <?php foreach ($agents as $agent): ?>
                    <option value="<?= htmlspecialchars($agent['user_id']) ?>" <?= $agent_id == $agent['user_id'] ? 'selected' : '' ?>><?= htmlspecialchars($agent['fullname']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="sale_id">Sale ID:</label>
            <input type="text" name="sale_id" id="sale_id" value="<?= htmlspecialchars($sale_id) ?>">

            <label for="from">From:</label>
            <input type="date" name="from" id="from" value="<?= htmlspecialchars($from) ?>">

            <label for="to">To:</label>
            <input type="date" name="to" id="to" value="<?= htmlspecialchars($to) ?>">

            <label for="withholding_status">Status:</label>
            <select name="withholding_status" id="withholding_status">
                <option value="">All Statuses</option>
                <option value="ticketed" <?= $withholding_status_filter == 'ticketed' ? 'selected' : '' ?>>Ticketed</option>
                <option value="paid" <?= $withholding_status_filter == 'paid' ? 'selected' : '' ?>>Paid</option>
                <option value="unticketed_unpaid" <?= $withholding_status_filter == 'unticketed_unpaid' ? 'selected' : '' ?>>Unticketed/Unpaid</option>
            </select>

            <button type="submit">Filter</button>
        </form>

        <h2>Withholding List</h2>
        <table class="withholding-table">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Agent Name</th>
                    <th>Amount</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Date Created</th>
                    <th>Date Ticketed</th>
                    <th>Date Paid</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($withholdings): ?>
                    <?php foreach ($withholdings as $withholding): ?>
                        <tr>
                            <td><?= htmlspecialchars($withholding['sale_id']) ?></td>
                            <td><?= htmlspecialchars($withholding['agent_name']) ?></td>
                            <td><?= htmlspecialchars($withholding['withholding_amount']) ?></td>
                            <td><?= htmlspecialchars($withholding['withholding_reason']) ?></td>
                            <td><?= htmlspecialchars($withholding['withholding_status']) ?></td>
                            <td><?= htmlspecialchars($withholding['date_created']) ?></td>
                            <td><?= htmlspecialchars($withholding['date_ticketed']) ?></td>
                            <td><?= htmlspecialchars($withholding['date_paid']) ?></td>
                            <td>
                                <form method="post" class="actions-form">
                                    <input type="hidden" name="withholding_id" value="<?= htmlspecialchars($withholding['withholding_id']) ?>">
                                    <button type="submit" name="mark_paid">Mark as Paid</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No withholdings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>