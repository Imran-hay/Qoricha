<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'agent') {
    header("Location: login.php");
    exit();
}

require 'config.php';
require 'agent_sidebar.php'; // Include the sidebar for agents

$message = "";

// Handle form submission for adding a new repayment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_repayment'])) {
    $sale_id = $_POST['sale_id'];
    $amount = $_POST['amount'];
    $repayment_date = $_POST['repayment_date'];

    try {
        // Insert the repayment with 'pending' status
        $stmt = $pdo->prepare("INSERT INTO repayments (sale_id, amount, repayment_date, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$sale_id, $amount, $repayment_date]);
        $message = "Repayment added successfully!";
    } catch (PDOException $e) {
        $message = "Error adding repayment: " . $e->getMessage();
    }
}

// Fetch all sales with payment method "Credit" and status "Approved"
try {
    $stmt = $pdo->prepare("
        SELECT sales.*
        FROM sales
        WHERE sales.payment_type = 'Credit' AND sales.status = 'Approved'
        ORDER BY sales.due_date DESC  -- Assuming due_date is relevant for ordering
    ");
    $stmt->execute();
    $credit_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching credit sales: " . $e->getMessage();
    $credit_sales = [];
}

// Function to fetch repayments for a specific sale
function getRepaymentsForSale($pdo, $sale_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM repayments WHERE sale_id = ? ORDER BY repayment_date DESC");
        $stmt->execute([$sale_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching repayments: " . $e->getMessage());
        return [];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Credit Sales & Repayments</title>
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

        /* Sales List Table */
        .sales-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        .sales-list th,
        .sales-list td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .sales-list th {
            background-color: #3498db;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 1.1em;
        }

        .sales-list tbody tr:hover {
            background-color: #f5f5f5;
        }

        /* Repayments Section */
        .repayments-section {
            margin-top: 30px;
            padding: 20px;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        }

        .repayments-section h3 {
            color: #34495e;
            margin-bottom: 15px;
            font-size: 1.8em;
        }

        .repayments-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .repayments-list th,
        .repayments-list td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .repayments-list th {
            background-color: #2980b9;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9em;
        }

        .repayments-list tbody tr:hover {
            background-color: #f0f0f0;
        }

        /* Add Repayment Form */
        .add-repayment-form {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #ecf0f1;
        }

        .add-repayment-form h4 {
            color: #34495e;
            margin-bottom: 10px;
            font-size: 1.4em;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #34495e;
            font-weight: bold;
            font-size: 0.95em;
        }

        .form-group input[type="text"],
        .form-group input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            font-size: 16px;
            color: #34495e;
            box-sizing: border-box;
        }

        .add-repayment-form button[type="submit"] {
            background-color: #27ae60;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .add-repayment-form button[type="submit"]:hover {
            background-color: #219653;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 98%;
                padding: 25px;
            }

            .sales-list {
                overflow-x: auto;
            }

            .sales-list th,
            .sales-list td {
                white-space: nowrap;
                font-size: 0.9em;
            }

            .repayments-list {
                overflow-x: auto;
            }

            .repayments-list th,
            .repayments-list td {
                white-space: nowrap;
                font-size: 0.85em;
            }

            .add-repayment-form input[type="text"],
            .add-repayment-form input[type="date"] {
                font-size: 14px;
            }

            .add-repayment-form button[type="submit"] {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Manage Credit Sales & Repayments</h1>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <table class="sales-list">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Quantity</th>
                    <th>Item ID</th>
                    <th>Total Amount</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($credit_sales)): ?>
                    <tr>
                        <td colspan="6">No credit sales found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($credit_sales as $sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                            <td><?php echo htmlspecialchars($sale['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($sale['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($sale['total_amount']); ?></td>
                            <td><?php echo htmlspecialchars($sale['due_date']); ?></td>
                            <td>
                                <div class="repayments-section">
                                    <h3>Repayments for Sale ID: <?php echo htmlspecialchars($sale['sale_id']); ?></h3>
                                    <table class="repayments-list">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $repayments = getRepaymentsForSale($pdo, $sale['sale_id']);
                                                if (empty($repayments)):
                                            ?>
                                                <tr><td colspan="3">No repayments found for this sale.</td></tr>
                                            <?php else: ?>
                                                <?php foreach ($repayments as $repayment): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($repayment['repayment_date']); ?></td>
                                                        <td><?php echo htmlspecialchars($repayment['amount']); ?></td>
                                                        <td><?php echo htmlspecialchars($repayment['status']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>

                                    <div class="add-repayment-form">
                                        <h4>Add New Repayment</h4>
                                        <form method="POST" action="">
                                            <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($sale['sale_id']); ?>">
                                            <div class="form-group">
                                                <label for="amount">Amount:</label>
                                                <input type="text" id="amount" name="amount" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="repayment_date">Date:</label>
                                                <input type="date" id="repayment_date" name="repayment_date" required>
                                            </div>
                                            <button type="submit" name="add_repayment">Add Repayment</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>