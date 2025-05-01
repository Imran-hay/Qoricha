<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit();
}
require 'agent_sidebar.php';
require 'config.php';

// Fetch products for the dropdown
$products = $pdo->query("SELECT item_id, item_name, price FROM items")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
$message = ""; // Initialize message variable
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form handling logic here...
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
            background-color: #f9f9f9;
            color: #333;
        }
        .content {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 20px auto;
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
            color: #0a888f; /* Updated heading color */
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
            background-color: #0a888f; /* Button color */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        button:hover {
            background-color: #0a7b7f; /* Darker shade on hover */
        }
        .message {
            margin-bottom: 20px;
            text-align: center;
            color: #d9534f; /* Red color for error messages */
        }
        #bank_statement_container {
            display: none;
        }
    </style>
    <script>
        function clearForm() {
            document.getElementById("customer_name").value = "";
            document.getElementById("quantity").value = 1;
            document.getElementById("product_id").selectedIndex = 0;
            document.getElementById("total_amount").value = "";
            document.getElementById("due_date").value = "";
            document.getElementById("transaction_number").value = "";
            document.getElementById("bank_statement").value = "";
        }

        function updateTotal() {
            const quantity = document.getElementById("quantity").value;
            const price = document.getElementById("product_id").options[document.getElementById("product_id").selectedIndex].getAttribute("data-price");
            document.getElementById("total_amount").value = (quantity * price).toFixed(2);
        }
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
                <label for="product_id">Product:</label>
                <select id="product_id" name="product_id" required onchange="updateTotal()">
                    <option value="" disabled selected>Select a product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['item_id']; ?>" data-price="<?php echo $product['price']; ?>">
                            <?php echo htmlspecialchars($product['item_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="total_amount">Total Amount:</label>
                <input type="text" id="total_amount" name="total_amount" readonly>
            </div>
            <div>
                <label for="payment_type">Payment Type:</label>
                <select id="payment_type" name="payment_type" required>
                    <option value="Credit" selected>Credit</option> <!-- Credit set as default -->
                    <option value="Cash">Cash</option>
                </select>
            </div>
            <div>
                <label for="due_date">Due Date:</label>
                <input type="date" id="due_date" name="due_date" required>
            </div>
            <div id="bank_statement_container">
                <label for="transaction_number">Transaction Number:</label>
                <input type="text" id="transaction_number" name="transaction_number" required>
                <label for="bank_statement">Upload Bank Statement Slip:</label>
                <input type="file" id="bank_statement" name="bank_statement" accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <button type="submit">Submit Sale</button>
            <?php if (isset($show_undo) && $show_undo): ?>
                <button type="button" onclick="clearForm()">Undo</button>
            <?php endif; ?>
        </form>
    </div>

    <script>
        document.getElementById("payment_type").addEventListener("change", function() {
            var bankStatementContainer = document.getElementById("bank_statement_container");
            if (this.value === "Cash") {
                bankStatementContainer.style.display = "block";
            } else {
                bankStatementContainer.style.display = "none";
            }
        });
    </script>
</body>
</html>