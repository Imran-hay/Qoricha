<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $agent_id = $_SESSION['user_id'];
    $amount = $_POST['amount'];
    $receipt_number = $_POST['receipt_number'];
    $bank_transaction_number = $_POST['bank_transaction_number'];

    $stmt = $pdo->prepare("INSERT INTO repayments (agent_id, amount, receipt_number, bank_transaction_number) VALUES (?, ?, ?, ?)");
    $stmt->execute([$agent_id, $amount, $receipt_number, $bank_transaction_number]);

    header("Location: agents.php"); // Redirect back to agent dashboard
    exit();
}
?>