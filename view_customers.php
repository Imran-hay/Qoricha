<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    //header("Location: login.php");
    //exit();
}
require 'agent_sidebar.php';
require 'config.php';

// Pagination settings
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
    <title>View Customers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        /* General body and content styles (consistent with dashboard) */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            color: #343a40;
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            margin-left: 240px; /* Sidebar width */
            transition: margin-left 0.3s ease;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 900px; /* Increased max-width for table */
            margin: 20px auto;
            position: relative; /* Add position relative */
        }

        .content.shifted {
            margin-left: 0;
        }

        h1 {
            margin-bottom: 20px;
            text-align: center;
            color: #764ba2; /* Consistent color */
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 16px;
        }

        th {
            background-color: #f9f9f9;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        /* Message Styles */
        .message {
            margin-top: 20px;
            text-align: center;
            color: #d9534f;
        }

        /* Export Button Style */
        .export-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* Sidebar gradient */
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            /* position: absolute;  Position the button absolutely */
            /* bottom: 20px;  Distance from the bottom */
            /* right: 20px;  Distance from the right */
            float: right; /* Float the button to the right */
            margin-top: 10px; /* Add some top margin for spacing */
        }

        .export-button:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        /* Pagination Styles */
        .pagination {
            margin-top: 20px;
            text-align: center;
            clear: both; /* Clear the float */
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
            background-color: #764ba2;
            color: white;
            border-color: #764ba2;
        }

        .pagination .page-info {
            display: inline-block;
            margin: 0 10px;
            font-size: 16px;
            color: #555;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .content {
                margin-left: 0;
            }
            th, td {
                font-size: 14px;
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Customer List</h1>
        <?php if (isset($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>



        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>TIN Number</th> <!-- Add TIN column header -->
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
                            <td><?php echo htmlspecialchars($customer['tin']); ?></td> <!-- Display TIN number -->
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