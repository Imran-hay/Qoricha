<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    //header("Location: login.php");
    //exit();
}
if (isset($_GET['download'])) {
    require 'config.php'; // Only require what's needed for the download

    $sale_id = $_GET['download'];

    // Fetch the bank statement file path from the database
    $stmt = $pdo->prepare("SELECT bank_statement FROM sales WHERE sale_id = ?");
    $stmt->execute([$sale_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['bank_statement']) {
        $filepath = $result['bank_statement'];

        if (file_exists($filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        }
    }
    // If we get here, the download failed - we'll continue with the page
}
require 'cashier_sidebar.php'; // Assuming you have a cashier sidebar
require 'config.php';

// Function to update the balance
function updateBalance($pdo, $amount, $is_addition = false) {
    // Check if we're already in a transaction
    $inTransaction = $pdo->inTransaction();

    try {
        // Only begin a new transaction if we're not already in one
        if (!$inTransaction) {
            $pdo->beginTransaction();
        }

        // Get the current balance
        $stmt = $pdo->prepare("SELECT current_balance FROM balance LIMIT 1 FOR UPDATE");
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute balance query.");
        }

        $balance = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$balance) {
            throw new Exception("Balance record not found. Please create a balance record first.");
        }

        $current_balance = (float)$balance['current_balance'];
        $new_balance = $is_addition ? ($current_balance + $amount) : ($current_balance - $amount);

        // Update the balance
        $updateStmt = $pdo->prepare("UPDATE balance SET current_balance = ?");
        if (!$updateStmt->execute([$new_balance])) {
            throw new Exception("Failed to execute balance update.");
        }

        // Only commit if we started the transaction
        if (!$inTransaction) {
            $pdo->commit();
        }

        return true;
    } catch (Exception $e) {
        // Only rollback if we started the transaction AND we're still in a transaction
        if (!$inTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Balance update error: " . $e->getMessage());
        throw $e; // Re-throw the exception to be caught by the calling function
    }
}


// Handle approval/rejection/undo actions
$message = ''; // Initialize message variable
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sale_id = $_POST['sale_id'];
    $action = $_POST['action'];

    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Fetch the total amount for the sale BEFORE updating the status
        $stmt = $pdo->prepare("SELECT total_amount, status FROM sales WHERE sale_id = ?");
        $stmt->execute([$sale_id]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sale) {
            throw new Exception("Sale not found.");
        }

        $total_amount = (float)$sale['total_amount'];
        $old_status = $sale['status'];


        if ($action === 'approve') {
            $new_status = 'approved';
            if ($old_status !== 'approved') {
                updateBalance($pdo, $total_amount, true); // Add to balance
            }
        } elseif ($action === 'reject') {
            $new_status = 'rejected';
            // No balance update needed on rejection
        } elseif ($action === 'undo') {
            $new_status = 'pending';
            if ($old_status === 'approved') {
                updateBalance($pdo, $total_amount, false); // Subtract from balance
            }
        } else {
            // Invalid action
            throw new Exception("Invalid action.");
        }

        // Update the sale status
        $stmt = $pdo->prepare("UPDATE sales SET status = ? WHERE sale_id = ?");
        $stmt->execute([$new_status, $sale_id]);

        // Commit the transaction
        $pdo->commit();

        $message = "Sale status updated successfully.";
    } catch (Exception $e) {
        // Rollback the transaction
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = "Error updating sale status: " . $e->getMessage();
    }
}

// Handle bank statement download
if (isset($_GET['download'])) {
    $sale_id = $_GET['download'];

    // Fetch the bank statement file path from the database
    $stmt = $pdo->prepare("SELECT bank_statement FROM sales WHERE sale_id = ?");
    $stmt->execute([$sale_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result && $result['bank_statement']) {
        $filepath = $result['bank_statement'];

        if (file_exists($filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        } else {
            $message = "Bank statement file not found at: " . htmlspecialchars($filepath);
        }
    } else {
        $message = "Bank statement not found for Sale ID: " . $sale_id;
    }
}

// Fetch all cash sales, including the bank_statement field
$stmt = $pdo->prepare("SELECT s.sale_id, s.customer_name, s.quantity, i.item_name, s.total_amount, s.due_date, s.payment_type, s.status, u.fullname AS agent_name, s.bank_statement
                     FROM sales s
                     JOIN items i ON s.item_id = i.item_id
                     JOIN users u ON s.user_id = u.user_id
                     WHERE s.payment_type = 'cash'
                     ORDER BY s.sale_id");
$stmt->execute([]);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Cash Sales</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .content {
            margin-left: 100px; /* Adjust for sidebar width */
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
            padding-left: 20px;
            margin-left: 20px;
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
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .action-buttons button {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .approve-button {
            background-color: #4CAF50;
            color: white;
        }
        .reject-button {
            background-color: #f44336;
            color: white;
        }
        .undo-button {
            background-color: #008CBA;
            color: white;
        }
        .download-button {
            background-color: #808080; /* Grey color */
            color: white;
        }
    </style>
    <script>
        <?php if ($message): ?>
            alert("<?php echo htmlspecialchars($message); ?>");
        <?php endif; ?>
    </script>
</head>
<body>
    <div class="content">
        <h1>Confirm Cash Sales</h1>
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
                    <th>Bank Statement</th>
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
                            <td><?php echo htmlspecialchars($sale['agent_name']); ?></td>
                            <td><?php echo isset($sale['status']) ? htmlspecialchars($sale['status']) : 'N/A'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if (isset($sale['status']) && $sale['status'] === 'pending'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($sale['sale_id']); ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="approve-button">Approve</button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($sale['sale_id']); ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="reject-button">Reject</button>
                                        </form>
                                    <?php elseif (isset($sale['status']) && ($sale['status'] === 'approved' || $sale['status'] === 'rejected')): ?>
                                        <form method="POST">
                                            <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($sale['sale_id']); ?>">
                                            <input type="hidden" name="action" value="undo">
                                            <button type="submit" class="undo-button">Undo</button>
                                        </form>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                    if (!empty($sale['bank_statement'])) {
                                        echo '<a href="?download=' . htmlspecialchars($sale['sale_id']) . '" class="download-button">Download</a>';
                                    } else {
                                        echo 'Not Available';
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11">No cash sales found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>