<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $repayment_id = $_POST['repayment_id'];

    // Fetch repayment details
    $stmt = $pdo->prepare("SELECT * FROM repayments WHERE id = ?");
    $stmt->execute([$repayment_id]);
    $repayment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Approve the repayment
    $stmt = $pdo->prepare("UPDATE repayments SET status = 'approved' WHERE id = ?");
    $stmt->execute([$repayment_id]);

    // Update remaining credit
    $stmt = $pdo->prepare("UPDATE credits SET remaining_credit = remaining_credit - ? WHERE agent_id = ?");
    $stmt->execute([$repayment['amount'], $repayment['agent_id']]);

    header("Location: cashier_dashboard.php");
    exit();
}