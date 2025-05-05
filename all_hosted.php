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
$search = $_GET['search'] ?? '';
$error_message = '';
$success_message = '';
$customers = [];

// Fetch customers data
$sql = "SELECT c.*, u.fullname AS agent_name
        FROM customers c
        LEFT JOIN users u ON c.agent_id = u.user_id
        WHERE 1=1"; // Start with a "true" condition

$params = [];

if ($search) {
    $sql .= " AND (c.name LIKE ? OR c.email LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hosted Customers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
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

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        form input[type="text"] {
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            width: 300px;
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

        .customer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
            background-color: #fff;
        }

        .customer-table th,
        .customer-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .customer-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .customer-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .customer-table a {
            color: #007bff;
            text-decoration: none;
        }

        .customer-table a:hover {
            text-decoration: underline;
        }

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

        /* Responsive Design */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                padding: 10px;
            }

            form {
                padding: 15px;
            }

            form input[type="text"] {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Hosted Customers</h1>
                <p>Manage hosted customer accounts.</p>
            </div>
        </div>

        <?php if ($error_message): ?>
            <div class="message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="message success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <form method="get">
            <label for="search">Search:</label>
            <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <h2>Customer List</h2>
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Agent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($customers): ?>
    <?php foreach ($customers as $customer): ?>
        <tr>
            <td><?= htmlspecialchars($customer['name'] ?? '') ?></td>
            <td><?= htmlspecialchars($customer['email'] ?? '') ?></td>
            <td><?= htmlspecialchars($customer['phone'] ?? '') ?></td>
            <td><?= htmlspecialchars($customer['address'] ?? '') ?></td>
            <td><?= htmlspecialchars($customer['agent_name'] ?? 'N/A') ?></td>
            <td>
                <a href="customer_details.php?customer_id=<?= htmlspecialchars($customer['customer_id'] ?? '') ?>">View Details</a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6">No customers found.</td>
    </tr>
<?php endif; ?>
 </tbody>
        </table>
    </div>
</body>
</html>