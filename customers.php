<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    // exit();
}

require 'sidebar.php';
require 'config.php';

// Initialize message variables
$success_message = "";
$error_message = "";

// Handle form submission for adding a new customer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_customer'])) {
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $tin = $_POST['tin'];

    // Insert into database
    try {
        $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone, address, tin) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $phone, $address, $tin])) {
            $success_message = "Customer added successfully!";
        } else {
            $error_message = "Error adding customer.";
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Pagination settings for the customer list
$results_per_page = 10;

// Get current page number
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int) $_GET['page'];
} else {
    $current_page = 1;
}

// Calculate offset
$offset = ($current_page - 1) * $results_per_page;

// Fetch total number of customers
try {
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM customers");
    $total_stmt->execute();
    $total_results = $total_stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Database error fetching customer count: " . $e->getMessage());
    $total_results = 0;
    $message = "Error fetching customer count: " . $e->getMessage();
}

// Fetch customers for current page
try {
    $stmt = $pdo->prepare("SELECT * FROM customers ORDER BY name LIMIT :offset, :results_per_page");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':results_per_page', $results_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching customers: " . $e->getMessage());
    $customers = []; // Assign an empty array to avoid errors later
    $message = "Error fetching customers: " . $e->getMessage(); // Optional: Display an error message
}

// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers</title>
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

        /* Add Customer Form Styles (Initially Hidden) */
        .add-customer-form {
            display: none;
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .add-customer-form h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            text-align: left;
        }

        .add-customer-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .add-customer-form input[type="text"],
        .add-customer-form input[type="email"],
        .add-customer-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .add-customer-form button {
            background-color: #28a745; /* Green */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .add-customer-form button:hover {
            background-color: #218838;
        }

        /* Add Customer Button */
        .add-customer-button {
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

        .add-customer-button:hover {
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
    <h1>Manage Customers</h1>
    <p>Add, edit, or manage customer information.</p>
</div>
</div>

        <?php if (isset($error_message) && $error_message != ""): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (isset($success_message) &&  $success_message != ""): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <button class="add-customer-button" onclick="toggleAddCustomerForm()">Add New Customer</button>

        <div id="addCustomerForm" class="add-customer-form">
            <h2>Add New Customer</h2>
            <form action="" method="POST">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" required>

                <label for="address">Address</label>
                <textarea id="address" name="address" rows="4" required></textarea>

                <label for="tin">TIN Number</label>
                <input type="text" id="tin" name="tin" required>

                <button type="submit" name="add_customer">Add Customer</button>
            </form>
        </div>

        <h2>Customer List</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>TIN Number</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($customers) > 0): ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                            <td><?php echo htmlspecialchars($customer['address']); ?></td>
                            <td><?php echo htmlspecialchars($customer['tin']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No customers found.</td>
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
        function toggleAddCustomerForm() {
            var form = document.getElementById('addCustomerForm');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Set document title
            doc.text("Customer List", 20, 10);

            // AutoTable settings
            const tableData = <?php echo json_encode($customers); ?>; // Pass PHP array to JavaScript
            const tableHeaders = ["Name", "Email", "Phone", "Address", "TIN Number"];

            // Prepare data for autoTable
            const data = tableData.map(customer => [
                customer.name,
                customer.email,
                customer.phone,
                customer.address,
                customer.tin
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
            doc.save("customer_list.pdf");
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
</body>
</html>