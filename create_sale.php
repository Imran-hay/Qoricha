<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'agent') {
    // header("Location: login.php"); // Uncomment this line in production
    // For debugging, comment out the redirect and display an error message
    // echo "You are not authorized to view this page.";
    // exit();
}
require 'agent_sidebar.php';
require 'config.php';

// Fetch products for the dropdown (we still need the item_id and item_name)
$products = $pdo->query("SELECT item_id, item_name, stock FROM items")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_name = $_POST['customer_name'];
    $quantity = (int)$_POST['quantity'];  // Cast to integer
    $item_id = $_POST['item_id'];
    $selling_price = $_POST['selling_price'];
    $total_amount = $quantity * $selling_price;
    $payment_type = $_POST['payment_type'];
    $due_date = $_POST['due_date'];
    $transaction_number = $_POST['transaction_number'] ?? '';

    // File upload handling
    $bank_statement = null;
    if ($payment_type == "Cash" && isset($_FILES['bank_statement']) && $_FILES['bank_statement']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["bank_statement"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $allowed_types = ["pdf", "jpg", "jpeg", "png"];
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["bank_statement"]["tmp_name"], $target_file)) {
                $bank_statement = $target_file;
            } else {
                $message = "Sorry, there was an error uploading your file.";
            }
        } else {
            $message = "Sorry, only PDF, JPG, JPEG, and PNG files are allowed.";
        }
    }

    if (empty($message)) {
        // Start a transaction to ensure atomicity
        try {
            $pdo->beginTransaction();

            // 1. Check if there's enough stock
            $stmt = $pdo->prepare("SELECT stock FROM items WHERE item_id = ?");
            $stmt->execute([$item_id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item || $item['stock'] < $quantity) {
                throw new Exception("Insufficient stock for this item.");
            }

            // 2. Insert the sale
            $stmt = $pdo->prepare("
                INSERT INTO sales (customer_name, quantity, item_id, total_amount, payment_type, due_date, transaction_number, bank_statement, user_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$customer_name, $quantity, $item_id, $total_amount, $payment_type, $due_date, $transaction_number, $bank_statement, $_SESSION['user_id'], 'pending']); // Added 'pending' status


            // 3. Update the stock
            $new_stock = $item['stock'] - $quantity;
            $stmt = $pdo->prepare("UPDATE items SET stock = ? WHERE item_id = ?");
            $stmt->execute([$new_stock, $item_id]);

            // Commit the transaction
            $pdo->commit();

            $message = "Sale created successfully and stock updated!";
        } catch (PDOException $e) {
            // Rollback the transaction if there's an error
            $pdo->rollBack();
            $message = "Error creating sale: " . $e->getMessage();
        } catch (Exception $e) {
            // Rollback the transaction if there's an error
            $pdo->rollBack();
            $message = "Error creating sale: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Sale</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
<<<<<<< HEAD
            background-color: #f4f6f9;
            color: #343a40;
=======
            background-color: #f9f9f9;
            color: #333;
            margin-left: 280px; /* Adjust for sidebar width */
>>>>>>> 339a5cbb56c6b6509fa1dd5f729f84c034a2e84a
        }
        .content {
            padding: 30px;
            padding-right: 50px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 20px auto;
            margin-left: 80px; /* Adjust for sidebar width */
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
            color: #764ba2;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        input[type="text"][readonly] {
            background-color: #f0f0f0;
        }
        button {
            padding: 10px;
            background-color: #764ba2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        button:hover {
            background-color: #0a7b7f;
        }
        .message {
            margin-bottom: 20px;
            text-align: center;
            color: #d9534f;
        }
        #bank_statement_container {
            display: none;
        }
    </style>
    <script>
        function clearForm() {
            document.getElementById("customer_name").value = "";
            document.getElementById("quantity").value = 1;
            document.getElementById("item_id").selectedIndex = 0;
            document.getElementById("selling_price").value = "";
            document.getElementById("total_amount").value = "";
            document.getElementById("due_date").value = "";
            document.getElementById("transaction_number").value = "";
            document.getElementById("bank_statement").value = "";
        }

        function updateTotal() {
            const quantity = parseFloat(document.getElementById("quantity").value) || 0;
            const selling_price = parseFloat(document.getElementById("selling_price").value) || 0;
            const total = quantity * selling_price;
            document.getElementById("total_amount").value = total.toFixed(2);
        }

        document.addEventListener("DOMContentLoaded", function() {
            var paymentTypeSelect = document.getElementById("payment_type");
            var bankStatementContainer = document.getElementById("bank_statement_container");

            function toggleBankStatementFields() {
                if (paymentTypeSelect.value === "Cash") {
                    bankStatementContainer.style.display = "block";
                } else {
                    bankStatementContainer.style.display = "none";
                }
            }

            toggleBankStatementFields();
            paymentTypeSelect.addEventListener("change", toggleBankStatementFields);
        });
    </script>
</head>
<body>
    <div class="content">
        <h1>Create Sale</h1>
        <?php if (isset($message)) : ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" action="">
            <div>
                <label for="customer_name">Customer Name:</label>
                <input type="text" id="customer_name" name="customer_name" required>
            </div>
            <div>
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" min="1" value="1" required onchange="updateTotal()">
            </div>
            <div>
                <label for="item_id">Product:</label>
                <select id="item_id" name="item_id" required>
                    <option value="" disabled selected>Select a product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['item_id']; ?>">
                            <?php echo htmlspecialchars($product['item_name'] . " (Stock: " . $product['stock'] . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="selling_price">Selling Price:</label>
                <input type="number" id="selling_price" name="selling_price" step="0.01" min="0" required onchange="updateTotal()">
            </div>
            <div>
                <label for="total_amount">Total Amount:</label>
                <input type="text" id="total_amount" name="total_amount" readonly>
            </div>
            <div>
                <label for="payment_type">Payment Type:</label>
                <select id="payment_type" name="payment_type" required>
                    <option value="Credit" selected>Credit</option>
                    <option value="Cash">Cash</option>
                </select>
            </div>
            <div>
                <label for="due_date">Due Date:</label>
                <input type="date" id="due_date" name="due_date" required>
            </div>
            <div id="bank_statement_container">
                <label for="transaction_number">Transaction Number:</label>
                <input type="text" id="transaction_number" name="transaction_number">
                <label for="bank_statement">Upload Bank Statement Slip:</label>
                <input type="file" id="bank_statement" name="bank_statement" accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <button type="submit">Submit Sale</button>
            <?php if (isset($show_undo) && $show_undo): ?>
                <button type="button" onclick="clearForm()">Undo</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>