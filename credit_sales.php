<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // exit();
}

require 'sidebar.php';
require 'config.php';

// Initialize message variables
$success_message = "";
$error_message = "";
$credit_message = ""; // Message for credit sale form

// Handle form submission for credit sales
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['credit_sale'])) {
    $customer_name = $_POST['customer_name'];
    $quantity = (int)$_POST['quantity'];  // Cast to integer
    $item_id = $_POST['item_id'];
    $selling_price = $_POST['selling_price'];
    $total_amount = $quantity * $selling_price;
    $payment_type = 'Credit'; // Force payment type to Credit
    $due_date = $_POST['due_date'];

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
            INSERT INTO sales (customer_name, quantity, item_id, total_amount, payment_type, due_date, user_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$customer_name, $quantity, $item_id, $total_amount, $payment_type, $due_date, $_SESSION['user_id'], 'pending']); // Added 'pending' status

        // 3. Update the stock
        $new_stock = $item['stock'] - $quantity;
        $stmt = $pdo->prepare("UPDATE items SET stock = ? WHERE item_id = ?");
        $stmt->execute([$new_stock, $item_id]);

        // Commit the transaction
        $pdo->commit();

        $credit_message = "Credit sale created successfully and stock updated!";
    } catch (PDOException $e) {
        // Rollback the transaction if there's an error
        $pdo->rollBack();
        $credit_message = "Error creating credit sale: " . $e->getMessage();
    } catch (Exception $e) {
        // Rollback the transaction if there's an error
        $pdo->rollBack();
        $credit_message = "Error creating credit sale: " . $e->getMessage();
    }
}

// Pagination settings for the credit sales list
$results_per_page = 10;

// Get current page number
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int) $_GET['page'];
} else {
    $current_page = 1;
}

// Calculate offset
$offset = ($current_page - 1) * $results_per_page;

// Fetch total number of credit sales
try {
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE payment_type = 'Credit'");
    $total_stmt->execute();
    $total_results = $total_stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Database error fetching credit sale count: " . $e->getMessage());
    $total_results = 0;
    $message = "Error fetching credit sale count: " . $e->getMessage();
}

// Fetch credit sales for current page
try {
    $stmt = $pdo->prepare("SELECT s.sale_id, s.customer_name, s.quantity, i.item_name, s.total_amount, s.due_date, s.payment_type, s.status
                           FROM sales s
                           JOIN items i ON s.item_id = i.item_id
                           WHERE s.payment_type = 'Credit'
                           ORDER BY s.sale_id
                           LIMIT :offset, :results_per_page");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':results_per_page', $results_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $credit_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching credit sales: " . $e->getMessage());
    $credit_sales = [];
    $message = "Error fetching credit sales: " . $e->getMessage();
}

// Fetch products for the dropdown (we still need the item_id and item_name)
$products = $pdo->query("SELECT item_id, item_name, stock FROM items")->fetchAll(PDO::FETCH_ASSOC);

// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Credit Sales</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        /* General body and content styles (consistent with dashboard) */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            margin: 0;
            padding: 0px;
            color: ##4a5568;
        }

        .content {
            margin-left: 120px; /* Adjust for sidebar width */
            padding: 30px;
            transition: var(--transition);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .page-title h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .page-title p {
            color: #718096;
            margin: 5px 0 0;
            font-size: 14px;
        }



        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
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

        /* Message Styles */
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

        /* Credit Sale Form Styles (Initially Hidden) */
        .credit-sale-form {
            display: none;
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .credit-sale-form h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            text-align: left;
        }

        .credit-sale-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .credit-sale-form input[type="text"],
        .credit-sale-form input[type="number"],
        .credit-sale-form input[type="date"],
        .credit-sale-form select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .credit-sale-form button {
            background-color: #28a745; /* Green */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .credit-sale-form button:hover {
            background-color: #218838;
        }

        /* Add Credit Sale Button */
        .add-credit-sale-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 15px;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .add-credit-sale-button:hover {
            background-color: #0056b3;
        }

        /* Export Button Style */
        .export-button {
            background-color: #6c757d; /* Gray */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            float: right;
            margin-top: 10px;
        }

        .export-button:hover {
            background-color: #5a6268;
        }

        /* Pagination Styles */
        .pagination {
            margin-top: 20px;
            text-align: center;
            clear: both;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            text-decoration: none;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            color: #333;
            border-radius: 4px;
            margin: 0 4px;
        }

        .pagination a:hover {
            background-color: #eee;
        }

        .pagination .current {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination .page-info {
            display: inline-block;
            margin: 0 10px;
            font-size: 16px;
            color: #555;
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
    <h1>Manage Credit Sale</h1>
    <p>View and Record a new credit transaction by selecting the customer and adding items to the sale.</p>
</div>
</div>

        <?php if (isset($error_message) && $error_message != ""): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (isset($credit_message) &&  $credit_message != ""): ?>
            <div class="message success"><?php echo htmlspecialchars($credit_message); ?></div>
        <?php endif; ?>

        <button class="add-credit-sale-button" onclick="toggleCreditSaleForm()">Add New Credit Sale</button>

        <div id="creditSaleForm" class="credit-sale-form">
            <h2>Add Credit Sale</h2>
            <form method="POST" action="">
                <input type="hidden" name="credit_sale" value="1">
                <label for="customer_name">Customer Name:</label>
                <input type="text" id="customer_name" name="customer_name" required>

                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" min="1" value="1" required>

                <label for="item_id">Product:</label>
                <select id="item_id" name="item_id" required>
                    <option value="" disabled selected>Select a product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['item_id']; ?>">
                            <?php echo htmlspecialchars($product['item_name'] . " (Stock: " . $product['stock'] . ")"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="selling_price">Selling Price:</label>
                <input type="number" id="selling_price" name="selling_price" step="0.01" min="0" required>

                <label for="due_date">Date:</label>
                <input type="date" id="due_date" name="due_date" required>

                <button type="submit">Create Credit Sale</button>
            </form>
        </div>

        <h2>Credit Sale List</h2>
        <table>
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Customer Name</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($credit_sales) > 0): ?>
                    <?php foreach ($credit_sales as $sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                            <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($sale['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($sale['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($sale['total_amount']); ?></td>
                            <td><?php echo htmlspecialchars($sale['due_date']); ?></td>
                            <td><?php echo htmlspecialchars($sale['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No credit sales found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <button class="export-button" onclick="exportToPDF()">Export to PDF</button>

        <!-- Pagination Links -->
        <div class="pagination">
            <span class="page-info">Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
            <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>">&lt;&lt;</a>
            <?php endif; ?>

            <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>">&gt;&gt;</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleCreditSaleForm() {
            var form = document.getElementById('creditSaleForm');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Set document title
            doc.text("Credit Sale List", 20, 10);

            // AutoTable settings
            const tableData = <?php echo json_encode($credit_sales); ?>; // Pass PHP array to JavaScript
            const tableHeaders = ["Sale ID", "Customer Name", "Product", "Quantity", "Amount", "Date", "Status"];

            // Prepare data for autoTable
            const data = tableData.map(sale => [
                sale.sale_id,
                sale.customer_name,
                sale.item_name,
                sale.quantity,
                sale.total_amount,
                sale.due_date,
                sale.status
            ]);

            // Y position for the table
            let startY = 20;

            // AutoTable
            doc.autoTable({
                head: [tableHeaders],
                body: data,
                startY: startY,
            });

            // Save the PDF
            doc.save("credit_sale_list.pdf");
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
</body>

</html>