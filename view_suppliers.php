<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect or handle unauthorized access
    // exit();  // Uncomment this line in a real application
}

require 'config.php'; // Database connection
require 'sidebar.php'; // Assuming you have a sidebar

// Initialize message variables
$success_message = "";
$error_message = "";

// Handle form submission for adding a new supplier
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_supplier'])) {
    // Get form data
    $supplier_name = $_POST['supplier_name'];
    $contact_name = $_POST['contact_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $company_name = $_POST['company_name'];
    $tin = $_POST['tin'];
    $notes = $_POST['notes'];

    // Insert into database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO suppliers (supplier_name, contact_name, email, phone, address, company_name, tin, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$supplier_name, $contact_name, $email, $phone, $address, $company_name, $tin, $notes])) {
            $success_message = "Supplier added successfully!";
        } else {
            $error_message = "Error adding supplier.";
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Pagination settings for the supplier list
$results_per_page = 10;

// Get current page number
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int) $_GET['page'];
} else {
    $current_page = 1;
}

// Calculate offset
$offset = ($current_page - 1) * $results_per_page;

// Fetch total number of suppliers
try {
    $total_stmt = $pdo->prepare("SELECT COUNT(*) FROM suppliers");
    $total_stmt->execute();
    $total_results = $total_stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Database error fetching supplier count: " . $e->getMessage());
    $total_results = 0;
    $message = "Error fetching supplier count: " . $e->getMessage();
}

// Fetch suppliers for current page
try {
    $stmt = $pdo->prepare("SELECT * FROM suppliers ORDER BY supplier_name LIMIT :offset, :results_per_page");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':results_per_page', $results_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching suppliers: " . $e->getMessage());
    $suppliers = []; // Assign an empty array to avoid errors later
    $message = "Error fetching suppliers: " . $e->getMessage(); // Optional: Display an error message
}

// Calculate total number of pages
$total_pages = ceil($total_results / $results_per_page);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Suppliers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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

        /* Add Supplier Form Styles (Initially Hidden) */
        .add-supplier-form {
            display: none;
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .add-supplier-form h2 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
            text-align: left;
        }

        .add-supplier-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .add-supplier-form input[type="text"],
        .add-supplier-form input[type="email"],
        .add-supplier-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .add-supplier-form button {
            background-color: #28a745; /* Green */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .add-supplier-form button:hover {
            background-color: #218838;
        }

        /* Add Supplier Button */
        .add-supplier-button {
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

        .add-supplier-button:hover {
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

            th,
            td {
                font-size: 14px;
                padding: 8px 10px;
            }
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h1>Manage Suppliers</h1>
                <p>Add, edit, or manage supplier information.</p>
            </div>
        </div>

        <?php if (isset($error_message) && $error_message != ""): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (isset($success_message) &&  $success_message != ""): ?>
            <div class="message success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <button class="add-supplier-button" onclick="toggleAddSupplierForm()">Add New Supplier</button>

        <div id="addSupplierForm" class="add-supplier-form">
            <h2>Add New Supplier</h2>
            <form action="" method="POST">
                <label for="supplier_name">Supplier Name</label>
                <input type="text" id="supplier_name" name="supplier_name" required>

                <label for="contact_name">Contact Name</label>
                <input type="text" id="contact_name" name="contact_name">

                <label for="email">Email</label>
                <input type="email" id="email" name="email">

                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone">

                <label for="address">Address</label>
                <textarea id="address" name="address" rows="3"></textarea>

                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name">

                <label for="tin">TIN Number</label>
                <input type="text" id="tin" name="tin">

                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3"></textarea>

                <button type="submit" name="add_supplier">Add Supplier</button>
            </form>
        </div>

        <h2>Supplier List</h2>
        <table>
            <thead>
                <tr>
                    <th>Supplier Name</th>
                    <th>Contact Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Company</th>
                    <th>TIN</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($suppliers) > 0): ?>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($supplier['supplier_name']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['contact_name']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['company_name']); ?></td>
                            <td><?php echo htmlspecialchars($supplier['tin']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No suppliers found.</td>
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
        function toggleAddSupplierForm() {
            var form = document.getElementById('addSupplierForm');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }

        function exportToPDF() {
            // Implement PDF export logic here (similar to the customer list)
            alert("PDF export functionality will be implemented here."); // Placeholder
        }
    </script>
</body>

</html>