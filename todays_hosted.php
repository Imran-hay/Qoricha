<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    //header("Location: login.php");
    //exit();
}
require 'agent_sidebar.php'; // Include your sidebar for navigation
require 'config.php'; // Include your database connection settings

// Fetch customers who made purchases today
$stmt = $pdo->prepare("
    SELECT 
        c.customer_id, 
        c.name, 
        c.email, 
        c.phone, 
        c.tin 
    FROM customers c
    JOIN sales s ON c.name = s.name
    WHERE s.user_id = :agent_id AND DATE(s.created_at) = CURDATE()
");
$stmt->execute(['agent_id' => $_SESSION['user_id']]);
$today_customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Hosted Customers</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
            color: #343a40;
        }

        .content {
            margin-left: 220px; /* Adjust for sidebar width */
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #764ba2;
            margin-bottom: 25px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden; /* Ensures the box-shadow and border-radius apply correctly */
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f2f2f2; /* Light gray for the header */
            color: #343a40; /* Dark gray for the header text */
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e9ecef; /* A slightly darker gray on hover */
            transition: background-color 0.3s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }
            table {
                margin-top: 15px;
            }
            th, td {
                padding: 10px;
                font-size: 14px;
            }
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Today's Hosted Customers</h1>
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
                <?php if (count($today_customers) > 0): ?>
                    <?php foreach ($today_customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['customer_id']); ?></td>
                            <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td><?php echo htmlspecialchars($customer['tin']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No customers hosted today.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>