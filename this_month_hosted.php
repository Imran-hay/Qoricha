<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit();
}
require 'agent_sidebar.php'; // Include your sidebar for navigation
require 'config.php'; // Include your database connection settings

// Fetch customers who made purchases this month
$stmt = $pdo->prepare("
    SELECT c.customer_id, c.name, c.email, c.phone, c.tin_number 
    FROM customers c
    JOIN transactions t ON c.customer_id = t.customer_id
    WHERE t.agent_id = :agent_id AND MONTH(t.created_at) = MONTH(CURDATE()) AND YEAR(t.created_at) = YEAR(CURDATE())
");
$stmt->execute(['agent_id' => $_SESSION['user_id']]);
$this_month_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>This Month Hosted Customers</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .content {
            margin-left: 220px; /* Adjust for sidebar width */
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #f2f2f2;
        }
        tr:hover {
            background: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>This Month Hosted Customers</h1>
        <table>
            <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>TIN Number</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($this_month_customers) > 0): ?>
                    <?php foreach ($this_month_customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['customer_id']); ?></td>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td><?php echo htmlspecialchars($customer['tin_number']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No customers hosted this month.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>