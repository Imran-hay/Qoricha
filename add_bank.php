<?php
session_start();

// Include database configuration
require 'config.php';
require 'sidebar.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $bank_name = $_POST['bank_name'];
    $bank_id = $_POST['bank_id'];
    $holder_name = $_POST['holder_name'];
    $account_number = $_POST['account_number'];

    // Prepare the insert statement
    $stmt = $pdo->prepare("
        INSERT INTO banks (bank_name, bank_id, holder_name, account_number)
        VALUES (?, ?, ?, ?)
    ");

    // Execute the statement with parameters
    if ($stmt->execute([$bank_name, $bank_id, $holder_name, $account_number])) {
        echo "<script>alert('Bank added successfully!'); window.location.href='add_bank.php';</script>";
    } else {
        echo "<script>alert('Error adding bank. Please try again.'); window.location.href='add_bank.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Bank</title>
    <link rel="stylesheet" href="add_bank.css">
</head>
<body>
    <header>
        <!-- Header content here (optional) -->
    </header>
    <main>
        <div class="form-container">
            <h2>Add New Bank Account</h2>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="bank_name">Bank Name:</label>
                    <input type="text" id="bank_name" name="bank_name" required>
                </div>
                <div class="form-group">
                    <label for="bank_id">Bank ID:</label>
                    <input type="text" id="bank_id" name="bank_id" required>
                </div>
                <div class="form-group">
                    <label for="holder_name">Holder Name:</label>
                    <input type="text" id="holder_name" name="holder_name" required>
                </div>
                <div class="form-group">
                    <label for="account_number">Account Number:</label>
                    <input type="text" id="account_number" name="account_number" required>
                </div>
                <input type="submit" name="add_bank" value="Add Bank">
            </form>
        </div>
    </main>
    <script src="js/script.js"></script>
</body>
</html>