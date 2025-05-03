<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'cashier') {
    header("Location: login.php");
    exit();
}

require 'config.php';
require 'cashier_sidebar.php'; // Include the cashier sidebar

$message = "";

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

// Handle form submission for approving or rejecting a repayment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_repayment'])) {
        $repayment_id = $_POST['repayment_id'];
        $amount = (float)$_POST['amount']; // Get amount from the form
        $inTransaction = $pdo->inTransaction();
        if (!$inTransaction) {
            $pdo->beginTransaction();
        }

        try {
            // Update the repayment status to 'completed'
            $stmt = $pdo->prepare("UPDATE repayments SET status = 'completed' WHERE repayment_id = ?");
            if (!$stmt->execute([$repayment_id])) {
                throw new Exception("Failed to update repayment status.");
            }

            // Update the balance (add the repayment amount)
            updateBalance($pdo, $amount, true); // true for addition

            // Commit the transaction if we started it
            if (!$inTransaction) {
                $pdo->commit();
            }

            $message = "Repayment approved successfully!";
        } catch (Exception $e) {
            // Rollback the transaction if we started it AND we're still in a transaction
            if (!$inTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = "Error approving repayment: " . $e->getMessage();
            error_log("Repayment approval error: " . $e->getMessage());
        }
    } elseif (isset($_POST['reject_repayment'])) {
        $repayment_id = $_POST['repayment_id'];

        try {
            // Update the repayment status to 'rejected'
            $stmt = $pdo->prepare("UPDATE repayments SET status = 'failed' WHERE repayment_id = ?");
            $stmt->execute([$repayment_id]);
            $message = "Repayment rejected successfully!";
        } catch (PDOException $e) {
            $message = "Error rejecting repayment: " . $e->getMessage();
        }
    }
}

// Fetch all repayments
try {
    $stmt = $pdo->prepare("
        SELECT repayments.*, sales.sale_id, sales.total_amount
        FROM repayments
        JOIN sales ON repayments.sale_id = sales.sale_id
        ORDER BY repayments.repayment_date DESC
    ");
    $stmt->execute();
    $repayments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching repayments: " . $e->getMessage();
    $repayments = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Repayments</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            width: 95%;
            max-width: 1400px;
            background-color: #fff;
            padding: 40px;
            margin: 30px auto;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 3em;
        }

        .message {
            text-align: center;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            color: #fff;
        }

        .success {
            background-color: #27ae60;
        }

        .error {
            background-color: #e74c3c;
        }

        /* Repayments List Table */
        .repayments-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        .repayments-list th,
        .repayments-list td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .repayments-list th {
            background-color: #3498db;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 1.1em;
        }

        .repayments-list tbody tr:hover {
            background-color: #f5f5f5;
        }

        .repayments-list .pending {
            background-color: #f39c12;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8em;
        }

        .repayments-list .completed {
            background-color: #27ae60;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8em;
        }

        .repayments-list .failed {
            background-color: #e74c3c;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8em;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: left;
        }

        .action-buttons button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
        }

        .action-buttons .approve {
            background-color: #27ae60;
            color: #fff;
        }

        .action-buttons .approve:hover {
            background-color: #219653;
        }

        .action-buttons .reject {
            background-color: #e74c3c;
            color: #fff;
        }

        .action-buttons .reject:hover {
            background-color: #c0392b;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 98%;
                padding: 25px;
            }

            .repayments-list {
                overflow-x: auto;
            }

            .repayments-list th,
            .repayments-list td {
                white-space: nowrap;
                font-size: 0.9em;
            }

            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .action-buttons button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify Repayments</h1>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <table class="repayments-list">
            <thead>
                <tr>
                    <th>Repayment ID</th>
                    <th>Sale ID</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($repayments)): ?>
                    <tr>
                        <td colspan="6">No repayments found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($repayments as $repayment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($repayment['repayment_id']); ?></td>
                            <td><?php echo htmlspecialchars($repayment['sale_id']); ?></td>
                            <td><?php echo htmlspecialchars($repayment['repayment_date']); ?></td>
                            <td><?php echo htmlspecialchars($repayment['amount']); ?></td>
                            <td>
                                <?php
                                    $statusClass = strtolower($repayment['status']);
                                    echo '<span class="' . htmlspecialchars($statusClass) . '">' . htmlspecialchars($repayment['status']) . '</span>';
                                ?>
                            </td>
                            <td>
                                <?php if ($repayment['status'] == 'pending'): ?>
                                    <div class="action-buttons">
                                        <form method="POST" action="">
                                            <input type="hidden" name="repayment_id" value="<?php echo htmlspecialchars($repayment['repayment_id']); ?>">
                                            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($repayment['amount']); ?>"> <!-- Amount to add to balance -->
                                            <button type="submit" name="approve_repayment" class="approve">Approve</button>
                                        </form>
                                        <form method="POST" action="">
                                            <input type="hidden" name="repayment_id" value="<?php echo htmlspecialchars($repayment['repayment_id']); ?>">
                                            <button type="submit" name="reject_repayment" class="reject">Reject</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>