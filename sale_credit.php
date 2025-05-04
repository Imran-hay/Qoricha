<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit();
}
require 'sidebar.php'; // Include your sidebar for navigation
require 'config.php'; // Include your database connection settings

// Pagination settings
$limit = 10; // Number of items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Fetch total credit sales for pagination
$total_stmt = $pdo->prepare("
    SELECT COUNT(*) FROM transactions 
    WHERE agent_id = :agent_id AND payment_method = 'credit'
");
$total_stmt->execute(['agent_id' => $_SESSION['user_id']]);
$total_sales = $total_stmt->fetchColumn();
$total_pages = ceil($total_sales / $limit);

// Fetch credit sales transactions for the logged-in agent with pagination
$stmt = $pdo->prepare("
    SELECT t.transaction_id, c.name AS customer_name, t.amount, t.created_at 
    FROM transactions t
    JOIN customers c ON t.customer_id = c.customer_id
    WHERE t.agent_id = :agent_id AND t.payment_method = 'credit'
    LIMIT :limit OFFSET :start
");
$stmt->bindValue('agent_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue('limit', $limit, PDO::PARAM_INT);
$stmt->bindValue('start', $start, PDO::PARAM_INT);
$stmt->execute();
$credit_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Sales Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .content {
            margin-left: 220px; /* Adjust for sidebar width */
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-bottom: 20px;
            color: #0a888f; /* Updated heading color */
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
            font-weight: bold;
        }
        tr:hover {
            background: #e9e9e9; /* Lighter hover color */
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            padding: 10px 15px;
            margin: 0 5px;
            background: #0a888f;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .pagination a:hover {
            background: #0a7b7f; /* Darker shade on hover */
        }
        .pagination span {
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Credit Sales Report</h1>
        <table>
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Customer Name</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($credit_sales) > 0): ?>
                    <?php foreach ($credit_sales as $sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sale['transaction_id']); ?></td>
                            <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($sale['amount'], 2)); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($sale['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="no-data">No credit sales found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>