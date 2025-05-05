<?php
session_start();

// 1. Database Connection
require 'config.php'; // Assuming this file defines $pdo

// 2. Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    // header("Location: login.php"); // Replace with your login page URL
    // exit();
}

// 3. Sidebar Integration
require 'sidebar.php'; // Include your sidebar

// 4. Page Logic

// Initialize variables
$agent_id = $_GET['agent_id'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$commission_status_filter = $_GET['commission_status'] ?? '';
$error_message = '';
$success_message = '';
$sales = [];
$total_commission = 0;

// Handle form submission for marking commissions as paid
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_paid'])) {
    // Get the sale IDs to mark as paid
    $sale_ids = $_POST['sale_ids'];

    // Validate that $sale_ids is an array and not empty
    if (is_array($sale_ids) && !empty($sale_ids)) {
        // Prepare the SQL query (using a prepared statement to prevent SQL injection)
        $placeholders = implode(',', array_fill(0, count($sale_ids), '?')); // Create placeholders for the IN clause
        $sql = "UPDATE sales SET commission_status = 'paid' WHERE sale_id IN ($placeholders)";
        $stmt = $pdo->prepare($sql);

        // Execute the query
        try {
            $pdo->beginTransaction(); // Start a transaction for atomicity
            $stmt->execute($sale_ids);
            $pdo->commit(); // Commit the transaction if successful

            $success_message = "Commissions marked as paid successfully!";

        } catch (PDOException $e) {
            $pdo->rollBack(); // Rollback the transaction if there's an error
            $error_message = "Error marking commissions as paid: " . $e->getMessage();
        }
    } else {
        $error_message = "No sales selected to mark as paid.";
    }
}

// Fetch agents for the dropdown
$agents = $pdo->query("SELECT user_id, fullname FROM users WHERE role = 'agent'")->fetchAll(PDO::FETCH_ASSOC);

// Fetch sales data
if ($agent_id && $from && $to) {
    $sql = "SELECT sale_id, customer_name, total_amount, due_date, commission_status FROM sales WHERE user_id = ? AND due_date BETWEEN ? AND ?";
    $params = [$agent_id, $from, $to];

    // Add commission status filter if selected
    if ($commission_status_filter) {
        $sql .= " AND commission_status = ?";
        $params[] = $commission_status_filter;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total commission
    foreach ($sales as $sale) {
        $total_commission += $sale['total_amount'] * 0.01; // 1% commission
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Calculator</title>
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

        /* Commission Calculator Container */
        .commission-calculator-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Form Styling */
        form {
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        tr:nth-child(even) {
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

        /* Total Commission Styling */
        .total-commission {
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 10px;
            }

            .commission-calculator-container {
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
        <!-- Commission Calculator Content -->
        <div class="page-header">
            <div class="page-title">
                <h1>Commission Calculator</h1>
                <p>Calculate agent commissions based on sales data.</p>
            </div>
        </div>

        <div class="commission-calculator-container">
            <?php if ($error_message): ?>
                <div class="message error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="message success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <form method="get">
                <label for="agent_id">Agent:</label>
                <select name="agent_id" id="agent_id">
                    <option value="">Select Agent</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= htmlspecialchars($agent['user_id']) ?>" <?= $agent_id == $agent['user_id'] ? 'selected' : '' ?>><?= htmlspecialchars($agent['fullname']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="from">From:</label>
                <input type="date" name="from" id="from" value="<?= htmlspecialchars($from) ?>">

                <label for="to">To:</label>
                <input type="date" name="to" id="to" value="<?= htmlspecialchars($to) ?>">

                <label for="commission_status">Commission Status:</label>
                <select name="commission_status" id="commission_status">
                    <option value="">All</option>
                    <option value="unpaid" <?= $commission_status_filter == 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                    <option value="paid" <?= $commission_status_filter == 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="partially_paid" <?= $commission_status_filter == 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                </select>

                <button type="submit">Calculate</button>
            </form>

            <?php if ($sales): ?>
                <form method="post">
                    <table>
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Customer Name</th>
                                <th>Total Amount</th>
                                <th>Sale Date</th>
                                <th>Commission</th>
                                <th>Commission Status</th>
                                <th>Mark Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sale['sale_id']) ?></td>
                                    <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($sale['total_amount']) ?></td>
                                    <td><?= htmlspecialchars($sale['due_date']) ?></td>
                                    <td><?= htmlspecialchars($sale['total_amount'] * 0.01) ?></td>
                                    <td><?= htmlspecialchars($sale['commission_status'] ?? 'Not Set') ?></td>
                                    <td><input type="checkbox" name="sale_ids[]" value="<?= htmlspecialchars($sale['sale_id']) ?>"></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="mark_paid">Mark as Paid</button>
                </form>

                <div class="total-commission">
                    Total Commission: <?= htmlspecialchars(number_format($total_commission, 2)) ?>
                </div>
            <?php elseif ($agent_id && $from && $to): ?>
                <p>No sales found for the selected agent and date range.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>