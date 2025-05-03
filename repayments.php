<?php
session_start();
require 'config.php';
require 'cashier_sidebar.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id']; // Use user ID from session
    $customer_name = $_POST['customer_name'];
    $invoice_number = $_POST['invoice_number'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $transaction_number = $_POST['bank_transaction_number'] ?? null;
    $reference_number = $_POST['reference_number'] ?? null;
    $employee_id = $_POST['employee_id']; // Employee ID

    // Prepare SQL to record the repayment
    $stmt = $pdo->prepare("INSERT INTO repayments (user_id, customer_name, amount, invoice_id, bank_transaction_number, reference_number, payment_method, employee_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $customer_name, $amount, $invoice_number, $transaction_number, $reference_number, $payment_method, $employee_id]);

    // Notify admin (implement your notification method here)

    // Update the customer's outstanding amount (implement your logic here)

    header("Location: agents.php"); // Redirect back to agent dashboard
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Repayment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }
        button {
            padding: 10px 15px;
        }
        #bank_fields, #cheque_fields {
            display: none;
        }
    </style>
    <script>
        function toggleFields() {
            const paymentMethod = document.getElementById("payment_method").value;
            document.getElementById("bank_fields").style.display = paymentMethod === "bank_transfer" ? "block" : "none";
            document.getElementById("cheque_fields").style.display = paymentMethod === "cheque" ? "block" : "none";
        }
    </script>
</head>
<body>
    <h1>Credit Repayment</h1>
    <form id="repayment-form" method="POST" action="" enctype="multipart/form-data">
        <label for="customer_name">Customer Name:</label>
        <input type="text" id="customer_name" name="customer_name" required>

        <label for="receipt_number">Invoice Number:</label>
        <input type="text" id="receipt_number" name="invoice_number" required>

        <label for="amount">Amount to Repay:</label>
        <input type="number" id="amount" name="amount" required step="0.01">

        <label for="payment_method">Payment Method:</label>
        <select id="payment_method" name="payment_method" required onchange="toggleFields()">
            <option value="cash">Cash</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="cheque">Cheque</option>
        </select>

        <div id="bank_fields">
            <label for="bank_transaction_number">Bank Transaction Number:</label>
            <input type="text" id="bank_transaction_number" name="bank_transaction_number">
            <label for="bank_slip">Upload Bank Slip:</label>
            <input type="file" id="bank_slip" name="bank_slip" accept="image/*">
        </div>

        <div id="cheque_fields">
            <label for="reference_number">Cheque Reference Number:</label>
            <input type="text" id="reference_number" name="reference_number">
            <label for="cheque_slip">Upload Cheque Slip:</label>
            <input type="file" id="cheque_slip" name="cheque_slip" accept="image/*">
        </div>

        <label for="employee_id">Employee ID:</label>
        <input type="text" id="employee_id" name="employee_id" required>

        <button type="submit">Submit Repayment</button>
    </form>
</body>
</html>