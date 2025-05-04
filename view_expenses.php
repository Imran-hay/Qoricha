<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require 'config.php';
require 'sidebar.php';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* General body and content styles (consistent with dashboard) */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            color: #343a40;
        }

        .content {
            margin-left: 280px;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        h1 {
            margin-bottom: 25px;
            color: #4361ee;
            text-align: center;
            font-weight: 600;
        }

        h2 {
            color: #4361ee;
            margin-top: 30px;
            font-weight: 500;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border: 1px solid #e9ecef;
        }

        th {
            background: #4361ee;
            color: white;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e6f0ff;
        }

        /* Message Styles */
        .message {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

        /* Expense Form Styles */
        .add-expense-form {
            display: none;
            margin-bottom: 30px;
            padding: 25px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .add-expense-form h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #4361ee;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .add-expense-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }

        .add-expense-form input[type="text"],
        .add-expense-form input[type="date"],
        .add-expense-form input[type="number"],
        .add-expense-form select,
        .add-expense-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .add-expense-form input[type="text"]:focus,
        .add-expense-form input[type="date"]:focus,
        .add-expense-form input[type="number"]:focus,
        .add-expense-form select:focus,
        .add-expense-form textarea:focus {
            border-color: #4361ee;
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .add-expense-form button {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .add-expense-form button:hover {
            background-color: #218838;
        }

        /* Add Expense Button */
        .add-expense-button {
            background-color: #4361ee;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 20px;
            display: inline-block;
            transition: background-color 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .add-expense-button:hover {
            background-color: #3a56d4;
        }

        /* Delete Button Style */
        .delete {
            color: #dc3545;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .delete:hover {
            color: #c82333;
            text-decoration: underline;
        }

        /* Pagination Styles */
        .pagination {
            margin-top: 30px;
            text-align: center;
        }

        .pagination a {
            display: inline-block;
            padding: 10px 15px;
            text-decoration: none;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #4361ee;
            border-radius: 6px;
            margin: 0 5px;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background-color: #e6f0ff;
            border-color: #4361ee;
        }

        .pagination .current {
            background-color: #4361ee;
            color: white;
            border-color: #4361ee;
        }

        .pagination .page-info {
            display: inline-block;
            margin: 0 15px;
            font-size: 16px;
            color: #495057;
        }

        /* Responsive Styles */
        @media (max-width: 1200px) {
            .content {
                margin-left: 0;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 10px;
            }
            
            .add-expense-button {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="content">
        <h1>Manage Expenses</h1>

        <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <button class="add-expense-button" onclick="toggleAddExpenseForm()">
            <i class="fas fa-plus-circle"></i> Add New Expense
        </button>

        <div id="addExpenseForm" class="add-expense-form">
            <h2>Add New Expense</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="category_id">Category:</label>
                    <select id="category_id" name="category_id" required>
                        <?php if (empty($categories)): ?>
                        <option value="">No categories found</option>
                        <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>">
                            <?php echo htmlspecialchars($category['category_name']); ?></option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="expense_date">Date:</label>
                    <input type="date" id="expense_date" name="expense_date" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <button type="submit" name="add_expense">
                    <i class="fas fa-save"></i> Save Expense
                </button>
            </form>
        </div>

        <h2>Expense Records</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Actions</th>
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
                    <td>₱<?php echo number_format($expense['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($expense['expense_date']); ?></td>
                    <td><?php echo htmlspecialchars($expense['description']); ?></td>
                    <td>
                        <a href="#" class="delete" onclick="confirmDelete('<?php echo $expense['expense_id']; ?>')">
                            <i class="fas fa-trash-alt"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleAddExpenseForm() {
            var form = document.getElementById('addExpenseForm');
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
</body>
</html>