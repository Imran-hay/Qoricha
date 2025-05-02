<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';
require 'cashier_sidebar.php'; // Include the sidebar for the logged-in user

$user_id = $_SESSION['user_id'];
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


// Handle form submission for adding a new withdrawal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_withdrawal'])) {
    $amount = (float)$_POST['amount'];

    // Start a transaction
    $inTransaction = $pdo->inTransaction();
    if (!$inTransaction) {
        $pdo->beginTransaction();
    }

    try {
        // Check if the user has sufficient balance
        $stmt = $pdo->prepare("SELECT current_balance FROM balance LIMIT 1");
        $stmt->execute();
        $balanceData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$balanceData || $amount > $balanceData['current_balance']) {
            throw new Exception("Insufficient balance.");
        }

        // Insert the withdrawal
        $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, amount) VALUES (?, ?)");
        if (!$stmt->execute([$user_id, $amount])) {
            throw new Exception("Failed to add withdrawal.");
        }

        // Update the balance (subtract the withdrawal amount)
        updateBalance($pdo, $amount, false); // false for subtraction

        // Commit the transaction if we started it
        if (!$inTransaction) {
            $pdo->commit();
        }

        $message = "Withdrawal added successfully!";
    } catch (Exception $e) {
        // Rollback the transaction if we started it AND we're still in a transaction
        if (!$inTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = "Error adding withdrawal: " . $e->getMessage();
        error_log("Withdrawal error: " . $e->getMessage());
    }
}

// Fetch withdrawal history
try {
    $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY withdrawal_date DESC");
    $stmt->execute([$user_id]);
    $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching withdrawal history: " . $e->getMessage();
    $withdrawals = [];
}

// Fetch current balance
try {
    $stmt = $pdo->prepare("SELECT current_balance FROM balance LIMIT 1");
    $stmt->execute();
    $balanceData = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_balance = $balanceData ? $balanceData['current_balance'] : 0;
} catch (PDOException $e) {
    $message = "Error fetching current balance: " . $e->getMessage();
    $current_balance = 0;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Balance</title>
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
            max-width: 1200px;
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

        /* Balance Display */
        .balance-display {
            background-color: #3498db;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .balance-display h2 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .balance-display p {
            font-size: 1.2em;
        }

        /* Withdrawal Form */
        .withdrawal-form {
            margin-bottom: 30px;
            padding: 25px;
            border-radius: 10px;
            background-color: #ecf0f1;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        }

        .withdrawal-form h3 {
            color: #34495e;
            margin-bottom: 15px;
            font-size: 1.8em;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #34495e;
            font-weight: bold;
            font-size: 0.95em;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            font-size: 16px;
            color: #34495e;
            box-sizing: border-box;
        }

        .withdrawal-form button[type="submit"] {
            background-color: #27ae60;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .withdrawal-form button[type="submit"]:hover {
            background-color: #219653;
        }

        /* Withdrawal History Table */
        .withdrawal-history {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        .withdrawal-history th,
        .withdrawal-history td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .withdrawal-history th {
            background-color: #3498db;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 1.1em;
        }

        .withdrawal-history tbody tr:hover {
            background-color: #f5f5f5;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 98%;
                padding: 25px;
            }

            .balance-display h2 {
                font-size: 2em;
            }

            .balance-display p {
                font-size: 1em;
            }

            .withdrawal-form input[type="text"] {
                font-size: 14px;
            }

            .withdrawal-form button[type="submit"] {
                font-size: 14px;
            }

            .withdrawal-history {
                overflow-x: auto;
            }

            .withdrawal-history th,
            .withdrawal-history td {
                white-space: nowrap;
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Withdraw Balance</h1>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="balance-display">
            <h2>Current Balance</h2>
            <p>$<?php echo htmlspecialchars(number_format($current_balance, 2)); ?></p>
        </div>

        <div class="withdrawal-form">
            <h3>Add New Withdrawal</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="text" id="amount" name="amount" required>
                </div>
                <button type="submit" name="add_withdrawal">Withdraw</button>
            </form>
        </div>

        <table class="withdrawal-history">
            <thead>
                <tr>
                    <th>Withdrawal ID</th>
                    <th>Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($withdrawals)): ?>
                    <tr>
                        <td colspan="3">No withdrawals found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($withdrawals as $withdrawal): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($withdrawal['withdrawal_id']); ?></td>
                            <td><?php echo htmlspecialchars($withdrawal['withdrawal_date']); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($withdrawal['amount'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>