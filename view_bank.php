<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit();
}
require 'agent_sidebar.php'; // Include your sidebar for navigation
require 'config.php'; // Include your database connection settings

// Fetch bank accounts
$stmt = $pdo->prepare("SELECT * FROM bank_accounts");
$stmt->execute();
$bank_accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to generate PDF
function generatePDF($bank_accounts) {
    require('fpdf/fpdf.php'); // Make sure to download and include FPDF library
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Company Bank Accounts', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, 'Bank Name', 1);
    $pdf->Cell(60, 10, 'Account Number', 1);
    $pdf->Cell(40, 10, 'Account Type', 1);
    $pdf->Ln();

    foreach ($bank_accounts as $account) {
        $pdf->Cell(40, 10, $account['bank_name'], 1);
        $pdf->Cell(60, 10, $account['account_number'], 1);
        $pdf->Cell(40, 10, $account['account_type'], 1);
        $pdf->Ln();
    }

    $pdf->Output('D', 'bank_accounts.pdf'); // Force download
}

if (isset($_POST['export_pdf'])) {
    generatePDF($bank_accounts);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bank Accounts</title>
    <link rel="stylesheet" href="style.css"> <!-- Your CSS file -->
    <style>
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 20px auto;
        }
        h1 {
            color: #0a888f; /* Updated heading color */
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #0a888f;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        button {
            background-color: #0a888f; /* Button color */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
            display: block;
            margin: 0 auto; /* Center the button */
        }
        button:hover {
            background-color: #0a7b7f; /* Darker shade on hover */
        }
    </style>
</head>
<body>
    <div class="content">
        <h1>Company Bank Accounts</h1>
        <table>
            <thead>
                <tr>
                    <th>Bank Name</th>
                    <th>Account Number</th>
                    <th>Account Type</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($bank_accounts) > 0): ?>
                    <?php foreach ($bank_accounts as $account): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($account['bank_name']); ?></td>
                            <td><?php echo htmlspecialchars($account['account_number']); ?></td>
                            <td><?php echo htmlspecialchars($account['account_type']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No bank accounts found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <form method="POST">
            <button type="submit" name="export_pdf">Export to PDF</button>
        </form>
    </div>
</body>
</html>