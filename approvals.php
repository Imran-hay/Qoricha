<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // header("Location: login.php");
    //exit();
}
require 'sidebar.php'; // Assuming you have an admin sidebar
require 'config.php';

// Handle approval/rejection actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sale_id = $_POST['sale_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $new_status = 'approved';
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
    } else {
        // Invalid action
        $message = "Invalid action.";
    }

    if (isset($new_status)) {
        try {
            $stmt = $pdo->prepare("UPDATE sales SET status = ? WHERE sale_id = ?");
            $stmt->execute([$new_status, $sale_id]);
            $message = "Sale status updated successfully.";
        } catch (PDOException $e) {
            $message = "Error updating sale status: " . $e->getMessage();
        }
    }
}

// Fetch all sales
$stmt = $pdo->query("SELECT s.sale_id, s.customer_name, s.quantity, i.item_name, s.total_amount, s.due_date, s.payment_type, s.status, u.fullname AS agent_name
                     FROM sales s
                     JOIN items i ON s.item_id = i.item_id
                     JOIN users u ON s.user_id = u.user_id
                     ORDER BY s.status, s.sale_id");
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Approvals</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0px;
            color: #4a5568;
        }
        .content {
            margin-left: 120px; /* Adjust for sidebar width */
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }
       
        .page-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 30px;
            margin-top: 30px;
        }


        .page-title h1 {
            font-size: 32px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .page-title p {
            color: #718096;
            margin: 5px 0 0;
            font-size: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .approve-button {
            background-color: #28a745; /* Green */
            color: white;
        }
        .approve-button:hover {
            background-color: #218838;
        }
        .reject-button {
            background-color: #dc3545; /* Red */
            color: white;
        }
        .reject-button:hover {
            background-color: #c82333;
        }
        .message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
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
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
                width: 100%;
            }
            table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="page-header">
        <div class="page-title">
          <h1>Credit Sales Approvals</h1>
           <p>Review and manage credit sales submitted by your agents.</p>
        </div>
        <?php if (isset($message)): ?>
            <div class="message <?php echo (strpos($message, 'Error') !== false) ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Customer Name</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th> Date</th>
                    <th>Payment Type</th>
                    <th>Agent</th>
                    <th>Status</th>
                    <th>Actions</th>
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
                            <td>₱<?php echo number_format($sale['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($sale['due_date']); ?></td>
                            <td><?php echo htmlspecialchars($sale['payment_type']); ?></td>
                            <td><?php echo htmlspecialchars($sale['agent_name']); ?></td>
                            <td><?php echo isset($sale['status']) ? htmlspecialchars($sale['status']) : 'N/A'; ?></td>
                            <td>
                                <?php if (isset($sale['status']) && $sale['status'] === 'pending' && isset($sale['payment_type']) && $sale['payment_type'] === 'Credit'): ?>
                                    <div class="action-buttons">
                                        <form method="POST" action="">
                                            <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($sale['sale_id']); ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="approve-button">Approve</button>
                                        </form>
                                        <form method="POST" action="">
                                            <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($sale['sale_id']); ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="reject-button">Reject</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <?php echo isset($sale['status']) ? htmlspecialchars($sale['status']) : 'N/A'; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">No sales found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>