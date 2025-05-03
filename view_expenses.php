<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require 'config.php';

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
        // Only rollback if we started the transaction
        if (!$inTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Balance update error: " . $e->getMessage());
        throw $e;
    }
}

// Handle form submission for adding a new expense
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_expense'])) {
        $category_id = $_POST['category_id'];
        $amount = $_POST['amount'];
        $expense_date = $_POST['expense_date'];
        $description = $_POST['description'];
        $success = false;

        try {
            $pdo->beginTransaction(); // Start a transaction

            $stmt = $pdo->prepare("INSERT INTO expenses (category_id, amount, expense_date, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$category_id, $amount, $expense_date, $description]);

            // Update the balance (subtract the expense amount)
            $amount = floatval($amount); // Convert amount to float
            if (!updateBalance($pdo, $amount)) {
                throw new Exception("Failed to update balance after adding expense.");
            }

            $pdo->commit(); // Commit the transaction
            $message = "Expense added successfully!";
            $success = true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack(); // Rollback the transaction on error
            }
            $message = "Error adding expense: " . $e->getMessage();
        }
    }

    // Handle delete expense
    if (isset($_POST['delete_expense'])) {
        $expense_id = $_POST['expense_id'];
        $success = false;

        try {
            $pdo->beginTransaction(); // Start a transaction

            // Get the expense amount before deleting
            $stmt = $pdo->prepare("SELECT amount FROM expenses WHERE expense_id = ?");
            $stmt->execute([$expense_id]);
            $expense = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$expense) {
                throw new Exception("Expense not found.");
            }

            $amount = $expense['amount'];

            // Delete the expense
            $stmt = $pdo->prepare("DELETE FROM expenses WHERE expense_id = ?");
            $stmt->execute([$expense_id]);

            // Update the balance (add the expense amount back)
            if (!updateBalance($pdo, $amount, true)) { // true for addition
                throw new Exception("Failed to update balance after deleting expense.");
            }

            $pdo->commit(); // Commit the transaction
            $message = "Expense deleted successfully!";
            $success = true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack(); // Rollback the transaction on error
            }
            $message = "Error deleting expense: " . $e->getMessage();
        }
    }
}

// Fetch all expenses with category names
try {
    $stmt = $pdo->prepare("
        SELECT expenses.*, expense_categories.category_name
        FROM expenses
        JOIN expense_categories ON expenses.category_id = expense_categories.category_id
        ORDER BY expense_date DESC
    ");
    $stmt->execute();
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching expenses: " . $e->getMessage();
    $expenses = [];
}

// Fetch all expense categories for the add expense form
try {
    $stmt = $pdo->prepare("SELECT category_id, category_name FROM expense_categories ORDER BY category_name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching categories: " . $e->getMessage();
    $categories = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Expenses</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            background-color: #fff;
            padding: 30px;
            margin: 30px auto;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5em;
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

        /* Add Expense Form (Initially Hidden) */
        .add-expense-form {
            display: none; /* Initially hidden */
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 8px;
            background-color: #ecf0f1;
        }

        .add-expense-form h2 {
            color: #34495e;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #34495e;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            font-size: 16px;
            color: #34495e;
            box-sizing: border-box;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .add-expense-form button[type="submit"] {
            background-color: #3498db;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .add-expense-form button[type="submit"]:hover {
            background-color: #2980b9;
        }

        /* Expense List Table */
        .expense-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .expense-list th,
        .expense-list td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .expense-list th {
            background-color: #3498db;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
        }

        .expense-list tbody tr:hover {
            background-color: #f0f0f0;
        }

        .expense-list .actions {
            text-align: center;
        }

        .expense-list .actions a {
            display: inline-block;
            margin: 0 5px;
            color: #fff;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .expense-list .actions .delete {
            background-color: #e74c3c;
        }

        .expense-list .actions a:hover {
            opacity: 0.8;
        }

        /* Add Expense Button */
        .add-expense-button {
            background-color: #27ae60;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
            display: inline-block;
            text-decoration: none;
        }

        .add-expense-button:hover {
            background-color: #219653;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 20px;
            }

            .expense-list {
                overflow-x: auto;
            }

            .expense-list th,
            .expense-list td {
                white-space: nowrap;
            }
        }
    </style>
    <script>
        function toggleAddExpenseForm() {
            var form = document.querySelector('.add-expense-form');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }

        function confirmDelete(expenseId) {
            if (confirm("Are you sure you want to delete this expense?")) {
                // Create a form dynamically and submit it
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'manage_expenses.php'; // Submit to the same page

                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_expense';
                input.value = 'true';
                form.appendChild(input);

                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'expense_id';
                idInput.value = expenseId;
                form.appendChild(idInput);

                document.body.appendChild(form); // Add to the document
                form.submit(); // Submit the form
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Manage Expenses</h1>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <a href="#" class="add-expense-button" onclick="toggleAddExpenseForm()">Add New Expense</a>

        <div class="add-expense-form">
            <h2>Add New Expense</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="category_id">Category:</label>
                    <select id="category_id" name="category_id" required>
                        <?php if (empty($categories)): ?>
                            <option value="">No categories found</option>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="text" id="amount" name="amount" required>
                </div>
                <div class="form-group">
                    <label for="expense_date">Date:</label>
                    <input type="date" id="expense_date" name="expense_date" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <button type="submit" name="add_expense">Add Expense</button>
            </form>
        </div>

        <h2>Existing Expenses</h2>
        <table class="expense-list">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="5">No expenses found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($expenses as $expense): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($expense['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($expense['amount']); ?></td>
                            <td><?php echo htmlspecialchars($expense['expense_date']); ?></td>
                            <td><?php echo htmlspecialchars($expense['description']); ?></td>
                            <td class="actions">
                                <a href="#" class="delete" onclick="confirmDelete('<?php echo $expense['expense_id']; ?>')"><i class="fas fa-trash-alt"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>