<?php
session_start();
require 'config.php'; // Include your database connection settings

if (isset($_GET['item_id'])) {
    $item_id = intval($_GET['item_id']);
    $stmt = $pdo->prepare("SELECT stock FROM items WHERE item_id = :item_id");
    $stmt->execute([':item_id' => $item_id]);
    $stock = $stmt->fetchColumn();

    echo json_encode(['stock' => $stock]);
} else {
    echo json_encode(['stock' => 0]);
}
?>