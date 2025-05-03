<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    /*header("Location: login.php");
    exit()*/;
}
require 'agent_sidebar.php';
require 'config.php';

// Fetch sales approvals for the logged-in agent that are pending
try {
    $stmt = $pdo->prepare("SELECT s.sale_id, s.customer_name, s.quantity, i.item_name, s.total_amount, s.due_date, s.payment_type, s.status
                            FROM sales s
                            JOIN items i ON s.item_id = i.item_id
                            WHERE s.user_id = ? AND s.status = 'pending'");
    $stmt->execute([$_SESSION['user_id']]);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching pending sales: " . $e->getMessage());
    $sales = []; // Assign an empty array to avoid errors later
    $message = "Error fetching pending sales: " . $e->getMessage(); // Optional: Display an error message
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Pending Sales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* General body and content styles (consistent with dashboard) */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            color: #343a40;
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 240px; /* Sidebar width */
            transition: margin-left 0.3s ease;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 900px; /* Increased max-width for table */
            margin: 20px auto;
        }

        .content.shifted {
            margin-left: 0;
        }

        h1 {
            margin-bottom: 20px;
            text-align: center;
            color: #764ba2; /* Consistent color */
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 16px;
        }

        th {
            background-color: #f9f9f9;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        /* Message Styles */
        .message {
            margin-top: 20px;
            text-align: center;
            color: #d9534f;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
            }
            th, td {
                font-size: 14px;
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Pending Sales</h1>
        <?php if (isset($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Customer Name</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Payment Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($sales) > 0): ?>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                            <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($sale['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($sale['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($sale['total_amount']); ?></td>
                            <td><?php echo htmlspecialchars($sale['due_date']); ?></td>
                            <td><?php echo htmlspecialchars($sale['payment_type']); ?></td>
                            <td><?php echo htmlspecialchars($sale['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No pending sales found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>