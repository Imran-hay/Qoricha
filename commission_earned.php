<?php
session_start();

// 1. Database Connection
require 'config.php'; // Assuming this file defines $pdo

// 2. Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'agent') {
    header("Location: login.php"); // Replace with your login page URL
    exit();
}

// 3. Sidebar Integration
require 'agent_sidebar.php'; // Include your agent sidebar

// 4. Page Logic

// Initialize variables
$agent_id = (int)$_SESSION['user_id']; // Get agent ID from session
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$error_message = '';
$sales = [];
$total_commission = 0;

// Fetch sales data for the agent
if ($from && $to) {
    $sql = "SELECT sale_id, customer_name, total_amount, due_date FROM sales WHERE user_id = ? AND due_date BETWEEN ? AND ?";
    $params = [$agent_id, $from, $to];

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
    <title>My Commission</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        /* General body and content styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: #4a5568;
            margin: 0;
            padding: 0;
        }

        .content {
            margin-left: 350px; /* Adjust for sidebar width */
            padding: 30px;
            transition: var(--transition);
        
        }
        .page-title h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--dark);
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
            padding: 50px;
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

        /* New styles for commission summary */
        .commission-summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f0f0; /* Light gray background */
            border-radius: 5px;
            text-align: right;
            font-size: 16px;
            font-weight: 500;
        }

        .commission-amount {
            color: #28a745; /* Green color for the amount */
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="content">
        <!-- Commission Calculator Content -->
        <div class="page-header">
            <div class="page-title">
                <h1>My Commission</h1>
                <p>View your commission earnings based on sales data.</p>
            </div>
        </div>

        <div class="commission-calculator-container">
            <?php if ($error_message): ?>
                <div class="message error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <form method="get">
                <label for="from">From:</label>
                <input type="date" name="from" id="from" value="<?= htmlspecialchars($from) ?>">

                <label for="to">To:</label>
                <input type="date" name="to" id="to" value="<?= htmlspecialchars($to) ?>">

                <button type="submit">Calculate</button>
            </form>

            <?php if ($sales): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Customer Name</th>
                            <th>Total Amount</th>
                            <th>Sale Date</th>
                            <th>Commission</th>
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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="commission-summary">
                    Total Commission: <span class="commission-amount">₱<?= htmlspecialchars(number_format($total_commission, 2)) ?></span>
                </div>
            <?php elseif ($from && $to): ?>
                <p>No sales found for the selected date range.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>