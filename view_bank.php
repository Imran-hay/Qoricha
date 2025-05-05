<?php
session_start();

// Include database configuration
require 'config.php';
require 'sidebar.php';

// Fetch banks from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM banks ORDER BY bank_name ASC"); // Order by bank name
    $stmt->execute();
    $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching banks: " . $e->getMessage();
    $banks = []; // Ensure $banks is an empty array to avoid errors later
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Banks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="view_banks.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
    <style>
        /* Style for the export button */
        .export-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }

        .export-button:hover {
            background-color: #3e8e41;
        }
    </style>
</head>
<body>
    <header>
        <!-- Header content here (optional) -->
    </header>
    <main>
        <div class="container">
            <h2>View Bank Accounts</h2>

            <?php if (count($banks) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Bank Name</th>
                            <th>Bank ID</th>
                            <th>Holder Name</th>
                            <th>Account Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banks as $bank): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($bank['bank_name']); ?></td>
                                <td><?php echo htmlspecialchars($bank['bank_id']); ?></td>
                                <td><?php echo htmlspecialchars($bank['holder_name']); ?></td>
                                <td><?php echo htmlspecialchars($bank['account_number']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-banks">No banks found in the database.</p>
            <?php endif; ?>

            <!-- Export to PDF Button -->
            <button class="export-button" onclick="exportToPDF()">Export to PDF</button>
        </div>
    </main>
    <script src="js/script.js"></script>
    <script>
        function exportToPDF() {
            // Check if jsPDF is loaded
            if (typeof jsPDF === 'undefined') {
                alert('jsPDF library is not loaded. Please check your script includes.');
                return;
            }

            // New jsPDF instance
            const doc = new jsPDF();

            // Document title
            doc.text("Bank Accounts List", 20, 10);

            // Table data
            const tableData = <?php echo json_encode($banks); ?>;

            // Table headers
            const tableHeaders = ["Bank Name", "Bank ID", "Holder Name", "Account Number"];

            // Data for autoTable
            const data = tableData.map(bank => [
                bank.bank_name,
                bank.bank_id,
                bank.holder_name,
                bank.account_number
            ]);

            // Start Y position
            let startY = 20;

            // AutoTable options
            const options = {
                head: [tableHeaders],
                body: data,
                startY: startY,
                didDrawPage: function(data) {
                    // Header
                    doc.setFontSize(20);
                    doc.setTextColor(40);
                    doc.text("Bank Accounts List", data.settings.margin.left, 10);

                    // Footer
                    let str = "Page " + doc.internal.getNumberOfPages();
                    doc.setFontSize(10);

                    // jsPDF 1.4+ uses getHeight, <1.4 uses .height
                    let pageSize = doc.internal.pageSize;
                    let pageHeight = pageSize.height ? pageSize.height : pageSize.getHeight();
                    doc.text(str, data.settings.margin.left, pageHeight - 10);
                },
                margin: {
                    top: 15
                }
            };

            // AutoTable
            doc.autoTable(options);

            // Save the PDF
            doc.save("bank_accounts_list.pdf");
        }
    </script>
</body>
</html>