<?php
session_start();

// Include database configuration
require 'config.php';

// Ensure the user is logged in and has a user ID
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    // exit();
}

// (Optional) Check User Role
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['role'] !== 'agent') {
        echo "Access Denied."; // Or redirect to an error page
        exit();
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}

require 'agent_sidebar.php'; // Use the agent sidebar

$agent_id = (int)$_SESSION['user_id']; // Sanitize user ID

// Fetch credit sales for the agent
try {
    $stmt = $pdo->prepare("
        SELECT sale_id, customer_name, total_amount, created_at
        FROM sales
        WHERE user_id = ? AND payment_type = 'credit' AND status = 'approved'
        ORDER BY created_at DESC
    ");
    $stmt->execute([$agent_id]);
    $credit_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit(); // Or handle the error more gracefully
}

// Calculate total commission (assuming 1% commission)
$commission_rate = 0.01;
$total_commission = 0;

foreach ($credit_sales as $sale) {
    $total_commission += $sale['total_amount'] * $commission_rate;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Credit Sales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Copy the CSS from agent_dashboard.php or view_items.php here */
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --primary-light: #e6f0ff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #fd7e14;
            --info: #17a2b8;
            --dark: #343a40;
            --light: #f8f9fa;
            --white: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 8px 22px rgba(0,0,0,0.1);
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

        .data-table th,
        .data-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table th {
            background-color: #f7fafc;
            font-weight: 600;
            color: #4a5568;
        }

        .data-table tr:hover {
            background-color: #edf2f7;
        }

        .commission-summary {
            margin-top: 20px;
            padding: 15px;
            background-color: var(--light);
            border-radius: var(--border-radius);
            text-align: right;
            font-size: 16px;
            font-weight: 500;
        }

        .commission-amount {
            color: var(--success);
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h2>Credit Sales</h2>
                <p>Your credit sales and commissions</p>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <?php if (count($credit_sales) > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Customer Name</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($credit_sales as $sale): ?>
                                <tr>
                                    <td><?= htmlspecialchars($sale['sale_id']) ?></td>
                                    <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                                    <td>₱<?= number_format($sale['total_amount'], 2) ?></td>
                                    <td><?= date('M d, Y', strtotime($sale['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No credit sales found.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="commission-summary">
            Total Commission: <span class="commission-amount">₱<?= number_format($total_commission, 2) ?></span>
        </div>
    </div>
</body>
</html>